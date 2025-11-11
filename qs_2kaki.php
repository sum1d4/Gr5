<?php
// 1. 問題データの設定 (現在は仮データを使用)
$correct_answer = "新"; 
$choices = ["新", "親"]; 

// 2. 問題情報をセッションに保存
session_start();
$_SESSION['correct_answer'] = $correct_answer;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>漢字選択問題</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .quiz-container { 
            max-width: 500px; 
            margin: 50px auto; 
            padding: 40px 20px 20px; 
            border: 1px solid #ccc; 
            position: relative; 
        }
        /* 戻るボタン */
        .back-button { 
            position: absolute; 
            top: 10px;        
            left: 10px;       
            font-size: 24px; 
            padding: 5px; 
            border: 1px solid #00f; 
            border-radius: 5px; 
            text-decoration: none; 
            background-color: white; 
            color: #00f;
            z-index: 10; 
        }
        /* 問題ボックス（黒板をイメージ） */
        .question-box { 
            background-color: #046307; 
            color: white; 
            padding: 50px 20px; 
            margin-bottom: 20px; 
            border-radius: 10px; 
        }
        /* 疑問符のエリア */
        .placeholder { background-color: white; width: 100px; height: 100px; line-height: 100px; font-size: 40px; border-radius: 10px; display: inline-block; margin-bottom: 15px; }
        /* 読み仮名 */
        .reading-text { font-size: 30px; margin-bottom: 30px; }
        
        /* 選択肢コンテナ */
        .choices-container { 
            display: block; 
            margin-top: 20px;
        }
        
        /* 個別の選択肢コンテナ: 上下のマージンを大きくして縦長の印象を強調 */
        .choice-item {
            display: inline-block; 
            margin: 40px 15px; /* 上下 40px、左右 15px */
        }

        /* ボタン自体 */
        .choice-button { 
            background-color: white; 
            border: 2px solid #ccc; 
            padding: 20px 30px; 
            font-size: 30px; 
            border-radius: 10px; 
            cursor: pointer; 
            transition: background-color 0.2s;
            display: block; 
        }
        .choice-button:hover {
             background-color: #f0f0f0;
        }
    </style>
</head>
<body>

<div class="quiz-container">
    <a href="subject_select.php" class="back-button">←</a>

    <div class="question-box">
        <div class="placeholder">?</div>
        <div class="reading-text"><span style="font-size: 1.5em; color: yellow;">あたら</span></div>
    </div>
    
    <h2><?php echo htmlspecialchars("ただしいのはどっち？"); ?></h2>

    <form action="qs_2kaki_result.php" method="POST" class="choices-container">
        
        <?php foreach ($choices as $index => $choice): ?>
            <div class="choice-item">
                <input type="radio" id="choice_<?php echo $index; ?>" name="selected_answer" value="<?php echo htmlspecialchars($choice); ?>" style="display: none;" required>
                
                <label for="choice_<?php echo $index; ?>" class="choice-button">
                    <?php echo htmlspecialchars($choice); ?>
                </label>
            </div>
        <?php endforeach; ?>
        
        <input type="submit" value="決定" style="display: none;">
    </form>
</div>

<script>
    // 選択肢（ラベル）をクリックしたらフォームを送信する
    document.querySelectorAll('.choice-button').forEach(button => {
        button.addEventListener('click', function() {
            // 対応するラジオボタンを選択状態にする
            const radioId = this.getAttribute('for');
            document.getElementById(radioId).checked = true;
            
            // フォームを送信
            this.closest('form').submit();
        });
    });
</script>

</body>
</html>
