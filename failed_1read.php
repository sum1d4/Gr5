<?php
session_start();
require_once "db_config.php";

// ---------------------------------------------------------
// 0) ログインチェック
// ---------------------------------------------------------
if (!isset($_SESSION["user_id"])) {
    header("Location: Rogin.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$subject = "yomi"; // ← 教科は“yomi”だけに統一


// ---------------------------------------------------------
// 1) POST で来た（mode_select → failed 押した瞬間）
//    ⇒ 過去の failed セッション全部リセット
// ---------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    unset($_SESSION["failed_1read_list"]);
    unset($_SESSION["failed_1read_used"]);
    unset($_SESSION["failed_1read_qnum"]);
    unset($_SESSION["failed_1read_correct"]);
    unset($_SESSION["failed_1read_correct_answer"]);
    unset($_SESSION["failed_1read_session_id"]);
}


// ---------------------------------------------------------
// 2) 初回アクセス：failed モードのセッション初期化
// ---------------------------------------------------------
if (!isset($_SESSION["failed_1read_list"])) {

    // --------------- (A) 間違えた問題一覧 ---------------
    $sql = "
        SELECT problem_id
        FROM answer_record
        WHERE user_id = :uid
          AND subject = :sub
          AND is_correct = 0
        GROUP BY problem_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":uid" => $user_id,
        ":sub" => $subject
    ]);
    $failed = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($failed)) {
        die("まちがえたもんだいはありません！");
    }

    $_SESSION["failed_1read_list"]   = $failed;
    $_SESSION["failed_1read_used"]   = [];
    $_SESSION["failed_1read_qnum"]   = 1;
    $_SESSION["failed_1read_correct"] = 0;


    // --------------- (B) learning_session を作成 ---------------
    $total_q = min(10, count($failed));

    $sql2 = "
        INSERT INTO learning_session
            (user_id, subject, category, total_questions, correct_count, start_time)
        VALUES
            (:uid, :subj, 'failed', :tq, 0, NOW())
    ";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute([
        ":uid"  => $user_id,
        ":subj" => $subject,  // ★ここが「yomi1」
        ":tq"   => $total_q
    ]);

    $_SESSION["failed_1read_session_id"] = $pdo->lastInsertId();
}


// ---------------------------------------------------------
// 3) 10問制限
// ---------------------------------------------------------
$total_failed = count($_SESSION["failed_1read_list"]);
$max_questions = min(10, $total_failed);

if ($_SESSION["failed_1read_qnum"] > $max_questions) {

    // 結果を learning_session に記録
    $sql_up = "
        UPDATE learning_session
        SET correct_count = :cc, end_time = NOW()
        WHERE session_id = :sid
    ";
    $stmt_up = $pdo->prepare($sql_up);
    $stmt_up->execute([
        ":cc"  => $_SESSION["failed_1read_correct"],
        ":sid" => $_SESSION["failed_1read_session_id"]
    ]);

    session_unset();
    header("Location: final_result.php?total=$max_questions&correct=".$_SESSION["failed_1read_correct"]);
    exit;
}


// ---------------------------------------------------------
// 4) 未使用の問題からランダム出題
// ---------------------------------------------------------
$all  = $_SESSION["failed_1read_list"];
$used = $_SESSION["failed_1read_used"];

$remain = array_diff($all, $used);

if (empty($remain)) {
    header("Location: final_result.php?total=$max_questions&correct=".$_SESSION["failed_1read_correct"]);
    exit;
}

$question_id = $remain[array_rand($remain)];
$_SESSION["failed_1read_used"][] = $question_id;


// ---------------------------------------------------------
// 5) 問題の漢字取得
// ---------------------------------------------------------
$sql = "SELECT question_text FROM kanji WHERE question_id = :qid";
$stmt = $pdo->prepare($sql);
$stmt->execute([":qid" => $question_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$kanji = $row ? $row["question_text"] : "？";


// ---------------------------------------------------------
// 6) 読み（複数OK）
    // ---------------------------------------------------------
$sql = "SELECT reading_answer FROM kanji_reading WHERE question_id = :qid";
$stmt = $pdo->prepare($sql);
$stmt->execute([":qid" => $question_id]);
$answers = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$answers) {
    die("読みデータがありません。");
}

$_SESSION["failed_1read_correct_answer"] = $answers;
$correct_length = mb_strlen($answers[0], "UTF-8");

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>まちがえたもんだい（1年 よみ）</title>

<style>
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    background-color: #fff;
    font-family: "Hiragino Kaku Gothic ProN","Meiryo",sans-serif;
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
    display: flex;
    flex-direction: column;
    align-items: center;
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
        <a href="mode_select.php?grade=1&subject=yomi" class="back-icon">←</a>
    </div>

    <div class="board">
        <div class="kanji"><?= htmlspecialchars($kanji) ?></div>

        <div class="yellow-boxes">
            <?php for ($i = 0; $i < $correct_length; $i++): ?>
                <div class="yellow-box"></div>
            <?php endfor; ?>
        </div>
    </div>

    <form method="post" class="input-container" action="failed_1read_result.php">
        <input type="text" name="answer" placeholder="にゅうりょくしてね" required>
        <input type="hidden" name="question_id" value="<?= $question_id ?>">
        <button type="submit" class="check-button">こたえあわせ</button>
    </form>

</div>

</body>
</html>
