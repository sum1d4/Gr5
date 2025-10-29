<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>かこのもんだい</title>
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
     gap: 8px;
   }
   .correct {
     color: red;
     font-weight: bold;
   }
   .wrong {
     color: blue;
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
   // ✅ 仮の問題データ（正解: true / 不正解: false）
   const problems = [
     { question: "32+12=49", correct: false },
     { question: "56+14=70", correct: true },
     { question: "20+30=50", correct: true },
     { question: "42+19=60", correct: false },
     { question: "8+9=17", correct: true },
     { question: "15+13=29", correct: false },
   ];
   const list = document.getElementById("problem-list");
   // 履歴を自動で追加
   problems.forEach(p => {
     const div = document.createElement("div");
     div.classList.add("problem");
     const mark = document.createElement("span");
     mark.textContent = p.correct ? "○" : "×";
     mark.classList.add(p.correct ? "correct" : "wrong");
     const text = document.createElement("span");
     text.textContent = p.question;
     div.appendChild(mark);
     div.appendChild(text);
     list.appendChild(div);
   });
</script>
</body>
</html>
