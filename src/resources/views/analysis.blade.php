<x-layouts.app title="コース別分析">
    <div class="card">
        <h2>コース条件フィルタ</h2>
        <form action="{{ route('analysis.index') }}" method="get" style="display:grid; gap:1.25rem;">
            <div class="grid grid-cols-3">
                <div>
                    <label for="racecourse">競馬場</label>
                    <select id="racecourse" name="racecourse">
                        <option value="">すべて</option>
                        @foreach ($filters['racecourses'] as $option)
                            <option value="{{ $option }}" @selected(request('racecourse') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="course_type">コース種別</label>
                    <select id="course_type" name="course_type">
                        <option value="">すべて</option>
                        <option value="芝" @selected(request('course_type') === '芝')>芝</option>
                        <option value="ダート" @selected(request('course_type') === 'ダート')>ダート</option>
                    </select>
                </div>
                <div>
                    <label for="distance">距離</label>
                    <select id="distance" name="distance">
                        <option value="">すべて</option>
                        @foreach ($filters['distances'] as $option)
                            <option value="{{ $option }}" @selected(request('distance') === (string) $option)>{{ $option }}m</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3">
                <div>
                    <label for="weather">天候</label>
                    <select id="weather" name="weather">
                        <option value="">すべて</option>
                        @foreach ($filters['weathers'] as $option)
                            <option value="{{ $option }}" @selected(request('weather') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="track_condition">馬場状態</label>
                    <select id="track_condition" name="track_condition">
                        <option value="">すべて</option>
                        @foreach ($filters['track_conditions'] as $option)
                            <option value="{{ $option }}" @selected(request('track_condition') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="direction">回り</label>
                    <select id="direction" name="direction">
                        <option value="">すべて</option>
                        <option value="右" @selected(request('direction') === '右')>右</option>
                        <option value="左" @selected(request('direction') === '左')>左</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="date_from">開催日（開始）</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div>
                <label for="date_to">開催日（終了）</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div>
                <button type="submit">分析する</button>
            </div>
        </form>
    </div>

    @if ($summary['total_races'] > 0)
        <div class="card">
            <h2>該当レース概要</h2>
            <div class="grid grid-cols-3">
                <div class="stat-card">
                    <h3>対象レース数</h3>
                    <p><strong>{{ number_format($summary['total_races']) }}</strong> レース</p>
                </div>
                <div class="stat-card">
                    <h3>集計対象頭数</h3>
                    <p><strong>{{ number_format($summary['total_entries']) }}</strong> 頭</p>
                </div>
                <div class="stat-card">
                    <h3>平均単勝人気</h3>
                    <p><strong>{{ number_format($summary['average_popularity'], 1) }}</strong> 番人気</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>優勝馬の傾向</h2>
            <div class="grid grid-cols-3">
                <div>
                    <h3 style="margin-bottom:0.75rem;">枠番別勝利数</h3>
                    <table>
                        <thead>
                            <tr><th>枠番</th><th>勝利数</th><th>ROI</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($winnerStats['frame_number'] as $frame => $data)
                                <tr>
                                    <td>{{ $frame }}</td>
                                    <td>{{ $data['count'] }}</td>
                                    <td>{{ number_format($data['roi'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 style="margin-bottom:0.75rem;">馬番別勝利数</h3>
                    <table>
                        <thead>
                            <tr><th>馬番</th><th>勝利数</th><th>ROI</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($winnerStats['horse_number'] as $number => $data)
                                <tr>
                                    <td>{{ $number }}</td>
                                    <td>{{ $data['count'] }}</td>
                                    <td>{{ number_format($data['roi'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 style="margin-bottom:0.75rem;">人気別勝利数</h3>
                    <table>
                        <thead>
                            <tr><th>人気</th><th>勝利数</th><th>ROI</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($winnerStats['popularity'] as $popularity => $data)
                                <tr>
                                    <td>{{ $popularity }}</td>
                                    <td>{{ $data['count'] }}</td>
                                    <td>{{ number_format($data['roi'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="grid grid-cols-2" style="margin-top:1.5rem;">
                <div>
                    <h3 style="margin-bottom:0.75rem;">性別別勝利数</h3>
                    <table>
                        <thead>
                            <tr><th>性別</th><th>勝利数</th><th>平均人気</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($winnerStats['sex'] as $sex => $data)
                                <tr>
                                    <td>{{ $sex ?: '不明' }}</td>
                                    <td>{{ $data['count'] }}</td>
                                    <td>{{ number_format($data['average_popularity'], 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 style="margin-bottom:0.75rem;">脚質別勝利数</h3>
                    <table>
                        <thead>
                            <tr><th>脚質</th><th>勝利数</th><th>平均人気</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($winnerStats['running_style'] as $style => $data)
                                <tr>
                                    <td>{{ $style ?: '不明' }}</td>
                                    <td>{{ $data['count'] }}</td>
                                    <td>{{ number_format($data['average_popularity'], 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>3着以内の傾向</h2>
            <table>
                <thead>
                    <tr>
                        <th>指標</th>
                        <th>最多出現</th>
                        <th>出現回数</th>
                        <th>割合</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>枠番</td>
                        <td>{{ $topThreeStats['frame_number']['value'] }}</td>
                        <td>{{ $topThreeStats['frame_number']['count'] }}</td>
                        <td>{{ number_format($topThreeStats['frame_number']['ratio'] * 100, 1) }}%</td>
                    </tr>
                    <tr>
                        <td>馬番</td>
                        <td>{{ $topThreeStats['horse_number']['value'] }}</td>
                        <td>{{ $topThreeStats['horse_number']['count'] }}</td>
                        <td>{{ number_format($topThreeStats['horse_number']['ratio'] * 100, 1) }}%</td>
                    </tr>
                    <tr>
                        <td>人気</td>
                        <td>{{ $topThreeStats['popularity']['value'] }}</td>
                        <td>{{ $topThreeStats['popularity']['count'] }}</td>
                        <td>{{ number_format($topThreeStats['popularity']['ratio'] * 100, 1) }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>購入シミュレーション（予算: ¥10,000）</h2>
            <p style="color:#4b5563; font-size:0.95rem;">対象レースにおいて勝ち組み合わせへ毎回100円ずつ投票した場合の収支シミュレーションです。</p>
            <table>
                <thead>
                    <tr>
                        <th>券種</th>
                        <th>対象レース数</th>
                        <th>投資額</th>
                        <th>払戻合計</th>
                        <th>ROI</th>
                        <th>10,000円投資時の期待収支</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($investment['bet_types'] as $type => $data)
                        <tr>
                            <td>{{ $data['label'] }}</td>
                            <td>{{ $data['races'] }}</td>
                            <td>¥{{ number_format($data['investment']) }}</td>
                            <td>¥{{ number_format($data['payout']) }}</td>
                            <td>{{ number_format($data['roi'], 2) }}</td>
                            <td>¥{{ number_format($data['expected_return'] - $investment['budget']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($investment['recommendation'])
                <div class="stat-card" style="margin-top:1.5rem;">
                    <h3>推奨プラン</h3>
                    <p>{{ $investment['recommendation'] }}</p>
                </div>
            @endif
        </div>
    @else
        <div class="card">
            <h2>該当データがありません</h2>
            <p style="color:#4b5563; font-size:0.95rem;">条件を変更して再度検索してください。</p>
        </div>
    @endif
</x-layouts.app>
