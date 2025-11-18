<?php
session_start();
require_once "db_config.php";

// --------------------------------------------
// 1) 学習セッションがなければ作成（読みと同じ動作）
// --------------------------------------------
if (!isset($_SESSION["learning_session_id"])) {

    // 書き問題も総数を固定（10問）
    $total_questions = 10;

    $sql = "INSERT INTO learning_session 
            (user_id, subject, category, total_questions, correct_count, start_time)
            VALUES (:user_id, 'kaki', 'normal', :tq, 0, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->bindValue(":tq", $total_questions, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION["learning_session_id"] = $pdo->lastInsertId();
}

// --------------------------------------------
// 2) 初回アクセスなら進行データを初期化
// --------------------------------------------
if (!isset($_SESSION["kaki_current_q"])) {
    $_SESSION["kaki_current_q"] = 1;
    $_SESSION["kaki_used_questions"] = [];
    $_SESSION["kaki_correct_count"] = 0;
}

// --------------------------------------------
// 3) 10問終わったら結果画面へ
// --------------------------------------------
if ($_SESSION["kaki_current_q"] > 10) {

    $total = 10;
    $correct = $_SESSION["kaki_correct_count"];

    header("Location: final_result.php?total=$total&correct=$correct");
    exit;
}

// --------------------------------------------
// 4) 未使用の問題をランダム取得（kanji テーブル）
// --------------------------------------------
$used = $_SESSION["kaki_used_questions"];
$ph    = implode(",", array_fill(0, count($used), "?"));

$sql = "
    SELECT question_id, question_text, question_okurigana, answer, choice
    FROM kanji
    WHERE target_grade = 1 
      AND kanji_type = '書き'
";

if (!empty($used)) {
    $sql .= " AND question_id NOT IN ($ph)";
}

$sql .= " ORDER BY RAND() LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute($used);

$q = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$q) {
    die("利用できる書き問題がありません。");
}

// 取得データ
$question_id     = $q["question_id"];
$question_text   = $q["question_text"];
$question_okuri  = $q["question_okurigana"];
$correct_answer  = $q["answer"];
$wrong_choice    = $q["choice"];

// 選択肢をランダム並び替え
$choices = [$correct_answer, $wrong_choice];
shuffle($choices);

// 出題済みに登録
$_SESSION["kaki_used_questions"][] = $question_id;

// 正解をセッションに保存
$_SESSION["kaki_correct_answer"] = $correct_answer;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>漢字選択問題</title>
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
    <a href="subject_select.php" class="back-button">←</a>

    <div class="question-box">
        <div class="reading-text">
            <span style="color: yellow;">
                <?= htmlspecialchars($question_text) ?>
            </span>

            <?php if (!empty($question_okuri)): ?>
                <span class="okuri">
                    <?= htmlspecialchars($question_okuri) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <h2>ただしいのはどっち？</h2>

    <form action="qs_1kaki_result.php" method="POST" class="choices-container">

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
