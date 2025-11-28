<?php
// 1. データベース設定ファイルの読み込み
require_once 'db_config.php';

// ----------------------------------------------------

// POSTリクエストから 'grade' と 'subject' を取得 (mode_select_keisan.phpからのPOSTを想定)
// ※これは「戻るボタン」や「画面遷移」のために保持します
$selected_grade = '';
$selected_subject = '';

if (isset($_POST['grade'])) {
    $selected_grade = htmlspecialchars($_POST['grade']);
}
if (isset($_POST['subject'])) {
    $selected_subject = htmlspecialchars($_POST['subject']);
}

// ----------------------------------------------------
// 🏆 データベースからハイスコアを取得する処理
// ----------------------------------------------------
// 🚨【ランキング設定】学年(2)と教科(tasi)で固定して取得
$ranking_grade = 2;
$ranking_subject = 'tasi';

$high_scores = []; // 配列を初期化

try {
    // カテゴリー('score')で絞り込み
    // スコアの高い順 > タイムの早い順 で上位3つを取得
    $sql = "SELECT score 
            FROM score_attack 
            WHERE target_age = :grade 
              AND subject = :subject 
              AND category = 'score'
            ORDER BY score DESC, total_time ASC 
            LIMIT 3";
    
    // DB接続オブジェクト（$pdo）は db_config.php で定義されていることを前提とする
    $stmt = $pdo->prepare($sql);
    
    // 固定値をバインド
    $stmt->bindValue(':grade', $ranking_grade, PDO::PARAM_INT);
    $stmt->bindValue(':subject', $ranking_subject, PDO::PARAM_STR);
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // データベースの結果を表示用配列に格納
    foreach ($results as $row) {
        // ラベルは固定で「もん」とする
        $high_scores[] = ['score' => $row['score'], 'label' => 'もん'];
    }

} catch (PDOException $e) {
    // エラー時はログに記録
    error_log("Score Fetch Error: " . $e->getMessage());
    // 画面には空の配列、またはエラーメッセージを表示する
    $high_scores = []; // エラー時は空とする
}

// ----------------------------------------------------

// 戻るボタンのリンク先を mode_select_keisan.php に設定
// ※学年と教科を渡して戻る（モード選択画面に戻る）
$back_url = "mode_select_keisan.php?grade={$selected_grade}&subject={$selected_subject}";

// ホームボタンのリンク先
$home_url = "index.php"; 

// 🚨 【修正ロジック】スタートボタンの遷移先を固定 🚨
// ユーザーの要望により、遷移先を score_question2tasi.php に固定します。
$start_page = 'score_question2tasi.php'; 

// 遷移先のページに grade と subject をクエリパラメータで渡す
$query_params = "?grade={$selected_grade}&subject={$selected_subject}";
$start_page_with_params = $start_page . $query_params;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スコアアタック</title>
    <style>
        /* ----------------------- グローバルスタイル ----------------------- */
        body {
            font-family: sans-serif;
            background-color: #e0f2f1; /* 背景色 */
            display: flex;
            justify-content: center;
            align-items: flex-start; /* 上部に寄せる */
            min-height: 100vh;
            margin: 0;
            padding-top: 50px; /* 上部にスペース */
        }

        /* メインコンテナ - 画面中央に配置されるカード */
        .container {
            width: 300px; /* スマホ画面を意識した幅 */
            padding: 20px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative; /* 戻るボタン配置用 */
        }

        /* ----------------------- ① 戻るボタン ----------------------- */
        .back-button-area {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-button {
            width: 40px;
            height: 40px;
            background-color: #00897b;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5em;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.1s;
        }

        .back-button:active {
            transform: scale(0.95);
        }

        /* ----------------------- タイトル・説明文 ----------------------- */
        .title-area {
            margin-top: 50px; /* 戻るボタンとのスペースを確保 */
            color: #004d40;
        }

        h1 {
            font-size: 1.6em;
            margin-bottom: 5px;
            font-weight: bold;
        }

        p.description {
            font-size: 0.9em;
            color: #333;
            margin-bottom: 20px;
        }

        /* ----------------------- ② 記録表示エリア ----------------------- */
        .score-record {
            width: 80%;
            margin: 0 auto 30px auto;
            border: 4px solid #00897b; /* 濃いエメラルドグリーン */
            border-radius: 10px;
            padding: 15px 0;
            background-color: #f7fff7;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .score-record h2 {
            font-size: 1.2em;
            color: #00897b;
            margin-top: 0;
            margin-bottom: 10px;
            border-bottom: 2px solid #b2dfdb;
            padding-bottom: 5px;
            display: inline-block;
        }

        .score-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
            width: 80%;
            margin: 0 auto;
        }

        .score-list li {
            font-size: 1.4em;
            padding: 5px 0;
            color: #333;
            border-bottom: 1px dashed #b2dfdb;
        }
        
        .score-list li:last-child {
            border-bottom: none;
        }

        .score-list span.rank {
            font-weight: bold;
            color: #004d40;
            margin-right: 10px;
            display: inline-block;
            width: 25px;
        }

        .score-list span.value {
            font-weight: 900;
            color: #e53935; /* 赤色で強調 */
            margin-left: 5px;
        }

        /* ----------------------- ③ スタートボタン ----------------------- */
        .start-button {
            width: 90%;
            padding: 15px 0;
            background: linear-gradient(145deg, #2196f3, #1976d2); /* 青のグラデーション */
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 1.8em;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 8px 15px rgba(33, 150, 243, 0.4);
            transition: all 0.2s ease;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .start-button:hover {
            box-shadow: 0 10px 20px rgba(33, 150, 243, 0.6);
        }

        .start-button:active {
            transform: translateY(2px);
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.4);
        }

        /* ----------------------- ④ ホームボタン ----------------------- */
        .home-button-area {
            margin-top: 10px;
        }
        .home-button {
            width: 50px;
            height: 50px;
            background-color: #607d8b; /* 灰色 */
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 2em;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.1s;
        }
        .home-button:active {
            transform: scale(0.95);
        }

        /* アイコン用 (シンプルなSVG) */
        .icon-arrow-left {
            width: 20px;
            height: 20px;
            fill: white;
            transform: translateX(-2px);
        }
        .icon-home {
            width: 30px;
            height: 30px;
            fill: white;
        }
    </style>
</head>
<body>

    <div class="container">

        <div class="back-button-area">
            <a href="<?php echo htmlspecialchars($back_url); ?>">
                <button class="back-button" aria-label="前の画面に戻る">
                    <svg class="icon-arrow-left" viewBox="0 0 24 24">
                        <path d="M15.41 7.41L14 6L8 12L14 18L15.41 16.59L10.83 12z"/>
                    </svg>
                </button>
            </a>
        </div>

        <div class="title-area">
            <h1>スコアアタック画面</h1>
            <p class="description">じかんない に たくさん もんだい を とこう!!</p>
        </div>
        
        <div class="score-record">
            <h2>いまのきろく</h2>
            <ul class="score-list">
                <?php if (empty($high_scores)): ?>
                    <li style="text-align:center; font-size:1em; color:#777;">まだ きろく は ないよ</li>
                <?php else: ?>
                    <?php foreach ($high_scores as $index => $score_data): ?>
                        <li>
                            <span class="rank"><?php echo $index + 1; ?>.</span>
                            <span class="value"><?php echo htmlspecialchars($score_data['score']); ?></span>
                            <span style="font-size:0.8em;"><?php echo htmlspecialchars($score_data['label']); ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <a href="<?php echo htmlspecialchars($start_page_with_params); ?>">
            <button class="start-button">
                スタート
            </button>
        </a>

        <div class="home-button-area">
            <a href="<?php echo htmlspecialchars($home_url); ?>">
                <button class="home-button" aria-label="ホーム画面に戻る">
                    <svg class="icon-home" viewBox="0 0 24 24">
                        <path d="M12 5.69L17 10.19V18H15V12H9V18H7V10.19L12 5.69ZM12 3L2 12H5V20H11V14H13V20H19V12H22L12 3Z"/>
                    </svg>
                </button>
            </a>
        </div>

    </div>

</body>
</html>
