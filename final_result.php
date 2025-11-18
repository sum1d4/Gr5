<?php
session_start();
require_once "db_config.php";

/* -------------------------
   GETパラメータの取得
------------------------- */
$total_questions = $_GET['total'] ?? 10;
$correct         = $_GET['correct'] ?? 0;

/* -------------------------
   1) learning_session を更新
------------------------- */
if (!empty($_SESSION["learning_session_id"])) {

    $sql = "UPDATE learning_session
            SET correct_count = :correct,
                end_time = NOW(),
                total_questions = :total
            WHERE session_id = :sid";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":correct" => $correct,
        ":total"   => $total_questions,
        ":sid"     => $_SESSION["learning_session_id"],
    ]);
}

/* -------------------------
   2) セッションリセット
      ※ learning_session_id は最後に消す！
------------------------- */
unset($_SESSION["kaki_current_q"]);
unset($_SESSION["kaki_used_questions"]);
unset($_SESSION["kaki_correct_count"]);
unset($_SESSION["kaki_correct_answer"]);

unset($_SESSION["yomi_current_q"]);
unset($_SESSION["yomi_used_questions"]);
unset($_SESSION["yomi_correct_count"]);
unset($_SESSION["yomi_correct_answer"]);

unset($_SESSION["learning_session_id"]); // ← 最後に消す！

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
    <?= $total_questions . "のうち " . $correct . "もん！"; ?>
  </div>

  <br>

  <a href="index.php" class="button">よくできました！</a>
</div>

</body>
</html>
