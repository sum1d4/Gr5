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
$subject = "kaki"; // ← 一年書きの過去の間違えた問題を対象にする


// ---------------------------------------------------------
// 1) mode_select から POST で来たら failed セッションをリセット
// ---------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    unset($_SESSION["failed_1kaki_list"]);
    unset($_SESSION["failed_1kaki_used"]);
    unset($_SESSION["failed_1kaki_qnum"]);
    unset($_SESSION["failed_1kaki_correct"]);
    unset($_SESSION["failed_1kaki_correct_answer"]);
    unset($_SESSION["failed_1kaki_session_id"]);
}


// ---------------------------------------------------------
// 2) 初回アクセス：間違えた問題一覧を取得 → failed セッション初期化
// ---------------------------------------------------------
if (!isset($_SESSION["failed_1kaki_list"])) {

    // (A) 過去に間違えた問題一覧
    $sql = "
        SELECT problem_id
        FROM answer_record
        WHERE user_id = :uid
          AND subject = 'kaki'
          AND is_correct = 0
        GROUP BY problem_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":uid" => $user_id
    ]);
    $failed = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($failed)) {
        die("まちがえたもんだいはありません！");
    }

    $_SESSION["failed_1kaki_list"]    = $failed;
    $_SESSION["failed_1kaki_used"]    = [];
    $_SESSION["failed_1kaki_qnum"]    = 1;
    $_SESSION["failed_1kaki_correct"] = 0;


    // (B) failed モード用 learning_session を作成
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
        ":subj" => $subject,
        ":tq"   => $total_q
    ]);

    $_SESSION["failed_1kaki_session_id"] = $pdo->lastInsertId();
}


// ---------------------------------------------------------
// 3) 10問終了チェック
// ---------------------------------------------------------
$total_failed  = count($_SESSION["failed_1kaki_list"]);
$max_questions = min(10, $total_failed);

if ($_SESSION["failed_1kaki_qnum"] > $max_questions) {

    // 結果を learning_session に保存
    $sql_up = "
        UPDATE learning_session
        SET correct_count = :cc, end_time = NOW()
        WHERE session_id = :sid
    ";
    $stmt_up = $pdo->prepare($sql_up);
    $stmt_up->execute([
        ":cc"  => $_SESSION["failed_1kaki_correct"],
        ":sid" => $_SESSION["failed_1kaki_session_id"]
    ]);

    // セッション削除
    unset($_SESSION["failed_1kaki_list"]);
    unset($_SESSION["failed_1kaki_used"]);
    unset($_SESSION["failed_1kaki_qnum"]);
    unset($_SESSION["failed_1kaki_correct"]);
    unset($_SESSION["failed_1kaki_correct_answer"]);
    unset($_SESSION["failed_1kaki_session_id"]);

    header("Location: final_result.php?total=$max_questions&correct=".$_SESSION["failed_1kaki_correct"]);
    exit;
}


// ---------------------------------------------------------
// 4) 未使用問題からランダム1問
// ---------------------------------------------------------
$all  = $_SESSION["failed_1kaki_list"];
$used = $_SESSION["failed_1kaki_used"];

$remain = array_diff($all, $used);
if (empty($remain)) {
    header("Location: final_result.php?total=$max_questions&correct=".$_SESSION["failed_1kaki_correct"]);
    exit;
}

$question_id = $remain[array_rand($remain)];
$_SESSION["failed_1kaki_used"][] = $question_id;


// ---------------------------------------------------------
// 5) 書き問題データ取得
// ---------------------------------------------------------
$sql = "
    SELECT question_text, question_okurigana, answer, choice
    FROM kanji
    WHERE question_id = :qid
";
$stmt = $pdo->prepare($sql);
$stmt->execute([":qid" => $question_id]);
$q = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$q) die("問題データがありません");

$question_text  = $q["question_text"];
$question_okuri = $q["question_okurigana"];
$correct_answer = $q["answer"];
$wrong_choice   = $q["choice"];

// 2択シャッフル
$choices = [$correct_answer, $wrong_choice];
shuffle($choices);

$_SESSION["failed_1kaki_correct_answer"] = $correct_answer;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>まちがえたもんだい（1年 かき）</title>

<style>
body { font-family: Arial, sans-serif; text-align: center; }
.quiz-container {
    max-width: 500px;
    margin: 50px auto;
    padding: 40px 20px 20px;
    border: 1px solid #ccc;
    position: relative;
}
.back-button {
    position: absolute;
    top: 10px;
    left: 10px;
    font-size: 24px;
    padding: 5px;
    border: 1px solid #00f;
    border-radius: 5px;
    text-decoration: none;
    background-color: white;
    color: #00f;
}
.question-box {
    background-color: #046307;
    color: white;
    padding: 50px 20px;
    margin-bottom: 20px;
    border-radius: 10px;
}
.reading-text { font-size: 38px; margin-top: 20px; }
.okuri {
    font-size: 22px;
    color: white;
    margin-left: 5px;
}
.choices-container { margin-top: 20px; }
.choice-item { display: inline-block; margin: 40px 15px; }
.choice-button {
    background-color: white;
    border: 2px solid #ccc;
    padding: 20px 30px;
    font-size: 30px;
    border-radius: 10px;
    cursor: pointer;
    display: block;
}
</style>
</head>
<body>

<div class="quiz-container">
    <a href="mode_select.php?grade=1&subject=kaki" class="back-button">←</a>

    <div class="question-box">
        <div class="reading-text">
            <span style="color: yellow;"><?= htmlspecialchars($question_text) ?></span>
            <?php if (!empty($question_okuri)): ?>
                <span class="okuri"><?= htmlspecialchars($question_okuri) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <h2>ただしいのはどっち？</h2>

    <form action="failed_1kaki_result.php" method="POST" class="choices-container">
        <input type="hidden" name="question_id" value="<?= $question_id ?>">

        <?php foreach ($choices as $i => $c): ?>
            <div class="choice-item">
                <input type="radio" id="choice_<?= $i ?>" name="selected_answer"
                       value="<?= htmlspecialchars($c) ?>"
                       required style="display:none;">
                <label for="choice_<?= $i ?>" class="choice-button">
                    <?= htmlspecialchars($c) ?>
                </label>
            </div>
        <?php endforeach; ?>
    </form>
</div>

<script>
document.querySelectorAll('.choice-button').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('for');
        document.getElementById(id).checked = true;
        this.closest('form').submit();
    });
});
</script>

</body>
</html>
