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
            .study-btn {
                background: #ffa726;
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
        <div class ="cloud-box">
            🎯もくひょう
            <select id="target">
                <option>10</option>
                <option selected>20</option>
                <option>30</option>
                <option>40</option>
                <option>50</option>
            </select>
            もん！<br>
            ⭐いま <span id ="now">0</span> もん！
        </div>
        <button class="btn study-btn" onclick="location.href='subject_select.php'">✏️べんきょうする</button>
        <button class="btn record-btn" onclick="location.href='history_select.php'">📝きろくをみる</button>
    </body>
</html>
