<?php
// mode_select.php
// モード選択画面

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
$home_url = "home.php"; 


// 🎯 遷移先変数の定義 🎯
$normal_mode_action = "question.php";       // ふつうモード デフォルト
$unanswered_mode_action = "unanswered.php"; // 未出題モード デフォルト
$low_accuracy_action = "low_accuracy.php";  // ★ まちがえたもんだい デフォルト
$score_attack_action = "score_attack.php";  // スコアアタック デフォルト

// 'yomi' または 'kaki' が選択されている場合
if ($selected_subject === 'yomi' || $selected_subject === 'kaki') {
    // 学年と教科に応じた遷移先を設定
    if ($selected_grade === '1') {
        if ($selected_subject === 'yomi') {
            $normal_mode_action = "qs_1read.php";       // 1年よみ (ふつう)
            $unanswered_mode_action = "un_1read.php";   // 1年よみ (未出題)
            $low_accuracy_action = "failed_1read.php";  // 1年よみ (まちがえた)
            $score_attack_action = "1read_attack.php";  // 1年よみ (スコアアタック)
        } elseif ($selected_subject === 'kaki') {
            $normal_mode_action = "qs_1kaki.php";       // 1年かき (ふつう)
            $unanswered_mode_action = "un_1kaki.php";   // 1年かき (未出題)
            $low_accuracy_action = "failed_1kaki.php";  // 1年かき (まちがえた)
            $score_attack_action = "1kaki_attack.php";  // 1年かき (スコアアタック)
        }
    } elseif ($selected_grade === '2') {
        if ($selected_subject === 'yomi') {
            $normal_mode_action = "qs_2read.php";       // 2年よみ (ふつう)
            $unanswered_mode_action = "un_2read.php";   // 2年よみ (未出題)
            $low_accuracy_action = "failed_2read.php";  // 2年よみ (まちがえた)
            $score_attack_action = "2read_attack.php";  // 2年よみ (スコアアタック)
        } elseif ($selected_subject === 'kaki') {
            $normal_mode_action = "qs_2kaki.php";       // 2年かき (ふつう)
            $unanswered_mode_action = "un_2kaki.php";   // 2年かき (未出題)
            $low_accuracy_action = "failed_2kaki.php";  // 2年かき (まちがえた)
            $score_attack_action = "2kaki_attack.php";  // 2年かき (スコアアタック)
        }
    }
}
// その他の教科 (tashizan/hikizanなど) の場合は question.php / score_attack.php / unanswered.php / low_accuracy.php のまま

// 共通のクエリパラメータ文字列を生成 (HTMLでは使わないが念のため残す)
$query_params = "grade={$selected_grade}&subject={$selected_subject}";
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>モード選択 (<?php echo "{$selected_grade}年 - {$selected_subject}"; ?>)</title>
    <style>
        /* (CSSコードは省略) */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f0f0;
            padding-top: 50px;
        }
        .mode-container {
            width: 300px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
   /* ================================
       🎨 統一ボタンデザイン
    ================================= */
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
        transition: background-color 0.3s, transform 0.1s;
    }
 
    .mode-button:active {
        transform: translateY(2px);
    }
 
    /* ふつう（青） */
    .normal {
        background: linear-gradient(to bottom, #64b5f6, #1976d2);
    }
    .normal:hover {
        background-color: #42a5f5;
    }
 
    /* やったことない（赤） */
    .unanswered {
        background: linear-gradient(to bottom, #ff867c, #e53935);
    }
    .unanswered:hover {
        background-color: #d32f2f;
    }
 
    /* にがて（オレンジ） */
    .low-accuracy {
        background: linear-gradient(to bottom, #ffb74d, #f57c00);
    }
    .low-accuracy:hover {
        background-color: #ef6c00;
    }
 
    /* スコアアタック（黄） */
    .score-attack {
        background: linear-gradient(to bottom, #ffeb3b, #fbc02d);
        color: #333;
    }
    .score-attack:hover {
        background-color: #ffcc00;
    }
 
    /* ホーム（グレー） */
    .home {
        background: linear-gradient(to bottom, #bdbdbd, #616161);
    }
    .home:hover {
        background-color: #757575;
    }
 
    /* 戻るボタン */
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
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
 
    /* 雲デザイン用 */
    .target-info-cloud {
        background: #fff;
        border: 2px solid #ccc;
        border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
        margin: 20px auto;
        padding: 20px;
        width: 85%;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        border-radius: 5px;
        width: 70px;
        height: 30px;
        text-align: center;
    }
 
    .current-number {
        border: 1px solid #72a8e8;
        background-color: #e6f0ff;
        padding: 3px 5px;
        border-radius: 5px;
        color: #72a8e8;
        font-weight: bold;
    }    
    </style>
 
    </style>
</head>
<body>

<div class="mode-container">
    
    <div class="back-button-container">
        <a href="<?php echo $back_url; ?>" class="back-button">←</a>
    </div>
    
    <div class="target-info-cloud">
        <div class="target-line">
            <span class="icon">🎯</span>もくひょう
            <select class="target-select" id="target_count_select">
                <?php 
                // PHPループで10から100まで10刻みでオプションを生成
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
    
    
    <form action="<?php echo $normal_mode_action; ?>" method="GET" id="normal_form">
        <input type="hidden" name="grade" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
        <input type="hidden" name="mode" value="normal">
        <input type="hidden" name="count" id="normal_count_input" value="20"> 
        <button type="submit" class="mode-button normal">
            ふつうモード
        </button>
    </form>

    <form action="<?php echo $unanswered_mode_action; ?>" method="POST">
        <input type="hidden" name="grade" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
        <button type="submit" class="mode-button unanswered">
            やったことないもんだい
        </button>
    </form>
    
    <form action="<?php echo $low_accuracy_action; ?>" method="POST">
        <input type="hidden" name="grade" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
        <button type="submit" class="mode-button low-accuracy">
            まちがえたもんだい
        </button>
    </form>

    <form action="<?php echo $score_attack_action; ?>" method="POST">
        <input type="hidden" name="grade" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
        <button type="submit" class="mode-button score-attack">
            スコアアタック
        </button>
    </form>

    <hr>

    <button type="button" class="mode-button home" onclick="location.href='<?php echo $home_url; ?>';">
        🏠 ホームにもどる
    </button>
</div>

<script>
    // 目標数のセレクトボックスの値が変わったら、ふつうモードのhiddenフィールドの値を更新する
    const targetSelect = document.getElementById('target_count_select');
    const normalCountInput = document.getElementById('normal_count_input');

    // 初期値のセット
    normalCountInput.value = targetSelect.value; 

    // 変更時のイベントリスナー
    targetSelect.addEventListener('change', function() {
        normalCountInput.value = this.value;
    });

</script>

</body>
</html>
