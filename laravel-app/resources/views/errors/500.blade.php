<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>サーバーエラー - 学校図書管理システム</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8f9fa;
            color: #4f4f4f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 28rem;
        }
        .code {
            font-size: 6rem;
            font-weight: bold;
            color: rgba(122, 176, 212, 0.3);
            line-height: 1;
            margin-bottom: 1.5rem;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.75rem;
        }
        p {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #7ab0d4;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: background 0.2s;
        }
        a:hover { background: #5e9cc4; }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">500</div>
        <h1>サーバーエラーが発生しました</h1>
        <p>一時的な問題が発生しています。しばらく時間をおいてから再度お試しください。</p>
        <a href="/">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            トップページに戻る
        </a>
    </div>
</body>
</html>
