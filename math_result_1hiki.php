<?php
// math_result_1hiki.php
session_start();
require_once 'db_config.php'; // DB接続

// ★デバッグ用：エラーを画面に表示する設定 (動作確認が済んだら削除/コメントアウトしてください)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ====================================================
// ★【最重要修正点】ユーザーIDをセッションから取得
// ====================================================
// 実際の認証システムが 'user_id' というキーを使っていると想定
// もしキーが 'id' など異なる場合は、ここを修正してください。
$user_id = $_SESSION['user_id'] ?? 0; 
// セッションにIDがない場合は仮に 0 または null を使用（登録エラーの原因となる可能性あり）
// ====================================================

// データの受け取り
$user_answer = isset($_POST['user_answer']) ? (string)$_POST['user_answer'] : null;
$question = isset($_POST['question']) ? htmlspecialchars($_POST['question']) : '';
$correct_answer = isset($_POST['correct_answer']) ? (string)$_POST['correct_answer'] : null;
$current_question_num = isset($_POST['current_question_num']) ? (int)$_POST['current_question_num'] : 1;

// 正誤判定
$is_correct = ($user_answer !== null && $correct_answer !== null && $user_answer === $correct_answer);
$total_questions = 10;

// 正解ならセッションを加算
if ($is_correct) {
    $_SESSION['correct_count'] = ($_SESSION['correct_count'] ?? 0) + 1;
}

// 画面表示用の変数準備
$next_question_num = $current_question_num + 1;
$current_score = $_SESSION['correct_count'] ?? 0;

// ====================================================
// 10問目（最終問題）が終了した時の処理
// ====================================================
if ($current_question_num >= $total_questions) {
    $button_label = 'こんかいのけっか';
    
    // ▼▼▼ データベース登録処理（最終修正版） ▼▼▼
    try {
        // テーブル名 'learning_session' を使用
        $sql = "INSERT INTO learning_session (user_id, subject, category, total_questions, correct_count, start_time, end_time) 
                VALUES (:user_id, :subject, :category, :total_questions, :correct_count, :start_time, :end_time)";
        
        $stmt = $pdo->prepare($sql);
        
        // パラメータ設定：user_id をセッションから取得した値に変更
        $params = [
            ':user_id' => $user_id,             // ★修正済み
            ':subject' => '1hiki',
            ':category' => 'normal',
            ':total_questions' => $total_questions,
            ':correct_count' => $current_score,
            ':start_time' => $_SESSION['start_time'] ?? date('Y-m-d H:i:s'),
            ':end_time' => date('Y-m-d H:i:s')
        ];

        $stmt->execute($params);

        // 成功したらセッションをクリアして進む
        unset($_SESSION['correct_count']);
        unset($_SESSION['start_time']);

        // 最終画面へ
        $next_page_url = 'final_result.php?correct=' . $current_score . '&total=' . $total_questions;

    } catch (PDOException $e) {
        // エラーが発生したら、画面に大きく表示して止める
        echo "<div style='background:#ffebee; color:#c62828; padding:20px; border:2px solid red; margin:20px;'>";
        echo "<h1>データベース登録エラーが発生しました (T_T)</h1>";
        echo "<p><strong>エラー内容:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<hr>";
        echo "<h3>現在のデータ:</h3>";
        echo "<ul>";
        echo "<li><strong>User ID:</strong> " . $user_id . " (セッションから取得)</li>";
        echo "<li><strong>正解数:</strong> " . $current_score . "問</li>";
        echo "</ul>";
        echo "<p>※User IDが **0** の場合、セッションにIDが設定されていません。認証処理を見直してください。</p>";
        echo "</div>";
        exit;
    }
    // ▲▲▲ データベース登録処理ここまで ▲▲▲

} else {
    // 途中なら次の問題へ
    $button_label = 'つぎのもんだい ►';
    $next_page_url = 'math_question_1hiki.php?q=' . $next_question_num;
}

// 画面表示用メッセージ
if ($is_correct) {
    $message_label = 'せいかい！ 🎉'; $message_color = '#4caf50'; $message_bg = '#e8f5e9';
} else {
    $message_label = 'ざんねん... 😥'; $message_color = '#d32f2f'; $message_bg = '#ffebee';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>計算結果 (第<?php echo $current_question_num; ?>問)</title>
    <style>
        body { background: linear-gradient(to bottom, #b3e5fc, #81d4fa); font-family: sans-serif; display: flex; flex-direction: column; align-items: center; padding-top: 50px; min-height: 100vh; }
        .main-content { width: 90%; max-width: 380px; }
        .result-container { padding: 25px 20px; background-color: #fff; border-radius: 12px; box-shadow: 0 6px 15px rgba(0,0,0,0.15); text-align: center; }
        .message-label { font-size: 2.5em; font-weight: 900; color: <?php echo $message_color; ?>; background-color: <?php echo $message_bg; ?>; border: 4px solid <?php echo $message_color; ?>; display: inline-block; padding: 15px 30px; margin: 25px 0; border-radius: 10px; }
        .next-button { background-color: #42a5f5; color: white; border: none; padding: 15px 30px; font-size: 1.5em; border-radius: 8px; width: 90%; max-width: 250px; margin: 20px auto 0 auto; text-decoration: none; display: block; text-align: center; }
    </style>
</head>
<body>
    <div class="main-content">
        <p>【もんだい <?php echo $current_question_num; ?> のけっか】</p>
        <div class="result-container">
            <div class="message-label"><?php echo $message_label; ?></div>
            <?php if (!$is_correct): ?>
            <div class="correct-answer-area">せいかいは <span><?php echo htmlspecialchars($correct_answer); ?></span> でした。</div>
            <?php endif; ?>

            <a href="<?php echo htmlspecialchars($next_page_url); ?>" class="next-button">
                <?php echo $button_label; ?>
            </a>
        </div>
    </div>
</body>
</html>
