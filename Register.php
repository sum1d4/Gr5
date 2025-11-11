<?php
// Register.php

// 1. セッションを開始 (エラーメッセージ表示のため)
session_start();

// 2. データベース接続設定ファイルを読み込む
// (このファイルと同じ場所に db_config.php があることを確認してください)
require_once 'db_config.php'; 

$error_message = '';
$success_message = '';

// --- POSTリクエスト（フォーム送信）があった場合の処理 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'] ?? '';
    $user_grade = $_POST['user_grade'] ?? '';

    // 3. バリデーション
    if (empty($user_name) || empty($user_grade)) {
        $error_message = 'ニックネームと がくねん の両方を入力してください。';
    } elseif (!in_array($user_grade, ['1', '2'])) {
        $error_message = 'がくねん は「1」か「2」を入力してください。';
    } else {
        
        // ★ 4. データベースへの挿入処理
        try {
            // 4a. ユーザーIDを生成 (簡易的なランダム文字列)
            $user_id = substr(bin2hex(random_bytes(4)), 0, 8);

            // 4b. SQL文を準備 (プリペアドステートメント)
            $sql_insert = "INSERT INTO user (user_id, user_name, user_grade) VALUES (?, ?, ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            
            // 4c. SQL文を実行
            $stmt_insert->execute([$user_id, $user_name, $user_grade]);
            
            $success_message = 'とうろく が かんりょうしました！ ログインがめんに もどってください。';

        } catch (PDOException $e) {
            // 4d. SQLエラーが発生した場合の処理
            if ($e->getCode() == 23000) { // 一意制約違反 (ユーザー名が重複した場合)
                // (user_name に UNIQUE 制約を追加した場合に機能します)
                $error_message = 'そのニックネームは すでに つかわれています。';
            } else {
                error_log('登録エラー: ' . $e->getMessage());
                $error_message = 'データベース エラーです。とうろく できませんでした。';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>あたらしく とうろく | Learn+</title>
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- 基本設定 --- */
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Noto Sans JP', 'Inter', sans-serif;
            background: linear-gradient(170deg, #b3e5fc, #81d4fa);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        /* --- コンテナ --- */
        .register-container {
            background-color: white;
            padding: 30px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 320px;
        }

        /* --- タイトル --- */
        .title {
            color: #0277bd; /* 濃い青 */
            font-size: 1.8rem; /* 28px */
            font-weight: 700;
            margin: 0 0 25px 0;
        }

        /* --- フォームのレイアウト --- */
        .form-group {
            margin-bottom: 20px; /* 各入力欄の間のスペース */
            width: 100%;
        }

        .label {
            color: #0277bd; /* 濃い青 */
            font-size: 1.1rem; /* 18px */
            font-weight: 700;
            display: block; /* ブロック要素にして改行 */
            margin-bottom: 8px; /* ラベルと入力欄の間のスペース */
        }

        .text-input {
            width: 90%;
            padding: 12px 15px;
            border: 2px solid #b0bec5; /* やや濃いグレーの枠線 */
            border-radius: 10px;
            font-size: 1.1rem;
            text-align: center;
            box-sizing: border-box; /* paddingを含めて幅を計算 */
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .text-input:focus {
            border-color: #0288d1; /* フォーカス時に青く */
            box-shadow: 0 0 8px rgba(2,136,209,0.2);
            outline: none;
        }

        /* --- ボタン --- */
        .button {
            cursor: pointer;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            width: 100%; /* 幅を統一 */
            box-sizing: border-box;
        }

        .register-button {
            background: linear-gradient(to bottom, #81c784, #4caf50); /* 緑 */
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }
        .register-button:hover {
            background: linear-gradient(to bottom, #66bb6a, #43a047);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .back-link {
            display: inline-flex; /* アイコンとテキストを横並び */
            align-items: center;
            justify-content: center;
            gap: 8px; /* アイコンとテキストの間 */
            background: #f0f0f0;
            color: #1565c0; /* 青 */
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .back-link:hover {
            background-color: #e0e0e0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* --- メッセージ --- */
        .message {
            font-weight: 700;
            padding: 10px;
            border-radius: 8px;
            margin: 0 0 15px 0;
            font-size: 0.95rem;
        }
        .error {
            background-color: #ffebee;
            color: #c62828; /* 濃い赤 */
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32; /* 濃い緑 */
        }
        
    </style>
</head>
<body>
    <div class="register-container">
        <h1 class="title">あたらしく とうろく</h1>

        <!-- エラーまたは成功メッセージの表示 -->
        <?php if ($error_message): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <p class="message success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <!-- フォームの送信先を自分自身 (Register.php) に設定 -->
        <form action="Register.php" method="POST">
            
            <!-- ニックネーム入力 (縦並び) -->
            <div class="form-group">
                <label for="user_name" class="label">ニックネーム</label>
                <input type="text" id="user_name" name="user_name" placeholder="なまえ" class="text-input" required>
            </div>
            
            <!-- 学年入力 (縦並び) -->
            <div class="form-group">
                <label for="user_grade" class="label">がくねん</label>
                <input type="text" id="user_grade" name="user_grade" placeholder="1 または 2" class="text-input" required>
            </div>

            <!-- 登録ボタンはフォーム内に配置 -->
            <button type="submit" class="button register-button">とうろく する</button>
        </form>
        
        <a href="Rogin.php" class="button back-link">
            <span>←</span>
            <span>ログインがめんに もどる</span>
        </a>
    </div>
</body>
</html>
