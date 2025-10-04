<x-layouts.app title="CSVインポート">
    <div class="card">
        <h2>レースデータCSVインポート</h2>
        <p style="color:#4b5563; font-size:0.95rem;">過去レースの実績データをCSVで取り込みます。1行に1頭分のレコードが含まれる形式を想定しています。</p>
        <form action="{{ route('import.store') }}" method="post" enctype="multipart/form-data" style="margin-top:1.5rem; display:grid; gap:1rem; max-width:480px;">
            @csrf
            <div>
                <label for="csv_file">CSVファイル</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv,text/csv">
            </div>
            <div>
                <button type="submit">インポートを実行</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>CSVフォーマット</h2>
        <p style="color:#4b5563; font-size:0.95rem;">以下のヘッダ名を持つUTF-8のCSVを想定しています。不要な列があっても構いませんが、最低限レース識別に必要な列（開催日、競馬場、距離、頭数、馬番など）はご用意ください。</p>
        <table>
            <thead>
                <tr>
                    <th>列名</th>
                    <th>説明</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>race_date</td><td>開催日（YYYY-MM-DD）</td></tr>
                <tr><td>race_name</td><td>レース名（任意）</td></tr>
                <tr><td>racecourse</td><td>競馬場</td></tr>
                <tr><td>course_type</td><td>芝 / ダート</td></tr>
                <tr><td>weather</td><td>天候</td></tr>
                <tr><td>track_condition</td><td>馬場状態</td></tr>
                <tr><td>distance</td><td>距離 (m)</td></tr>
                <tr><td>direction</td><td>右 / 左</td></tr>
                <tr><td>number_of_turns</td><td>コーナー数（任意）</td></tr>
                <tr><td>number_of_runners</td><td>出走頭数</td></tr>
                <tr><td>frame_number</td><td>枠番</td></tr>
                <tr><td>horse_number</td><td>馬番</td></tr>
                <tr><td>horse_name</td><td>馬名</td></tr>
                <tr><td>sex</td><td>性別</td></tr>
                <tr><td>running_style</td><td>脚質（逃げ・先行など）</td></tr>
                <tr><td>popularity</td><td>単勝人気</td></tr>
                <tr><td>finish_position</td><td>着順</td></tr>
                <tr><td>win_odds</td><td>単勝オッズ</td></tr>
                <tr><td>win_payout</td><td>単勝払戻（100円）</td></tr>
                <tr><td>place_payout</td><td>複勝払戻（100円）</td></tr>
                <tr><td>exacta_combination</td><td>馬単の着順組み合わせ</td></tr>
                <tr><td>exacta_payout</td><td>馬単払戻（100円）</td></tr>
                <tr><td>quinella_combination</td><td>馬連の組み合わせ</td></tr>
                <tr><td>quinella_payout</td><td>馬連払戻（100円）</td></tr>
                <tr><td>trifecta_combination</td><td>三連単の組み合わせ</td></tr>
                <tr><td>trifecta_payout</td><td>三連単払戻（100円）</td></tr>
            </tbody>
        </table>
    </div>
</x-layouts.app>
