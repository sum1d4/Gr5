<?php
// データベース設定ファイルの読み込み
// 実際の環境に合わせてファイル名を変更してください
require_once 'db_config.php';

// セッションを開始 (user_idを取得するために必須)
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

// データの定義とバリデーション
$target_age = 2;        // ★ 2年生
$subject = 'hiki';      // ★ 科目: ひき算
$category = 'score';    // ★ カテゴリ: スコア

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

// ユーザーIDをセッションから取得
$user_id = $_SESSION['user_id'] ?? null; 
$user_id = filter_var($user_id, FILTER_VALIDATE_INT);

// user_id が無効な場合は認証エラー
if ($user_id === false || $user_id === null || $user_id <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User ID not found in session or invalid. Please log in.']);
    exit;
}


// データベースへの挿入処理
$sql = "INSERT INTO score_attack (user_id, target_age, subject, category, score, total_time) 
        VALUES (:user_id, :target_age, :subject, :category, :score, :total_time)";

try {
    // $pdo は 'db_config.php' で定義されていると仮定
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
    echo json_encode(['success' => true, 'message' => 'Score registered successfully (Hikizan 2D).']);

} catch (PDOException $e) {
    // データベース挿入失敗時のエラー処理
    error_log('DB Insert Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database registration failed.']);
}

?>
