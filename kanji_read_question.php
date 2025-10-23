<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>漢字クイズ</title>
<style>
    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        background-color: #fff;
        font-family: "Hiragino Kaku Gothic ProN", "Meiryo", sans-serif;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* スマホ全体サイズ想定 */
    .container {
        width: 100%;
        max-width: 390px; /* iPhone14 幅基準 */
        height: 844px;    /* iPhone14 高さ基準 */
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* 上部AppBar部分 */
    .appbar {
        width: 100%;
        height: 48px;
        display: flex;
        align-items: center;
        padding-left: 12px;
        background-color: #fff;
        box-shadow: 0 0 0 rgba(0, 0, 0, 0);
    }

    .back-icon {
        font-size: 28px;
        color: #007aff;
        text-decoration: none;
    }

    /* 黒板部分 */
    .board {
        margin-top: 100px;
        width: 220px;
        height: 160px;
        background-color: #2e7d32;
        border-radius: 10px;
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .kanji {
        font-size: 60px;
        color: white;
        font-weight: bold;
    }

    .yellow-boxes {
        position: absolute;
        right: 14px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 12px;
    }

    .yellow-box {
        width: 24px;
        height: 24px;
        border: 2px solid yellow;
    }

    /* 入力欄 */
    .input-container {
        margin-top: 60px;
    }

    input[type="text"] {
        width: 220px;
        padding: 10px;
        font-size: 20px;
        text-align: center;
        border: 1px solid #ccc;
        border-radius: 10px;
        outline: none;
    }

    /* 答え合わせボタン */
    .check-button {
        margin-top: 25px;
        padding: 14px 50px;
        font-size: 20px;
        font-weight: 500;
        color: black;
        background-color: white;
        border: 1.5px solid #555;
        border-radius: 8px;
        cursor: pointer;
    }

    .check-button:hover {
        background-color: #f3f3f3;
    }

    /* 結果表示 */
    .result {
        margin-top: 20px;
        font-size: 20px;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="container">
    <!-- 上部戻るボタン -->
    <div class="appbar">
        <a href="#" class="back-icon">←</a>
    </div>

    <!-- 黒板 -->
    <div class="board">
        <div class="kanji">週</div>
        <div class="yellow-boxes">
            <div class="yellow-box"></div>
            <div class="yellow-box"></div>
            <div class="yellow-box"></div>
        </div>
    </div>

    <!-- 入力フォーム -->
    <form method="post" class="input-container">
        <input type="text" name="answer" placeholder="入力してください" required>
        <br>
        <button type="submit" class="check-button">答え合わせ</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $answer = trim($_POST["answer"]);
        $correct = "末"; // 正解例

        if ($answer === $correct) {
            echo "<div class='result' style='color: green;'>⭕ 正解！</div>";
        } else {
            echo "<div class='result' style='color: red;'>❌ 不正解（正解は「{$correct}」）</div>";
        }
    }
    ?>
</div>

</body>
</html>

