<?php
// PHPコードをHTMLより前に配置し、必要な変数を定義します
$question_kanji = "週"; // 今回の問題の漢字
$correct_answer = "しゅう"; // 正解の読み
// 正解の文字数を取得（マルチバイト文字対応）
$correct_length = mb_strlen($correct_answer, 'UTF-8'); 
?>

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
        max-width: 390px;
        height: 844px;
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

    /* 横並びの四角を縦に配置するためのコンテナ */
    .yellow-boxes {
        position: absolute;
        right: 14px;
        display: flex;
        flex-direction: column; /* 四角を縦に並べる */
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

</style>
</head>
<body>

<div class="container">
    <div class="appbar">
        <a href="subject_select.php" class="back-icon">←</a>
    </div>

    <div class="board">
        <div class="kanji"><?php echo htmlspecialchars($question_kanji); ?></div>
        
        <div class="yellow-boxes">
            <?php
            // $correct_length の数だけループ
            for ($i = 0; $i < $correct_length; $i++) {
                echo '<div class="yellow-box"></div>';
            }
            ?>
        </div>
    </div>

    <form method="post" class="input-container" action="qs_1read_result.php">
        
        <input type="text" name="answer" placeholder="にゅうりょくしてね" required>
        <br>
        
        <input type="hidden" name="question_kanji" value="<?php echo htmlspecialchars($question_kanji); ?>">
        <input type="hidden" name="correct_answer" value="<?php echo htmlspecialchars($correct_answer); ?>">
        
        <button type="submit" class="check-button">こたえあわせ</button>
    </form>
</div>

</body>
</html>
