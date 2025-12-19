<?php
session_start();

// ★デバッグ用 (確認が終わったら削除してください)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. データの受け取り
$user_answer = isset($_POST['user_answer']) ? (string)$_POST['user_answer'] : null;
$correct_answer = isset($_POST['correct_answer']) ? (string)$_POST['correct_answer'] : null;
$question = isset($_POST['question']) ? htmlspecialchars($_POST['question']) : '';
$current_question_num = isset($_POST['current_question_num']) ? (int)$_POST['current_question_num'] : 1;

// ★学年と教科も受け取る (前の画面のformにhiddenで入っている前提)
$grade = isset($_POST['grade']) ? htmlspecialchars($_POST['grade']) : '1';
$subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : 'hiki'; // デフォルト

// 2. 正誤判定
$is_correct = ($user_answer !== null && $correct_answer !== null && $user_answer === $correct_answer);
$total_questions = 10;

// 正解ならセッションのスコアを加算
if ($is_correct) {
    $_SESSION['correct_count'] = ($_SESSION['correct_count'] ?? 0) + 1;
}
$current_score = $_SESSION['correct_count'] ?? 0;

// 次の問題番号
$next_question_num = $current_question_num + 1;


// ====================================================
// 3. 遷移先の決定ロジック
// ====================================================

// 10問目（最終問題）が終了した場合
if ($current_question_num >= $total_questions) {
    
    $button_label = 'こんかいのけっか';
    
    // ★重要: 最終結果画面へ。スコア、合計、学年、教科をパラメータで渡す
    $next_page_url = "final_result_guest.php?correct={$current_score}&total={$total_questions}&grade={$grade}&subject={$subject}";

} else {
    // 途中（1〜9問目）なら次の問題へ
    $button_label = 'つぎのもんだい ►';
    
    // ★教科によって次の問題ファイルのURLを変える（汎用的に使えるように修正）
    if ($subject === 'tashizan') {
        // 1年たしざん or 2年たしざん
        if ($grade == '1') {
            $script_name = 'math_question_1tasi_guest.php';
        } else {
            $script_name = 'math_question_2tasi_guest.php';
        }
    } elseif ($subject === 'hikizan') {
        // 1年ひきざん or 2年ひきざん
        if ($grade == '1') {
            $script_name = 'math_question_1hiki_guest.php';
        } else {
            $script_name = 'math_question_2hiki_guest.php';
        }
    } else {
        // デフォルト（元のコードにあったもの）
        $script_name = 'math_question_1hiki_guest.php';
    }

    // 次の問題番号と、学年・教科情報を引き継ぐ
    $next_page_url = "{$script_name}?q={$next_question_num}&grade={$grade}&subject={$subject}";
}


// 4. 画面表示用メッセージ設定
if ($is_correct) {
    $message_label = 'せいかい！ 🎉';
    $message_color = '#4caf50';
    $message_bg = '#e8f5e9';
} else {
    $message_label = 'ざんねん... 😥';
    $message_color = '#d32f2f';
    $message_bg = '#ffebee';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>計算結果 (第<?php echo $current_question_num; ?>問)</title>
    <style>
        body { 
            background: linear-gradient(to bottom, #b3e5fc, #81d4fa); 
            font-family: sans-serif; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            padding-top: 50px; 
            min-height: 100vh; 
        }
        .main-content { width: 90%; max-width: 380px; }
        .result-container { 
            padding: 25px 20px; 
            background-color: #fff; 
            border-radius: 12px; 
            box-shadow: 0 6px 15px rgba(0,0,0,0.15); 
            text-align: center; 
        }
        .message-label { 
            font-size: 2.5em; 
            font-weight: 900; 
            color: <?php echo $message_color; ?>; 
            background-color: <?php echo $message_bg; ?>; 
            border: 4px solid <?php echo $message_color; ?>; 
            display: inline-block; 
            padding: 15px 30px; 
            margin: 25px 0; 
            border-radius: 10px; 
        }
        .correct-answer-area {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #333;
        }
        .correct-answer-area span {
            font-weight: bold;
            color: #d32f2f;
            font-size: 1.4rem;
        }
        .next-button { 
            background-color: #42a5f5; 
            color: white; 
            border: none; 
            padding: 15px 30px; 
            font-size: 1.5em; 
            border-radius: 8px; 
            width: 90%; 
            max-width: 250px; 
            margin: 20px auto 0 auto; 
            text-decoration: none; 
            display: block; 
            text-align: center; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .next-button:hover {
            background-color: #1e88e5;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <p>【もんだい <?php echo $current_question_num; ?> のけっか】</p>
        <div class="result-container">
            
            <div class="message-label"><?php echo $message_label; ?></div>
            
            <?php if (!$is_correct): ?>
                <div class="correct-answer-area">
                    せいかいは <span><?php echo htmlspecialchars($correct_answer); ?></span> でした。
                </div>
            <?php endif; ?>

            <a href="<?php echo htmlspecialchars($next_page_url); ?>" class="next-button">
                <?php echo $button_label; ?>
            </a>
            
        </div>
    </div>
</body>
</html>
