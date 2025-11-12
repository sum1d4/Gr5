<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3分間スコアアタック！漢字書き取り選択ゲーム</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* フォント設定 */
        body { font-family: 'Inter', sans-serif; }
        
        /* 選択肢ボタンの基本的なスタイル */
        .choice-button { 
            background-color: white; 
            border: 4px solid #f0a040; /* Orange-ish border */
            padding: 25px 35px; 
            font-size: 40px; 
            border-radius: 12px; 
            cursor: pointer; 
            transition: all 0.2s;
            display: inline-block; 
            box-shadow: 0 4px #d08020; /* 押し込み効果のためのシャドウ */
            font-weight: bold;
            color: #333;
            min-width: 120px; /* 漢字が1文字でもボタン幅を確保 */
        }
        .choice-button:hover {
            background-color: #fce7d8; /* Light orange hover */
            transform: translateY(-2px);
            box-shadow: 0 6px #d08020;
        }
        .choice-button:active {
            transform: translateY(2px);
            box-shadow: 0 2px #d08020;
        }

        /* 問題ボックス (黒板イメージ) */
        #question-box { 
            background-color: #046307; 
            color: white; 
            padding: 40px 20px; 
            margin-bottom: 30px; 
            border-radius: 10px; 
            box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
            min-height: 180px;
        }
        
        /* 読み仮名 */
        .reading-text { 
            font-size: 48px; 
            font-weight: bold;
            color: yellow; 
            margin-top: 10px;
        }
        
        /* 選択肢コンテナ */
        .choices-container { 
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        /* 選択肢に合わせたフィードバック */
        .feedback-correct {
            color: #10b981; /* Green-500 */
        }
        .feedback-incorrect {
            color: #ef4444; /* Red-500 */
        }
        
        /* モーダル表示制御 */
        .pointer-events-none {
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div id="game-container" class="w-full max-w-md bg-white shadow-2xl rounded-xl p-6 transition-all duration-500">
        
        <div class="flex justify-end mb-4">
            <button id="quit-button" class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold py-1 px-3 rounded-md shadow-md transition duration-200 hidden" onclick="quitGame()">
                ゲームをやめる
            </button>
        </div>

        <div class="flex justify-between items-center mb-6 border-b pb-3">
            <div class="text-lg font-semibold text-gray-700">
                スコア: <span id="score" class="text-green-600 text-2xl font-bold ml-1">0</span> てん
            </div>
            <div class="text-2xl font-extrabold text-red-600 bg-red-100 px-3 py-1 rounded-lg shadow-inner">
                のこり: <span id="timer">3:00</span>
            </div>
        </div>

        <div id="question-area" class="text-center mb-6">
            
            <div id="question-box">
                <div class="text-2xl font-semibold mb-2">この「よみ」にあうかんじは？</div>
                
                <div class="reading-text" id="reading-display"></div>
            </div>

            <div class="choices-container" id="choices-container">
                </div>
            
            <p id="feedback" class="h-6 mt-4 text-xl font-bold"></p>
        </div>
    </div>

    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 transition-opacity duration-300">
        <div id="modal-content" class="bg-white p-8 rounded-xl w-11/12 max-w-sm text-center shadow-2xl scale-100 transition-transform duration-500">
            <h2 id="modal-title" class="text-3xl font-bold text-gray-800 mb-4">スコアアタック！</h2>
            <p id="modal-message" class="text-gray-600 mb-6 text-lg">
                せいかいをえらんでね！
            </p>
            
            <button id="start-button" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-full text-xl shadow-lg transition duration-200" onclick="startGame()">
                ゲームスタート
            </button>
            
        </div>
    </div>

    <script>
        // --- 漢字書き取り問題リスト (ご提供いただいたデータから抽出・修正) ---
        // データはJavaScriptで扱えるよう、()から[]に修正されています。
        // データ構造の解釈: [3]:読み本体, [4]:送り仮名, [5]:正解漢字, [6]:ダミー漢字
        const RAW_PROBLEMS = [
            ['KJ001R', '1', '読み', '一', null, '', ''],
            ['KJ001W', '1', '書き', 'いち', '', '一', '二'],
            ['KJ002R', '1', '読み', '右', null, '', ''],
            ['KJ002W', '1', '書き', 'みぎ', '', '右', '左'],
            ['KJ003R', '1', '読み', '雨', null, '', ''],
            ['KJ003W', '1', '書き', 'あめ', '', '雨', '雪'],
            ['KJ004R', '1', '読み', '円', null, '', ''],
            ['KJ004W', '1', '書き', 'えん', '', '円', '玉'],
            ['KJ005R', '1', '読み', '王', null, '', ''],
            ['KJ005W', '1', '書き', 'おう', '', '王', '玉'],
            ['KJ006R', '1', '読み', '音', null, '', ''],
            ['KJ006W', '1', '書き', 'おと', '', '音', '日'],
            ['KJ007R', '1', '読み', '下', null, '', ''],
            ['KJ007W', '1', '書き', 'した', '', '下', '上'],
            ['KJ008R', '1', '読み', '火', null, '', ''],
            ['KJ008W', '1', '書き', 'ひ', '', '火', '水'],
            ['KJ009R', '1', '読み', '花', null, '', ''],
            ['KJ009W', '1', '書き', 'はな', '', '花', '草'],
            ['KJ010R', '1', '読み', '貝', null, '', ''],
            ['KJ010W', '1', '書き', 'かい', '', '貝', '見'],
            ['KJ011R', '1', '読み', '学', null, '', ''],
            ['KJ011W', '1', '書き', 'がく', '', '学', '字'],
            ['KJ012R', '1', '読み', '気', null, '', ''],
            ['KJ012W', '1', '書き', 'き', '', '気', '天'],
            ['KJ013R', '1', '読み', '九', null, '', ''],
            ['KJ013W', '1', '書き', 'きゅう', '', '九', '丸'],
            ['KJ014R', '1', '読み', '休', null, '', ''],
            ['KJ014W', '1', '書き', 'やす', 'み', '休', '体'],
            ['KJ015R', '1', '読み', '玉', null, '', ''],
            ['KJ015W', '1', '書き', 'たま', '', '玉', '王'],
            ['KJ016R', '1', '読み', '金', null, '', ''],
            ['KJ016W', '1', '書き', 'きん', '', '金', '全'],
            ['KJ017R', '1', '読み', '空', null, '', ''],
            ['KJ017W', '1', '書き', 'そら', '', '空', '穴'],
            ['KJ018R', '1', '読み', '月', null, '', ''],
            ['KJ018W', '1', '書き', 'つき', '', '月', '日'],
            ['KJ019R', '1', '読み', '犬', null, '', ''],
            ['KJ019W', '1', '書き', 'いぬ', '', '犬', '太'],
            ['KJ020R', '1', '読み', '見', null, '', ''],
            ['KJ020W', '1', '書き', 'み', 'る', '見', '貝'],
            ['KJ021R', '1', '読み', '五', null, '', ''],
            ['KJ021W', '1', '書き', 'ご', '', '五', '三'],
            ['KJ022R', '1', '読み', '口', null, '', ''],
            ['KJ022W', '1', '書き', 'くち', '', '口', '日'],
            ['KJ023R', '1', '読み', '校', null, '', ''],
            ['KJ023W', '1', '書き', 'こう', '', '校', '木'],
            ['KJ024R', '1', '読み', '左', null, '', ''],
            ['KJ024W', '1', '書き', 'ひだり', '', '左', '右'],
            ['KJ025R', '1', '読み', '三', null, '', ''],
            ['KJ025W', '1', '書き', 'さん', '', '三', '川'],
            ['KJ026R', '1', '読み', '山', null, '', ''],
            ['KJ026W', '1', '書き', 'やま', '', '山', '川'],
            ['KJ027R', '1', '読み', '子', null, '', ''],
            ['KJ027W', '1', '書き', 'こ', '', '子', '字'],
            ['KJ028R', '1', '読み', '四', null, '', ''],
            ['KJ028W', '1', '書き', 'し', '', '四', '円'],
            ['KJ029R', '1', '読み', '糸', null, '', ''],
            ['KJ029W', '1', '書き', 'いと', '', '糸', '系'],
            ['KJ030R', '1', '読み', '字', null, '', ''],
            ['KJ030W', '1', '書き', 'じ', '', '字', '学'],
            ['KJ031R', '1', '読み', '耳', null, '', ''],
            ['KJ031W', '1', '書き', 'みみ', '', '耳', '目'],
            ['KJ032R', '1', '読み', '七', null, '', ''],
            ['KJ032W', '1', '書き', 'なな', '', '七', '匕'],
            ['KJ033R', '1', '読み', '車', null, '', ''],
            ['KJ033W', '1', '書き', 'くるま', '', '車', '東'],
            ['KJ034R', '1', '読み', '手', null, '', ''],
            ['KJ034W', '1', '書き', 'て', '', '手', '毛'],
            ['KJ035R', '1', '読み', '十', null, '', ''],
            ['KJ035W', '1', '書き', 'じゅう', '', '十', '千'],
            ['KJ036R', '1', '読み', '出', null, '', ''],
            ['KJ036W', '1', '書き', 'で', 'る', '出', '山'],
            ['KJ037R', '1', '読み', '女', null, '', ''],
            ['KJ037W', '1', '書き', 'おんな', '', '女', '子'],
            ['KJ038R', '1', '読み', '小', null, '', ''],
            ['KJ038W', '1', '書き', 'ちい', 'さい', '小', '少'],
            ['KJ039R', '1', '読み', '上', null, '', ''],
            ['KJ039W', '1', '書き', 'うえ', '', '上', '下'],
            ['KJ040R', '1', '読み', '森', null, '', ''],
            ['KJ040W', '1', '書き', 'もり', '', '森', '林'],
            ['KJ041R', '1', '読み', '人', null, '', ''],
            ['KJ041W', '1', '書き', 'ひと', '', '人', '入'],
            ['KJ042R', '1', '読み', '水', null, '', ''],
            ['KJ042W', '1', '書き', 'みず', '', '水', '氷'],
            ['KJ043R', '1', '読み', '正', null, '', ''],
            ['KJ043W', '1', '書き', 'ただ', 'しい', '正', '止'],
            ['KJ044R', '1', '読み', '生', null, '', ''],
            ['KJ044W', '1', '書き', 'なま', '', '生', '牛'],
            ['KJ045R', '1', '読み', '青', null, '', ''],
            ['KJ045W', '1', '書き', 'あお', '', '青', '清'],
            ['KJ046R', '1', '読み', '夕', null, '', ''],
            ['KJ046W', '1', '書き', 'ゆう', '', '夕', 'タ'],
            ['KJ047R', '1', '読み', '石', null, '', ''],
            ['KJ047W', '1', '書き', 'いし', '', '石', '右'],
            ['KJ048R', '1', '読み', '赤', null, '', ''],
            ['KJ048W', '1', '書き', 'あか', '', '赤', '土'],
            ['KJ049R', '1', '読み', '千', null, '', ''],
            ['KJ049W', '1', '書き', 'せん', '', '千', '十'],
            ['KJ050R', '1', '読み', '川', null, '', ''],
            ['KJ050W', '1', '書き', 'かわ', '', '川', '三'],
            ['KJ051R', '1', '読み', '先', null, '', ''],
            ['KJ051W', '1', '書き', 'さき', '', '先', '牛'],
            ['KJ052R', '1', '読み', '早', null, '', ''],
            ['KJ052W', '1', '書き', 'はや', 'い', '早', '草'],
            ['KJ053R', '1', '読み', '草', null, '', ''],
            ['KJ053W', '1', '書き', 'くさ', '', '草', '早'],
            ['KJ054R', '1', '読み', '足', null, '', ''],
            ['KJ054W', '1', '書き', 'あし', '', '足', '止'],
            ['KJ055R', '1', '読み', '村', null, '', ''],
            ['KJ055W', '1', '書き', 'むら', '', '村', '林'],
            ['KJ056R', '1', '読み', '大', null, '', ''],
            ['KJ056W', '1', '書き', 'おお', 'きい', '大', '犬'],
            ['KJ057R', '1', '読み', '男', null, '', ''],
            ['KJ057W', '1', '書き', 'おとこ', '', '男', '田'],
            ['KJ058R', '1', '読み', '竹', null, '', ''],
            ['KJ058W', '1', '書き', 'たけ', '', '竹', '本'],
            ['KJ059R', '1', '読み', '中', null, '', ''],
            ['KJ059W', '1', '書き', 'なか', '', '中', '口'],
            ['KJ060R', '1', '読み', '虫', null, '', ''],
            ['KJ060W', '1', '書き', 'むし', '', '虫', '中'],
            ['KJ061R', '1', '読み', '町', null, '', ''],
            ['KJ061W', '1', '書き', 'まち', '', '町', '田'],
            ['KJ062R', '1', '読み', '天', null, '', ''],
            ['KJ062W', '1', '書き', 'てん', '', '天', '夫'],
            ['KJ063R', '1', '読み', '田', null, '', ''],
            ['KJ063W', '1', '書き', 'た', '', '田', '町'],
            ['KJ064R', '1', '読み', '土', null, '', ''],
            ['KJ064W', '1', '書き', 'つち', '', '土', '士'],
            ['KJ065R', '1', '読み', '二', null, '', ''],
            ['KJ065W', '1', '書き', 'に', '', '二', '三'],
            ['KJ066R', '1', '読み', '日', null, '', ''],
            ['KJ066W', '1', '書き', 'ひ', '', '日', '曰'],
            ['KJ067R', '1', '読み', '入', null, '', ''],
            ['KJ067W', '1', '書き', 'い', 'る', '入', '人'],
            ['KJ068R', '1', '読み', '年', null, '', ''],
            ['KJ068W', '1', '書き', 'とし', '', '年', '午'],
            ['KJ069R', '1', '読み', '白', null, '', ''],
            ['KJ069W', '1', '書き', 'しろ', '', '白', '百'],
            ['KJ070R', '1', '読み', '八', null, '', ''],
            ['KJ070W', '1', '書き', 'はち', '', '八', '入'],
            ['KJ071R', '1', '読み', '百', null, '', ''],
            ['KJ071W', '1', '書き', 'ひゃく', '', '百', '白'],
            ['KJ072R', '1', '読み', '文', null, '', ''],
            ['KJ072W', '1', '書き', 'ふみ', '', '文', '又'],
            ['KJ073R', '1', '読み', '木', null, '', ''],
            ['KJ073W', '1', '書き', 'き', '', '木', '本'],
            ['KJ074R', '1', '読み', '本', null, '', ''],
            ['KJ074W', '1', '書き', 'ほん', '', '本', '木'],
            ['KJ075R', '1', '読み', '名', null, '', ''],
            ['KJ075W', '1', '書き', 'な', '', '名', '夕'],
            ['KJ076R', '1', '読み', '目', null, '', ''],
            ['KJ076W', '1', '書き', 'め', '', '目', '耳'],
            ['KJ077R', '1', '読み', '立', null, '', ''],
            ['KJ077W', '1', '書き', 'た', 'つ', '立', '位'],
            ['KJ078R', '1', '読み', '力', null, '', ''],
            ['KJ078W', '1', '書き', 'ちから', '', '力', '刀'],
            ['KJ079R', '1', '読み', '林', null, '', ''],
            ['KJ079W', '1', '書き', 'はやし', '', '林', '森'],
            ['KJ080R', '1', '読み', '六', null, '', ''],
            ['KJ080W', '1', '書き', 'ろく', '', '六', '穴']
        ];

        // '書き'問題のみを抽出・整形
        const KANJI_WRITING_PROBLEMS = [];
        RAW_PROBLEMS.forEach(row => {
            if (row[2] === '書き') {
                // ★修正点1: 読み仮名の結合順序を修正 (読み本体 + 送り仮名)
                const yomi = (row[3] || '') + (row[4] || ''); 
                
                KANJI_WRITING_PROBLEMS.push({
                    id: row[0],
                    yomi: yomi, 
                    // ★修正点2: データ構造のインデックスを正しく指定
                    correct: row[5],   // インデックス [5] を正解漢字とする
                    distractor: row[6] // インデックス [6] をダミー漢字とする
                });
            }
        });
        
        // DOM要素の取得
        const timerDisplay = document.getElementById('timer');
        const scoreDisplay = document.getElementById('score');
        const readingDisplay = document.getElementById('reading-display'); 
        const choicesContainer = document.getElementById('choices-container');
        const feedbackDisplay = document.getElementById('feedback');
        const overlay = document.getElementById('overlay');
        const modalContent = document.getElementById('modal-content');
        const startButton = document.getElementById('start-button');
        const quitButton = document.getElementById('quit-button'); 

        // ゲーム状態変数
        let gameState = 'ready'; 
        let score = 0;
        let totalSolved = 0;
        let timeLeft = 180; // 3分 = 180秒
        let timerInterval;
        let currentCorrectAnswer = ''; 

        /**
         * 配列をシャッフルするユーティリティ関数
         */
        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        /**
         * 時間表示を mm:ss 形式にフォーマットする
         */
        function formatTime(totalSeconds) {
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        /**
         * タイマーを開始する
         */
        function startTimer() {
            timerInterval = setInterval(() => {
                timeLeft--;
                timerDisplay.textContent = formatTime(timeLeft);

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    endGame();
                }
            }, 1000);
        }

        /**
         * 漢字問題をランダムに生成し、選択肢を表示する
         */
        function generateQuestion() {
            if (KANJI_WRITING_PROBLEMS.length === 0) {
                readingDisplay.textContent = '問題がありません！';
                return;
            }
            
            // 問題リストからランダムに問題を選択
            const randomIndex = Math.floor(Math.random() * KANJI_WRITING_PROBLEMS.length);
            const problem = KANJI_WRITING_PROBLEMS[randomIndex];
            
            const reading = problem.yomi;
            currentCorrectAnswer = problem.correct;
            
            // 選択肢の配列を作成し、シャッフルする
            // ★修正点3: 正解とダミーが空文字でないことを確認して配列に加える
            let choices = [];
            if (problem.correct) {
                choices.push(problem.correct);
            }
            if (problem.distractor) {
                choices.push(problem.distractor);
            }

            // 選択肢が2つ未満の場合、ゲーム続行不能なのでエラー表示
            if (choices.length < 2) {
                console.error("問題データ不備: 選択肢が2つ未満です。", problem);
                readingDisplay.textContent = 'データエラー（問題をスキップします）';
                setTimeout(generateQuestion, 500);
                return;
            }

            choices = shuffleArray(choices);

            // 読み仮名を表示
            readingDisplay.textContent = reading;
            
            // 選択肢ボタンを生成
            choicesContainer.innerHTML = ''; // 選択肢をクリア
            choices.forEach(choice => {
                const button = document.createElement('button');
                button.className = 'choice-button';
                button.textContent = choice;
                button.onclick = () => submitAnswer(choice);
                choicesContainer.appendChild(button);
            });
            
            feedbackDisplay.textContent = ''; // フィードバックをクリア
        }

        /**
         * ゲームを開始する
         */
        function startGame() {
            if (gameState === 'playing') return;

            // 状態リセット
            gameState = 'playing';
            score = 0;
            totalSolved = 0;
            timeLeft = 180;
            
            // UIリセット
            scoreDisplay.textContent = score;
            timerDisplay.textContent = formatTime(timeLeft);
            
            // モーダルを非表示にし、ゲーム中断ボタンを表示
            overlay.classList.add('opacity-0', 'pointer-events-none');
            quitButton.classList.remove('hidden');

            // 終了時に生成されたボタンコンテナを削除
            const existingEndButtons = modalContent.querySelector('#end-buttons');
            if(existingEndButtons) {
                existingEndButtons.remove();
            }
            startButton.style.display = 'block'; 

            generateQuestion();
            startTimer();
        }

        /**
         * ゲームを途中で終了する
         */
        function quitGame() {
            if (gameState !== 'playing') return;

            clearInterval(timerInterval);
            timeLeft = 0; 

            endGame();
        }

        /**
         * ゲームを終了し、結果とボタンを表示する
         */
        function endGame() {
            gameState = 'finished';
            clearInterval(timerInterval);
            
            // ゲーム中断ボタンを非表示
            quitButton.classList.add('hidden');

            const modalTitle = document.getElementById('modal-title');
            const modalMessage = document.getElementById('modal-message');

            modalTitle.textContent = 'ゲームおわり！';
            modalMessage.innerHTML = `
                <p class="text-xl font-bold mb-4">おつかれさまでした！</p>
                <p class="text-left mb-2">♦ といたかず: <span class="text-indigo-600 font-extrabold">${totalSolved}</span> もん</p>
                <p class="text-left mb-4">♦ せいかいしたかず: <span class="text-green-600 font-extrabold text-2xl">${score}</span> てん</p>
                <p class="text-left text-sm text-gray-500">※ せいとうりつは ${totalSolved > 0 ? ((score / totalSolved) * 100).toFixed(1) : 0}% です</p>
            `;

            // 初期スタートボタンを非表示にする
            startButton.style.display = 'none';

            // 終了画面用のボタンコンテナを生成
            let buttonContainer = document.getElementById('end-buttons');
            if (!buttonContainer) {
                buttonContainer = document.createElement('div');
                buttonContainer.id = 'end-buttons';
                buttonContainer.className = 'flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4 mt-6';
                modalContent.appendChild(buttonContainer);
            } else {
                buttonContainer.innerHTML = ''; 
            }

            // 1. もういちど！ボタン
            const retryButton = document.createElement('button');
            retryButton.className = 'bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-full text-lg shadow-lg transition duration-200 flex-1 w-full sm:w-auto';
            retryButton.textContent = 'もういちど！';
            retryButton.onclick = startGame;
            buttonContainer.appendChild(retryButton);

            // 2. ホームへ戻るボタン (aタグとして仮に設定)
            const homeButton = document.createElement('a');
            homeButton.href = 'home.php'; 
            homeButton.className = 'bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-full text-lg shadow-lg transition duration-200 flex-1 w-full sm:w-auto text-center';
            homeButton.textContent = 'ホームへもどる';
            buttonContainer.appendChild(homeButton);
            
            overlay.classList.remove('opacity-0', 'pointer-events-none');
        }

        // ------------------------- 入力操作 -------------------------

        /**
         * 回答を提出し、正誤判定を行う
         */
        function submitAnswer(selectedKanji) {
            if (gameState !== 'playing') return;

            totalSolved++; // といた問題数をカウント
            
            const isCorrect = (selectedKanji === currentCorrectAnswer); // ★要件通り、正しい答えのみ正解に

            // 選択肢を非アクティブ化 (連打防止)
            choicesContainer.querySelectorAll('.choice-button').forEach(button => {
                button.disabled = true;
                button.style.opacity = '0.5';
                button.style.pointerEvents = 'none';
            });
            
            if (isCorrect) {
                score++;
                scoreDisplay.textContent = score;
                // 正解のフィードバック
                feedbackDisplay.textContent = 'せいかい！';
                feedbackDisplay.className = 'h-6 mt-4 text-xl font-bold feedback-correct animate-pulse';
            } else {
                // 不正解のフィードバック
                feedbackDisplay.textContent = `ざんねん... (正解は「${currentCorrectAnswer}」)`;
                feedbackDisplay.className = 'h-6 mt-4 text-xl font-bold feedback-incorrect';
            }

            // 次の問題を少し待ってから生成
            setTimeout(() => {
                if (gameState === 'playing') {
                    generateQuestion();
                }
            }, 700);
        }

        // 初期ロード時にスタート画面を表示
        window.onload = function() {
            timerDisplay.textContent = formatTime(timeLeft); 
            readingDisplay.textContent = 'スタートを待っています';
            
            // 念のため、初期状態のオーバーレイを表示
            overlay.classList.remove('opacity-0', 'pointer-events-none');
        };

    </script>

</body>
</html>
