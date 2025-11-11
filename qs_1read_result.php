<?php
// qs_1read_result.php
// 前のページからPOSTされたデータを処理し、結果を表示する

// POSTデータの受け取りとサニタイズ
$question_kanji = isset($_POST['question_kanji']) ? htmlspecialchars($_POST['question_kanji']) : '不明な漢字';
$user_answer = isset($_POST['answer']) ? htmlspecialchars($_POST['answer']) : '';
$correct_answer = isset($_POST['correct_answer']) ? htmlspecialchars($_POST['correct_answer']) : '不明な正解';

// 答え合わせロジック
// trim() で前後空白を除去した上で比較
$is_correct = (trim($user_answer) === $correct_answer);

// 結果表示用のメッセージとスタイルを決定 (漢字選択問題と統一)
$result_message = $is_correct ? 'せいかい！' : 'ざんねん…';
$result_emoji = $is_correct ? '🎉' : '🤔';
$result_class = $is_correct ? 'correct' : 'incorrect';
$correct_display = $is_correct ? 'よくできました！' : "せいかいは「{$correct_answer}」でした";

// 次の問題、または次の画面へのリンクを設定 (漢字選択問題と統一)
$next_button_link = 'qs_1read.php'; // 仮に次の問題も同じ画面 (qs_1read.php) に遷移する想定
$quit_button_link = 'subject_select.php'; // やめる (問題一覧) に戻る想定
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>けっか</title>
<style>
    /* CSSは漢字選択問題の結果画面と完全に同一 */
    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        background-color: #f5f5f5;
        font-family: "Hiragino Kaku Gothic ProN", "Meiryo", sans-serif;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .container {
        width: 100%;
        max-width: 390px;
        background-color: #fff;
        padding: 30px 20px;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    /* 結果メッセージ */
    .result-box {
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        font-size: 36px;
        font-weight: bold;
        color: white;
        transition: background-color 0.3s;
    }

    .result-box.correct {
        background-color: #4CAF50; /* 緑色 */
        box-shadow: 0 4px 10px rgba(76, 175, 80, 0.5);
    }

    .result-box.incorrect {
        background-color: #F44336; /* 赤色 */
        box-shadow: 0 4px 10px rgba(244, 67, 54, 0.5);
    }

    .result-emoji {
        font-size: 60px;
        display: block;
        margin-bottom: 10px;
    }

    /* 問題と答えの表示 */
    .info-container {
        margin-bottom: 30px;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        background-color: #fafafa;
    }

    .question-info {
        font-size: 24px;
        margin-bottom: 15px;
    }

    .answer-info {
        font-size: 20px;
        font-weight: 500;
        color: #333;
    }

    .correct-display {
        font-size: 22px;
        font-weight: bold;
        color: #1a73e8;
        margin-top: 15px;
    }

    /* ボタン群 */
    .button-group {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .action-button {
        padding: 15px 25px;
        font-size: 20px;
        font-weight: bold;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.1s;
        text-decoration: none; /* aタグ対応 */
        color: white; /* aタグ対応 */
    }

    .action-button:active {
        transform: translateY(2px);
    }

    .next-button {
        background-color: #1a73e8;
    }

    .menu-button {
        background-color: #ccc;
        color: #333;
    }
</style>
</head>
<body>

<div class="container">
    
    <div class="result-box <?php echo $result_class; ?>">
        <span class="result-emoji"><?php echo $result_emoji; ?></span>
        <?php echo $result_message; ?>
    </div>

    <div class="info-container">
        <div class="question-info">もんだい: <?php echo $question_kanji; ?> の読み</div>
        <div class="answer-info">あなたのこたえ: <?php echo $user_answer; ?></div>
        <div class="correct-display"><?php echo $correct_display; ?></div>
    </div>
    
    <div class="button-group">
        <a href="<?php echo $next_button_link; ?>" class="action-button next-button">
            つぎのもんだいへ
        </a>
        <a href="<?php echo $quit_button_link; ?>" class="action-button menu-button">
            やめる
        </a>
    </div>

</div>

</body>
</html>
