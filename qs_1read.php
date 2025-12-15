<?php
session_start();
require_once "db_config.php";

/* --------------------------------------------
   0) ログインチェック
-------------------------------------------- */
if (!isset($_SESSION["user_id"])) {
    header("Location: Rogin.php");
    exit;
}

/* --------------------------------------------
   1) 初回なら学習セッションを作成
-------------------------------------------- */
if (!isset($_SESSION["learning_session_id"])) {

    $sql = "INSERT INTO learning_session 
            (user_id, subject, category, total_questions, correct_count, start_time)
            VALUES (:uid, '1yomi', 'normal', 10, 0, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":uid", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION["learning_session_id"] = $pdo->lastInsertId();
}

/* --------------------------------------------
   2) 初期化（1問目）
-------------------------------------------- */
if (!isset($_SESSION["current_q"])) {
    $_SESSION["current_q"] = 1;
    $_SESSION["correct_count"] = 0;
    $_SESSION["read_questions"] = [];
}

/* --------------------------------------------
   3) 10問終わり判定
-------------------------------------------- */
if ($_SESSION["current_q"] > 10) {
    $correct = $_SESSION["correct_count"];
    header("Location: final_result.php?total=10&correct={$correct}");
    exit;
}

/* --------------------------------------------
   4) 未使用の読み問題をランダム取得
-------------------------------------------- */
$used = $_SESSION["read_questions"];
$placeholders = implode(",", array_fill(0, count($used), "?"));

$sql = "
    SELECT question_id, question_text 
    FROM kanji
    WHERE question_id LIKE 'KJ0%' 
      AND question_id LIKE '%R'
";

if (!empty($used)) {
    $sql .= " AND question_id NOT IN ($placeholders)";
}

$sql .= " ORDER BY RAND() LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute($used);

$q = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$q) { die("利用可能な問題がありません。"); }

$question_id    = $q["question_id"];
$question_kanji = $q["question_text"];

$_SESSION["read_questions"][] = $question_id;

/* --------------------------------------------
   5) 正解読み取得
-------------------------------------------- */
$sql2 = "SELECT reading_answer FROM kanji_reading WHERE question_id = :qid";
$stmt2 = $pdo->prepare($sql2);
$stmt2->bindValue(":qid", $question_id);
$stmt2->execute();
$correct_answers = $stmt2->fetchAll(PDO::FETCH_COLUMN);

$correct_length = mb_strlen($correct_answers[0], "UTF-8");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>漢字クイズ</title>
<style>
<?php /* --- デザインそのまま --- */ ?>
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
        <a href="reset_quiz_session.php" class="back-icon">←</a>
    </div>

    <div class="board">
        <div class="kanji"><?php echo htmlspecialchars($question_kanji); ?></div>

        <div class="yellow-boxes">
            <?php for ($i = 0; $i < $correct_length; $i++): ?>
                <div class="yellow-box"></div>
            <?php endfor; ?>
        </div>
    </div>

    <form method="post" class="input-container" action="qs_1read_result.php">
        <input type="text" name="answer" placeholder="にゅうりょくしてね" required>
        <br>

        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">

        <!-- answer_record 保存用 -->
        <input type="hidden" name="subject" value="yomi">
        <input type="hidden" name="session_id" value="<?php echo $_SESSION['learning_session_id']; ?>">
        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

        <button type="submit" class="check-button">こたえあわせ</button>
    </form>

    <script>
    window.onload = function() {
        const inputBox = document.querySelector('input[name="answer"]');
        if (inputBox) {
            inputBox.focus();
        }
    };
    </script>

</div>

</body>
</html>
