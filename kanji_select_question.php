<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>漢字クイズ</title>
    <style>
        body {
            background-color: #fff;
            font-family: 'Hiragino Kaku Gothic ProN', 'Meiryo', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 0;
            height: 100vh;
        }

        /* 上部の戻るボタン */
        .header {
            width: 100%;
            display: flex;
            align-items: center;
            padding: 10px;
        }

        .back-button {
            font-size: 24px;
            color: #007aff;
            cursor: pointer;
            text-decoration: none;
        }

        /* 黒板部分 */
        .board {
            position: relative;
            background-color: #2e7d32;
            width: 220px;
            height: 160px;
            border-radius: 10px;
            margin-top: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .kanji {
            font-size: 60px;
            color: white;
            font-weight: bold;
        }

        /* 右側の黄色い四角 */
        .yellow-boxes {
            position: absolute;
            right: 10px;
            top: 30px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .yellow-box {
            width: 24px;
            height: 24px;
            border: 2px solid yellow;
        }

        /* 入力欄 */
        .input-container {
            margin-top: 40px;
        }

        input[type="text"] {
            width: 220px;
            padding: 10px;
            font-size: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            text-align: center;
        }

        /* ボタン */
        .check-button {
            margin-top: 25px;
            padding: 10px 40px;
            font-size: 18px;
            border-radius: 8px;
            border: 1.5px solid #333;
            background-color: white;
            cursor: pointer;
        }

        .check-button:hover {
            background-color: #f3f3f3;
        }

        .result {
            margin-top: 20px;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="#" class="back-button">←</a>
    </div>

    <div class="board">
        <div class="kanji">週</div>
        <div class="yellow-boxes">
            <div class="yellow-box"></div>
            <div class="yellow-box"></div>
            <div class="yellow-box"></div>
        </div>
    </div>

    <form method="post" class="input-container">
        <input type="text" name="answer" placeholder="入力してください" required>
        <br>
        <button type="submit" class="check-button">答え合わせ</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $answer = trim($_POST["answer"]);

        // 正解判定（例：「末」が正解だと仮定）
        $correct = "末";

        if ($answer === $correct) {
            echo "<div class='result' style='color: green;'>⭕ 正解！</div>";
        } else {
            echo "<div class='result' style='color: red;'>❌ 不正解（正解は「{$correct}」）</div>";
        }
    }
    ?>
</body>
</html>

