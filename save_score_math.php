<?php
// 1. データベース設定ファイルの読み込み
// これにより、$pdo 変数に接続済みオブジェクトが格納されます
require_once 'db_config.php';

// ----------------------------------------------------

// セッションを開始
// user_idを取得するために必須
session_start();

header('Content-Type: application/json');

// POSTリクエストかどうか確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed.']);
    exit;
}

// POSTデータ（JSON形式）を受け取り
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data.']);
    exit;
}

// 2. データの取得とバリデーション
// target_age (対象学年), subject (科目), category (カテゴリ) は固定値として設定
$target_age = 1;
$subject = 'hiki'; // ★ 科目を「計算 (calc)」に変更
$category = 'score'; // ★ カテゴリを「1桁引き算 (subtraction_1d)」に変更

// score (正解数)
$score = filter_var($data['score'] ?? null, FILTER_VALIDATE_INT);
if ($score === false || $score < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid score or missing score.']);
    exit;
}

// total_time (プレイ時間)
$total_time = filter_var($data['total_time'] ?? null, FILTER_VALIDATE_INT);
if ($total_time === false || $total_time < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid total_time or missing total_time.']);
    exit;
}

// ユーザーIDをセッションから取得し、INT型としてバリデーション
$user_id = $_SESSION['user_id'] ?? null; 
$user_id = filter_var($user_id, FILTER_VALIDATE_INT);

// user_id がセッションに見つからない、または有効な数値でない場合はエラー
if ($user_id === false || $user_id === null || $user_id <= 0) {
    // 認証されていないユーザーとみなし、401 Unauthorizedまたは400 Bad Requestを返す
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User ID not found in session or invalid. Please log in.']);
    exit;
}


// 3. データベースへの挿入処理 (プリペアドステートメントを使用)
$sql = "INSERT INTO score_attack (user_id, target_age, subject, category, score, total_time) 
        VALUES (:user_id, :target_age, :subject, :category, :score, :total_time)";

try {
    $stmt = $pdo->prepare($sql);

    // バインド
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT); 
    $stmt->bindValue(':target_age', $target_age, PDO::PARAM_INT);
    $stmt->bindValue(':subject', $subject, PDO::PARAM_STR);
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':score', $score, PDO::PARAM_INT);
    $stmt->bindValue(':total_time', $total_time, PDO::PARAM_INT);

    $stmt->execute();

    // 成功応答
    echo json_encode(['success' => true, 'message' => 'Score registered successfully (Math Subtraction 1D).']);

} catch (PDOException $e) {
    // データベース挿入失敗時のエラー処理
    error_log('DB Insert Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database registration failed.']);
}

?>
