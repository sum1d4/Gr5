<?php
// kanji_detail.php

// 1. セッションを開始
session_start();

// 2. ログイン状態の確認
if (!isset($_SESSION['user_id'])) {
    header('Location: Rogin.php');
    exit;
}

// 3. データベース接続設定ファイルを読み込む
require_once 'db_config.php';

// 4. GETパラメータから session_id を取得
$session_id = $_GET['session_id'] ?? null;
if (!$session_id) {
    header('Location: history_select.php'); 
    exit;
}

// 5. セッション情報と回答履歴を取得
try {
    // 該当セッションの情報を取得
    $sql_session = "SELECT subject, category, total_questions, correct_count, start_time FROM learning_session 
                    WHERE session_id = ? AND user_id = ?";
    $stmt_session = $pdo->prepare($sql_session);
    $stmt_session->execute([$session_id, $_SESSION['user_id']]);
    $session_info = $stmt_session->fetch(PDO::FETCH_ASSOC);

    if (!$session_info) {
        $error_message = '指定されたきろくが見つかりません。';
        $records = [];
    } else {
        // 該当セッションの全回答履歴を取得
        $sql_answers = "SELECT problem_id, user_answer, is_correct, session_id FROM answer_record 
                        WHERE session_id = ? ORDER BY record_id ASC";
        $stmt_answers = $pdo->prepare($sql_answers);
        $stmt_answers->execute([$session_id]);
        $answer_records = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);

        $subject = $session_info['subject'];
        $records = [];

        // 6. 問題ごとの詳細情報と複数正解を結合
        foreach ($answer_records as $answer) {
            $problem_id = $answer['problem_id'];
            $question_text = '';
            $correct_answers = []; // 複数正解を格納するための配列に変更
            $db_table = '';

            // subject に応じて検索テーブルを決定
            if (strpos($subject, 'yomi') !== false) {
                // 読み問題 ('1yomi', '2yomi') の場合 -> kanji_reading
                $db_table = 'kanji_reading';
                
                // 1. 問題文（reading_text）を取得
                $sql_question = "SELECT reading_text FROM kanji_reading WHERE question_id = ? LIMIT 1";
                $stmt_question = $pdo->prepare($sql_question);
                $stmt_question->execute([$problem_id]);
                $question_detail = $stmt_question->fetch(PDO::FETCH_ASSOC);
                $question_text = $question_detail['reading_text'] ?? '問題なし';
                
                // 2. 複数ある可能性のある正解（reading_answer）をすべて取得
                $sql_answers_all = "SELECT reading_answer FROM kanji_reading WHERE question_id = ?";
                $stmt_answers_all = $pdo->prepare($sql_answers_all);
                $stmt_answers_all->execute([$problem_id]);
                $correct_answers = $stmt_answers_all->fetchAll(PDO::FETCH_COLUMN);

            } elseif (strpos($subject, 'kaki') !== false) {
                // 書き問題 ('1kaki', '2kaki') の場合 -> kanji
                $db_table = 'kanji';
                
                // 問題文 (question_text) と正解 (answer) を取得
                $sql_detail = "SELECT question_text, answer FROM kanji WHERE question_id = ?";
                $stmt_detail = $pdo->prepare($sql_detail);
                $stmt_detail->execute([$problem_id]);
                $detail = $stmt_detail->fetch(PDO::FETCH_ASSOC);
                
                $question_text = $detail['question_text'] ?? '問題なし';
                // 書き問題の正解は一つと想定し、配列に格納
                $correct_answers = [$detail['answer'] ?? '正解なし'];
            }

            $records[] = [
                'question_text' => $question_text,
                'correct_answers' => $correct_answers, // 配列として格納
                'user_answer' => $answer['user_answer'],
                'is_correct' => $answer['is_correct'],
            ];
        }

    }

} catch (PDOException $e) {
    // 開発環境向けエラー表示: 'データベースからきろくのしゅとくにしっぱいしました: ' . $e->getMessage();
    $error_message = 'データベースからきろくのしゅとくにしっぱいしました。'; 
    $session_info = null;
    $records = [];
}

// subject と category の表示名を定義
$subject_map = [
    '1yomi' => 'いちねんせい よみ',
    '2yomi' => 'にねんせい よみ',
    '1kaki' => 'いちねんせい かき',
    '2kaki' => 'にねんせい かき',
];

$category_map = [
    'normal' => 'ふつうモード', 
    'unanswered' => 'やったことないもんだい',
    'low_accuracy' => 'にがてもんだい',
];

$display_subject = $subject_map[$session_info['subject'] ?? ''] ?? '不明';
$display_category = $category_map[$session_info['category'] ?? ''] ?? '不明';
$display_date = date('Y/m/d H:i', strtotime($session_info['start_time'] ?? 'now'));
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>かんじの詳細</title>
<link rel="stylesheet" href="style.css" />
<style>
    /* -------------------------------------- */
    /* ベーススタイル（history_kanji.php と統一） */
    /* -------------------------------------- */
    body {
        font-family: "Hiragino Sans", "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
        background: linear-gradient(135deg, #fce4ec 0%, #ffffff 100%);
        margin: 0;
        padding: 0;
        color: #333;
    }
    .header {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        background-color: #f48fb1;
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
    }
    .back-btn {
        text-decoration: none;
        font-size: 30px;
        color: #880e4f;
        margin-right: 15px;
        font-weight: bold;
    }
    h1 {
        font-size: 24px;
        margin: 0;
        color: #4a148c;
        font-weight: 900;
    }
    .content {
        background: #fff;
        border-radius: 25px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        width: 95%;
        max-width: 600px;
        margin: 30px auto;
        padding: 20px;
    }
    .scroll-area {
        max-height: 500px;
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

    /* -------------------------------------- */
    /* 詳細情報ヘッダー */
    /* -------------------------------------- */
    .summary {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px double #f48fb1;
    }
    .summary p {
        margin: 5px 0;
        font-size: 16px;
        font-weight: 600;
    }
    .summary .main-result {
        font-size: 20px;
        color: #880e4f;
        font-weight: 900;
    }

    /* -------------------------------------- */
    /* 問題ごとのレコード */
    /* -------------------------------------- */
    .problem-record {
        padding: 15px 0;
        border-bottom: 1px dashed #f8bbd0;
        line-height: 1.5;
    }
    .problem-record:last-child {
        border-bottom: none;
    }

    .question-text {
        font-size: 24px;
        font-weight: 900;
        color: #4a148c;
        margin-bottom: 8px;
    }

    .status {
        font-size: 16px;
        font-weight: 700;
        padding: 5px 0;
        border-radius: 5px;
    }

    .status.correct {
        color: #2e7d32;
        background-color: #e8f5e9;
        padding: 5px 10px;
        display: inline-block; /* インラインブロックにして幅を内容に合わせる */
    }
    .status.incorrect {
        color: #d32f2f;
        background-color: #ffebee;
        padding: 5px 10px;
        display: inline-block;
    }
    
    .answer-details {
        margin-top: 5px;
        font-size: 15px;
        padding-top: 5px; /* 上に少し隙間を開ける */
    }
    
    .answer-details div {
        margin-top: 5px;
    }

    .answer-details .label {
        font-weight: 700;
        margin-right: 5px;
        color: #555;
    }
    
    .answer-details .user-answer-text {
        color: #d84315; /* ユーザーの答えはオレンジ */
        font-weight: 900;
        margin-right: 15px;
    }

    .answer-details .correct-answer-list {
        display: inline;
        color: #004d40; /* 正解は濃い緑 */
        font-weight: 900;
    }

</style>
</head>
<body>
<div class="background">
<header>
<div class="header">
     <a href="history_kanji.php" class="back-btn">←</a>
     <h1><?php echo htmlspecialchars($display_subject); ?>のしょうさい</h1>
</div>
</header>
<main>

<div class="content">

    <?php if (isset($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php elseif ($session_info): ?>

        <div class="summary">
            <p>きろくび：<?php echo htmlspecialchars($display_date); ?></p>
            <p>モード：<?php echo htmlspecialchars($display_category); ?></p>
            <p class="main-result">
                せいせき: <span class="main-result-score"><?php echo htmlspecialchars($session_info['correct_count']); ?></span>
                せいかい / <?php echo htmlspecialchars($session_info['total_questions']); ?> もん
            </p>
        </div>

        <div class="scroll-area">
            <?php foreach ($records as $r): ?>
                <div class="problem-record">
                    <div class="question-text">
                        <?php 
                            echo (strpos($session_info['subject'], 'kaki') !== false) 
                                ? '「' . htmlspecialchars($r['question_text']) . '」を書きましょう'
                                : htmlspecialchars($r['question_text']); 
                        ?>
                    </div>
                    
                    <?php if ($r['is_correct'] == 1): ?>
                        <div class="status correct">
                            ✅ せいかい！
                        </div>
                    <?php else: ?>
                        <div class="status incorrect">
                            ❌ ふせいかい
                        </div>
                    <?php endif; ?>

                    <div class="answer-details">
                        <div class="user-answer-row">
                            <span class="label">あなたのこたえ:</span> 
                            <span class="user-answer-text"><?php echo htmlspecialchars($r['user_answer']); ?></span>
                        </div>
                        
                        <div class="correct-answer-row">
                            <span class="label">せいかい:</span> 
                            <span class="correct-answer-list"><?php echo htmlspecialchars(implode('、', $r['correct_answers'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($records)): ?>
                <p style="text-align: center; color: #555;">このセッションの回答きろくがありません。</p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>
</main>
</div>
<script>
    document.querySelector('.back-btn').onclick = function() {
        window.location.href = 'history_kanji.php';
        return false;
    };
</script>
</body>
</html>
