<?php
// Rogin.php

// 1. セッションを開始 (必須：ログイン状態を記憶するため)
session_start();

// 2. データベース接続設定ファイルを読み込む
require_once 'db_config.php';

$error_message = '';

// --- POSTリクエスト（フォーム送信）があった場合の処理 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = $_POST['nickname'] ?? '';
    $grade = $_POST['grade'] ?? '';

    if (empty($nickname) || empty($grade)) {
        $error_message = 'ニックネームと がくねん を入力してください。';
    } else {
        
        // ★ データベースでユーザーを検索
        try {
            // 3. SQL文を準備 (プリペアドステートメントで安全に)
            $sql = "SELECT * FROM user WHERE user_name = ? AND user_grade = ?";
            $stmt = $pdo->prepare($sql);
            
            // 4. SQL文を実行
            $stmt->execute([$nickname, $grade]);
            
            // 5. 検索結果を取得
            $user = $stmt->fetch();

            if ($user) {
                // --- ログイン成功 ---
                // 6. セッションにユーザー情報を保存 (★最重要)
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_grade'] = $user['user_grade'];
                
                // 7. ホーム画面にリダイレクト
                header('Location: index.php');
                exit;

            } else {
                // --- ログイン失敗 ---
                $error_message = 'ニックネーム または がくねん が ちがいます。';
            }

        } catch (PDOException $e) {
            $error_message = 'データベース エラー: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ログイン</title>
<!-- (スタイルシートはご自身のstyle.cssを読み込んでください) -->
<style>
    /* (スタイルは元のRogin.phpやRegister.phpのものを参考にしてください) */
    body {
        background: linear-gradient(to bottom, #b3e5fc, #81d4fa);
        font-family: "Arial", sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
    }
    .login-container {
        text-align: center;
    }
    .title { color: #2e7d32; }
    .label { color: #2e7d32; font-size: 1.2rem; display: block; margin: 15px 0 5px; }
    .text-input {
        width: 220px; padding: 10px; border: 2px solid #999;
        border-radius: 10px; font-size: 1rem; text-align: center;
    }
    .decision-button {
        margin-top: 30px; background: linear-gradient(to bottom, #81c784, #4caf50);
        color: white; border: none; border-radius: 10px;
        font-size: 1.2rem; padding: 10px 60px; cursor: pointer;
        box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
    }
    .register-link { margin-top: 20px; }
    .register-button {
        display: inline-flex; align-items: center; text-decoration: none;
        background: #f0f0f0; color: #1565c0; padding: 10px 20px;
        border-radius: 10px; font-size: 1.1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .arrow-icon { font-size: 1.5rem; margin-right: 10px; }
    .error { color: red; font-weight: bold; }
</style>
</head>
<body>
<div class="sky-background">
<div class="login-container">
    <h1 class="title">ログイン</h1>

    <!-- エラーメッセージの表示 -->
    <?php if ($error_message): ?>
        <p class="error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <!-- フォームの送信先を自分自身 (Rogin.php) に設定 -->
    <form action="Rogin.php" method="POST"> 
        <div class="input-group">
            <label for="nickname" class="label">ニックネーム</label>
            <input type="text" id="nickname" name="nickname" placeholder="なまえ" class="text-input" required>
        </div>
        <div class="input-group">
            <label for="grade" class="label">がくねん</label>
            <input type="text" id="grade" name="grade" placeholder="がくねん" class="text-input" required>
        </div>
        <button type="submit" class="button decision-button">けってい</button>
    </form>
    <div class="register-link">
        <a href="Register.php" class="button register-button">
            <span class="arrow-icon">←</span>
            <span class="text">あたらしくとうろく</span>
        </a>
    </div>
</div>
</div>
</body>
</html>
