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
$back_url = "subject_select_guest.php";

// ホームボタンのリンク先
$home_url = "index_guest.php"; // home_guest.phpではなく、ホーム画面のindex_guest.phpに統一

// 共通のクエリパラメータ文字列を生成 (未使用だが残しておく)
$query_params = "grade={$selected_grade}&subject={$selected_subject}";

// ゲストユーザーのため、進捗はダミー値または0とする
$today_count = 0;
$current_target = '??'; 
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>モード選択 | Learn+</title>
    <style>
        /* (スタイルの変更はありません) */
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
            /* border: 1px solid #ddd; */ /* ゲスト画面の統一性を保つため非表示 */
            padding: 25px;
            /* background-color: white; */ /* ゲスト画面の統一性を保つため非表示 */
            border-radius: 10px;
            /* box-shadow: 0 4px 10px rgba(0,0,0,0.1); */ /* ゲスト画面の統一性を保つため非表示 */
        }
        
        h3 {
            margin-top: 5px;
            margin-bottom: 25px; 
            color: #2e7d32;
            font-size: 20px;
        }
        
        /* 目標・現在数表示エリアのスタイルを home.php に統一 */
        .target-info-cloud {
            margin: 20px auto 30px auto; 
            background: white;
            /* ログインメッセージ表示用に、ボーダーと角を修正 */
            border-radius: 50px; /* カプセル型に統一 */
            padding: 15px;
            width: 80%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2); /* 影を強調 */
            text-align: center;
            color: #2e7d32;
            border: none;
        }
        
        /* ログインメッセージのスタイル */
        .login-message {
            display: block;
            font-size: 1rem;
            font-weight: bold;
            color: #d81b60; /* 目立つ色 */
            padding: 5px 0;
            line-height: 1.5;
        }
        
        .mode-button {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 15px 0; 
            font-size: 1.3rem;
            cursor: pointer;
            border: none;
            border-radius: 30px;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: background-color 0.3s;
        }
        
        .normal { 
            background: linear-gradient(to bottom, #64b5f6, #1976d2); 
        }
        .normal:hover {
            background-color: #42a5f5;
        }
        .score-attack { 
            /* スコアアタックはゲスト向けには非表示とする */
            display: none;
        }

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

        /* ゲストモードでは進捗表示は非表示にする */
        .current-line {
            display: none; 
        }

        .icon {
            margin-right: 5px;
        }
        
        hr {
            margin: 20px 0;
            border-color: #ddd;
        }
        
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
        <span class="login-message">
            🎯 ログインすることでさらにモードをあそべます！！
        </span>
    </div>
    
    
    <form action="" method="GET" id="normal_form">
        <input type="hidden" name="grade" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
        <input type="hidden" name="mode" value="normal">
        <input type="hidden" name="count" id="normal_count_input" value="20"> 
        <button type="submit" class="mode-button normal">
            ふつうモード
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

    // 目標数に関する要素とロジックを削除/コメントアウト
    // const targetSelect = document.getElementById('target_count_select'); 
    const normalCountInput = document.getElementById('normal_count_input');
    const normalForm = document.getElementById('normal_form'); 
    const scoreAttackForm = document.getElementById('score_attack_form'); 

    // 1. 目標数のセレクトボックスの値が変わったら、hiddenフィールドの値を更新するロジックは削除
    // normalCountInput.value = targetSelect.value; 
    // targetSelect.addEventListener('change', function() {
    //     normalCountInput.value = this.value;
    // });
    
    // 2. 🚨 ふつうモードとスコアアタックの遷移先を動的に変更するロジック
    function updateFormActions() {
        let normalActionUrl = 'question.php'; // デフォルト
        let attackActionUrl = 'score_attack.php'; // デフォルト
        
        // ふつうモードの遷移先設定
        if (selectedGrade === '1') {
            if (selectedSubject === 'tashizan') {
                normalActionUrl = 'math_question_1tasi_guest.php';
                attackActionUrl = '1tasi_attack_guest.php';
            } else if (selectedSubject === 'hikizan') {
                normalActionUrl = 'math_question_1hiki_guest.php';
                attackActionUrl = '1hiki_attack_guest.php'; 
            }
        } else if (selectedGrade === '2') {
            if (selectedSubject === 'tashizan') {
                normalActionUrl = 'math_question_2tasi_guest.php';
                attackActionUrl = '2tasi_attack_guest.php'; 
            } else if (selectedSubject === 'hikizan') {
                normalActionUrl = 'math_question_2hiki_guest.php';
                attackActionUrl = '2hiki_attack_guest.php'; 
            }
        }
        
        // フォームの action 属性を更新
        normalForm.setAttribute('action', normalActionUrl);
        // スコアアタックはゲストモードでは非表示だが、一応アクションも設定
        scoreAttackForm.setAttribute('action', attackActionUrl); 
    }

    // ページロード時に遷移先を設定
    updateFormActions();

</script>

</body>
</html>
