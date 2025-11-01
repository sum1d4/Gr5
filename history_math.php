
     
<meta charset="UTF-8">
<title>算数履歴</title>
<style>
  body {
    font-family: "Hiragino Kaku Gothic ProN", sans-serif;
    background: linear-gradient(#b9e3ff, #ffffff);
    margin: 0;
    padding: 0;
  }

  .header {
    display: flex;
    align-items: center;
    padding: 10px 20px;
  }

  .back-btn {
    text-decoration: none;
    font-size: 24px;
    color: #007bff;
    margin-right: 10px;
  }

  h1 {
    font-size: 22px;
    margin: 0;
    color: #333;
  }

  .content {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    width: 280px;
    margin: 20px auto;
    padding: 20px;
  }

  .switch-buttons {
    display: flex;
    justify-content: center;
    margin-bottom: 15px;
  }

  .switch-buttons button {
    background: #d8eaff;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    padding: 8px 16px;
    margin: 0 5px;
    cursor: pointer;
    box-shadow: 0 2px 0 rgba(0,0,0,0.2);
  }

  .switch-buttons button:hover {
    transform: scale(1.05);
  }

  .record {
    border-bottom: 1px solid #ccc;
    padding: 10px 0;
    text-align: left;
  }

  .record:last-child {
    border-bottom: none;
  }

  .record .title {
    font-weight: bold;
    color: #e33;
  }

  .record .result {
    font-size: 14px;
    margin: 3px 0;
  }

  .record .detail {
    font-size: 14px;
    color: #007bff;
    text-decoration: none;
  }

  .record .detail:hover {
    text-decoration: underline;
  }

  /* スクロール可能に */
  .scroll-area {
    max-height: 320px;
    overflow-y: auto;
  }
</style>
</head>
<body>

<div class="header">
  <a href="index.php" class="back-btn">←</a>
  <h1>かこのもんだい</h1>
</div>

<div class="content">
  <div class="switch-buttons">
    <button>＋ たしざん</button>
    <button>－ ひきざん</button>
  </div>

  <div class="scroll-area">
    <?php
      // PHPでサンプルデータを用意
      $records = [
        ['correct' => 8, 'total' => 10],
        ['correct' => 9, 'total' => 10],
        ['correct' => 10, 'total' => 10],
      ];

      foreach ($records as $r) {
        $rate = round(($r['correct'] / $r['total']) * 100);
        echo "<div class='record'>
                <div class='title'>{$r['total']}もんだい {$r['correct']} せいかい</div>
                <div class='result'>せいとうりつ：{$rate}％</div>
                <a href='#' class='detail'>しょうさいを見る</a>
              </div>";
      }
    ?>
  </div>
</div>
</head>
</html>

