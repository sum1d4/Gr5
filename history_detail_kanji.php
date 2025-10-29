<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>かこのもんだい（漢字）</title>
<style>
   body {
     margin: 0;
     font-family: "Hiragino Maru Gothic ProN", sans-serif;
     background: linear-gradient(#bce0ff, #e9f7ff);
     display: flex;
     flex-direction: column;
     align-items: center;
     min-height: 100vh;
   }
   header {
     width: 100%;
     display: flex;
     align-items: center;
     padding: 10px;
     background: transparent;
   }
   header h1 {
     flex: 1;
     text-align: center;
     font-size: 24px;
     color: #333;
   }
   .back-btn {
     background: none;
     border: none;
     font-size: 24px;
     cursor: pointer;
     color: #007aff;
   }
   main {
     width: 90%;
     max-width: 400px;
     background: white;
     border-radius: 15px;
     padding: 15px;
     box-shadow: 0 4px 10px rgba(0,0,0,0.1);
     overflow-y: auto;
     max-height: 70vh;
   }
   .problem {
     font-size: 20px;
     padding: 10px;
     border-bottom: 1px solid #ddd;
     display: flex;
     align-items: center;
     justify-content: space-between;
   }
   .left {
     display: flex;
     align-items: center;
     gap: 10px;
   }
   .mark {
     font-size: 22px;
     font-weight: bold;
   }
   .correct {
     color: red;
   }
   .wrong {
     color: blue;
   }
   .kanji {
     border: 1px solid #999;
     border-radius: 50%;
     width: 28px;
     height: 28px;
     text-align: center;
     line-height: 28px;
     font-weight: bold;
     background: #f8f8f8;
   }
   .yomi {
     color: goldenrod;
     font-weight: bold;
   }
</style>
</head>
<body>
<header>
<button class="back-btn" onclick="history.back()">←</button>
<h1>かこのもんだい</h1>
</header>
<main id="problem-list">
<!-- JavaScriptで追加される -->
</main>
<script>
   // ✅ 漢字問題データ（正誤: true/false）
   const problems = [
     { kanji: "親", yomi: "あたらしい", correct: false },
     { kanji: "新", yomi: "あたらしい", correct: true },
     { kanji: "週", yomi: "しゅう", correct: false },
     { kanji: "週", yomi: "しゅう", correct: true },
   ];
   const list = document.getElementById("problem-list");
   // データを繰り返し追加
   problems.forEach(p => {
     const div = document.createElement("div");
     div.classList.add("problem");
     const left = document.createElement("div");
     left.classList.add("left");
     const mark = document.createElement("span");
     mark.textContent = p.correct ? "○" : "×";
     mark.classList.add("mark", p.correct ? "correct" : "wrong");
     const kanji = document.createElement("span");
     kanji.classList.add("kanji");
     kanji.textContent = p.kanji;
     const yomi = document.createElement("span");
     yomi.classList.add("yomi");
     yomi.textContent = p.yomi;
     left.appendChild(mark);
     left.appendChild(kanji);
     left.appendChild(yomi);
     div.appendChild(left);
     list.appendChild(div);
   });
</script>
</body>
</html
