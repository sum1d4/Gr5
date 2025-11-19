<?php
// history_math.php (想定)

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
    '1tasi' => 'いちねんせい たしざん',
    '2tasi' => 'にねんせい たしざん',
    '1hiki' => 'いちねんせい ひきざん',
    '2hiki' => 'にねんせい ひきざん',
    // 他の計算系 subject があればここに追加
];

$category_map = [
    'normal' => 'ふつうモード',
    // 他の category があればここに追加
];

// 6. 計算系の履歴をデータベースから取得（category を追加）
try {
    $sql = "SELECT subject, category, total_questions, correct_count, start_time FROM learning_session 
             WHERE user_id = ? AND subject NOT IN ('1yomi', '2yomi','1kaki','2kaki')
             ORDER BY start_time DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // ユーザーに表示するメッセージから技術的なエラー内容は削除
    $error_message = 'データベースからきろくのしゅとくにしっぱいしました。'; 
    $records = [];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>さんすうりれき</title>
<style>
    /* -------------------------------------- */
    /* 全体の設定 */
    /* -------------------------------------- */
    body {
        font-family: "Hiragino Sans", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
        background: linear-gradient(135deg, #c6e9ff 0%, #f0f8ff 100%); /* 明るい青のグラデーション */
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
        background-color: #81d4fa; /* 鮮やかな水色 */
        box-shadow: 0 4px 6px rgba(0,0,0,0.15); /* 影を強調 */
    }

    .back-btn {
        text-decoration: none;
        font-size: 30px; /* サイズアップ */
        color: #00897b; /* 濃いエメラルドグリーン */
        margin-right: 15px;
        font-weight: bold;
    }

    h1 {
        font-size: 28px; /* サイズアップ */
        margin: 0;
        color: #1a237e; /* 濃い青 */
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
        border-bottom: 2px solid #b3e5fc; /* 実線に変更、色を濃く */
        padding: 15px 5px; /* 上下の余白を増やす */
        text-align: left;
        line-height: 1.6; /* 行間を広げ、非常に見やすく */
    }

    .record:last-child {
        border-bottom: none;
    }

    .record .subject-name {
        /* 種類を濃い青で強調 */
        display: block;
        font-size: 18px; 
        color: #0d47a1; 
        margin-bottom: 5px;
        font-weight: 800; 
    }
    
    .record .mode {
        /* モード表示を濃いめの灰色に */
        display: block;
        font-size: 14px; 
        color: #546e7a; 
        margin-bottom: 5px;
    }

    .record .title {
        /* 成績（〇問中〇正解）エリア */
        font-weight: 800;
        color: #d84315; /* オレンジで強調 */
        font-size: 20px; /* サイズアップ */
        margin: 5px 0;
    }
    
    /* 数字を特に強調するためのクラスを追加 */
    .record .title .highlight-number,
    .record .result .highlight-number {
        font-size: 22px; 
        font-weight: 900;
        color: #e53935; /* 赤で強調 */
        margin: 0 2px;
    }

    .record .result {
        /* 正答率のラベル */
        font-size: 16px; 
        margin: 3px 0;
        color: #212121;
        font-weight: 600;
    }

    .record .datetime {
        font-size: 14px; /* 日時を見やすいサイズに */
        color: #757575;
        font-weight: normal;
        margin-left: 10px;
    }


    /* スクロール可能に */
    .scroll-area {
        max-height: 400px; /* 高さを少し増やし、見やすく */
        overflow-y: auto;
    }
    .error {
        color: #d32f2f;
        font-weight: bold;
        text-align: center;
        padding: 10px;
        background-color: #ffebee;
        border-radius: 10px;
    }
</style>
</head>
<body>

<div class="header">
    <a href="history_select.php" class="back-btn">←</a>
    <h1>かこのもんだい</h1>
</div>

<div class="content">
    
    <div class="scroll-area">
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <?php
        if (empty($records)) {
            echo "<p style='text-align:center; font-size: 16px; color: #555;'>まだきろくがありません。</p>";
        }
        
        foreach ($records as $r) {
            // 正答率を計算
            $rate = ($r['total_questions'] > 0) ? round(($r['correct_count'] / $r['total_questions']) * 100) : 0;
            
            // subject の表示名を取得
            $subject_name = $subject_map[$r['subject']] ?? $r['subject'];
            
            // category の表示名を取得
            $category_name = $category_map[$r['category']] ?? $r['category'];
            
            // start_time の形式を調整
            $date_time = date('Y/m/d H:i', strtotime($r['start_time']));
            
            echo "<div class='record'>";
            
            // 問題の種類（いちねんせい たしざん など）と日時を表示
            echo "<div class='subject-name'>{$subject_name} <span class='datetime'>({$date_time})</span></div>";
            
            // category (モード) を表示
            echo "<div class='mode'>モード: {$category_name}</div>"; 
            
            // 結果を表示 (数字に強調クラスを適用)
            echo "<div class='title'><span class='highlight-number'>{$r['correct_count']}</span> せいかい / <span class='highlight-number'>{$r['total_questions']}</span> もん</div>";
            echo "<div class='result'>せいとうりつ：<span class='highlight-number'>{$rate}</span>％</div>";
            
            echo "</div>";
        }
        ?>
    </div>
</div>
</body>
</html>
