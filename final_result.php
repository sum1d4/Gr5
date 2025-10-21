<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>最終画面</title>

<div class="container">
  <h1>おわりー！</h1>
  <p class="correct">せいかいのかずは....</p>

  <?php
    // 正解数と総問題数をGETパラメータから取得
    $total_questions = $_GET['total'] ?? 10;
    $is_correct = $_GET['correct'] ?? 0;
  ?>

  <div class="score-box">
    // 正解数と総問題数を表示
    <?php echo $total_questions . "のうち " . $is_correct . "もん！"; ?>
  </div>
  <br>
    // homeに戻るボタン
    <a href="Home.php" class="button">よくできました！</a>

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
  }

  .button:hover {
    transform: scale(1.05);
  }

</style>
</div>

</body>
</html>


