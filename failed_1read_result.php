<?php
session_start();
require_once "db_config.php";

// --------------------------------------
// 不正アクセス
// --------------------------------------
if (!isset($_POST["question_id"]) || !isset($_POST["answer"])) {
    die("不正なアクセスです。");
}

$question_id = $_POST["question_id"];
$user_answer = trim($_POST["answer"]);


// --------------------------------------
// 正解データ（セッションに保存済み）
// --------------------------------------
if (!isset($_SESSION["failed_1read_correct_answer"])) {
    die("正解データがありません。");
}

$correct_answers = $_SESSION["failed_1read_correct_answer"];


// --------------------------------------
// 問題の漢字（表示用）
// --------------------------------------
$sql = "SELECT question_text FROM kanji WHERE question_id = :qid";
$stmt = $pdo->prepare($sql);
$stmt->execute([":qid" => $question_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$question_kanji = $row ? $row["question_text"] : "？";


// --------------------------------------
// 正誤判定
// --------------------------------------
$is_correct = in_array($user_answer, $correct_answers);

if ($is_correct) {
    $_SESSION["failed_1read_correct"]++;
}


// ===================================================
// ★ answer_record に保存（subject=yomi1, category=failed）
// ===================================================
$sql_rec = "
    INSERT INTO answer_record
    (session_id, subject, problem_id, user_id, user_answer, is_correct)
    VALUES (:sid, :sub, :pid, :uid, :ua, :isc)
";

$stmt_rec = $pdo->prepare($sql_rec);
$stmt_rec->bindValue(":sid", $_SESSION["failed_1read_session_id"], PDO::PARAM_INT);
$stmt_rec->bindValue(":sub", "yomi1");   // ← 教科だけ
$stmt_rec->bindValue(":pid", $question_id);
$stmt_rec->bindValue(":uid", $_SESSION["user_id"]);
$stmt_rec->bindValue(":ua", $user_answer);
$stmt_rec->bindValue(":isc", $is_correct ? 1 : 0, PDO::PARAM_INT);
$stmt_rec->execute();


// --------------------------------------
// 次の問題へ
// --------------------------------------
$_SESSION["failed_1read_qnum"]++;


// --------------------------------------
// 終了なら final_result
// --------------------------------------
$total_failed = count($_SESSION["failed_1read_list"]);
$max_questions = min(10, $total_failed);

if ($_SESSION["failed_1read_qnum"] > $max_questions) {

    $total   = $max_questions;
    $correct = $_SESSION["failed_1read_correct"];

    // learning_session 更新
    $sql_up = "
        UPDATE learning_session
        SET correct_count = :cc, end_time = NOW()
        WHERE session_id = :sid
    ";
    $stmt_up = $pdo->prepare($sql_up);
    $stmt_up->execute([
        ":cc"  => $correct,
        ":sid" => $_SESSION["failed_1read_session_id"]
    ]);

    // セッション片付け
    unset($_SESSION["failed_1read_list"]);
    unset($_SESSION["failed_1read_used"]);
    unset($_SESSION["failed_1read_qnum"]);
    unset($_SESSION["failed_1read_correct"]);
    unset($_SESSION["failed_1read_correct_answer"]);
    unset($_SESSION["failed_1read_session_id"]);

    header("Location: final_result.php?total=$total&correct=$correct");
    exit;
}


// --------------------------------------
// 表示用テキスト
// --------------------------------------
$result_message = $is_correct ? "せいかい！" : "ざんねん…";
$result_emoji   = $is_correct ? "🎉" : "🤔";
$result_class   = $is_correct ? "correct" : "incorrect";

$correct_display = $is_correct
    ? "よくできました！"
    : "せいかいは「" . implode(" / ", $correct_answers) . "」でした";

$next_link = "failed_1read.php";
$menu_link = "mode_select.php?grade=1&subject=yomi";

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>けっか</title>

<style>
/* デザインはそのまま */
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
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
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

    <div class="result-box <?= $result_class ?>">
        <div class="result-emoji"><?= $result_emoji ?></div>
        <?= $result_message ?>
    </div>

    <div class="info-container">
        <div class="question-info">
            もんだい: <?= htmlspecialchars($question_kanji) ?> の読み
        </div>

        <div class="answer-info">
            あなたのこたえ: <?= htmlspecialchars($user_answer) ?>
        </div>

        <div class="correct-display">
            <?= htmlspecialchars($correct_display) ?>
        </div>
    </div>

    <div class="button-group">
        <a href="<?= $next_link ?>" class="action-button next-button">つぎのもんだいへ</a>
        <a href="<?= $menu_link ?>" class="action-button menu-button">メニューにもどる</a>
    </div>

</div>

</body>
</html>
