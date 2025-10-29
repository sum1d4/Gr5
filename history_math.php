<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>かこのもんだい</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="background">
<header>
<button class="back-btn" onclick="history.back()">←</button>
<h1>かこのもんだい</h1>
</header>
<main>
<h2>もんだいきりかえ</h2>
<div class="switch-btns">
<button class="switch active" onclick="switchMode('tashizan')">＋ たしざん</button>
<button class="switch" onclick="switchMode('hikizan')">− ひきざん</button>
</div>
<div id="record-list" class="record-list">

</div>
</main>
</div>
<script>
   
   const historyData = [
     { total: 10, correct: 8, rate: 80 },
     { total: 10, correct: 9, rate: 90 },
     { total: 10, correct: 10, rate: 100 },
     { total: 20, correct: 15, rate: 75 },
     { total: 5, correct: 3, rate: 60 }
   ];
   
   function renderHistory(data) {
     const list = document.getElementById("record-list");
     list.innerHTML = ""; 
     data.forEach(item => {
       const div = document.createElement("div");
       div.classList.add("record");
       div.innerHTML = `
<p><span class="highlight">${item.total}</span>もんだい　
<span class="highlight">${item.correct}</span>せいかい</p>
<p>せいとうりつ：<span class="highlight">${item.rate}%</span></p>
<a href="#" class="detail-link">しょうさいを見る</a>
       `;
       list.appendChild(div);
     });
   }
   // ボタン切り替え（今は見た目だけ
   function switchMode(mode) {
     const buttons = document.querySelectorAll(".switch");
     buttons.forEach(btn => btn.classList.remove("active"));
     if (mode === "tashizan") {
       buttons[0].classList.add("active");
     } else {
       buttons[1].classList.add("active");
     }
     
     renderHistory(historyData);
   }
   
   renderHistory(historyData);
</script>
</body>
</html>