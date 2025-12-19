<?php
// math_question_1hiki.php
session_start();

// ------------------------------------------
// 問題のランダム生成
// ------------------------------------------
$num1 = rand(5, 9); 
$num2 = rand(1, $num1 - 1); 
$operator = '-';
$question = "{$num1} {$operator} {$num2}";
$correct_answer = $num1 - $num2; 
// ------------------------------------------

// 現在の問題番号を取得
$current_question_num = isset($_GET['q']) ? (int)$_GET['q'] : 1;

// ★重要: 1問目の場合、正解数を0にし、開始時間を記録する
if ($current_question_num === 1) {
    $_SESSION['correct_count'] = 0;
    $_SESSION['start_time'] = date('Y-m-d H:i:s'); // 開始時刻
}

// 画面遷移先設定
$result_page = 'math_result_1hiki.php';
$back_page = 'subject_select.php'; // 戻るボタンの先（ファイルがなければ#でOK）
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>計算問題 - 入力画面 | Learn+</title>
    <style>
        /* CSSスタイル（変更なし） */
        body { background: linear-gradient(to bottom, #b3e5fc, #81d4fa); font-family: 'Inter', sans-serif; display: flex; flex-direction: column; align-items: center; margin: 0; padding-top: 50px; min-height: 100vh; }
        .main-content { width: 90%; max-width: 380px; display: flex; flex-direction: column; align-items: center; }
        .header-controls { width: 100%; max-width: 380px; text-align: left; margin-bottom: 5px; }
        .back-button { background-color: #666; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 1.5em; display: flex; justify-content: center; align-items: center; }
        .question-num { width: 100%; font-size: 1.1em; color: #2e7d32; font-weight: bold; margin-bottom: 15px; text-align: center; }
        .question-container { width: 95%; margin: 0 auto 20px auto; border: 2px solid #66bb6a; padding: 10px; background-color: white; border-radius: 12px; }
        .question-box { background-color: #4caf50; color: white; padding: 20px 10px; border-radius: 8px; margin-bottom: 15px; font-size: 3em; font-weight: 700; text-align: center; }
        .answer-area { text-align: center; }
        #user_answer { width: 150px; height: 55px; text-align: right; font-size: 2.5em; font-weight: 700; border: 3px solid #1565c0; border-radius: 8px; padding: 5px 10px; background-color: #e3f2fd; color: #1565c0; outline: none; }
        .keypad-container { width: 100%; max-width: 380px; margin: 0 auto; padding: 15px; background-color: white; border-radius: 12px; border: 1px solid #ccc; }
        .keypad { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .keypad button { background-color: #e0e0e0; border: none; height: 70px; font-size: 1.8em; font-weight: 700; cursor: pointer; border-radius: 10px; }
        .backspace-button-row { grid-column: span 1; background-color: #ff9800 !important; color: white !important; font-size: 1.2em !important; }
        .submit-button-row { grid-column: span 1; background-color: #42a5f5 !important; color: white !important; font-size: 1.5em !important; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header-controls">
            <button class="back-button" onclick="location.href='<?php echo htmlspecialchars($back_page); ?>'">←</button>
        </div>
        <p class="question-num"> <?php echo $current_question_num; ?> もん/ 10 もん</p>
        
        <div class="question-container">
            <div class="question-box">
                <?php echo htmlspecialchars($question); ?> =
            </div>
            <div class="answer-area">
                <input type="text" id="user_answer" name="user_answer" readonly maxlength="3">
            </div>
        </div>

        <div class="keypad-container">
            <form id="mathForm" action="<?php echo htmlspecialchars($result_page); ?>" method="post">
                <input type="hidden" name="user_answer" id="hidden_user_answer">
                <input type="hidden" name="question" value="<?php echo htmlspecialchars($question); ?>">
                <input type="hidden" name="correct_answer" value="<?php echo htmlspecialchars($correct_answer); ?>">
                <input type="hidden" name="current_question_num" value="<?php echo $current_question_num; ?>">

                <div class="keypad">
                    <button type="button" onclick="inputNumber(1)">1</button>
                    <button type="button" onclick="inputNumber(2)">2</button>
                    <button type="button" onclick="inputNumber(3)">3</button>
                    <button type="button" onclick="inputNumber(4)">4</button>
                    <button type="button" onclick="inputNumber(5)">5</button>
                    <button type="button" onclick="inputNumber(6)">6</button>
                    <button type="button" onclick="inputNumber(7)">7</button>
                    <button type="button" onclick="inputNumber(8)">8</button>
                    <button type="button" onclick="inputNumber(9)">9</button>
                    <button type="button" class="backspace-button-row" onclick="backspace()">もどる</button> 
                    <button type="button" onclick="inputNumber(0)">0</button>
                    <button type="submit" class="submit-button-row" name="submit_answer">こたえ</button> 
                </div>
            </form>
        </div>
    </div> 
    <script>
        const answerInput = document.getElementById('user_answer');
        const hiddenAnswerInput = document.getElementById('hidden_user_answer');
        function inputNumber(num) { if (answerInput.value.length < 3) answerInput.value += num; }
        function backspace() { answerInput.value = answerInput.value.slice(0, -1); }
        document.getElementById('mathForm').onsubmit = function() {
            hiddenAnswerInput.value = answerInput.value;
            if (hiddenAnswerInput.value === '') { alert('答えを入力してください。'); return false; }
            return true;
        };
    </script>
</body>
</html>
