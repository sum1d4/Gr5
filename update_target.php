<?php
session_start();
require_once "db_config.php"; // データベース接続

// 1. ログインしていない場合はログイン画面へ
if (!isset($_SESSION["user_id"])) {
    header("Location: Rogin.php"); // ※ここもRogin.phpに合わせています
    exit;
}

// 2. 直接URLを叩かれた場合などはindexへ戻す
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// フォームから送られてきたデータを受け取る
$new_target = filter_input(INPUT_POST, "target_questions", FILTER_VALIDATE_INT);
// どのページから来たか（戻るため）
$from_page = filter_input(INPUT_POST, "from_page", FILTER_SANITIZE_STRING);

// もし戻るページが空なら index.php にする
if (!$from_page) {
    $from_page = "index.php";
}

// 3. データが正しい数値かチェック
if ($new_target === false || $new_target <= 0) {
    // 変な数値なら更新せずに元のページへ戻す
    header("Location: " . $from_page);
    exit;
}

try {
    // --- DB更新処理 ---

    // まず、このユーザーの目標データがすでにあるか確認
    $sql_check = "SELECT target_id FROM target WHERE user_id = :uid LIMIT 1";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindValue(":uid", $user_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $exists = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        // A. データがあるなら → 更新 (UPDATE)
        $sql = "UPDATE target 
                SET target_questions = :tq, created_at = NOW() 
                WHERE user_id = :uid";
    } else {
        // B. データがないなら → 新規作成 (INSERT)
        $sql = "INSERT INTO target (user_id, target_questions, created_at) 
                VALUES (:uid, :tq, NOW())";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":tq", $new_target, PDO::PARAM_INT);
    $stmt->bindValue(":uid", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // 4. 元のページに戻る（これで画面が切り替わったように見えません）
    header("Location: " . $from_page);
    exit;

} catch (PDOException $e) {
    exit("データベースエラー: " . $e->getMessage());
}
?>