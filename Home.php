<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Learn+</title>
        <style>
            body{
                background:  linear-gradient(to bottom,#b3e5fc,#81d4fa);
                font-family: "Arial",sans-serif;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height:100vh;
                margin:0;
            }

            .title{
                padding: 15px 40px;
                border:2px solid black;
                border-radius: 10px;
                background-color: white;
                font-size: 2rem;
                font-weight: bold;
                text-align: center;
            }
            .button-box{
                margin-top: 50px;
                display:flex;
                flex-direction: column;
                gap: 20px;
                width:80%;
                max-width: 300px;

            }
            .btn {
                padding: 12px;
                background-color: white;
                border: 2px dashed red;
                border: radius 10px; 
                font-size: 1.2rem;
                cursor: pointer;
                transition:0.3s;
                width:100%;
            }
            .btn:hover{
                background-color: #ffebee;
            }
            .terms{
                position:fixed;
                bottom:10px;
                left:10px;
                font-size: 0.8rem;
                color:#333
            }
            @media(max-width:600px){
                .title{
                    font-size: 1.6rem;
                    padding:10px;
                }
                .btn{
                    font-size: 1rem;
                    padding:10px;
                }
                .button-box{
                    margin-top: 40px;
                    gap:15px;
                }
            }
        </style>
    </head>
    <body>
    <div class="title">Learn+</div>
    <div class="button-box">
    <button class="btn" onclick="location.href='Rogin.php'">ログイン</button>
    <button class="btn" onclick="location.href='index_guest.php'">ゲスト</button>
    </div>
    <div class="terms">
        <a href="#" style="text-decoration:none; color:#333">利用規約</a></div>
    </body>
</html>
