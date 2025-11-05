<?php
// モード選択画面 (けいさん用を想定)

// クエリパラメータから学年と教科を受け取る (subject_select.phpからのリダイレクトを想定)
$selected_grade = '';
$selected_subject = '';

// GETリクエストから 'grade' と 'subject' を取得
if (isset($_GET['grade'])) {
    // サニタイズ
    $selected_grade = htmlspecialchars($_GET['grade']);
}
if (isset($_GET['subject'])) {
    // サニタイズ
    $selected_subject = htmlspecialchars($_GET['subject']);
}

// 戻るボタンのリンク先を subject_select.php に設定
$back_url = "subject_select.php";

// ホームボタンのリンク先
$home_url = "index.php"; 

// 共通のクエリパラメータ文字列を生成
$query_params = "grade={$selected_grade}&subject={$selected_subject}";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>モード選択 | Learn+</title>
    <style>
        body {
            /* 統一された背景 */
            background: linear-gradient(to bottom, #b3e5fc, #81d4fa); 
            font-family: Arial, sans-serif;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 50px;
            min-height: 100vh;
            margin: 0;
        }
        .mode-container {
            width: 350px;
            max-width: 90%;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 25px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        h3 {
            margin-top: 5px;
            margin-bottom: 25px; 
            color: #2e7d32; /* 統一された見出し色 */
            font-size: 20px;
        }
        
        /* 目標・現在数表示エリアのスタイルを home.php に統一 */
        .target-info-cloud {
            margin: 20px auto 30px auto; 
            background: white;
            border: 2px solid #81c784;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            padding: 15px;
            width: 80%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            color: #2e7d32;
        }
        
        .mode-button {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 15px 0; 
            font-size: 1.3rem; /* 統一されたボタン文字サイズ */
            cursor: pointer;
            border: none;
            border-radius: 30px; /* 統一された丸み */
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: background-color 0.3s;
        }
        
        /* ふつうモード: 青系を維持 */
        .normal { 
            background: linear-gradient(to bottom, #64b5f6, #1976d2); 
        }
        .normal:hover {
            background-color: #42a5f5;
        }
        /* スコアアタック: 黄色系を維持 */
        .score-attack { 
            background: linear-gradient(to bottom, #ffeb3b, #fbc02d);
            color: #333; 
        }
        .score-attack:hover {
            background-color: #ffcc00;
        }

        /* 戻るボタンのスタイルを subject_select.php に統一 */
        .back-button-container {
            text-align: left;
            margin-bottom: 15px;
        }
        .back-button {
            display: inline-flex;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #666;
            color: white;
            font-size: 24px;
            font-weight: bold;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            text-decoration: none;
        }

        .target-line, .current-line {
            font-size: 16px;
            margin: 5px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .target-select {
            font-size: 16px;
            font-weight: bold;
            border: 1px solid #e85a5a; 
            background-color: #ffcc99; 
            color: #e85a5a;
            padding: 3px;
            margin: 0 5px;
            border-radius: 3px;
            width: 70px;
            height: 30px;
        }
        
        .current-number {
            border: 1px solid #72a8e8;
            background-color: #e6f0ff;
            padding: 3px 5px;
            border-radius: 3px;
            margin: 0 5px;
            color: #72a8e8;
            font-weight: bold;
        }
        .icon {
            margin-right: 5px;
        }
        
        hr {
            margin: 20px 0;
            border-color: #ddd;
        }
        
        /* ホームボタン: ログインボタンと同じ緑のグラデーションに統一 */
        .home { 
            background: linear-gradient(to bottom, #81c784, #4caf50);
            font-size: 24px;
            padding: 10px;
        } 
        .home:hover {
             background-color: #66bb6a;
        }
    </style>
</head>
<body>

<div class="mode-container">
    
    <div class="back-button-container">
        <a href="<?php echo $back_url; ?>" class="back-button">←</a>
    </div>

    <h3>モードをせんたく</h3>

    <div class="target-info-cloud">
        <div class="target-line">
            <span class="icon">🎯</span>もくひょう
            <select class="target-select" id="target_count_select">
                <?php 
                // PHPループで10から990まで10刻みでオプションを生成
                for ($i = 10; $i <= 100; $i += 10) {
                    // デフォルト値20を設定
                    $selected = ($i == 20) ? 'selected' : '';
                    echo "<option value=\"{$i}\" {$selected}>{$i}</option>";
                }
                ?>
            </select>
            もん
        </div>
        <div class="current-line">
            <span class="icon">⭐</span>いま
            <span class="current-number">0</span>
            もん!
        </div>
    </div>
    
    
    <form action="question.php" method="GET" id="normal_form">
        <input type="hidden" name="grade" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
        <input type="hidden" name="mode" value="normal">
        <input type="hidden" name="count" id="normal_count_input" value="20"> <button type="submit" class="mode-button normal">
            ふつうモード
        </button>
    </form>
    
    <form action="score_attack.php" method="POST">
        <input type="hidden" name="grade" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
        <button type="submit" class="mode-button score-attack">
            スコアアタック
        </button>
    </form>

    <hr>

    <button type="button" class="mode-button home" onclick="location.href='<?php echo $home_url; ?>';">
        🏠
    </button>
</div>

<script>
    // PHPから学年と教科の値を取得
    const selectedGrade = "<?php echo $selected_grade; ?>";
    const selectedSubject = "<?php echo $selected_subject; ?>";

    const targetSelect = document.getElementById('target_count_select');
    const normalCountInput = document.getElementById('normal_count_input');
    const normalForm = document.getElementById('normal_form'); // ふつうモードのフォームを取得

    // 1. 目標数のセレクトボックスの値が変わったら、hiddenフィールドの値を更新するロジック
    normalCountInput.value = targetSelect.value; 

    targetSelect.addEventListener('change', function() {
        normalCountInput.value = this.value;
    });


    // 2. 🚨 ふつうモードの遷移先を動的に変更するロジック
    function updateNormalFormAction() {
        let actionUrl = 'question.php'; // デフォルトの遷移先

        if (selectedGrade === '1') {
            if (selectedSubject === 'tashizan') {
                actionUrl = 'math_question_1tasi.php';
            } else if (selectedSubject === 'hikizan') {
                actionUrl = 'math_question_1hiki.php';
            }
        } else if (selectedGrade === '2') {
            if (selectedSubject === 'tashizan') {
                actionUrl = 'math_question_2tasi.php';
            } else if (selectedSubject === 'hikizan') {
                actionUrl = 'math_question_2hiki.php';
            }
        }
        
        // フォームの action 属性を更新
        normalForm.setAttribute('action', actionUrl);
    }

    // ページロード時に遷移先を設定
    updateNormalFormAction();

</script>

</body>
</html>
