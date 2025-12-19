<?php
session_start();

/* -------------------------
   すべてのクイズ関連セッションをリセット
------------------------- */

// ▼ 1年 よみ
unset($_SESSION["yomi_current_q"]);
unset($_SESSION["yomi_used_questions"]);
unset($_SESSION["yomi_correct_count"]);
unset($_SESSION["yomi_correct_answer"]);

// ▼ 1年 かき
unset($_SESSION["kaki_current_q"]);
unset($_SESSION["kaki_used_questions"]);
unset($_SESSION["kaki_correct_count"]);
unset($_SESSION["kaki_correct_answer"]);

// ▼ 2年 よみ
unset($_SESSION["current_q2"]);
unset($_SESSION["correct_count2"]);

// ▼ 2年 かき
unset($_SESSION["kaki2_current_q"]);
unset($_SESSION["kaki2_used_questions"]);
unset($_SESSION["kaki2_correct_count"]);
unset($_SESSION["kaki2_correct_answer"]);

// 計算（たし算・ひき算）を今後作るならここに追加もOK

// ▼ 共通
unset($_SESSION["learning_session_id"]);

// --- セッション初期化はここまで ---

// --- POSTで選択を受け取り、リダイレクト ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!empty($_POST['grade']) && !empty($_POST['subject'])) {

        $grade = htmlspecialchars($_POST['grade'], ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8');

        // デフォルト：漢字 → mode_select.php
        $redirect = "mode_select_guest.php";

        // 計算の場合は mode_select_keisan.php に変更
        if ($subject === 'tashizan' || $subject === 'hikizan') {
            $redirect = "mode_select_keisan_guest.php";
        }

        header("Location: {$redirect}?grade={$grade}&subject={$subject}");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>教科選択 | Learn+</title>
    <style>
        body {
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
        .container {
            width: 450px;
            max-width: 90%;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px; 
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .back-button-container {
            text-align: left;
            margin-bottom: 25px; 
        }
        .back-button {
            width: 45px; 
            height: 45px;
            display: inline-flex;
            border-radius: 50%;
            background-color: #666;
            color: white;
            font-size: 26px;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            text-decoration: none;
            transition: 0.3s;
        }
        .back-button:hover { background-color: #444; }

        h2 {
            margin-bottom: 30px;
            font-size: 24px;
            color: #2e7d32;
        }
        .selection-group {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .grade-button {
            padding: 12px 20px;
            font-size: 16px; 
            border-radius: 20px;
            display: inline-block;
            border: 2px solid #66bb6a;
            background-color: white;
            color: #2e7d32;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        input[type="radio"] { display: none; }
        input[type="radio"]:checked + label.grade-button {
            background-color: #81c784;
            color: white;
            border-color: #4caf50;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
            transform: translateY(1px);
        }

        .subject-grid {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-bottom: 25px;
        }
        .subject-column {
            flex: 1; 
            padding: 10px;
            background-color: #f0f4f8;
            border-radius: 8px;
        }
        .subject-column h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #1565c0;
            border-bottom: 2px solid #bbdefb;
            padding-bottom: 5px;
        }

        .subject-button-label {
            display: block;
            width: 90%;
            margin: 10px auto;
            padding: 15px 0;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: 0.3s;
        }
        .yomi, .kaki { background-color: #ef5350; }
        .tashizan, .hikizan { background-color: #42a5f5; }

        input[type="radio"]:checked + label.subject-button-label {
            box-shadow: 0 0 0 4px white, 0 0 0 6px #4caf50;
            transform: translateY(-2px);
        }

        .kettui {
            background: linear-gradient(to bottom, #81c784, #4caf50);
            border-radius: 30px;
            font-size: 1.3rem;
            padding: 10px 40px;
            width: 250px;
            margin-top: 15px;
        }
        .home-link {
            display: block;
            margin-top: 25px;
            color: #777;
            text-decoration: none;
            font-size: 24px;
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="back-button-container">
        <a href="index_guest.php" class="back-button">←</a>
    </div>

    <h2>きょうかをせんたく</h2>

    <form action="" method="POST"> 

        <div class="selection-group">
            <div class="grade-select">
                <input type="radio" id="grade_1" name="grade" value="1" checked>
                <label for="grade_1" class="grade-button">1ねんせい</label>
            </div>
            <div class="grade-select">
                <input type="radio" id="grade_2" name="grade" value="2">
                <label for="grade_2" class="grade-button">2ねんせい</label>
            </div>
        </div>

        <div class="subject-grid">
            <div class="subject-column">
                <h3>かんじ</h3>
                <input type="radio" id="sub_yomi" name="subject" value="yomi">
                <label for="sub_yomi" class="subject-button-label yomi">よみ</label>

                <input type="radio" id="sub_kaki" name="subject" value="kaki">
                <label for="sub_kaki" class="subject-button-label kaki">かき</label>
            </div>
            
            <div class="subject-column">
                <h3>けいさん</h3>
                <input type="radio" id="sub_tashizan" name="subject" value="tashizan">
                <label for="sub_tashizan" class="subject-button-label tashizan">たしざん</label>

                <input type="radio" id="sub_hikizan" name="subject" value="hikizan">
                <label for="sub_hikizan" class="subject-button-label hikizan">ひきざん</label>
            </div>
        </div>

        <button type="submit" class="subject-button-label kettui">けってい</button>

    </form>

    <a href="home.php" class="home-link">🏠</a>

</div>

</body>
</html>
