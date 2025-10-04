<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceEntry;
use App\Models\RacePayout;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class RaceImportController extends Controller
{
    public function create(): View
    {
        return view('import');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimetypes:text/plain,text/csv,text/tsv', 'max:10240'],
        ], [
            'csv_file.required' => 'CSVファイルを選択してください。',
            'csv_file.mimetypes' => 'CSV形式のファイルを指定してください。',
            'csv_file.max' => 'ファイルサイズは10MB以下にしてください。',
        ]);

        $file = $validated['csv_file'];
        $path = $file->getRealPath();

        if (! $path || ! file_exists($path)) {
            return back()->withErrors('ファイルを読み込めませんでした。');
        }

        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->withErrors('CSVファイルを開けませんでした。');
        }

        $header = null;
        $line = 0;
        $imported = [
            'races' => 0,
            'entries' => 0,
            'payouts' => 0,
        ];
        $payoutTracker = [];

        try {
            DB::transaction(function () use ($handle, &$header, &$line, &$imported, &$payoutTracker) {
                while (($row = fgetcsv($handle)) !== false) {
                    $line++;
                    if ($line === 1) {
                        $header = $this->prepareHeader($row);
                        continue;
                    }

                    if (! $header || count($header) === 0) {
                        continue;
                    }

                    if ($this->isEmptyRow($row)) {
                        continue;
                    }

                    $values = array_slice($row, 0, count($header));
                    $values = array_pad($values, count($header), null);
                    $values = array_map(function ($value) {
                        if ($value === null) {
                            return null;
                        }

                        return is_string($value) ? trim($value) : $value;
                    }, $values);
                    $record = array_combine($header, $values);

                    if ($record === false) {
                        continue;
                    }

                    if (! isset($record['race_date'], $record['racecourse'], $record['horse_number'])) {
                        continue;
                    }

                    if ($this->normalizeString($record['racecourse']) === null) {
                        continue;
                    }

                    if ($this->toNullableInt($record['horse_number']) === null) {
                        continue;
                    }

                    $race = $this->persistRace($record);
                    if ($race->wasRecentlyCreated) {
                        $imported['races']++;
                    }

                    $entry = $this->persistEntry($race, $record);
                    if ($entry->wasRecentlyCreated) {
                        $imported['entries']++;
                    }

                    $imported['payouts'] += $this->persistPayouts($race, $record, $payoutTracker);
                }
            });
        } catch (Throwable $exception) {
            fclose($handle);

            return back()->withErrors('インポート中にエラーが発生しました: '.$exception->getMessage());
        }

        fclose($handle);

        return back()->with('status', sprintf('レース:%d件 / 出走馬:%d頭 / 払戻:%d件を登録しました。', $imported['races'], $imported['entries'], $imported['payouts']));
    }

    /**
     * @param  array<int, string|null>  $row
     * @return array<int, string>
     */
    private function prepareHeader(array $row): array
    {
        $headers = array_map(function ($value) {
            $value = $value ?? '';
            $value = trim($value);
            $value = Str::of($value)->ltrim("\xEF\xBB\xBF")->toString();

            return $value;
        }, $row);

        if (empty(array_filter($headers))) {
            return [];
        }

        return $headers;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn ($value) => $value !== null && trim($value) !== ''));
    }

    /**
     * @param  array<string, string|null>  $record
     */
    private function persistRace(array $record): Race
    {
        $attributes = [
            'race_date' => Arr::get($record, 'race_date'),
            'racecourse' => $this->normalizeString(Arr::get($record, 'racecourse')),
            'race_name' => $this->normalizeString(Arr::get($record, 'race_name')),
        ];

        $courseType = $this->normalizeString(Arr::get($record, 'course_type'));
        if ($courseType !== null && ! in_array($courseType, ['芝', 'ダート'], true)) {
            $courseType = null;
        }

        $direction = $this->normalizeString(Arr::get($record, 'direction'));
        if ($direction !== null && ! in_array($direction, ['右', '左'], true)) {
            $direction = null;
        }

        $race = Race::firstOrNew($attributes);
        $race->fill([
            'course_type' => $courseType,
            'weather' => $this->normalizeString(Arr::get($record, 'weather')),
            'track_condition' => $this->normalizeString(Arr::get($record, 'track_condition')),
            'distance' => (int) Arr::get($record, 'distance', 0),
            'direction' => $direction,
            'number_of_turns' => $this->toNullableInt(Arr::get($record, 'number_of_turns')),
            'number_of_runners' => (int) Arr::get($record, 'number_of_runners', 0),
        ]);
        $race->save();

        return $race;
    }

    /**
     * @param  array<string, string|null>  $record
     */
    private function persistEntry(Race $race, array $record): RaceEntry
    {
        $entry = RaceEntry::firstOrNew([
            'race_id' => $race->id,
            'horse_number' => (int) Arr::get($record, 'horse_number'),
        ]);

        $entry->fill([
            'frame_number' => (int) Arr::get($record, 'frame_number', 0),
            'horse_name' => $this->normalizeString(Arr::get($record, 'horse_name')),
            'sex' => $this->normalizeString(Arr::get($record, 'sex')),
            'running_style' => $this->normalizeString(Arr::get($record, 'running_style')),
            'popularity' => $this->toNullableInt(Arr::get($record, 'popularity')),
            'finish_position' => $this->toNullableInt(Arr::get($record, 'finish_position')),
            'win_odds' => $this->toNullableFloat(Arr::get($record, 'win_odds')),
            'win_payout' => $this->toNullableInt(Arr::get($record, 'win_payout')),
            'place_payout' => $this->toNullableInt(Arr::get($record, 'place_payout')),
        ]);

        $entry->save();

        return $entry;
    }

    /**
     * @param  array<string, string|null>  $record
     * @param  array<string, array<string, bool>>  $tracker
     */
    private function persistPayouts(Race $race, array $record, array &$tracker): int
    {
        $count = 0;
        $raceKey = (string) $race->id;

        $payoutDefinitions = [
            'win' => [
                'combination' => Arr::get($record, 'horse_number'),
                'payout' => Arr::get($record, 'win_payout'),
                'odds' => Arr::get($record, 'win_odds'),
            ],
            'exacta' => [
                'combination' => Arr::get($record, 'exacta_combination'),
                'payout' => Arr::get($record, 'exacta_payout'),
            ],
            'quinella' => [
                'combination' => Arr::get($record, 'quinella_combination'),
                'payout' => Arr::get($record, 'quinella_payout'),
            ],
            'trifecta' => [
                'combination' => Arr::get($record, 'trifecta_combination'),
                'payout' => Arr::get($record, 'trifecta_payout'),
            ],
        ];

        foreach ($payoutDefinitions as $betType => $values) {
            $combination = $this->normalizeString($values['combination'] ?? null);
            $payout = $this->toNullableInt($values['payout'] ?? null);
            $odds = $this->toNullableFloat($values['odds'] ?? null);

            if ($combination === null || $payout === null) {
                continue;
            }

            $key = $raceKey.'-'.$betType.'-'.$combination;

            if (($tracker[$key] ?? false) === true) {
                continue;
            }

            $payoutModel = RacePayout::updateOrCreate([
                'race_id' => $race->id,
                'bet_type' => $betType,
                'combination' => $combination,
            ], [
                'payout' => $payout,
                'odds' => $odds,
            ]);

            $tracker[$key] = true;
            if ($payoutModel->wasRecentlyCreated) {
                $count++;
            }
        }

        return $count;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
