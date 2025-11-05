<?php
// PHPでPOSTされたデータを受け取り、リダイレクト処理を行う
$selected_grade = '';
$selected_subject = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['grade']) && isset($_POST['subject'])) {
        // サニタイズ
        $selected_grade = htmlspecialchars($_POST['grade']);
        $selected_subject = htmlspecialchars($_POST['subject']);
        
        // 🚨 修正ロジック: 選択された教科によってリダイレクト先を分岐
        $redirect_file = "mode_select.php"; // デフォルトの遷移先

        // たしざん または ひきざん が選択された場合
        if ($selected_subject === 'tashizan' || $selected_subject === 'hikizan') {
            $redirect_file = "mode_select_keisan.php";
        }
        
        // クエリパラメータ付きのURLを生成
        $redirect_url = "{$redirect_file}?grade={$selected_grade}&subject={$selected_subject}";
        
        // リダイレクト処理
        header("Location: " . $redirect_url);
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
        .container {
            width: 450px;
            max-width: 90%;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px; 
            background-color: white; /* コンテナ背景は白 */
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        /* 戻るボタンのスタイルを統一 */
        .back-button-container {
            text-align: left;
            margin-bottom: 25px; 
        }
        .back-button {
            width: 45px; 
            height: 45px;
            display: inline-flex;
            border-radius: 50%;
            background-color: #666; /* 濃いグレー */
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
        .back-button:hover {
            background-color: #444;
        }
        
        h2 {
            margin-bottom: 30px;
            font-size: 24px;
            color: #2e7d32; /* ログイン画面と同じ緑色 */
        }

        /* 学年選択グループ */
        .selection-group {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .grade-button {
            padding: 12px 20px;
            font-size: 16px; 
            border-radius: 20px; /* 丸みを強く */
            display: inline-block;
            border: 2px solid #66bb6a; /* 緑の枠線 */
            background-color: white;
            color: #2e7d32;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        input[type="radio"] {
            display: none;
        }
        input[type="radio"]:checked + label.grade-button {
            background-color: #81c784; /* チェック時は緑 */
            color: white;
            border-color: #4caf50;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
            transform: translateY(1px);
        }

        /* 科目選択エリア */
        .subject-grid {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-bottom: 25px;
        }
        .subject-column {
            flex: 1; 
            min-width: 0;
            padding: 10px;
            background-color: #f0f4f8; /* 薄いグレーの背景 */
            border-radius: 8px;
        }
        .subject-column h3 {
            font-size: 18px;
            margin: 0 0 15px 0;
            padding: 8px 0;
            color: #1565c0; /* 青色 */
            border-bottom: 2px solid #bbdefb;
        }

        /* 科目ボタン */
        .subject-button-label {
            display: block;
            width: 90%;
            margin: 10px auto; /* 上下のマージンを調整 */
            padding: 15px 0;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        /* かんじ（よみ/かき）のボタン色: 赤系 */
        .yomi, .kaki { background-color: #ef5350; } 
        /* けいさん（たしざん/ひきざん）のボタン色: 青系 */
        .tashizan, .hikizan { background-color: #42a5f5; } 
        
        input[type="radio"]:checked + label.subject-button-label {
            box-shadow: 0 0 0 4px white, 0 0 0 6px #4caf50; /* 緑の二重線 */
            transform: translateY(-2px);
        }

        /* けっていボタン */
        .kettui { 
            /* ログインボタンと同じ緑のグラデーション */
            background: linear-gradient(to bottom, #81c784, #4caf50); 
            border-radius: 30px; /* ログインボタンに合わせた丸み */
            font-size: 1.3rem; /* ログインボタンに合わせた文字サイズ */
            padding: 10px 40px;
            width: 250px;
            margin-top: 15px;
        } 

        /* ホームボタン */
        .home-link {
            display: block;
            margin-top: 25px;
            color: #777;
            text-decoration: none;
            font-size: 24px;
        }
        
        @media(max-width: 600px){
            .container {
                padding: 20px 15px;
            }
            .subject-grid {
                flex-direction: column; /* 縦並びに変更 */
                gap: 10px;
            }
            .subject-column {
                padding: 5px;
            }
            .kettui {
                width: 200px;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="back-button-container">
        <a href="home.php" class="back-button">←</a>
    </div>

    <h2>きょうかをせんたく</h2>

    <form action="" method="POST"> 

        <div class="selection-group">
            <div class="grade-select">
                <input type="radio" id="grade_1" name="grade" value="1" required checked>
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
                
                <input type="radio" id="sub_yomi" name="subject" value="yomi" required>
                <label for="sub_yomi" class="subject-button-label yomi">よみ</label>
    
                <input type="radio" id="sub_kaki" name="subject" value="kaki">
                <label for="sub_kaki" class="subject-button-label kaki">かき</label>
                
            </div>
            
            <div class="subject-column">
                <h3>けいさん</h3>
                
                <input type="radio" id="sub_tashizan" name="subject" value="tashizan" required>
                <label for="sub_tashizan" class="subject-button-label tashizan">たしざん</label>
    
                <input type="radio" id="sub_hikizan" name="subject" value="hikizan">
                <label for="sub_hikizan" class="subject-button-label hikizan">ひきざん</label>
                
            </div>
        </div>

        <button type="submit" class="subject-button-label kettui">
            けってい
        </button>

    </form>

    <a href="home.php" class="home-link">🏠</a>

</div>

</body>
</html>
