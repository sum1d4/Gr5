<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"content="width=device-width,initial-scale=1.0">
        <title>ログイン | Learn+</title>
        <style>
            body{
                background: linear-gradient(to bottom, #b3e5fc, #81d4fa);
                font-family:"Arial",sans-serif;
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:center;
                height:100vh;
                margin:0;
            }
            h1 {
                color: #2e7d32;
                font-size: 1.8rem;
                margin-bottom: 10px;
            }
            h2{
                color: #2e7d32;
                font-size: 1.2rem;
                margin:20px 0 5px;
            }

            input{
                width:220px;
                padding: 10px;
                border:2px solid #999;
                border-radius:10px;
                font-size: 1rem;
                text-align: center;
                box-shadow: 1px 1px 4px  rgba(0,0,0,0.2);
            }
            .login-button {
                margin-top: 30px;
                background: linear-gradient(to bottom, #81c784, #4caf50);
                color: white;
                font-size: 1.3rem;
                border: none;
                border-radius:30px;
                padding: 10px 60px;
                cursor: pointer;
                box-shadow:0 3px 5px rgba(0, 0, 0,0.2);
                transition: 0.3s;
            }
            .login-button:hover {
                background:  #66bb6a;
            }
            .register {
                margin-top: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .back-arrow {
                font-size: 1.8rem;
                color: #1565c0;
                cursor: pointer;
                text-decoration: none;
            }
            .register-button:hover {
                background-color: #1e88e5;
            }
            @media (max-width: 600px){
                input {
                    width: 180px;
                }
                .login-button {
                    padding: 10px 40px;
                    font-size: 1.1rem;
                }
            }
            </style>
    </head>
    <body>
        <h1>ログイン</h1>
        <h2>ニックネーム</h2>
        <input type="text" placeholder="なまえ">
        <h2>がくねん</h2>
        <input type="text" placeholder="がくねん">

        <button class="login-button"onclick="location.href='home.php'">けってい</button>
        <div class="register">
            <a href="index.php" class="back-arrow">←</a>
            <button class="regiter-button" onclick="location.href='register.php'">
                あたらしくとうろく
            </button>
        </div>
    </body>
</html>
