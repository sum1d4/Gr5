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
            $low_accuracy_action = "failed_1read.php";  // ★ 1年よみ (まちがえた)
            $score_attack_action = "1read_attack.php";  // 1年よみ (スコアアタック)
        } elseif ($selected_subject === 'kaki') {
            $normal_mode_action = "qs_1kaki.php";       // 1年かき (ふつう)
            $unanswered_mode_action = "un_1kaki.php";   // 1年かき (未出題)
            $low_accuracy_action = "failed_1kaki.php";  // ★ 1年かき (まちがえた)
            $score_attack_action = "1kaki_attack.php";  // 1年かき (スコアアタック)
        }
    } elseif ($selected_grade === '2') {
        if ($selected_subject === 'yomi') {
            $normal_mode_action = "qs_2read.php";       // 2年よみ (ふつう)
            $unanswered_mode_action = "un_2read.php";   // 2年よみ (未出題)
            $low_accuracy_action = "failed_2read.php";  // ★ 2年よみ (まちがえた)
            $score_attack_action = "2read_attack.php";  // 2年よみ (スコアアタック)
        } elseif ($selected_subject === 'kaki') {
            $normal_mode_action = "qs_2kaki.php";       // 2年かき (ふつう)
            $unanswered_mode_action = "un_2kaki.php";   // 2年かき (未出題)
            $low_accuracy_action = "failed_2kaki.php";  // ★ 2年かき (まちがえた)
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
        .mode-button {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            font-size: 18px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.3s;
        }
        .normal { background-color: #72a8e8; }
        .unanswered { background-color: #e85a5a; }
        .low-accuracy { background-color: #ff9933; }
        .score-attack { background-color: #ffcc00; color: #333; }
        .home { background-color: #aaaaaa; } 

        /* 戻るボタンのスタイル */
        .back-button-container {
            text-align: left;
            margin-bottom: 10px;
        }
        .back-button {
            display: inline-flex;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #aaaaaa;
            color: white;
            font-size: 24px;
            font-weight: bold;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: none;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
            text-decoration: none;
        }

        /* 目標・現在数表示エリアのスタイル */
        .target-info-cloud {
            background: #fff;
            border: 2px solid #ccc;
            /* 雲のような形 */
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            margin: 20px auto;
            padding: 20px;
            width: 80%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .target-line, .current-line {
            font-size: 16px;
            margin: 5px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* リストボックス (select) のスタイル */
        .target-select {
            font-size: 16px;
            font-weight: bold;
            border: 1px solid #e85a5a; 
            background-color: #ffcc99; 
            color: #e85a5a;
            padding: 3px;
            margin: 0 5px;
            border-radius: 3px;
            -webkit-appearance: menulist;
            -moz-appearance: menulist;
            appearance: menulist;
            text-align: center;
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
            やったことないもんざい
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
