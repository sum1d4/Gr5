<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>きょうかをせんたく</title>
<style>
  body {
    font-family: "Hiragino Kaku Gothic ProN", sans-serif;
    text-align: center;
    background: linear-gradient(#b9e3ff, #ffffff);
    margin: 0;
    height: 100vh;
    position: relative;
  }

  .container {
    margin-top: 80px;
  }

  h1 {
    font-size: 22px;
    color: #333;
    margin-bottom: 40px;
  }

  .subject-btn {
    display: block;
    width: 180px;
    margin: 15px auto;
    padding: 12px;
    border: none;
    border-radius: 15px;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    cursor: pointer;
    box-shadow: 0 4px 0 rgba(0,0,0,0.2);
    transition: 0.2s;
  }

  .subject-btn:hover {
    transform: scale(1.05);
  }

  .kanji {
    background: #ff9da4;
  }

  .keisan {
    background: #77b9ff;
  }

  .back-btn {
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 24px;
    color: #007bff;
    text-decoration: none;
  }

  .home-bar {
    position: absolute;
    bottom: 10px;
    left: 0;
    right: 0;
    text-align: center;
    color: gray;
    font-size: 14px;
  }
</style>
</head>
<body>

<!-- 戻るボタン -->
<a href="index.php" class="back-btn">←</a>

<div class="container">
  <h1>きょうかをせんたく</h1>

  <!-- 教科ボタン -->
  <form action="history_kanji.php" method="get">
    <button type="submit" class="subject-btn kanji">📖 かんじ ✏️</button>
  </form>

  <form action="history_math.php" method="get">
    <button type="submit" class="subject-btn keisan">➕ けいさん ➖</button>
  </form>
</div>

<!-- ホームバー -->
<div class="home-bar">🏠 ホーム</div>

</body>
</html>
