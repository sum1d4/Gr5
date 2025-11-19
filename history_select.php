<?php
// subject_select.php

// 1. セッションを開始（必須）
session_start();

// 2. ログイン状態の確認（ユーザーIDがなければログインページへリダイレクト）
if (!isset($_SESSION['user_id'])) {
    header('Location: Rogin.php');
    exit;
}

// 3. セッションからユーザー情報を取得
$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'ゲスト', ENT_QUOTES, 'UTF-8');
$user_grade = htmlspecialchars($_SESSION['user_grade'] ?? '不明', ENT_QUOTES, 'UTF-8');

// 💡 注意: 画面の見た目を変更しないというご要望に従い、表示のためのHTMLタグは追加していません。
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>きょうかをせんたく</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@500;700&display=swap');

body {
    font-family: 'M PLUS Rounded 1c', "Hiragino Kaku Gothic ProN", sans-serif;
    text-align: center;
    /* 背景をシンプルで明るいグラデーションに */
     background: linear-gradient(to bottom, #b3e5fc, #81d4fa);
    margin: 0;
    min-height: 100vh;
    position: relative;
    color: #333;
}

.container {
    padding-top: 100px;
    max-width: 400px;
    margin: 0 auto;
}

h1 {
    font-size: 24px;
    color: #007bff; /* メインカラーの青 */
    margin-bottom: 50px;
    font-weight: 700;
    padding: 10px 0;
    border-bottom: 2px solid #e0e0e0;
}

.subject-btn {
    display: block;
    width: 250px; /* 少し大きくして押しやすく */
    margin: 20px auto;
    padding: 15px 0;
    border: none;
    border-radius: 10px; /* 控えめな角丸 */
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    /* 控えめな影で立体感を出す */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background-color 0.2s, transform 0.1s;
}

.subject-btn:hover {
    opacity: 0.9;
}

.subject-btn:active {
    transform: translateY(2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.kanji {
    /* 明るいピンク */
    background-color: #ffb3b3;
}

.keisan {
    /* メインカラーの青 */
    background-color: #00bfff;
}

/* フォームのスタイルをリセットし、ボタンデザインを適用 */
form {
    margin: 0;
    padding: 0;
}

.back-btn {
    position: absolute;
    top: 25px;
    left: 25px;
    font-size: 28px;
    color: #007bff;
    text-decoration: none;
    transition: color 0.2s;
}

.back-btn:hover {
    color: #0056b3;
}

.home-bar {
    position: absolute;
    bottom: 20px;
    left: 0;
    right: 0;
    text-align: center;
    color: #aaa;
    font-size: 14px;
}
</style>
</head>
<body>

<a href="index.php" class="back-btn">← ホームへ</a>

<div class="container">
    <h1>きょうかをせんたく</h1>

    <form action="history_kanji.php" method="get">
        <button type="submit" class="subject-btn kanji">📖 かんじ ✏️</button>
    </form>

    <form action="history_math.php" method="get">
        <button type="submit" class="subject-btn keisan">➕ けいさん ➖</button>
    </form>
</div>

</body>
</html>
