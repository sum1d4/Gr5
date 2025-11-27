<?php
session_start();
require_once "db_config.php"; // DB接続設定

// ★★★ 1. ログインチェック ★★★
if (!isset($_SESSION["user_id"])) {
    header("Location: Rogin.php"); // Rogin.php へリダイレクト
    exit;
}
$user_id = $_SESSION["user_id"];

// クエリパラメータから学年と教科を受け取る
$selected_grade = '';
$selected_subject = '';

// GETリクエストから 'grade' と 'subject' を取得
if (isset($_GET['grade'])) {
    $selected_grade = htmlspecialchars($_GET['grade']);
}
if (isset($_GET['subject'])) {
    $selected_subject = htmlspecialchars($_GET['subject']);
}

// =========================================================
// ★ データベースから現在の目標値を取得
// =========================================================
$sql_target = "SELECT target_questions FROM target WHERE user_id = :uid LIMIT 1";
$stmt_t = $pdo->prepare($sql_target);
$stmt_t->bindValue(":uid", $user_id, PDO::PARAM_INT);
$stmt_t->execute();
$row_target = $stmt_t->fetch(PDO::FETCH_ASSOC);

// 設定がなければデフォルト値（20問）
$current_target = $row_target ? $row_target["target_questions"] : 20;

// =========================================================
// ★ 今日の解答数をカウントする
// =========================================================
$sql_count = "
    SELECT COUNT(*) 
    FROM answer_record
    INNER JOIN learning_session 
    ON answer_record.session_id = learning_session.session_id
    WHERE answer_record.user_id = :uid
    AND DATE(learning_session.start_time) = CURDATE()
";

$stmt_c = $pdo->prepare($sql_count);
$stmt_c->bindValue(":uid", $user_id, PDO::PARAM_INT);
$stmt_c->execute();
$today_count = $stmt_c->fetchColumn(); 
// =========================================================


// 戻るボタンのリンク先
$back_url = "subject_select.php";

// ホームボタンのリンク先
$home_url = "index.php"; 


// 🎯 遷移先変数の定義 🎯
$normal_mode_action = "question.php";       // ふつうモード デフォルト
$unanswered_mode_action = "unanswered.php"; // 未出題モード デフォルト
$low_accuracy_action = "low_accuracy.php";  // まちがえたもんだい デフォルト
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

$query_params = "grade={$selected_grade}&subject={$selected_subject}";
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>モード選択 (<?php echo "{$selected_grade}年 - {$selected_subject}"; ?>)</title>
    <style>
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
        
        /* ★★★ 変更点: index.php と同じカプセル型デザイン ★★★ */
        .cloud-box {
            background: white;
            border-radius: 50px; /* カプセル型 */
            padding: 15px 30px;
            text-align: center;
            box-shadow: 2px 3px 8px rgba(0,0,0,0.1);
            border: 2px solid #eee;
            position: relative;
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        .cloud-box select {
            font-size: 1.1rem;
            padding: 5px;
            border-radius: 8px;
            border: 2px solid #81d4fa;
            cursor: pointer;
            margin-left: 5px;
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
        .normal { background: linear-gradient(to bottom, #64b5f6, #1976d2); }
        .normal:hover { background-color: #42a5f5; }
     
        /* やったことない（赤） */
        .unanswered { background: linear-gradient(to bottom, #ff867c, #e53935); }
        .unanswered:hover { background-color: #d32f2f; }
     
        /* にがて（オレンジ） */
        .low-accuracy { background: linear-gradient(to bottom, #ffb74d, #f57c00); }
        .low-accuracy:hover { background-color: #ef6c00; }
     
        /* スコアアタック（黄） */
        .score-attack { background: linear-gradient(to bottom, #ffeb3b, #fbc02d); color: #333; }
        .score-attack:hover { background-color: #ffcc00; }
     
        /* ホーム（グレー） */
        .home { background: linear-gradient(to bottom, #bdbdbd, #616161); }
        .home:hover { background-color: #757575; }
     
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
    </style>
</head>
<body>

<div class="mode-container">
    
    <div class="back-button-container">
        <a href="<?php echo $back_url; ?>" class="back-button">←</a>
    </div>
    
    <div class="cloud-box">
        <form action="update_target.php" method="post" style="display:inline;">
            🎯もくひょう
            <select name="target_questions" onchange="this.form.submit()">
                <?php 
                for ($i = 10; $i <= 100; $i += 10) {
                    $selected = ($i == $current_target) ? 'selected' : '';
                    echo "<option value=\"{$i}\" {$selected}>{$i}もん</option>";
                }
                ?>
            </select>
            
            <input type="hidden" name="from_page" value="mode_select.php?grade=<?php echo $selected_grade; ?>&subject=<?php echo $selected_subject; ?>">
        </form>
        <br>
        
        ⭐いま <span style="color:#ff9800; font-weight:bold; font-size: 1.5rem;"><?php echo $today_count; ?></span> / <?php echo $current_target; ?> もん！
    </div>
    <form action="<?php echo $normal_mode_action; ?>" method="GET" id="normal_form">
        <input type="hidden" name="grade" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
        <input type="hidden" name="mode" value="normal">
        <input type="hidden" name="count" id="normal_count_input" value="<?php echo $current_target; ?>"> 
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

</body>
</html>