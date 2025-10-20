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
    $correct = $_GET['correct'] ?? 0;
  ?>

  <div class="score-box">
    <?php echo $total_questions . "のうち " . $correct . "もん！"; ?>
  </div>
  <br>// homeに戻るボタン
    <a href="Home.php" class="button">よくできました！</a>
</div>

</body>
</html>


