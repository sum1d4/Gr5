<?php
// -----------------------------
// 1. セッション開始
// -----------------------------
session_start();
require_once "db_config.php";

// セッション保存された正解
$correct_answer = $_SESSION["kaki2_correct_answer"] ?? null;

// POSTデータ
$user_answer  = $_POST["selected_answer"] ?? null;
$question_id  = $_POST["question_id"] ?? null;

// -----------------------------
// 2. 正誤判定
// -----------------------------
$is_correct = false;
if ($correct_answer !== null && $user_answer !== null) {
    $is_correct = ($user_answer === $correct_answer);
}

// -----------------------------
// 3. 正解数カウント
// -----------------------------
if (!isset($_SESSION["kaki2_correct_count"])) {
    $_SESSION["kaki2_correct_count"] = 0;
}
if ($is_correct) {
    $_SESSION["kaki2_correct_count"]++;
}

// 問題番号を進める
$_SESSION["kaki2_current_q"]++;

// -----------------------------
// 4. answer_record に保存
// -----------------------------
try {
    $sql = "
        INSERT INTO answer_record 
            (session_id, subject, problem_id, user_id, user_answer, is_correct)
        VALUES 
            (:session_id, :subject, :problem_id, :user_id, :user_answer, :is_correct)
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":session_id"  => $_SESSION["learning_session_id"],
        ":subject"     => "2kaki",
        ":problem_id"  => $question_id,
        ":user_id"     => $_SESSION["user_id"],
        ":user_answer" => $user_answer,
        ":is_correct"  => $is_correct ? 1 : 0
    ]);

} catch (PDOException $e) {
    echo "DB保存エラー: " . $e->getMessage();
}

// -----------------------------
// 5. 表示テキスト設定
// -----------------------------
$result_message = $is_correct ? "せいかい！" : "ざんねん…";
$result_emoji   = $is_correct ? "🎉" : "🤔";
$result_class   = $is_correct ? "correct" : "incorrect";
$correct_display = $is_correct
    ? "よくできました！"
    : "せいかいは「{$correct_answer}」でした";

// 次の問題
$next_button_link = "un_2kaki.php";
$quit_button_link = "subject_select.php";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>答え合わせ結果</title>
<style>
/* 以下 CSS はそのまま */
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

.result-box {
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    font-size: 36px;
    font-weight: bold;
    color: white;
}

.result-box.correct { background-color: #4CAF50; }
.result-box.incorrect { background-color: #F44336; }

.result-emoji {
    font-size: 60px;
    display: block;
    margin-bottom: 10px;
}

.info-container {
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #fafafa;
}

.answer-info { font-size: 20px; font-weight: 500; color: #333; }

.correct-display {
    font-size: 22px;
    font-weight: bold;
    color: #1a73e8;
    margin-top: 15px;
}

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
    text-decoration: none;
    color: white;
}

.next-button { background-color: #1a73e8; }
.menu-button { background-color: #ccc; color: #333; }
</style>
</head>
<body>

<div class="container">

    <div class="result-box <?php echo $result_class; ?>">
        <span class="result-emoji"><?php echo $result_emoji; ?></span>
        <?php echo $result_message; ?>
    </div>

    <div class="info-container">
        <div class="question-info">けっか</div>
        <div class="answer-info">あなたがえらんだかんじ: 
            <?php echo htmlspecialchars($user_answer ?? "未選択"); ?>
        </div>
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
