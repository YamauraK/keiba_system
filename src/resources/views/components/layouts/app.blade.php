@props(['title' => '競馬データ分析システム'])

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light dark;
        }
        * {
            box-sizing: border-box;
            font-family: 'Noto Sans JP', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        body {
            margin: 0;
            background: #f6f7fb;
            color: #1b1f24;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: #1f2937;
            color: #fff;
            padding: 1rem 1.5rem;
        }
        header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        nav {
            margin-top: 0.75rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        nav a {
            color: #d1d5db;
            text-decoration: none;
            font-size: 0.95rem;
        }
        nav a:hover {
            color: #fff;
        }
        main {
            flex: 1;
            padding: 2rem 1.5rem 3rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            margin-bottom: 2rem;
        }
        .card h2 {
            margin-top: 0;
            font-size: 1.35rem;
            color: #111827;
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 0.35rem;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.65rem 0.75rem;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 0.95rem;
            transition: border-color 0.2s ease;
            background: #fff;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        button {
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            background: #2563eb;
            color: #fff;
            transition: background 0.2s ease;
        }
        button:hover {
            background: #1d4ed8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        table th, table td {
            border: 1px solid #e5e7eb;
            padding: 0.6rem 0.75rem;
            text-align: center;
            font-size: 0.9rem;
        }
        table th {
            background: #f3f4f6;
            font-weight: 700;
        }
        .alert {
            padding: 0.85rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .grid {
            display: grid;
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        .stat-card {
            background: #f9fafb;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            border: 1px solid #e5e7eb;
        }
        .stat-card h3 {
            margin: 0 0 0.5rem;
            font-size: 1.1rem;
            color: #1f2937;
        }
        .stat-card p {
            margin: 0.15rem 0;
            color: #4b5563;
            font-size: 0.95rem;
        }
        footer {
            text-align: center;
            padding: 1.5rem 0;
            color: #6b7280;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>競馬データ分析システム</h1>
        <nav>
            <a href="{{ route('analysis.index') }}">分析ダッシュボード</a>
            <a href="{{ route('import.create') }}">CSVインポート</a>
        </nav>
    </header>
    <main>
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin:0; padding-left:1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>
    <footer>
        &copy; {{ date('Y') }} Keiba System
    </footer>
</body>
</html>
