<?php
session_start();
// データベース接続設定ファイル (db_config.php) の読み込みを想定
require_once "db_config.php"; 

// ===========================================================
// ★ 1) 初回アクセス時 learning_session を自動作成
// ===========================================================
if (!isset($_SESSION["learning_session_id"])) {

    $total_questions = 10; 

    // subjectは '1yomi'、categoryは 'unanswered'
    $sql = "INSERT INTO learning_session 
             (user_id, subject, category, total_questions, correct_count, start_time)
             VALUES (:uid, '1yomi', 'unanswered', :tq, 0, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":uid", $_SESSION["user_id"], PDO::PARAM_INT);
    $stmt->bindValue(":tq", $total_questions, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION["learning_session_id"] = $pdo->lastInsertId();
}

// ===========================================================
// 2) 10問の進行状況管理（初回初期化）
// (★ 変数名を 'read1_' プレフィックスに変更し独立性を確保)
// ===========================================================
if (!isset($_SESSION["read1_current_q"])) {
    $_SESSION["read1_current_q"] = 1;
    $_SESSION["read1_correct_count"] = 0;
    $_SESSION["read1_used_questions"] = []; // 当セッションで出題済みの問題ID
}

// ===========================================================
// 3) 10問終わったら final_result.php へ (リセット処理を確実化)
// ===========================================================
if ($_SESSION["read1_current_q"] > 10) {
    $total = 10;
    $correct = $_SESSION["read1_correct_count"];

    // ★ 1年生読み取り問題のセッション変数を破棄
    unset($_SESSION["read1_current_q"]);
    unset($_SESSION["read1_correct_count"]);
    unset($_SESSION["read1_used_questions"]);
    unset($_SESSION["learning_session_id"]);
    
    // 変更を強制的に保存してセッションを閉じる
    session_write_close(); 

    header("Location: final_result.php?total={$total}&correct={$correct}");
    exit;
}

// ===========================================================
// 4) 未出題の問題をランダム取得
// ===========================================================

// 4a) 過去に正解した問題IDを取得 (マスター済みの問題を除外)
$user_id = $_SESSION["user_id"];

// subjectを '1yomi' に限定して取得
$sql_mastered = "
    SELECT DISTINCT problem_id 
    FROM answer_record 
    WHERE user_id = :uid AND is_correct = 1 AND subject = '1yomi'
";
$stmt_mastered = $pdo->prepare($sql_mastered);
$stmt_mastered->bindValue(":uid", $user_id, PDO::PARAM_INT);
$stmt_mastered->execute();
$mastered_list = $stmt_mastered->fetchAll(PDO::FETCH_COLUMN);

// 4b) 出題から除外するリストを結合 (★ 新しいセッション変数名を使用)
// リストの値を文字列型に強制変換し、確実に空要素を除外
$exclude_list = array_unique(array_merge($mastered_list, $_SESSION["read1_used_questions"]));

// $exclude_list の中から偽の要素（空文字列、0など）を除外
$exclude_list = array_filter(array_map('strval', $exclude_list));
$exclude_list = array_values($exclude_list); 

// 4c) 未出題の問題をランダム取得
$sql_base = "
    SELECT question_id, question_text 
    FROM kanji
    WHERE question_id LIKE 'KJ0%' 
    AND question_id LIKE '%R'
";

if (!empty($exclude_list)) {
    // 除外リストに含まれない問題のみを選択
    $placeholders = implode(",", array_fill(0, count($exclude_list), "?"));
    $sql_base .= " AND question_id NOT IN ($placeholders) ";
}

$sql_base .= " ORDER BY RAND() LIMIT 1";

$stmt = $pdo->prepare($sql_base);

// 除外リストの値をSQLにバインドして実行
if (!empty($exclude_list)) {
    // $exclude_list に値がある場合のみ、引数として渡す
    $stmt->execute($exclude_list); // ★ 実行 (ライン101に対応)
} else {
    // $exclude_list が空の場合、引数なしで実行
    $stmt->execute();
}

$question = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$question) {
    // 未出題の問題が全て終了した場合の処理
    unset($_SESSION["read1_current_q"]);
    unset($_SESSION["read1_used_questions"]);
    unset($_SESSION["learning_session_id"]);
    session_write_close(); 
    
    die("未出題の問題が尽きました。素晴らしい！"); 
}

$question_id = $question["question_id"];
$question_kanji = $question["question_text"];

// 出題済みに追加 (★ 新しいセッション変数名を使用)
$_SESSION["read1_used_questions"][] = $question_id;

// ===========================================================
// 5) 読みデータ（1件だけ取得 → 文字数を出すため） 
// ===========================================================
$sql2 = "SELECT reading_answer FROM kanji_reading WHERE question_id = :qid LIMIT 1";
$stmt2 = $pdo->prepare($sql2);
$stmt2->bindValue(":qid", $question_id);
$stmt2->execute();
$row = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    // データベース設定に問題がある
    die("読みデータがありません");
}

$correct_answer = $row["reading_answer"];
$correct_length = mb_strlen($correct_answer, "UTF-8");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>漢字クイズ（未出題モード）</title>
<style>
/* --- CSSは省略 --- */
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
        <a href="subject_select.php" class="back-icon">←</a>
    </div>

    <div class="board">
        <div class="kanji"><?php echo htmlspecialchars($question_kanji); ?></div>

        <div class="yellow-boxes">
            <?php for ($i = 0; $i < $correct_length; $i++): ?>
                <div class="yellow-box"></div>
            <?php endfor; ?>
        </div>
    </div>

    <form method="post" class="input-container" action="un_1read_result.php">
        <input type="text" name="answer" placeholder="にゅうりょくしてね" required>
        <br>

        <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">

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
