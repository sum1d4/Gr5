<?php
// math_result_2tasi.php
// ★修正点1: セッションを開始 (これは元のコードにもありました)
session_start();
// ★DB接続設定ファイルを読み込みます
require_once 'db_config.php'; 

// ★デバッグ用：エラーを画面に表示する設定 (動作確認が済んだら削除/コメントアウトしてください)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ====================================================
// ★【最重要修正点】ユーザーIDをセッションから取得
// ====================================================
// 実際の認証システムが 'user_id' というキーを使っていると想定
$user_id = $_SESSION['user_id'] ?? 0; 
// ====================================================

// データの受け取り
$user_answer = isset($_POST['user_answer']) ? (string)$_POST['user_answer'] : null;
$question = isset($_POST['question']) ? htmlspecialchars($_POST['question']) : '問題がありません';
$correct_answer = isset($_POST['correct_answer']) ? (string)$_POST['correct_answer'] : null;
$current_question_num = isset($_POST['current_question_num']) ? (int)$_POST['current_question_num'] : 1;

// 正誤判定
$is_correct = ($user_answer !== null && $correct_answer !== null && (string)$user_answer === (string)$correct_answer);
$total_questions = 10; // 全問題数

// ★修正点2: 正解の場合、セッションの正解数カウンターをインクリメント
if ($is_correct) {
    // セッションの正解数を加算
    $_SESSION['correct_count'] = ($_SESSION['correct_count'] ?? 0) + 1;
}

// 画面表示用の変数準備
$next_question_num = $current_question_num + 1;
$current_score = $_SESSION['correct_count'] ?? 0;

// ====================================================
// 10問目（最終問題）が終了した時の処理 - DB登録ロジックを追加
// ====================================================
if ($current_question_num >= $total_questions) {
    $button_label = 'こんかいのけっか';
    
    // ▼▼▼ データベース登録処理（追加部分） ▼▼▼
    try {
        // テーブル名 'learning_session' を使用
        $sql = "INSERT INTO learning_session (user_id, subject, category, total_questions, correct_count, start_time, end_time)
                VALUES (:user_id, :subject, :category, :total_questions, :correct_count, :start_time, :end_time)";

        $stmt = $pdo->prepare($sql);

        // パラメータ設定
        $params = [
            ':user_id' => $user_id,                // セッションから取得したユーザーID
            ':subject' => '2tasi',                 // 教科/レベルを適切に設定 (今回は '2tasi' を使用)
            ':category' => 'normal',               // カテゴリ
            ':total_questions' => $total_questions,
            ':correct_count' => $current_score,
            ':start_time' => $_SESSION['start_time'] ?? date('Y-m-d H:i:s'), // セッションから開始時刻を取得
            ':end_time' => date('Y-m-d H:i:s')
        ];

        $stmt->execute($params);

        // 成功したらセッションをクリアして進む
        unset($_SESSION['correct_count']);
        unset($_SESSION['start_time']);

        // 最終画面へ
        $next_page_url = 'final_result.php?correct=' . $current_score . '&total=' . $total_questions;

    } catch (PDOException $e) {
        // エラー処理：データベースエラーが発生したら画面に表示
        echo "<div style='background:#ffebee; color:#c62828; padding:20px; border:2px solid red; margin:20px;'>";
        echo "<h1>データベース登録エラーが発生しました (T_T)</h1>";
        echo "<p><strong>エラー内容:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<hr>";
        echo "<h3>現在のデータ:</h3>";
        echo "<ul>";
        echo "<li><strong>User ID:</strong> " . $user_id . " (セッションから取得)</li>";
        echo "<li><strong>正解数:</strong> " . $current_score . "問</li>";
        echo "</ul>";
        echo "<p>※User IDが **0** の場合、セッションにIDが設定されていません。認証処理を見直してください。</p>";
        echo "</div>";
        exit;
    }
    // ▲▲▲ データベース登録処理ここまで ▲▲▲

} else {
    // 最終問題ではない場合
    $button_label = 'つぎのもんだい ►';
    // 遷移先: math_question_2tasi.phpに次の問題番号を渡す (元のコードを維持)
    $next_page_url = 'math_question_2tasi.php?q=' . $next_question_num;
}


// せいかい/ざんねん メッセージの設定
if ($is_correct) {
    $message_label = 'せいかい！ 🎉';
    $message_color = '#4caf50'; // 正解時は緑系
    $message_bg = '#e8f5e9'; // 薄い緑
} else {
    $message_label = 'ざんねん... 😥';
    $message_color = '#d32f2f'; // 不正解時は赤系
    $message_bg = '#ffebee'; // 薄い赤
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>計算結果 (第<?php echo $current_question_num; ?>問)</title>
    <style>
        /* (スタイルシートは変更なしのため省略。元のコードのままです) */
        body {
            background: linear-gradient(to bottom, #b3e5fc, #81d4fa);
            font-family: 'Inter', 'Noto Sans JP', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding-top: 50px;
            min-height: 100vh;
        }

        .main-content {
            width: 90%;
            max-width: 380px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* 結果コンテナ (白いカード) */
        .result-container {
            width: 100%;
            padding: 25px 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            text-align: center;
            box-sizing: border-box;
        }

        /* 問題番号表示 */
        .question-num-display {
            font-size: 1.2em;
            font-weight: bold;
            color: #1565c0;
            margin-bottom: 20px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e0e0e0;
        }

        /* 問題表示ボックス */
        .question-display {
            background-color: #4caf50;
            color: white;
            padding: 15px 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 2.5em;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2) inset;
        }

        /* ユーザー解答表示 */
        .answer-display {
            width: 150px;
            height: 55px;
            line-height: 55px;
            text-align: right;
            font-size: 2.5em;
            font-weight: 700;
            border: 3px solid #1565c0;
            border-radius: 8px;
            padding: 0 10px;
            background-color: #e3f2fd;
            color: #1565c0;
            box-sizing: border-box;
            margin: 5px auto 10px auto;
            display: block;
        }

        /* 正誤メッセージラベル */
        .message-label {
            font-size: 2.5em;
            font-weight: 900;
            color: <?php echo htmlspecialchars($message_color); ?>;
            background-color: <?php echo htmlspecialchars($message_bg); ?>;
            border: 4px solid <?php echo htmlspecialchars($message_color); ?>;
            display: inline-block;
            padding: 15px 30px;
            margin: 25px 0 25px 0;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* 不正解時の正解表示エリア */
        .correct-answer-area {
            margin-top: 15px;
            padding: 15px;
            background-color: #fffde7;
            border: 2px solid #ffc107;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }
        .correct-answer-area span {
            color: #ff5722;
            font-size: 1.6em;
            margin-left: 5px;
            font-weight: 900;
        }

        /* 次のボタン */
        .next-button {
            background-color: #42a5f5;
            color: white;
            border: none;
            padding: 15px 30px;
            cursor: pointer;
            font-size: 1.5em;
            font-weight: 700;
            border-radius: 8px;
            width: 90%;
            max-width: 250px;
            margin: 20px auto 0 auto;
            text-decoration: none;
            display: block;
            text-align: center; /* リンク要素なので中央揃えを追加 */
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: background-color 0.1s, transform 0.1s;
        }
        .next-button:active {
              background-color: #1e88e5;
              transform: translateY(1px);
        }
    </style>
</head>
<body>
    
    <div class="main-content">

        <p class="question-num-display">【もんだい <?php echo $current_question_num; ?> のけっか】</p>

        <div class="result-container">
            <div class="question-display">
                <?php echo $question; ?> =
            </div>

            <div class="answer-display">
                <?php echo htmlspecialchars($user_answer); ?>
            </div>
            
            <div class="message-label">
                <?php echo $message_label; ?>
            </div>

            <?php if (!$is_correct): // 不正解の場合（ざんねん... の場合） ?>
            <div class="correct-answer-area">
                せいかいは <span><?php echo htmlspecialchars($correct_answer); ?></span> でした。
            </div>
            <?php endif; ?>

            <a href="<?php echo htmlspecialchars($next_page_url); ?>" class="next-button">
                <?php echo $button_label; ?>
            </a>
        </div>
    </div>

</body>
</html>
