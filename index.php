<?php
session_start();
require_once "db_config.php"; // DB接続ファイル

// 1. ログインチェック
if (!isset($_SESSION["user_id"])) {
    header("Location: Rogin.php"); 
    exit;
}

$user_id = $_SESSION["user_id"];

// 2. データベースから現在の目標値を取得
$sql_target = "SELECT target_questions FROM target WHERE user_id = :uid LIMIT 1";
$stmt_t = $pdo->prepare($sql_target);
$stmt_t->bindValue(":uid", $user_id, PDO::PARAM_INT);
$stmt_t->execute();
$row_target = $stmt_t->fetch(PDO::FETCH_ASSOC);

// 設定がなければデフォルト値（20問）
$current_target = $row_target ? $row_target["target_questions"] : 20;

// 3. 今日の解答数をカウントする
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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ホーム | Learn+</title>
    <style>
        body{
            background: linear-gradient(to bottom, #b3e5fc, #81d4fa);
            font-family: "Arial",sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            margin: 0;
            padding-top: 60px;
        }
        .cloud-box{
            background: white;
            border-radius: 50px;
            padding:15px 30px;
            text-align: center;
            box-shadow: 2px 3px 8px rgba(0,0,0,0.2);
            position: relative;
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        select{
            font-size: 1rem;
            padding:2px 5px;
            border-radius: 5px;
            border: 1px solid #aaa;
        }
        .btn{
            display: block;
            width: 220px;
            text-align:  center;
            padding: 14px;
            border:none;
            border-radius: 20px;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 30px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: 0.3s;
        }
        .study-btn {
            background: linear-gradient(to bottom, #ffb74d, #fb8c00);
        }
        .record-btn {
            background: linear-gradient(to bottom, #ab47bc, #8e24aa);
            margin-top: 20px;
        }
        .record-btn:hover {
            background: #ab47bc;
        }
        @media(max-width: 600px){
            .cloud-box{
                font-size: 1rem;
                padding: 10px 20px;
            }
            .btn{
                width: 180px;
                font-size: 1rem;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="cloud-box">
        <form action="update_target.php" method="post" style="display:inline;">
            🎯もくひょう
            <select name="target_questions" onchange="this.form.submit()" style="font-size: 1.1rem; padding:5px; border-radius: 8px; border: 2px solid #81d4fa; cursor: pointer;">
                <?php 
                // ★ 10〜100までループで表示 ★
                for ($i = 10; $i <= 100; $i += 10) {
                    $selected = ($i == $current_target) ? 'selected' : '';
                    echo "<option value=\"{$i}\" {$selected}>{$i}もん</option>";
                }
                ?>
            </select>
            
            <input type="hidden" name="from_page" value="index.php">
        </form>
        <br>
        
        ⭐いま <span id="now" style="color:#ff9800; font-weight:bold; font-size: 1.5rem;"><?php echo $today_count; ?></span> / <?php echo $current_target; ?> もんといたよ！
    </div>

    <button class="btn study-btn" onclick="location.href='subject_select.php'">✏️べんきょうする</button>
    <button class="btn record-btn" onclick="location.href='history_select.php'">📝きろくをみる</button>
</body>
</html>