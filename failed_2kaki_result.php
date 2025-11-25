<?php
session_start();
require_once "db_config.php";

// -------------------------------
// 不正アクセスチェック
// -------------------------------
if (!isset($_POST["selected_answer"]) || !isset($_POST["question_id"])) {
    die("不正なアクセスです");
}

$user_answer = $_POST["selected_answer"];
$question_id = $_POST["question_id"];


// -------------------------------
// 正解データ（セッション）
// -------------------------------
if (!isset($_SESSION["failed_2kaki_correct_answer"])) {
    die("正解データがありません。");
}

$correct_answer = $_SESSION["failed_2kaki_correct_answer"];

$is_correct = ($user_answer === $correct_answer);

if ($is_correct) {
    $_SESSION["failed_2kaki_correct"]++;
}


// -------------------------------
// answer_record 保存
// -------------------------------
$sql_rec = "
    INSERT INTO answer_record
        (session_id, subject, problem_id, user_id, user_answer, is_correct)
    VALUES
        (:sid, 'kaki2_failed', :pid, :uid, :ua, :isc)
";
$stmt = $pdo->prepare($sql_rec);
$stmt->execute([
    ":sid" => $_SESSION["failed_2kaki_session_id"],
    ":pid" => $question_id,
    ":uid" => $_SESSION["user_id"],
    ":ua"  => $user_answer,
    ":isc" => $is_correct ? 1 : 0
]);


// -------------------------------
// 次の問題へ
// -------------------------------
$_SESSION["failed_2kaki_qnum"]++;

$total_failed  = count($_SESSION["failed_2kaki_list"]);
$max_questions = min(10, $total_failed);


// -------------------------------
// 終了チェック
// -------------------------------
if ($_SESSION["failed_2kaki_qnum"] > $max_questions) {

    $total   = $max_questions;
    $correct = $_SESSION["failed_2kaki_correct"];

    // learning_session 更新
    $sql_up = "
        UPDATE learning_session
        SET correct_count = :cc, end_time = NOW()
        WHERE session_id = :sid
    ";
    $stmt_up = $pdo->prepare($sql_up);
    $stmt_up->execute([
        ":cc"  => $correct,
        ":sid" => $_SESSION["failed_2kaki_session_id"]
    ]);

    header("Location: final_result.php?total=$total&correct=$correct");
    exit;
}


// -------------------------------
// 問題文取得（結果表示用）
// -------------------------------
$sql = "
    SELECT question_text, question_okurigana
    FROM kanji
    WHERE question_id = :qid
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":qid" => $question_id]);
$q = $stmt->fetch(PDO::FETCH_ASSOC);

$question_text  = $q ? $q["question_text"] : "？";
$question_okuri = $q ? $q["question_okurigana"] : "";


// -------------------------------
// 結果メッセージ生成
// -------------------------------
$result_message = $is_correct ? "せいかい！" : "ざんねん…";
$result_emoji   = $is_correct ? "🎉" : "🤔";
$result_class   = $is_correct ? "correct" : "incorrect";

$correct_display = $is_correct
    ? "よくできました！"
    : "せいかいは「{$correct_answer}」でした";

$next_link = "failed_2kaki.php";
$menu_link = "mode_select.php?grade=2&subject=kaki";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>けっか</title>

<style>
html, body {
    margin: 0; padding: 0;
    height: 100%;
    background-color: #f5f5f5;
    font-family: "Hiragino Kaku Gothic ProN","Meiryo",sans-serif;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
}

.container {
    width: 100%; max-width: 390px;
    background-color: #fff;
    padding: 30px 20px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    text-align: center;
}

.result-box {
    padding: 20px; border-radius: 10px;
    margin-bottom: 30px;
    font-size: 36px; font-weight: bold; color: white;
}
.result-box.correct { background-color: #4CAF50; }
.result-box.incorrect { background-color: #F44336; }

.result-emoji { font-size: 60px; margin-bottom: 10px; }

.info-container {
    margin-bottom: 30px;
    padding: 20px; border: 1px solid #ddd;
    border-radius: 10px; background-color: #fafafa;
}

.question-info { font-size: 24px; margin-bottom: 15px; }
.answer-info { font-size: 20px; font-weight: 500; }
.correct-display { font-size: 22px; font-weight: bold; color: #1a73e8; }

.button-group { display: flex; flex-direction: column; gap: 15px; }

.action-button {
    padding: 15px 25px;
    font-size: 20px;
    font-weight: bold;
    text-decoration: none;
    border-radius: 8px;
    color: white;
}
.next-button { background-color: #1a73e8; }
.menu-button { background-color: #ccc; color: #333; }
</style>

</head>
<body>

<div class="container">

    <div class="result-box <?= $result_class ?>">
        <span class="result-emoji"><?= $result_emoji ?></span>
        <?= $result_message ?>
    </div>

    <div class="info-container">

        <div class="question-info">
            もんだい: <?= htmlspecialchars($question_text) ?> <?= htmlspecialchars($question_okuri) ?>
        </div>

        <div class="answer-info">
            あなたのこたえ: <?= htmlspecialchars($user_answer) ?>
        </div>

        <div class="correct-display">
            <?= $correct_display ?>
        </div>

    </div>

    <div class="button-group">
        <a href="<?= $next_link ?>" class="action-button next-button">つぎのもんだいへ</a>
        <a href="<?= $menu_link ?>" class="action-button menu-button">メニューにもどる</a>
    </div>

</div>

</body>
</html>
