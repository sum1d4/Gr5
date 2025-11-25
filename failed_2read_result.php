<?php
session_start();
require_once "db_config.php";

// --------------------------------------
// 不正アクセスチェック
// --------------------------------------
if (!isset($_POST["answer"]) || !isset($_POST["question_id"])) {
    die("不正なアクセスです。");
}

$question_id = $_POST["question_id"];
$user_answer = trim($_POST["answer"]);


// --------------------------------------
// 正解データ取得
// --------------------------------------
if (!isset($_SESSION["failed_2read_correct_answer"])) {
    die("正解データがありません。");
}

$correct_answers = $_SESSION["failed_2read_correct_answer"];


// --------------------------------------
// 問題文取得
// --------------------------------------
$sql = "SELECT question_text FROM kanji WHERE question_id = :qid";
$stmt = $pdo->prepare($sql);
$stmt->execute([":qid" => $question_id]);
$question_kanji = $stmt->fetchColumn();
if (!$question_kanji) $question_kanji = "？";


// --------------------------------------
// 正誤判定
// --------------------------------------
$is_correct = in_array($user_answer, $correct_answers);


// --------------------------------------
// カウント増加
// --------------------------------------
if ($is_correct) {
    $_SESSION["failed_2read_correct"]++;
}


// --------------------------------------
// answer_record 保存
// --------------------------------------
$sql_rec = "
    INSERT INTO answer_record
        (session_id, subject, problem_id, user_id, user_answer, is_correct)
    VALUES
        (:sid, 'yomi2_failed', :pid, :uid, :ua, :isc)
";
$stmt = $pdo->prepare($sql_rec);
$stmt->execute([
    ":sid"  => $_SESSION["failed_2read_session_id"],
    ":pid"  => $question_id,
    ":uid"  => $_SESSION["user_id"],
    ":ua"   => $user_answer,
    ":isc"  => $is_correct ? 1 : 0
]);


// --------------------------------------
// 次の問題へ
// --------------------------------------
$_SESSION["failed_2read_qnum"]++;

$total_failed  = count($_SESSION["failed_2read_list"]);
$max_questions = min(10, $total_failed);


// --------------------------------------
// 終了処理
// --------------------------------------
if ($_SESSION["failed_2read_qnum"] > $max_questions) {

    // 学習記録更新
    $sql_up = "
        UPDATE learning_session
        SET correct_count = :cc, end_time = NOW()
        WHERE session_id = :sid
    ";
    $stmt_up = $pdo->prepare($sql_up);
    $stmt_up->execute([
        ":cc"  => $_SESSION["failed_2read_correct"],
        ":sid" => $_SESSION["failed_2read_session_id"]
    ]);

    header("Location: final_result.php?total=$max_questions&correct=".$_SESSION["failed_2read_correct"]);
    exit;
}


// --------------------------------------
// 表示テキスト生成
// --------------------------------------
$result_message = $is_correct ? "せいかい！" : "ざんねん…";
$result_emoji   = $is_correct ? "🎉" : "🤔";
$result_class   = $is_correct ? "correct" : "incorrect";

$correct_display = $is_correct
    ? "よくできました！"
    : "せいかいは「".implode(" / ", $correct_answers)."」でした";

$next_link = "failed_2read.php";
$menu_link = "mode_select.php?grade=2&subject=yomi";?>
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
