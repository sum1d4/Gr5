<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>漢字クイズ結果</title>
<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #fff8e1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        height: 100vh;
        font-family: "Hiragino Kaku Gothic ProN", "Meiryo", sans-serif;
    }

    /* 黒板部分 */
    .board {
        margin-top: 80px;
        width: 200px;
        height: 150px;
        background-color: #2e7d32;
        border-radius: 8px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* 白い漢字カード */
    .kanji-card {
        width: 80px;
        height: 80px;
        background-color: white;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 50px;
        font-weight: bold;
        color: black;
        box-shadow: 0 0 2px rgba(0,0,0,0.3);
    }

    /* よみがな（右側の縦書き） */
    .reading {
        position: absolute;
        right: 8px;
        top: 20px;
        color: yellow;
        font-size: 20px;
        writing-mode: vertical-rl;
        text-orientation: upright;
    }

    /* チョーク風の飾り */
    .chalks {
        position: absolute;
        bottom: 6px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 4px;
    }

    .chalk {
        width: 20px;
        height: 4px;
        border-radius: 2px;
    }

    .pink { background-color: #f8bbd0; }
    .yellow { background-color: #fff176; }
    .blue { background-color: #81d4fa; }
    .white { background-color: #ffffff; }

    /* 結果テキスト */
    .result {
        margin-top: 30px;
        font-size: 24px;
        color: red;
        font-weight: bold;
    }

    /* 次の問題ボタン */
    .next-button {
        margin-top: 20px;
        background-color: #64b5f6;
        color: white;
        font-size: 16px;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        box-shadow: 0 3px 0 #1976d2;
    }

    .next-button:active {
        transform: translateY(2px);
        box-shadow: 0 1px 0 #1976d2;
    }
</style>
</head>
<body>

    <!-- 黒板 -->
    <div class="board">
        <div class="kanji-card">週</div>
        <div class="reading">しゅう</div>
        <div class="chalks">
            <div class="chalk pink"></div>
            <div class="chalk yellow"></div>
            <div class="chalk blue"></div>
            <div class="chalk white"></div>
        </div>
    </div>

    <!-- 結果表示 -->
    <div class="result">せいかい！</div>

    <!-- 次の問題ボタン -->
    <form action="next_question.php" method="post">
        <button type="submit" class="next-button">つぎのもんだい ▶</button>
    </form>

</body>
</html>

