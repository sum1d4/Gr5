<?php
// 問題のランダム生成: 2桁同士の足し算 (前回のコードと同じ)
$num1 = rand(10, 99);
$num2 = rand(10, 99);
$operator = '+';

$question = "{$num1} {$operator} {$num2}";
$correct_answer = $num1 + $num2; 

// 現在の問題番号を取得 (GETリクエストから。無ければ1問目とする)
$current_question_num = isset($_GET['q']) ? (int)$_GET['q'] : 1;

// 画面遷移先
$result_page = 'math_result.php';
$back_page = 'subject_select.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>計算問題 - 入力画面 (第<?php echo $current_question_num; ?>問)</title>
    <style>
        /* 共通スタイル */
        body {
            font-family: 'Arial', sans-serif;
            /* 中央寄せを強化: body全体を中央に配置 */
            display: flex;
            flex-direction: column;
            align-items: center; /* 子要素を水平方向に中央揃え */
            margin-top: 50px;
            background-color: #f0f0f0;
            min-width: 380px; /* 最小幅を設定してレイアウト崩れを防ぐ */
        }
        
        /* メインコンテンツを内包するコンテナを新設し、中央寄せを確実にする */
        .main-content {
            width: 320px; /* 全体の幅を固定 */
            display: flex;
            flex-direction: column;
            align-items: center; /* 子要素を水平方向に中央揃え */
        }

        /* ------------------- ① 戻るボタンエリア ------------------- */
        .header-controls {
            width: 100%; /* 親要素(main-content)の幅いっぱい */
            max-width: 320px;
            text-align: left;
            margin-bottom: 5px; /* マージンを調整 */
        }
        .header-controls .back-button {
            background-color: #666;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.5em;
            line-height: 40px;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 10px; /* 画面の左端に寄せるための調整 */
        }
        
        .question-num {
            width: 100%;
            font-size: 0.8em;
            color: #555;
            margin-bottom: 10px;
            text-align: left; /* 問題番号は左揃えに戻す */
            padding-left: 10px;
            box-sizing: border-box;
        }


        /* ------------------- ② 問題と入力エリアのコンテナ ------------------- */
        .question-container {
            width: 300px; /* 固定幅 */
            margin: 0 auto 20px auto; /* 上下にマージン、左右は中央寄せ */
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            /* 中央寄せのためにdisplay: blockにする必要は通常ないが、flexの親で中央寄せしているため維持 */
        }
        
        .question-box {
            background-color: #38761d;
            color: white;
            padding: 15px 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 2.5em; 
            font-weight: bold;
            text-align: center;
        }
        
        /* ③ 解答入力テキストボックス */
        .answer-area {
            text-align: center;
        }
        #user_answer {
            width: 120px; 
            height: 40px;
            text-align: right;
            font-size: 2em;
            font-weight: bold;
            border: 3px solid #ccc;
            padding: 5px;
            background-color: white;
            box-sizing: border-box;
            margin: 0 auto;
            display: block; /* 念のためブロック要素にして中央寄せを確実にする */
        }

        /* ------------------- テンキーエリア (④〜⑮) ------------------- */
        .keypad-container {
            width: 320px; /* 親要素の幅に合わせて修正 */
            margin: 0 auto; /* 中央寄せ */
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
            width: 300px; /* テンキー内部の幅を調整 */
            margin: 0 auto; /* テンキー自体を中央寄せ */
        }
        .keypad button {
            background-color: #eee;
            border: 1px solid #ccc;
            padding: 15px 0;
            font-size: 1.3em;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.1s;
            height: 60px;
        }
        .keypad button:active {
            background-color: #ddd;
        }
        
        /* ひとつもどる (⑬) と こたえあわせ (⑮) の行 */
        /* 3列で構成する */
        .backspace-button-row {
             grid-column: span 1; 
             background-color: #f79646; 
             color: black; 
             font-size: 0.9em;
        }
        .submit-button-row {
            grid-column: span 1; /* 1列幅に変更して3列構成を維持 */
            background-color: #215e91; 
            color: white;
            font-size: 1.1em;
            font-weight: bold;
        }

    </style>
</head>
<body>

    <div class="main-content">

        <div class="header-controls">
            <button class="back-button" onclick="location.href='<?php echo htmlspecialchars($back_page); ?>'">←</button>
        </div>

        <p class="question-num"> <?php echo $current_question_num; ?> もん/  10 もん</p>
        
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

                    <button type="button" onclick="inputNumber(0)">0</button>
                    <button type="button" class="backspace-button-row" onclick="backspace()">もどる</button> 
                    <button type="submit" class="submit-button-row" name="submit_answer">こたえあわせ</button> 
                </div>
            </form>
        </div>
    </div> <script>
        const MAX_LENGTH = 3;
        const answerInput = document.getElementById('user_answer');
        const hiddenAnswerInput = document.getElementById('hidden_user_answer');

        function inputNumber(num) {
            if (answerInput.value.length < MAX_LENGTH) {
                answerInput.value += num;
            }
        }

        function backspace() {
            answerInput.value = answerInput.value.slice(0, -1);
        }

        document.getElementById('mathForm').onsubmit = function() {
            hiddenAnswerInput.value = answerInput.value;
            
            if (hiddenAnswerInput.value === '') {
                 alert('答えを入力してください。');
                 return false;
            }
            return true;
        };
    </script>

</body>
</html>
