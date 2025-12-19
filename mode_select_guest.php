<?php
session_start();

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
// 戻るボタンのリンク先
$back_url = "subject_select_guest.php";

// ホームボタンのリンク先
$home_url = "index_guest.php"; 

// 'yomi' または 'kaki' が選択されている場合
if ($selected_subject === 'yomi' || $selected_subject === 'kaki') {
    // 学年と教科に応じた遷移先を設定
    if ($selected_grade === '1') {
        if ($selected_subject === 'yomi') {
            $normal_mode_action = "qs_1read_guest.php";       // 1年よみ (ふつう)
        } elseif ($selected_subject === 'kaki') {
            $normal_mode_action = "qs_1kaki_guest.php";       // 1年かき (ふつう)
        }
    } elseif ($selected_grade === '2') {
        if ($selected_subject === 'yomi') {
            $normal_mode_action = "qs_2read_guest.php";       // 2年よみ (ふつう)
        } elseif ($selected_subject === 'kaki') {
            $normal_mode_action = "qs_2kaki_guest.php";       // 2年かき (ふつう)
        }
    }
}

// ゲストユーザーのため、進捗と目標はダミー値または空とする
$today_count = 0; // ゲストのため進捗は表示しないか0とする
$current_target = '??'; // ゲストのため目標は表示しない

// $query_params は使用しないため削除
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>モード選択 (<?php echo "{$selected_grade}年 - {$selected_subject}"; ?>)</title>
    <style>
        body {
            background: linear-gradient(to bottom, #b3e5fc, #81d4fa); /* グラデーションを追加 */
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 50px;
        }
        .mode-container {
            width: 300px;
            margin: 0 auto;
            /* border: 1px solid #ccc; */ /* 削除 */
            padding: 20px;
            /* background-color: #fff; */ /* 削除 */
            border-radius: 5px;
            /* box-shadow: 0 4px 8px rgba(0,0,0,0.1); */ /* 削除 */
        }
        
        /* ★★★ 変更点: index.php と同じカプセル型デザイン ★★★ */
        .cloud-box {
            background: white;
            border-radius: 50px; /* カプセル型 */
            padding: 15px 30px;
            text-align: center;
            box-shadow: 2px 3px 8px rgba(0,0,0,0.2); /* 影を強調 */
            border: none; /* 枠線を削除 */
            position: relative;
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 30px; /* 下のマージンを増やす */
        }
        
        /* 目標テキストのスタイル */
        .login-message {
            display: block;
            font-size: 1.1rem;
            font-weight: bold;
            color: #d81b60; /* 目立つ色 */
            padding: 5px 0;
            line-height: 1.5;
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
        .normal:hover { background: #42a5f5; }
     
        /* ホーム（グレー） */
        .home { background: linear-gradient(to bottom, #bdbdbd, #616161); margin-top: 30px;} /* 上マージン追加 */
        .home:hover { background: #757575; }
     
        /* 戻るボタン */
        .back-button-container {
            text-align: left;
            margin-bottom: 20px; /* 下マージンを増やす */
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
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div class="mode-container">
    
    <div class="back-button-container">
        <a href="<?php echo $back_url; ?>" class="back-button">←</a>
    </div>
    
    <div class="cloud-box">
        <span class="login-message">
            🎯 ログインすることでさらにモードをあそべます！！
        </span>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">
    </div>

    <form action="<?php echo $normal_mode_action; ?>" method="GET" id="normal_form">
        <input type="hidden" name="grade" value="<?php echo $selected_grade; ?>">
        <input type="hidden" name="subject" value="<?php echo $selected_subject; ?>">
        <input type="hidden" name="mode" value="normal">
        <input type="hidden" name="count" id="normal_count_input" value="10"> <button type="submit" class="mode-button normal">
            ふつうモード
        </button>
    </form>

    <button type="button" class="mode-button home" onclick="location.href='<?php echo $home_url; ?>';">
        🏠 ホームにもどる
    </button>
</div>

</body>
</html>
