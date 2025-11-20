<?php
session_start();
require_once "db_config.php";

// --------------------------------------------
// 【ログインチェック】
// --------------------------------------------
if (!isset($_SESSION["user_id"])) {
    header("Location: Rogin.php");
    exit;
}

// ===========================================================
// ★ 1) 初回アクセス時 learning_session を自動作成（2年・読み）
// ===========================================================
if (!isset($_SESSION["learning_session_id"])) {

    $total_questions = 10;

    $sql = "INSERT INTO learning_session 
            (user_id, subject, category, total_questions, correct_count, start_time)
            VALUES (:uid, '2yomi', 'normal', :tq, 0, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":uid", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->bindValue(":tq", $total_questions, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION["learning_session_id"] = $pdo->lastInsertId();
}

// ===========================================================
// 2) 10問の進行状況管理（初期化）
// ===========================================================
if (!isset($_SESSION["yomi2_current_q"])) {
    $_SESSION["yomi2_current_q"] = 1;
    $_SESSION["yomi2_correct_count"] = 0;
    $_SESSION["yomi2_used_questions"] = [];
}

// ===========================================================
// 3) 10問終わったら結果へ
// ===========================================================
if ($_SESSION["yomi2_current_q"] > 10) {

    $total    = 10;
    $correct  = $_SESSION["yomi2_correct_count"];

    header("Location: final_result.php?total={$total}&correct={$correct}");
    exit;
}

// ===========================================================
// 4) 2年生データから未出題の問題をランダムに取得
// ===========================================================

// 使用済み問題 ID
$used = $_SESSION["yomi2_used_questions"];
$ph   = implode(",", array_fill(0, count($used), "?"));

$sql = "
    SELECT question_id, question_text 
    FROM kanji
    WHERE target_grade = 2
      AND kanji_type = '読み'
      AND question_id LIKE '%R'
";

if (!empty($used)) {
    $sql .= " AND question_id NOT IN ($ph) ";
}

$sql .= " ORDER BY RAND() LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute($used);

$q = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$q) {
    die("利用可能な2年生の読み問題がありません。");
}

$question_id  = $q["question_id"];
$question_kanji = $q["question_text"];

// 出題済みに追加
$_SESSION["yomi2_used_questions"][] = $question_id;

// ===========================================================
// 5) 設問の読み（1件）を取得
// ===========================================================
$sql2 = "SELECT reading_answer FROM kanji_reading WHERE question_id = :qid LIMIT 1";
$stmt2 = $pdo->prepare($sql2);
$stmt2->bindValue(":qid", $question_id);
$stmt2->execute();
$row = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("読みデータがありません。");
}

$correct_answer = $row["reading_answer"];
$correct_length = mb_strlen($correct_answer, "UTF-8");

// 正解をセッションへ保存（結果判定で使用）
$_SESSION["yomi2_correct_answer"] = $correct_answer;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>2年生 漢字読み問題</title>
<style>
/* --- 一年生バージョンと同じデザイン --- */
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

.container {
    width: 100%;
    max-width: 390px;
    height: 844px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.appbar {
    width: 100%;
    height: 48px;
    display: flex;
    align-items: center;
    padding-left: 12px;
    background-color: #fff;
}

.back-icon {
    font-size: 28px;
    color: #007aff;
    text-decoration: none;
}

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
    gap: 12px;
}

.yellow-box {
    width: 24px;
    height: 24px;
    border: 2px solid yellow;
}

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
}

.check-button {
    margin-top: 25px;
    padding: 14px 50px;
    font-size: 20px;
    background-color: white;
    border: 1.5px solid #555;
    border-radius: 8px;
    cursor: pointer;
}

</style>
</head>
<body>

<div class="container">
    <div class="appbar">
        <a href="reset_quiz_session.php" class="back-btn">←</a>
    </div>

    <div class="board">
        <div class="kanji"><?php echo htmlspecialchars($question_kanji); ?></div>

        <div class="yellow-boxes">
            <?php for ($i = 0; $i < $correct_length; $i++): ?>
                <div class="yellow-box"></div>
            <?php endfor; ?>
        </div>
    </div>

    <form method="post" class="input-container" action="qs_2read_result.php">
        <input type="text" name="answer" placeholder="にゅうりょくしてね" required>
        <br>

        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">

        <!-- answer_record 保存用 -->
        <input type="hidden" name="subject" value="yomi2">
        <input type="hidden" name="session_id" value="<?php echo $_SESSION['learning_session_id']; ?>">
        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

        <button type="submit" class="check-button">こたえあわせ</button>
    </form>
</div>

</body>
</html>
