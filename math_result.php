<?php
// POSTデータを受け取る
$user_answer = isset($_POST['user_answer']) ? (string)$_POST['user_answer'] : null;
$question = isset($_POST['question']) ? htmlspecialchars($_POST['question']) : '問題がありません';
$correct_answer = isset($_POST['correct_answer']) ? (string)$_POST['correct_answer'] : null;
// 【追加】問題番号を受け取る
$current_question_num = isset($_POST['current_question_num']) ? (int)$_POST['current_question_num'] : 1;

// 正誤判定
$is_correct = ($user_answer !== null && $correct_answer !== null && (string)$user_answer === (string)$correct_answer);
$total_questions = 10; // 全問題数

// 次の画面への準備
$next_question_num = $current_question_num + 1;

// 最終問題 (10問目) かどうかを判定
if ($current_question_num >= $total_questions) {
    // 最終問題の場合
    $button_label = 'こんかいのけっか';
    $next_page_url = 'final_result.php'; // 遷移先: final_result.php
} else {
    // 最終問題ではない場合
    $button_label = 'つぎのもんだい ►';
    // 遷移先: math_question.phpに次の問題番号を渡す
    $next_page_url = 'math_question.php?q=' . $next_question_num;
}


// ③ せいかい/ざんねん メッセージ
if ($is_correct) {
    $message_label = 'せいかい！';
    $message_color = '#d9534f'; 
} else {
    $message_label = 'ざんねん...';
    $message_color = '#38761d'; 
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>計算結果 (第<?php echo $current_question_num; ?>問)</title>
    <style>
        /* CSSコードは前回と全く同じなので省略します */
        /* ... 省略 ... */
        /* ------------------- CSSコードは変更ありません ------------------- */

        /* 既存のCSSをそのまま使用します */
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
            background-color: #f0f0f0;
        }
        .result-container {
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            width: 300px; 
            text-align: center;
        }
        .question-display {
            background-color: #38761d;
            color: white;
            padding: 15px 10px;
            border-radius: 5px;
            margin-bottom: 5px; /* 調整済み */
            font-size: 2.5em; 
            font-weight: bold;
        }
        .answer-display {
            width: 120px; 
            height: 40px;
            line-height: 40px;
            text-align: right;
            font-size: 2em;
            font-weight: bold;
            border: 3px solid #ccc;
            padding: 5px;
            background-color: white;
            box-sizing: border-box;
            margin: 5px auto 10px auto; /* 調整済み */
        }
        .message-label {
            font-size: 2em;
            font-weight: bold;
            color: <?php echo htmlspecialchars($message_color); ?>;
            border: 3px dashed <?php echo htmlspecialchars($message_color); ?>;
            display: inline-block;
            padding: 10px 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .next-button {
            background-color: #4a86e8;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 1.2em;
            border-radius: 5px;
            width: 200px;
            margin: 10px auto;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    
    <p>【問題 <?php echo $current_question_num; ?> の結果】</p>

    <div class="result-container">
        <div class="question-display">
            <?php echo $question; ?>
        </div>

        <div class="answer-display">
            <?php echo htmlspecialchars($user_answer); ?>
        </div>
        
        <div class="message-label">
            <?php echo $message_label; ?>
        </div>

        <a href="<?php echo htmlspecialchars($next_page_url); ?>" class="next-button">
            <?php echo $button_label; ?>
        </a>
    </div>

</body>
</html>
