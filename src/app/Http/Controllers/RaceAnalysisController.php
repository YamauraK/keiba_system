<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\RacePayout;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RaceAnalysisController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->buildFilterOptions();
        $conditions = $this->extractConditions($request);

        $racesQuery = Race::query();

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                continue;
            }

            if ($column === 'date_from') {
                $racesQuery->whereDate('race_date', '>=', $value);
            } elseif ($column === 'date_to') {
                $racesQuery->whereDate('race_date', '<=', $value);
            } else {
                $racesQuery->where($column, $value);
            }
        }

        $raceIds = $racesQuery->pluck('id');

        $winners = RaceEntry::query()
            ->whereIn('race_id', $raceIds)
            ->where('finish_position', 1)
            ->get();

        $topThree = RaceEntry::query()
            ->whereIn('race_id', $raceIds)
            ->where('finish_position', '<=', 3)
            ->get();

        $summary = [
            'total_races' => $raceIds->count(),
            'total_entries' => $raceIds->isEmpty() ? 0 : RaceEntry::whereIn('race_id', $raceIds)->count(),
            'average_popularity' => $winners->avg('popularity') ?? 0.0,
        ];

        $winnerStats = [
            'frame_number' => $this->buildCountAndRoi($winners, 'frame_number'),
            'horse_number' => $this->buildCountAndRoi($winners, 'horse_number'),
            'popularity' => $this->buildCountAndRoi($winners, 'popularity'),
            'sex' => $this->buildCountAndAverage($winners, 'sex'),
            'running_style' => $this->buildCountAndAverage($winners, 'running_style'),
        ];

        $topThreeStats = [
            'frame_number' => $this->buildTopThreeSummary($topThree, 'frame_number'),
            'horse_number' => $this->buildTopThreeSummary($topThree, 'horse_number'),
            'popularity' => $this->buildTopThreeSummary($topThree, 'popularity'),
        ];

        $investment = $this->buildInvestmentAnalysis($raceIds);

        return view('analysis', [
            'filters' => $filters,
            'summary' => $summary,
            'winnerStats' => $winnerStats,
            'topThreeStats' => $topThreeStats,
            'investment' => $investment,
        ]);
    }

    /**
     * @return array<string, Collection<int, string|int>>
     */
    private function buildFilterOptions(): array
    {
        return [
            'racecourses' => Race::query()->select('racecourse')->whereNotNull('racecourse')->distinct()->orderBy('racecourse')->pluck('racecourse'),
            'distances' => Race::query()->select('distance')->where('distance', '>', 0)->distinct()->orderBy('distance')->pluck('distance'),
            'weathers' => Race::query()->select('weather')->whereNotNull('weather')->distinct()->orderBy('weather')->pluck('weather'),
            'track_conditions' => Race::query()->select('track_condition')->whereNotNull('track_condition')->distinct()->orderBy('track_condition')->pluck('track_condition'),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function extractConditions(Request $request): array
    {
        $conditions = [
            'racecourse' => $this->stringFilter($request, 'racecourse'),
            'course_type' => $this->stringFilter($request, 'course_type'),
            'distance' => $request->filled('distance') ? (int) $request->input('distance') : null,
            'weather' => $this->stringFilter($request, 'weather'),
            'track_condition' => $this->stringFilter($request, 'track_condition'),
            'direction' => $this->stringFilter($request, 'direction'),
            'date_from' => $this->parseDate($request->input('date_from')),
            'date_to' => $this->parseDate($request->input('date_to')),
        ];

        return array_filter($conditions, fn ($value) => $value !== null);
    }

    private function stringFilter(Request $request, string $key): ?string
    {
        $value = $request->string($key)->trim();

        return $value->isEmpty() ? null : $value->value();
    }

    private function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  Collection<int, RaceEntry>  $collection
     * @return array<string, array<string, float|int>>
     */
    private function buildCountAndRoi(Collection $collection, string $column): array
    {
        return $collection
            ->groupBy($column)
            ->filter(fn ($group, $key) => ! in_array($key, [null, '', 0], true))
            ->map(function (Collection $group) {
                $investment = max(1, $group->count()) * 100;
                $payout = (int) $group->sum('win_payout');
                $roi = $investment === 0 ? 0.0 : $payout / $investment;

                return [
                    'count' => $group->count(),
                    'roi' => $roi,
                ];
            })
            ->sortByDesc('count')
            ->toArray();
    }

    /**
     * @param  Collection<int, RaceEntry>  $collection
     * @return array<string, array<string, float|int>>
     */
    private function buildCountAndAverage(Collection $collection, string $column): array
    {
        return $collection
            ->groupBy(function ($item) use ($column) {
                $value = Arr::get($item, $column);

                return $value === null || $value === '' ? '不明' : $value;
            })
            ->map(function (Collection $group) {
                return [
                    'count' => $group->count(),
                    'average_popularity' => $group->avg('popularity') ?? 0.0,
                ];
            })
            ->sortByDesc('count')
            ->toArray();
    }

    /**
     * @param  Collection<int, RaceEntry>  $collection
     * @return array{value:string|int, count:int, ratio:float}
     */
    private function buildTopThreeSummary(Collection $collection, string $column): array
    {
        if ($collection->isEmpty()) {
            return [
                'value' => 'データなし',
                'count' => 0,
                'ratio' => 0.0,
            ];
        }

        $group = $collection
            ->filter(fn ($item) => ! in_array(Arr::get($item, $column), [null, '', 0], true))
            ->groupBy($column)
            ->map(fn (Collection $items) => $items->count())
            ->sortDesc();

        $value = $group->keys()->first();
        $count = $group->values()->first();
        $ratio = $collection->count() > 0 ? $count / $collection->count() : 0.0;

        return [
            'value' => $value,
            'count' => $count,
            'ratio' => $ratio,
        ];
    }

    /**
     * @param  Collection<int, int>  $raceIds
     * @return array{
     *     budget:int,
     *     recommendation:?string,
     *     bet_types:array<string, array{label:string, races:int, investment:int, payout:int, roi:float, expected_return:float}>
     * }
     */
    private function buildInvestmentAnalysis(Collection $raceIds): array
    {
        $budget = 10000;
        $betTypes = [
            'win' => '単勝',
            'exacta' => '馬単',
            'quinella' => '馬連',
            'trifecta' => '三連単',
        ];

        $result = [];
        $best = null;

        foreach ($betTypes as $type => $label) {
            $payouts = RacePayout::query()
                ->whereIn('race_id', $raceIds)
                ->where('bet_type', $type)
                ->get();

            $races = $payouts->count();
            $investment = $races * 100;
            $payout = (int) $payouts->sum('payout');
            $roi = $investment === 0 ? 0.0 : $payout / $investment;
            $expectedReturn = $roi * $budget;

            $result[$type] = [
                'label' => $label,
                'races' => $races,
                'investment' => $investment,
                'payout' => $payout,
                'roi' => $roi,
                'expected_return' => $expectedReturn,
            ];

            if ($races > 0 && ($best === null || $roi > $best['roi'])) {
                $best = [
                    'type' => $label,
                    'roi' => $roi,
                    'expected_return' => $expectedReturn,
                ];
            }
        }

        $recommendation = null;
        if ($best !== null) {
            $profit = $best['expected_return'] - $budget;
            $recommendation = sprintf(
                '%sを対象に1レース100円投資した場合の想定ROIは%.2fで、予算を全額投資すると約¥%sの%sになります。',
                $best['type'],
                $best['roi'],
                number_format(abs($profit)),
                $profit >= 0 ? '利益' : '損失'
            );
        }

        return [
            'budget' => $budget,
            'bet_types' => $result,
            'recommendation' => $recommendation,
        ];
    }
}
