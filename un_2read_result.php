<?php
// ==========================================
// qs_2read_result.php（2年生版）
// ==========================================
session_start();
require_once "db_config.php";

// ------------------
// POSTチェック
// ------------------
if (!isset($_POST["question_id"]) || !isset($_POST["answer"])) {
    die("不正なアクセスです。");
}

$question_id = $_POST["question_id"];
$user_answer = trim($_POST["answer"]);    // ユーザー入力

// ★ answer_record 用
$session_id = $_SESSION["learning_session_id"];
$user_id    = $_SESSION["user_id"];
$subject    = "yomi2";   // ← ここだけ変更


// ---------------------------------------------------
// 【A】セッション初期化（初回アクセス時）
// ---------------------------------------------------
if (!isset($_SESSION["current_q2"])) {
    $_SESSION["current_q2"] = 1;
    $_SESSION["correct_count2"] = 0;
}


// ================================
// ① 問題の漢字を取得（kanji）
// ================================
$sql1 = "SELECT question_text FROM kanji WHERE question_id = :qid LIMIT 1";
$stmt1 = $pdo->prepare($sql1);
$stmt1->bindValue(":qid", $question_id);
$stmt1->execute();
$row = $stmt1->fetch(PDO::FETCH_ASSOC);

$question_kanji = $row ? $row["question_text"] : "？";


// ================================
// ② 正しい読みを取得（kanji_reading）
// ================================
$sql2 = "SELECT reading_answer FROM kanji_reading WHERE question_id = :qid";
$stmt2 = $pdo->prepare($sql2);
$stmt2->bindValue(":qid", $question_id);
$stmt2->execute();
$correct_answers = $stmt2->fetchAll(PDO::FETCH_COLUMN);

if (!$correct_answers) {
    die("正解の読みが見つかりません。(ID:$question_id)");
}


// ================================
// ③ 正誤判定（複数読み対応）
// ================================
$is_correct = in_array($user_answer, $correct_answers);


// ---------------------------------------------------
// 【B】正解時はカウント＋1
// ---------------------------------------------------
if ($is_correct) {
    $_SESSION["correct_count2"]++;
}


// ---------------------------------------------------
// ★【C】answer_record に保存
// ---------------------------------------------------
$sql_rec = "
    INSERT INTO answer_record 
    (session_id, subject, problem_id, user_id, user_answer, is_correct)
    VALUES (:sid, :sub, :pid, :uid, :ua, :isc)
";
$stmt_rec = $pdo->prepare($sql_rec);
$stmt_rec->bindValue(":sid", $session_id);
$stmt_rec->bindValue(":sub", $subject);
$stmt_rec->bindValue(":pid", $question_id);
$stmt_rec->bindValue(":uid", $user_id);
$stmt_rec->bindValue(":ua", $user_answer);
$stmt_rec->bindValue(":isc", $is_correct ? 1 : 0, PDO::PARAM_INT);
$stmt_rec->execute();


// ---------------------------------------------------
// 【D】問題番号を進める
// ---------------------------------------------------
$_SESSION["current_q2"]++;


// ---------------------------------------------------
// 【E】10問終わったら final_result.php へ
// ---------------------------------------------------
if ($_SESSION["current_q2"] > 10) {

    $total = 10;
    $correct = $_SESSION["correct_count2"];

    // ★ learning_session の正解数を更新
    $sql_update = "
        UPDATE learning_session
        SET correct_count = :cc, end_time = NOW()
        WHERE session_id = :sid
    ";
    $stmt_up = $pdo->prepare($sql_update);
    $stmt_up->bindValue(":cc", $correct, PDO::PARAM_INT);
    $stmt_up->bindValue(":sid", $session_id, PDO::PARAM_INT);
    $stmt_up->execute();

    // セッション破棄（リセット）
    session_destroy();

    header("Location: final_result.php?total=$total&correct=$correct");
    exit;
}


// ================================
// ④ 表示用テキスト
// ================================
$result_message = $is_correct ? "せいかい！" : "ざんねん…";
$result_emoji   = $is_correct ? "🎉" : "🤔";
$result_class   = $is_correct ? "correct" : "incorrect";

$correct_display = $is_correct
    ? "よくできました！"
    : "せいかいは「" . implode(" / ", $correct_answers) . "」でした";

$next_button_link = "un_2read.php";  // ← ここも 2年生版
$quit_button_link = "subject_select.php";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>けっか</title>

<style>
/* 一年版と完全同じデザイン */
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
        <a href="<?= $next_button_link ?>" class="action-button next-button">つぎのもんだいへ</a>
        <a href="<?= $quit_button_link ?>" class="action-button menu-button">やめる</a>
    </div>

    <script>
    document.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();  // 変な動作を防ぐ
            const nextBtn = document.querySelector('.next-button');
            if (nextBtn) {
                nextBtn.click();
            }
        }
    });
    </script>
    
</div>

</body>
</html>
