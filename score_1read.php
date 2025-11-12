<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3分間スコアアタック！漢字の読みゲーム</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* フォント設定 */
        body { font-family: 'Inter', sans-serif; }
        
        /* 特殊キーの色 */
        .submit-key {
            background-color: #1d4ed8; /* blue-700 */
            color: white;
        }
        /* 入力ボックスに点滅カーソルを表示 */
        #user-answer {
            caret-color: #1d4ed8;
        }
        /* 答え合わせボタンを単独で配置するためのスタイル */
        .submit-container {
            margin-top: 20px;
        }
        .submit-button-large {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s;
        }
        /* 漢字表示エリアの高さを調整し、漢字を中央に配置 */
        #question-box {
            height: 96px; /* 96px (h-24)を維持 */
            display: flex;
            align-items: center;
            justify-content: center;
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
            <div id="question-box" class="bg-emerald-600 text-white p-4 rounded-lg shadow-xl text-5xl font-extrabold mb-4 h-24">
                
                <span id="kanji-display"></span>
                
                </div>
            
            <input 
                type="text" 
                id="user-answer" 
                class="w-full h-16 text-center text-4xl font-bold border-4 border-emerald-300 rounded-lg p-3 mx-auto shadow-md focus:border-emerald-500 transition-colors"
                maxlength="4" 
                placeholder="よみを入力"
                autocomplete="off"
            >
            <p id="feedback" class="h-6 mt-2 text-sm font-bold"></p>
        </div>

        <div class="submit-container">
            <button id="submit-button" class="submit-button-large submit-key" onclick="submitAnswer()">
                こたえあわせ (Enter)
            </button> 
        </div>

    </div>

    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 transition-opacity duration-300">
        <div id="modal-content" class="bg-white p-8 rounded-xl w-11/12 max-w-sm text-center shadow-2xl scale-100 transition-transform duration-500">
            <h2 id="modal-title" class="text-3xl font-bold text-gray-800 mb-4">スコアアタック！</h2>
            <p id="modal-message" class="text-gray-600 mb-6 text-lg">
                3ふんでどれだけせいかいできるかな？
            </p>
            
            <button id="start-button" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-full text-xl shadow-lg transition duration-200" onclick="startGame()">
                ゲームスタート
            </button>
            
        </div>
    </div>

    <script>
        // --- 漢字問題リスト（いただいたデータを解析して作成） ---
        const RAW_KANJI_DATA = [
            { kanji: '一', yomi: 'いち' }, { kanji: '一', yomi: 'いっ' }, { kanji: '一', yomi: 'ひと' },
            { kanji: '右', yomi: 'う' }, { kanji: '右', yomi: 'ゆう' }, { kanji: '右', yomi: 'みぎ' },
            { kanji: '雨', yomi: 'う' }, { kanji: '雨', yomi: 'あめ' },
            { kanji: '円', yomi: 'えん' }, { kanji: '円', yomi: 'まる' },
            { kanji: '王', yomi: 'おう' },
            { kanji: '音', yomi: 'おん' }, { kanji: '音', yomi: 'いん' }, { kanji: '音', yomi: 'おと' }, { kanji: '音', yomi: 'ね' },
            { kanji: '下', yomi: 'か' }, { kanji: '下', yomi: 'げ' }, { kanji: '下', yomi: 'した' }, { kanji: '下', yomi: 'しも' }, { kanji: '下', yomi: 'もと' }, { kanji: '下', yomi: 'さ' }, { kanji: '下', yomi: 'お' }, { kanji: '下', yomi: 'くだ' },
            { kanji: '火', yomi: 'か' }, { kanji: '火', yomi: 'ひ' }, { kanji: '火', yomi: 'び' }, { kanji: '火', yomi: 'ほ' },
            { kanji: '花', yomi: 'か' }, { kanji: '花', yomi: 'はな' },
            { kanji: '貝', yomi: 'かい' },
            { kanji: '学', yomi: 'がく' }, { kanji: '学', yomi: 'まな' },
            { kanji: '気', yomi: 'き' }, { kanji: '気', yomi: 'け' },
            { kanji: '九', yomi: 'きゅう' }, { kanji: '九', yomi: 'く' }, { kanji: '九', yomi: 'ここの' },
            { kanji: '休', yomi: 'きゅう' }, { kanji: '休', yomi: 'やす' },
            { kanji: '玉', yomi: 'ぎょく' }, { kanji: '玉', yomi: 'たま' },
            { kanji: '金', yomi: 'きん' }, { kanji: '金', yomi: 'こん' }, { kanji: '金', yomi: 'かね' }, { kanji: '金', yomi: 'かな' },
            { kanji: '空', yomi: 'くう' }, { kanji: '空', yomi: 'そら' }, { kanji: '空', yomi: 'あ' }, { kanji: '空', yomi: 'から' },
            { kanji: '月', yomi: 'げつ' }, { kanji: '月', yomi: 'がつ' }, { kanji: '月', yomi: 'つき' },
            { kanji: '犬', yomi: 'けん' }, { kanji: '犬', yomi: 'いぬ' },
            { kanji: '見', yomi: 'けん' }, { kanji: '見', yomi: 'み' },
            { kanji: '五', yomi: 'ご' }, { kanji: '五', yomi: 'いつ' },
            { kanji: '口', yomi: 'こう' }, { kanji: '口', yomi: 'く' }, { kanji: '口', yomi: 'くち' },
            { kanji: '校', yomi: 'こう' },
            { kanji: '左', yomi: 'さ' }, { kanji: '左', yomi: 'ひだり' },
            { kanji: '三', yomi: 'さん' }, { kanji: '三', yomi: 'み' },
            { kanji: '山', yomi: 'さん' }, { kanji: '山', yomi: 'やま' },
            { kanji: '子', yomi: 'し' }, { kanji: '子', yomi: 'す' }, { kanji: '子', yomi: 'こ' },
            { kanji: '四', yomi: 'し' }, { kanji: '四', yomi: 'よん' }, { kanji: '四', yomi: 'よ' },
            { kanji: '糸', yomi: 'し' }, { kanji: '糸', yomi: 'いと' },
            { kanji: '字', yomi: 'じ' }, { kanji: '字', yomi: 'あざ' },
            { kanji: '耳', yomi: 'じ' }, { kanji: '耳', yomi: 'みみ' },
            { kanji: '七', yomi: 'しち' }, { kanji: '七', yomi: 'なな' },
            { kanji: '車', yomi: 'しゃ' }, { kanji: '車', yomi: 'くるま' },
            { kanji: '手', yomi: 'しゅ' }, { kanji: '手', yomi: 'て' },
            { kanji: '十', yomi: 'じゅう' }, { kanji: '十', yomi: 'とお' }, { kanji: '十', yomi: 'と' },
            { kanji: '出', yomi: 'しゅつ' }, { kanji: '出', yomi: 'すい' }, { kanji: '出', yomi: 'だ' }, { kanji: '出', yomi: 'で' },
            { kanji: '女', yomi: 'じょ' }, { kanji: '女', yomi: 'にょ' }, { kanji: '女', yomi: 'おんな' }, { kanji: '女', yomi: 'め' },
            { kanji: '小', yomi: 'しょう' }, { kanji: '小', yomi: 'ちい' }, { kanji: '小', yomi: 'こ' }, { kanji: '小', yomi: 'お' },
            { kanji: '上', yomi: 'じょう' }, { kanji: '上', yomi: 'うえ' }, { kanji: '上', yomi: 'うわ' }, { kanji: '上', yomi: 'かみ' }, { kanji: '上', yomi: 'あ' }, { kanji: '上', yomi: 'のぼ' },
            { kanji: '森', yomi: 'しん' }, { kanji: '森', yomi: 'もり' },
            { kanji: '人', yomi: 'じん' }, { kanji: '人', yomi: 'にん' }, { kanji: '人', yomi: 'ひと' },
            { kanji: '水', yomi: 'すい' }, { kanji: '水', yomi: 'みず' },
            { kanji: '正', yomi: 'せい' }, { kanji: '正', yomi: 'しょう' }, { kanji: '正', yomi: 'ただ' }, { kanji: '正', yomi: 'まさ' },
            { kanji: '生', yomi: 'せい' }, { kanji: '生', yomi: 'しょう' }, { kanji: '生', yomi: 'い' }, { kanji: '生', yomi: 'う' }, { kanji: '生', yomi: 'なま' }, { kanji: '生', yomi: 'は' }, { kanji: '生', yomi: 'き' },
            { kanji: '青', yomi: 'せい' }, { kanji: '青', yomi: 'しょう' }, { kanji: '青', yomi: 'あお' },
            { kanji: '夕', yomi: 'せき' }, { kanji: '夕', yomi: 'ゆう' },
            { kanji: '石', yomi: 'せき' }, { kanji: '石', yomi: 'しゃく' }, { kanji: '石', yomi: 'いし' },
            { kanji: '赤', yomi: 'せき' }, { kanji: '赤', yomi: 'しゃく' }, { kanji: '赤', yomi: 'あか' },
            { kanji: '千', yomi: 'せん' }, { kanji: '千', yomi: 'ち' },
            { kanji: '川', yomi: 'せん' }, { kanji: '川', yomi: 'かわ' },
            { kanji: '先', yomi: 'せん' }, { kanji: '先', yomi: 'さき' },
            { kanji: '早', yomi: 'そう' }, { kanji: '早', yomi: 'はや' },
            { kanji: '草', yomi: 'そう' }, { kanji: '草', yomi: 'くさ' },
            { kanji: '足', yomi: 'そく' }, { kanji: '足', yomi: 'あし' }, { kanji: '足', yomi: 'た' },
            { kanji: '村', yomi: 'そん' }, { kanji: '村', yomi: 'むら' },
            { kanji: '大', yomi: 'だい' }, { kanji: '大', yomi: 'たい' }, { kanji: '大', yomi: 'おお' },
            { kanji: '男', yomi: 'だん' }, { kanji: '男', yomi: 'なん' }, { kanji: '男', yomi: 'おとこ' },
            { kanji: '竹', yomi: 'ちく' }, { kanji: '竹', yomi: 'たけ' },
            { kanji: '中', yomi: 'ちゅう' }, { kanji: '中', yomi: 'なか' },
            { kanji: '虫', yomi: 'ちゅう' }, { kanji: '虫', yomi: 'むし' },
            { kanji: '町', yomi: 'ちょう' }, { kanji: '町', yomi: 'まち' },
            { kanji: '天', yomi: 'てん' }, { kanji: '天', yomi: 'あめ' }, { kanji: '天', yomi: 'あま' },
            { kanji: '田', yomi: 'でん' }, { kanji: '田', yomi: 'た' },
            { kanji: '土', yomi: 'ど' }, { kanji: '土', yomi: 'と' }, { kanji: '土', yomi: 'つち' },
            { kanji: '二', yomi: 'に' }, { kanji: '二', yomi: 'ふた' },
            { kanji: '日', yomi: 'にち' }, { kanji: '日', yomi: 'じつ' }, { kanji: '日', yomi: 'ひ' }, { kanji: '日', yomi: 'び' }, { kanji: '日', yomi: 'か' },
            { kanji: '入', yomi: 'にゅう' }, { kanji: '入', yomi: 'い' }, { kanji: '入', yomi: 'はい' },
            { kanji: '年', yomi: 'ねん' }, { kanji: '年', yomi: 'とし' },
            { kanji: '白', yomi: 'はく' }, { kanji: '白', yomi: 'びゃく' }, { kanji: '白', yomi: 'しろ' },
            { kanji: '八', yomi: 'はち' }, { kanji: '八', yomi: 'や' },
            { kanji: '百', yomi: 'ひゃく' },
            { kanji: '文', yomi: 'ぶん' }, { kanji: '文', yomi: 'もん' }, { kanji: '文', yomi: 'ふみ' },
            { kanji: '木', yomi: 'ぼく' }, { kanji: '木', yomi: 'もく' }, { kanji: '木', yomi: 'き' }, { kanji: '木', yomi: 'こ' },
            { kanji: '本', yomi: 'ほん' }, { kanji: '本', yomi: 'もと' },
            { kanji: '名', yomi: 'めい' }, { kanji: '名', yomi: 'みょう' }, { kanji: '名', yomi: 'な' },
            { kanji: '目', yomi: 'もく' }, { kanji: '目', yomi: 'ぼく' }, { kanji: '目', yomi: 'め' },
            { kanji: '立', yomi: 'りつ' }, { kanji: '立', yomi: 'た' },
            { kanji: '力', yomi: 'りょく' }, { kanji: '力', yomi: 'りき' }, { kanji: '力', yomi: 'ちから' },
            { kanji: '林', yomi: 'りん' }, { kanji: '林', yomi: 'はやし' },
            { kanji: '六', yomi: 'ろく' }, { kanji: '六', yomi: 'む' }
        ];

        // データを漢字ごとに集約し、[読み]の配列を持つ形式に変換する
        const KANJI_PROBLEMS_MAP = RAW_KANJI_DATA.reduce((acc, current) => {
            if (!acc[current.kanji]) {
                acc[current.kanji] = [];
            }
            // 重複を避けるため、念のためチェック
            if (!acc[current.kanji].includes(current.yomi)) {
                acc[current.kanji].push(current.yomi);
            }
            return acc;
        }, {});

        // 最終的な問題リスト (ランダム選択のために配列に戻す)
        const KANJI_PROBLEMS = Object.keys(KANJI_PROBLEMS_MAP).map(kanji => ({
            kanji: kanji,
            yomis: KANJI_PROBLEMS_MAP[kanji]
        }));


        // DOM要素の取得
        const timerDisplay = document.getElementById('timer');
        const scoreDisplay = document.getElementById('score');
        const kanjiDisplay = document.getElementById('kanji-display'); // 漢字表示
        const userAnswerInput = document.getElementById('user-answer');
        const feedbackDisplay = document.getElementById('feedback');
        const overlay = document.getElementById('overlay');
        const modalContent = document.getElementById('modal-content');
        const modalTitle = document.getElementById('modal-title');
        const modalMessage = document.getElementById('modal-message');
        const startButton = document.getElementById('start-button');
        const quitButton = document.getElementById('quit-button'); 

        // ゲーム状態変数
        let gameState = 'ready'; // 'ready', 'playing', 'finished'
        let score = 0;
        let totalSolved = 0;
        let timeLeft = 180; // 3分 = 180秒
        let timerInterval;
        let correctAnswers = []; // 今回の漢字の正解の読みリスト (複数対応)

        // ------------------------- ゲーム制御関数 -------------------------

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
         * 漢字問題をランダムに生成する
         */
        function generateQuestion() {
            // 問題リストからランダムに漢字のペアを選択
            const randomIndex = Math.floor(Math.random() * KANJI_PROBLEMS.length);
            const problem = KANJI_PROBLEMS[randomIndex];
            
            const kanji = problem.kanji;
            correctAnswers = problem.yomis; // その漢字の全ての読みを正解リストとする

            // 漢字を表示
            kanjiDisplay.textContent = kanji;
            
            userAnswerInput.value = ''; // 回答欄をクリア
            feedbackDisplay.textContent = ''; // フィードバックをクリア
            userAnswerInput.focus(); // 回答欄にフォーカス
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
                startButton.style.display = 'block';
            }
            
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
            
            // ゲーム中断ボタンを非表示
            quitButton.classList.add('hidden');

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
            const buttonContainer = document.createElement('div');
            buttonContainer.id = 'end-buttons';
            buttonContainer.className = 'flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4 mt-6';

            // 1. もういちど！ボタン
            const retryButton = document.createElement('button');
            retryButton.className = 'bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-full text-lg shadow-lg transition duration-200 flex-1 w-full sm:w-auto';
            retryButton.textContent = 'もういちど！';
            retryButton.onclick = startGame;
            buttonContainer.appendChild(retryButton);

            // 2. ホームへ戻るボタン (home.phpへ遷移)
            const homeButton = document.createElement('a');
            homeButton.href = 'home.php'; 
            homeButton.className = 'bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-full text-lg shadow-lg transition duration-200 flex-1 w-full sm:w-auto text-center';
            homeButton.textContent = 'ホームへもどる';
            buttonContainer.appendChild(homeButton);

            modalContent.appendChild(buttonContainer);
            
            overlay.classList.remove('opacity-0', 'pointer-events-none');
        }


        // ------------------------- 入力操作 -------------------------

        // 物理キーボードのEnterキーでも提出できるようにする
        userAnswerInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Enterキーによるフォーム送信を防ぐ
                submitAnswer();
            }
        });

        /**
         * 回答を提出し、正誤判定を行う
         */
        function submitAnswer() {
            if (gameState !== 'playing') return;

            // 回答をひらがな小文字に統一し、スペースを削除
            const userAnswer = userAnswerInput.value.trim().toLowerCase().replace(/\s/g, ''); 
            
            if (userAnswer === '') {
                feedbackDisplay.textContent = 'よみをにゅうりょくしてね！';
                feedbackDisplay.className = 'h-6 mt-2 text-sm font-bold text-yellow-600';
                userAnswerInput.focus();
                return;
            }
            
            totalSolved++; // といた問題数をカウント
            
            // 正解判定ロジック: ユーザーの回答が正解リストのいずれかに含まれているかチェック
            const isCorrect = correctAnswers.includes(userAnswer);

            // どの読みが正解だったかをフィードバックに表示するために、正解リストの文字列を整形
            const correctFeedback = correctAnswers.join(' / ');
            
            if (isCorrect) {
                score++;
                scoreDisplay.textContent = score;
                // 正解のフィードバック
                feedbackDisplay.textContent = 'せいかい！';
                feedbackDisplay.className = 'h-6 mt-2 text-sm font-bold text-green-600 animate-pulse';
            } else {
                // 不正解のフィードバック
                feedbackDisplay.textContent = `ざんねん... (正解例: ${correctFeedback})`;
                feedbackDisplay.className = 'h-6 mt-2 text-sm font-bold text-red-600';
            }

            // 次の問題をすぐに生成
            setTimeout(() => {
                if (gameState === 'playing') {
                    generateQuestion();
                }
            }, 500);
        }

        // 初期ロード時にスタート画面を表示
        window.onload = function() {
            timerDisplay.textContent = formatTime(timeLeft); 
            // 初期状態の漢字を表示 (startGameで上書きされる)
            kanjiDisplay.textContent = 'スタート';
            userAnswerInput.placeholder = 'にゅうりょくしてね';
        };

    </script>

</body>
</html>
