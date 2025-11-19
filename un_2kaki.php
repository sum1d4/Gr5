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

// --------------------------------------------
// 1) 学習セッションがなければ作成
// --------------------------------------------
if (!isset($_SESSION["learning_session_id"])) {

    $total_questions = 10;

    $sql = "INSERT INTO learning_session 
            (user_id, subject, category, total_questions, correct_count, start_time)
            VALUES (:uid, '2kaki', 'unanswered', :tq, 0, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":uid", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->bindValue(":tq", $total_questions, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION["learning_session_id"] = $pdo->lastInsertId();
}

// --------------------------------------------
// 2) 初回アクセスなら進行データを初期化
// --------------------------------------------
if (!isset($_SESSION["kaki2_current_q"])) {
    $_SESSION["kaki2_current_q"] = 1;
    $_SESSION["kaki2_used_questions"] = [];
    $_SESSION["kaki2_correct_count"] = 0;
}

// --------------------------------------------
// 3) 10問終わったら結果へ
// --------------------------------------------
if ($_SESSION["kaki2_current_q"] > 10) {

    $total   = 10;
    $correct = $_SESSION["kaki2_correct_count"];

    header("Location: final_result.php?total={$total}&correct={$correct}");
    exit;
}
// =======================================================
// 4) 未出題の問題をランダム取得 
// =======================================================

// 4a) 過去に正解した問題IDを取得 (マスター済みの問題を除外)
$user_id = $_SESSION["user_id"];

// 過去の answer_record から、このユーザーが「正解 (is_correct = 1)」した problem_id を全て取得
$sql_mastered = "
    SELECT DISTINCT problem_id 
    FROM answer_record 
    WHERE user_id = :uid AND is_correct = 2
    AND subject = 'kaki' -- 書き問題のみを対象とする
";
$stmt_mastered = $pdo->prepare($sql_mastered);
$stmt_mastered->bindValue(":uid", $user_id, PDO::PARAM_INT);
$stmt_mastered->execute();
$mastered_list = $stmt_mastered->fetchAll(PDO::FETCH_COLUMN);


// 4b) 出題から除外するリストを結合 (クリーンアップとインデックスリセットを適用)
// 過去に正解した問題ID + 現在のセッションで出題済みの問題ID = 除外リスト
$used = $_SESSION["kaki2_used_questions"];

// 1. 全てを文字列化 2. 重複除去 3. 空要素・不正値を徹底排除
$exclude_list = array_unique(array_merge($mastered_list, $used));
$exclude_list = array_filter(array_map('strval', $exclude_list));


// 4c) 未出題・未完了の問題をランダム取得
// $placeholders = empty($exclude_list) ? "" : implode(",", array_fill(0, count($exclude_list), "?"));

$sql = "
    SELECT question_id, question_text, question_okurigana, answer, choice
    FROM kanji
    WHERE target_grade = 2 
      AND kanji_type = '書き'
";

if (!empty($exclude_list)) {
    // 除外リストに含まれない問題のみを選択
    $placeholder_count = count($exclude_list);
    $placeholders = implode(",", array_fill(0, $placeholder_count, "?"));
    $sql .= " AND question_id NOT IN ($placeholders)";
}

$sql .= " ORDER BY RAND() LIMIT 1";

$stmt = $pdo->prepare($sql);

// 除外リストの値をSQLにバインドして実行
if (!empty($exclude_list)) {
    // インデックスをリセットして渡す
    $stmt->execute(array_values($exclude_list)); 
} else {
    $stmt->execute();
}

$q = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$q) {
    // 未出題の問題が全て終了した場合の処理
    die("未出題の書き問題が尽きました。素晴らしい！");
}
// 取得データ
$question_id     = $q["question_id"];
$question_text   = $q["question_text"];
$question_okuri  = $q["question_okurigana"];
$correct_answer  = $q["answer"];
$wrong_choice    = $q["choice"];

// 選択肢をランダムに並べる
$choices = [$correct_answer, $wrong_choice];
shuffle($choices);

// 出題済みとして保存
$_SESSION["kaki2_used_questions"][] = $question_id;

// 正解を保存
$_SESSION["kaki2_correct_answer"] = $correct_answer;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>2年生 書き取り問題</title>
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

    <form action="un_2kaki_result.php" method="POST" class="choices-container">

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
