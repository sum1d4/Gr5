<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ログイン</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sky-background">
<div class="login-container">
<h1 class="title">ログイン</h1>
<form action="home.php" method="POST"> <div class="input-group">
<label for="nickname" class="label">ニックネーム</label>
<input type="text" id="nickname" name="nickname" placeholder="なまえ" class="text-input" required>
</div>
<div class="input-group">
<label for="grade" class="label">がくねん</label>
<input type="text" id="grade" name="grade" placeholder="がくねん" class="text-input" required>
</div>
<button type="submit" class="button decision-button">けってい</button>
</form>
<div class="register-link">
<a href="register.php" class="button register-button">
<span class="arrow-icon">←</span>
<span class="text">あたらしくとうろく</span>
</a>
</div>
</div>
</div>
</body>
</html>
 