<?php
session_start();

/* ---------------------------------------------
   🔄 すべての学習関連セッションを完全リセット
   ※ subject_select.php → 学年/教科選択へ戻る前提
--------------------------------------------- */

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
unset($_SESSION["yomi2_current_q"]);
unset($_SESSION["yomi2_used_questions"]);
unset($_SESSION["yomi2_correct_count"]);
unset($_SESSION["yomi2_correct_answer"]);

// ▼ 2年 かき
unset($_SESSION["kaki2_current_q"]);
unset($_SESSION["kaki2_used_questions"]);
unset($_SESSION["kaki2_correct_count"]);
unset($_SESSION["kaki2_correct_answer"]);

// ▼ 共通（学習ログ）
unset($_SESSION["learning_session_id"]);   // ← 最後に消す

/* ---------------------------------------------
   🔙 教科選択画面に戻す
--------------------------------------------- */
header("Location: subject_select.php");
exit;
