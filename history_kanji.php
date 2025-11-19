<?php
// history_kanji.php (想定)

// 1. セッションを開始（必須：user_idを取得するため）
session_start();

// 2. ログイン状態の確認
if (!isset($_SESSION['user_id'])) {
    header('Location: Rogin.php');
    exit;
}

// 3. データベース接続設定ファイルを読み込む
require_once 'db_config.php';

// 4. セッションから user_id を取得
$user_id = $_SESSION['user_id'];

// 5. subject と category の表示名を定義（ひらがなを使用）
$subject_map = [
    '1yomi' => 'いちねんせい よみ',
    '2yomi' => 'にねんせい よみ',
    '1kaki' => 'いちねんせい かき',
    '2kaki' => 'にねんせい かき',
    // 他の漢字系 subject があればここに追加
];

$category_map = [
    'normal' => 'ふつうモード', 
     'unanswered' => 'やったことないもんだい',
     'low_accuracy' => 'にがてもんだい',
    // 他の category があればここに追加
];

// 6. 漢字系の履歴をデータベースから取得
try {
    // subject が 'yomi' または 'kaki' を含み、かつ end_time が NULL ではないデータを取得
    $sql = "SELECT subject, category, total_questions, correct_count, start_time, session_id FROM learning_session 
             WHERE user_id = ? 
             AND (subject LIKE '%yomi%' OR subject LIKE '%kaki%')
             AND end_time IS NOT NULL  /* <<--- ★ この行を追加・修正しました */
             ORDER BY start_time DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // エラー処理
    $error_message = 'データベースからきろくのしゅとくにしっぱいしました。'; 
    $records = []; 
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>かんじのりれき</title>
<link rel="stylesheet" href="style.css" />
<style>
    /* -------------------------------------- */
    /* 全体の設定 */
    /* -------------------------------------- */
    body {
        /* フォントを太く、視認性の高いものに変更 */
        font-family: "Hiragino Sans", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
        background: linear-gradient(135deg, #fce4ec 0%, #ffffff 100%); /* 明るい背景グラデーション */
        margin: 0;
        padding: 0;
        color: #333; /* 基本の文字色を濃い灰色に */
    }

    /* -------------------------------------- */
    /* ヘッダー */
    /* -------------------------------------- */
    .header {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        background-color: #f48fb1; /* 少し濃いピンク */
        box-shadow: 0 4px 6px rgba(0,0,0,0.15); /* 影を強調 */
    }

    .back-btn {
        text-decoration: none;
        font-size: 30px; /* サイズアップ */
        color: #880e4f; /* 濃いピンク */
        margin-right: 15px;
        font-weight: bold;
    }

    h1 {
        font-size: 28px; /* サイズアップ */
        margin: 0;
        color: #4a148c; /* 紫 */
        font-weight: 900;
    }

    /* -------------------------------------- */
    /* コンテンツエリア */
    /* -------------------------------------- */
    .content {
        background: #fff;
        border-radius: 25px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1); /* 影を視覚的に強調 */
        width: 90%; /* 幅を広げて情報を見やすく */
        max-width: 400px;
        margin: 30px auto;
        padding: 25px;
    }

    /* -------------------------------------- */
    /* 履歴レコード */
    /* -------------------------------------- */
    .record {
        border-bottom: 2px solid #f8bbd0; /* 実線に変更、色を濃く */
        padding: 15px 5px; /* 上下の余白を増やす */
        text-align: left;
        line-height: 1.6; /* 行間を広げ、非常に見やすく */
    }

    .record:last-child {
        border-bottom: none;
    }
    
    .subject-info {
        display: block;
        margin-bottom: 5px;
        font-weight: 800;
        color: #004d40; /* 濃い緑で種類を強調 */
        font-size: 18px; /* 種類名を大きく */
    }

    .mode-info {
        display: block;
        font-size: 14px;
        color: #546e7a; /* モード表示 */
        margin-bottom: 5px;
    }

    .record .title {
        /* 成績（〇問中〇正解）エリア */
        font-weight: 800;
        color: #c62828; /* 深紅で強調 */
        font-size: 20px; /* サイズアップ */
        margin: 5px 0;
    }
    
    .record .highlight {
        /* 数字を特に強調 */
        font-size: 22px; 
        font-weight: 900;
        color: #d84315; /* 濃いオレンジで強調 */
        margin: 0 2px;
    }

    .record .result {
        /* 正答率のラベル */
        font-size: 16px;
        margin: 3px 0;
        color: #333;
        font-weight: 600;
    }

    .record .datetime {
        font-size: 14px; /* 日時を見やすいサイズに */
        color: #757575;
        font-weight: normal;
        margin-left: 10px;
    }
    
    .detail-link {
        display: inline-block;
        margin-top: 10px;
        font-size: 14px;
        color: #007bff; /* 標準的なリンク色 */
        text-decoration: underline;
    }

    /* -------------------------------------- */
    /* スクロールとエラー */
    /* -------------------------------------- */
    .scroll-area {
        max-height: 400px; /* 高さを少し増やし、見やすく */
        overflow-y: auto;
    }
    
    .error {
        color: #d32f2f; /* 濃い赤 */
        font-weight: bold;
        text-align: center;
        padding: 10px;
        background-color: #ffebee;
        border-radius: 10px;
    }
    /* レコードがない場合のメッセージ */
    .record-list > p {
        font-size: 16px;
        color: #555;
        padding: 10px 0;
    }
</style>
</head>
<body>
<div class="background">
<header>
<div class="header">
     <a href="history_select.php" class="back-btn">←</a>
     <h1>かこのもんだい</h1>
</div>
</header>
<main>

<div id="record-list" class="record-list scroll-area content">
    <?php if (isset($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php elseif (empty($records)): ?>
        <p style="text-align: center; color: #555;">まだきろくがありません。</p>
    <?php else: ?>
        <?php foreach ($records as $r): 
            $rate = ($r['total_questions'] > 0) ? round(($r['correct_count'] / $r['total_questions']) * 100) : 0;
            $subject_name = $subject_map[$r['subject']] ?? $r['subject'];
            $category_name = $category_map[$r['category']] ?? $r['category'];
            // 日時フォーマットを変更（和風に）
            $date_time = date('Y/m/d H:i', strtotime($r['start_time']));
        ?>
            <div class="record">
                <span class="subject-info">
                    <?php echo htmlspecialchars($subject_name); ?>
                    <span class="datetime"><?php echo htmlspecialchars($date_time); ?></span>
                </span>
                <span class="mode-info">
                    モード: <?php echo htmlspecialchars($category_name); ?>
                </span>
                <p class="title">
                    <span class="highlight"><?php echo htmlspecialchars($r['correct_count']); ?></span> せいかい / 
                    <span class="highlight"><?php echo htmlspecialchars($r['total_questions']); ?></span> もん
                </p>
                <p class="result">
                    せいとうりつ：<span class="highlight"><?php echo htmlspecialchars($rate); ?>%</span>
                </p>
                <a href="kanji_detail.php?session_id=<?php echo urlencode($r['session_id']); ?>" class="detail-link">しょうさいをみる</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</main>
</div>
<script>
    // 戻るボタンのJSイベントは現状維持
    document.querySelector('.back-btn').onclick = function() {
        location.href = 'history_select.php';
    };
    
    function switchMode(mode) {
      console.log(mode + "モードに切り替えました。");
    }
</script>
</body>
</html>
