<?php
session_start();

/* -------------------------
   書き問題のセッションをリセット
   ------------------------- */
unset($_SESSION["kaki_current_q"]);
unset($_SESSION["kaki_used_questions"]);
unset($_SESSION["kaki_correct_count"]);
unset($_SESSION["kaki_correct_answer"]);

/* -------------------------
   読み問題のセッションをリセット
   ------------------------- */
unset($_SESSION["yomi_current_q"]);
unset($_SESSION["yomi_used_questions"]);
unset($_SESSION["yomi_correct_count"]);
unset($_SESSION["yomi_correct_answer"]);

/* -------------------------
   学習セッションID（履歴記録用）もリセット
   ------------------------- */
unset($_SESSION["learning_session_id"]);

/* user_id はログイン用なので残す（破棄しない）
--------------------------------------- */

// GETパラメータ取得（正解数など）
$total_questions = $_GET['total'] ?? 10;
$is_correct      = $_GET['correct'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>最終画面</title>

<style>
  body {
    font-family: "Hiragino Kaku Gothic ProN", sans-serif;
    text-align: center;
    background: linear-gradient(#b9e3ff, #ffffff);
    height: 100vh;
    margin: 0;
  }

  .container {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    width: 250px;
    margin: 60px auto;
    padding: 20px;
  }

  h1 {
    font-size: 24px;
    color: #333;
    margin-bottom: 10px;
  }

  .correct {
    color: red;
    font-size: 20px;
    margin: 10px 0;
  }

  .score-box {
    background: #f9f9f9;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    font-size: 18px;
    display: inline-block;
    margin-bottom: 15px;
  }

  .button {
    display: inline-block;
    background: linear-gradient(#ffe45c, #f8c20e);
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 18px;
    font-weight: bold;
    color: #555;
    box-shadow: 0 3px 0 #d9a400;
    cursor: pointer;
    transition: 0.2s;
    text-decoration: none;
  }

  .button:hover {
    transform: scale(1.05);
  }
</style>
</head>
<body>

<div class="container">
  <h1>おわりー！</h1>
  <p class="correct">せいかいのかずは....</p>

  <div class="score-box">
    <?php echo $total_questions . "のうち " . $is_correct . "もん！"; ?>
  </div>

  <br>

  <a href="index.php" class="button">よくできました！</a>
</div>

</body>
</html>
