<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ISBNスキャン</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>バーコードをスキャンしてください</h1>
    <div id="reader" style="width: 300px;"></div>
    <div id="result"></div>
    <div id="book-info"></div>

    <script>
        function onScanSuccess(decodedText) {
            if (!decodedText.startsWith('978') && !decodedText.startsWith('979')) {
                document.getElementById('result').innerText = "これはISBNではありません。";
                return;
            }

            document.getElementById('result').innerText = "ISBNを検出: " + decodedText;

            // LaravelにPOST送信
            fetch('/isbn-fetch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ isbn: decodedText })
            })
            .then(res => res.json())
            .then(data => {
                if (data.title) {
                    document.getElementById('book-info').innerHTML = `
                        <h2>${data.title}</h2>
                        <p>著者: ${data.authors}</p>
                        <img src="${data.thumbnail}" alt="表紙">
                    `;
                } else {
                    document.getElementById('book-info').innerText = "情報が見つかりませんでした。";
                }
            });
        }

        new Html5Qrcode("reader").start(
            { facingMode: "environment" },
            { fps: 10, qrbox: 250 },
            onScanSuccess
        );
    </script>
</body>
</html>
