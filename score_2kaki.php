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
        // --- 漢字書き取り問題リスト ---
        // 🔴 修正点: 実行時エラーの原因だった 'NULL' を全て正しいキーワード 'null' (小文字) に修正しました。
        const RAW_PROBLEMS = [
 ['KJ081R', '2', '読み', '引', null, '', ''],
    ['KJ081W', '2', '書き', 'ひ', 'く', '引', '弓'],
    ['KJ082R', '2', '読み', '羽', null, '', null],
    ['KJ082W', '2', '書き', 'はね', null, '羽', '非'],
    ['KJ083R', '2', '読み', '雲', null, '', null],
    ['KJ083W', '2', '書き', 'くも', null, '雲', '雨'],
    ['KJ084R', '2', '読み', '園', null, '', null],
    ['KJ084W', '2', '書き', 'その', null, '園', '遠'],
    ['KJ085R', '2', '読み', '遠', null, '', null],
    ['KJ085W', '2', '書き', 'とお', 'い', '遠', '園'],
    ['KJ086R', '2', '読み', '何', null, '', null],
    ['KJ086W', '2', '書き', 'なに', null, '何', '科'],
    ['KJ087R', '2', '読み', '科', null, '', null],
    ['KJ087W', '2', '書き', 'か', null, '科', '何'],
    ['KJ088R', '2', '読み', '夏', null, '', null],
    ['KJ088W', '2', '書き', 'なつ', null, '夏', '家'],
    ['KJ089R', '2', '読み', '家', null, '', null],
    ['KJ089W', '2', '書き', 'いえ', null, '家', '夏'],
    ['KJ090R', '2', '読み', '歌', null, '', null],
    ['KJ090W', '2', '書き', 'うた', 'う', '歌', '画'],
    ['KJ091R', '2', '読み', '画', null, '', null],
    ['KJ091W', '2', '書き', 'かく', null, '画', '歌'],
    ['KJ092R', '2', '読み', '回', null, '', null],
    ['KJ092W', '2', '書き', 'まわ', 'る', '回', '会'],
    ['KJ093R', '2', '読み', '会', null, '', null],
    ['KJ093W', '2', '書き', 'あ', 'う', '会', '回'],
    ['KJ094R', '2', '読み', '海', null, '', null],
    ['KJ094W', '2', '書き', 'うみ', null, '海', '毎'],
    ['KJ095R', '2', '読み', '絵', null, '', null],
    ['KJ095W', '2', '書き', 'え', null, '絵', '会'],
    ['KJ096R', '2', '読み', '外', null, '', null],
    ['KJ096W', '2', '書き', 'そと', null, '外', '名'],
    ['KJ097R', '2', '読み', '角', null, '', null],
    ['KJ097W', '2', '書き', 'かど', null, '角', '用'],
    ['KJ098R', '2', '読み', '楽', null, '', null],
    ['KJ098W', '2', '書き', 'たの', 'しい', '楽', '白'],
    ['KJ099R', '2', '読み', '活', null, '', null],
    ['KJ099W', '2', '書き', 'い', 'きる', '活', '舌'],
    ['KJ100R', '2', '読み', '間', null, '', null],
    ['KJ100W', '2', '書き', 'あいだ', null, '間', '問'],
    ['KJ101R', '2', '読み', '丸', null, '', ''],
    ['KJ101W', '2', '書き', 'まる', 'い', '丸', '九'],
    ['KJ102R', '2', '読み', '岩', null, '', ''],
    ['KJ102W', '2', '書き', 'いわ', null, '岩', '石'],
    ['KJ103R', '2', '読み', '顔', null, '', ''],
    ['KJ103W', '2', '書き', 'かお', null, '顔', '頭'],
    ['KJ104R', '2', '読み', '汽', null, '', ''],
    ['KJ104W', '2', '書き', 'き', null, '汽', '気'],
    ['KJ105R', '2', '読み', '記', null, '', ''],
    ['KJ105W', '2', '書き', 'しる', 'す', '記', '言'],
    ['KJ106R', '2', '読み', '帰', null, '', ''],
    ['KJ106W', '2', '書き', 'かえ', 'る', '帰', '早'],
    ['KJ107R', '2', '読み', '弓', null, '', ''],
    ['KJ107W', '2', '書き', 'ゆみ', null, '弓', '引'],
    ['KJ108R', '2', '読み', '牛', null, '', ''],
    ['KJ108W', '2', '書き', 'うし', null, '牛', '午'],
    ['KJ109R', '2', '読み', '魚', null, '', ''],
    ['KJ109W', '2', '書き', 'さかな', null, '魚', '里'],
    ['KJ110R', '2', '読み', '京', null, '', ''],
    ['KJ110W', '2', '書き', 'きょう', null, '京', '高'],
    ['KJ111R', '2', '読み', '強', null, '', ''],
    ['KJ111W', '2', '書き', 'つよ', 'い', '強', '弱'],
    ['KJ112R', '2', '読み', '教', null, '', ''],
    ['KJ112W', '2', '書き', 'おし', 'える', '教', '数'],
    ['KJ113R', '2', '読み', '近', null, '', ''],
    ['KJ113W', '2', '書き', 'ちか', 'い', '近', '教'],
    ['KJ114R', '2', '読み', '兄', null, '', ''],
    ['KJ114W', '2', '書き', 'あに', null, '兄', '見'],
    ['KJ115R', '2', '読み', '形', null, '', ''],
    ['KJ115W', '2', '書き', 'かたち', null, '形', '計'],
    ['KJ116R', '2', '読み', '計', null, '', ''],
    ['KJ116W', '2', '書き', 'はか', 'る', '計', '形'],
    ['KJ117R', '2', '読み', '元', null, '', ''],
    ['KJ117W', '2', '書き', 'もと', null, '元', '言'],
    ['KJ118R', '2', '読み', '言', null, '', ''],
    ['KJ118W', '2', '書き', 'い', 'う', '言', '元'],
    ['KJ119R', '2', '読み', '原', null, '', ''],
    ['KJ119W', '2', '書き', 'はら', null, '原', '白'],
    ['KJ120R', '2', '読み', '戸', null, '', ''],
    ['KJ120W', '2', '書き', 'と', null, '戸', '古'],
    ['KJ121R', '2', '読み', '古', null, '', ''],
    ['KJ121W', '2', '書き', 'ふる', 'い', '古', '戸'],
    ['KJ122R', '2', '読み', '午', null, '', ''],
    ['KJ122W', '2', '書き', 'ご', null, '午', '牛'],
    ['KJ123R', '2', '読み', '後', null, '', ''],
    ['KJ123W', '2', '書き', 'あと', null, '後', '前'],
    ['KJ124R', '2', '読み', '語', null, '', ''],
    ['KJ124W', '2', '書き', 'かた', 'る', '語', '言'],
    ['KJ125R', '2', '読み', '工', null, '', ''],
    ['KJ125W', '2', '書き', 'こう', null, '工', '公'],
    ['KJ126R', '2', '読み', '公', null, '', ''],
    ['KJ126W', '2', '書き', 'おおやけ', null, '公', '工'],
    ['KJ127R', '2', '読み', '広', null, '', ''],
    ['KJ127W', '2', '書き', 'ひろ', 'い', '広', '店'],
    ['KJ128R', '2', '読み', '交', null, '', ''],
    ['KJ128W', '2', '書き', 'まじ', 'わる', '交', '父'],
    ['KJ129R', '2', '読み', '光', null, '', ''],
    ['KJ129W', '2', '書き', 'ひかり', null, '光', '当'],
    ['KJ130R', '2', '読み', '考', null, '', ''],
    ['KJ130W', '2', '書き', 'かんが', 'える', '考', '教'],
    ['KJ131R', '2', '読み', '行', null, '', ''],
    ['KJ131W', '2', '書き', 'い', 'く', '行', '休'],
    ['KJ132R', '2', '読み', '高', null, '', ''],
    ['KJ132W', '2', '書き', 'たか', 'い', '高', '京'],
    ['KJ133R', '2', '読み', '黄', null, '', ''],
    ['KJ133W', '2', '書き', 'き', null, '黄', '黒'],
    ['KJ134R', '2', '読み', '合', null, '', ''],
    ['KJ134W', '2', '書き', 'あ', 'う', '合', '谷'],
    ['KJ135R', '2', '読み', '谷', null, '', ''],
    ['KJ135W', '2', '書き', 'たに', null, '谷', '合'],
    ['KJ136R', '2', '読み', '国', null, '', ''],
    ['KJ136W', '2', '書き', 'くに', null, '国', '黒'],
    ['KJ137R', '2', '読み', '黒', null, '', ''],
    ['KJ137W', '2', '書き', 'くろ', 'い', '黒', '黄'],
    ['KJ138R', '2', '読み', '今', null, '', ''],
    ['KJ138W', '2', '書き', 'いま', null, '今', '才'],
    ['KJ139R', '2', '読み', '才', null, '', ''],
    ['KJ139W', '2', '書き', 'さい', null, '才', '今'],
    ['KJ140R', '2', '読み', '細', null, '', ''],
    ['KJ140W', '2', '書き', 'ほそ', 'い', '細', '組'],
    ['KJ141R', '2', '読み', '作', null, '', ''],
    ['KJ141W', '2', '書き', 'つく', 'る', '作', '字'],
    ['KJ142R', '2', '読み', '算', null, '', ''],
    ['KJ142W', '2', '書き', 'さん', null, '算', '鼻'],
    ['KJ143R', '2', '読み', '止', null, '', ''],
    ['KJ143W', '2', '書き', 'と', 'まる', '止', '正'],
    ['KJ144R', '2', '読み', '市', null, '', ''],
    ['KJ144W', '2', '書き', 'いち', null, '市', '姉'],
    ['KJ145R', '2', '読み', '矢', null, '', ''],
    ['KJ145W', '2', '書き', 'や', null, '矢', '天'],
    ['KJ146R', '2', '読み', '姉', null, '', ''],
    ['KJ146W', '2', '書き', 'あね', null, '姉', '市'],
    ['KJ147R', '2', '読み', '思', null, '', ''],
    ['KJ147W', '2', '書き', 'おも', 'う', '思', '田'],
    ['KJ148R', '2', '読み', '紙', null, '', ''],
    ['KJ148W', '2', '書き', 'かみ', null, '紙', '氏'],
    ['KJ149R', '2', '読み', '寺', null, '', ''],
    ['KJ149W', '2', '書き', 'てら', null, '寺', '時'],
    ['KJ150R', '2', '読み', '自', null, '', ''],
    ['KJ150W', '2', '書き', 'みずか', 'ら', '自', '白'],
    ['KJ151R', '2', '読み', '時', null, '', ''],
    ['KJ151W', '2', '書き', 'とき', null, '時', '寺'],
    ['KJ152R', '2', '読み', '室', null, '', ''],
    ['KJ152W', '2', '書き', 'むろ', null, '室', '空'],
    ['KJ153R', '2', '読み', '社', null, '', ''],
    ['KJ153W', '2', '書き', 'やしろ', null, '社', '土'],
    ['KJ154R', '2', '読み', '弱', null, '', ''],
    ['KJ154W', '2', '書き', 'よわ', 'い', '弱', '強'],
    ['KJ155R', '2', '読み', '首', null, '', ''],
    ['KJ155W', '2', '書き', 'くび', null, '首', '道'],
    ['KJ156R', '2', '読み', '秋', null, '', ''],
    ['KJ156W', '2', '書き', 'あき', null, '秋', '火'],
    ['KJ157R', '2', '読み', '週', null, '', ''],
    ['KJ157W', '2', '書き', 'しゅう', null, '週', '道'],
    ['KJ158R', '2', '読み', '春', null, '', ''],
    ['KJ158W', '2', '書き', 'はる', null, '春', '見'],
    ['KJ159R', '2', '読み', '書', null, '', ''],
    ['KJ159W', '2', '書き', 'か', 'く', '書', '者'],
    ['KJ160R', '2', '読み', '少', null, '', ''],
    ['KJ160W', '2', '書き', 'すく', 'ない', '少', '歩'],
    ['KJ161R', '2', '読み', '場', null, '', ''],
    ['KJ161W', '2', '書き', 'ば', null, '場', '陽'],
    ['KJ162R', '2', '読み', '色', null, '', ''],
    ['KJ162W', '2', '書き', 'いろ', null, '色', '食'],
    ['KJ163R', '2', '読み', '食', null, '', ''],
    ['KJ163W', '2', '書き', 'た', 'べる', '食', '色'],
    ['KJ164R', '2', '読み', '心', null, '', ''],
    ['KJ164W', '2', '書き', 'こころ', null, '心', '必'],
    ['KJ165R', '2', '読み', '新', null, '', ''],
    ['KJ165W', '2', '書き', 'あたら', 'しい', '新', '親'],
    ['KJ166R', '2', '読み', '親', null, '', ''],
    ['KJ166W', '2', '書き', 'おや', null, '親', '新'],
    ['KJ167R', '2', '読み', '図', null, '', ''],
    ['KJ167W', '2', '書き', 'はか', 'る', '図', '円'],
    ['KJ168R', '2', '読み', '数', null, '', ''],
    ['KJ168W', '2', '書き', 'かぞ', 'える', '数', '女'],
    ['KJ169R', '2', '読み', '西', null, '', ''],
    ['KJ169W', '2', '書き', 'にし', null, '西', '四'],
    ['KJ170R', '2', '読み', '声', null, '', ''],
    ['KJ170W', '2', '書き', 'こえ', null, '声', '先'],
    ['KJ171R', '2', '読み', '星', null, '', ''],
    ['KJ171W', '2', '書き', 'ほし', null, '星', '生'],
    ['KJ172R', '2', '読み', '晴', null, '', ''],
    ['KJ172W', '2', '書き', 'は', 'れる', '晴', '青'],
    ['KJ173R', '2', '読み', '切', null, '', ''],
    ['KJ173W', '2', '書き', 'き', 'る', '切', '刀'],
    ['KJ174R', '2', '読み', '雪', null, '', ''],
    ['KJ174W', '2', '書き', 'ゆき', null, '雪', '雨'],
    ['KJ175R', '2', '読み', '船', null, '', ''],
    ['KJ175W', '2', '書き', 'ふね', null, '船', '航'],
    ['KJ176R', '2', '読み', '線', null, '', ''],
    ['KJ176W', '2', '書き', 'せん', null, '線', '白'],
    ['KJ177R', '2', '読み', '前', null, '', ''],
    ['KJ177W', '2', '書き', 'まえ', null, '前', '後'],
    ['KJ178R', '2', '読み', '組', null, '', ''],
    ['KJ178W', '2', '書き', 'く', 'む', '組', '祖'],
    ['KJ179R', '2', '読み', '走', null, '', ''],
    ['KJ179W', '2', '書き', 'はし', 'る', '走', '足'],
    ['KJ180R', '2', '読み', '多', null, '', ''],
    ['KJ180W', '2', '書き', 'おお', 'い', '多', '夕'],
    ['KJ181R', '2', '読み', '太', null, '', ''],
    ['KJ181W', '2', '書き', 'ふと', 'い', '太', '大'],
    ['KJ182R', '2', '読み', '体', null, '', ''],
    ['KJ182W', '2', '書き', 'からだ', null, '体', '本'],
    ['KJ183R', '2', '読み', '台', null, '', ''],
    ['KJ183W', '2', '書き', 'だい', null, '台', '右'],
    ['KJ184R', '2', '読み', '地', null, '', ''],
    ['KJ184W', '2', '書き', 'ち', null, '地', '池'],
    ['KJ185R', '2', '読み', '池', null, '', ''],
    ['KJ185W', '2', '書き', 'いけ', null, '池', '地'],
    ['KJ186R', '2', '読み', '知', null, '', ''],
    ['KJ186W', '2', '書き', 'し', 'る', '知', '矢'],
    ['KJ187R', '2', '読み', '茶', null, '', ''],
    ['KJ187W', '2', '書き', 'ちゃ', null, '茶', '草'],
    ['KJ188R', '2', '読み', '昼', null, '', ''],
    ['KJ188W', '2', '書き', 'ひる', null, '昼', '尺'],
    ['KJ189R', '2', '読み', '長', null, '', ''],
    ['KJ189W', '2', '書き', 'なが', 'い', '長', '馬'],
    ['KJ190R', '2', '読み', '鳥', null, '', ''],
    ['KJ190W', '2', '書き', 'とり', null, '鳥', '馬'],
    ['KJ191R', '2', '読み', '朝', null, '', ''],
    ['KJ191W', '2', '書き', 'あさ', null, '朝', '直'],
    ['KJ192R', '2', '読み', '直', null, '', ''],
    ['KJ192W', '2', '書き', 'なお', 'す', '直', '朝'],
    ['KJ193R', '2', '読み', '通', null, '', ''],
    ['KJ193W', '2', '書き', 'とお', 'る', '通', '週'],
    ['KJ194R', '2', '読み', '弟', null, '', ''],
    ['KJ194W', '2', '書き', 'おとうと', null, '弟', '第'],
    ['KJ195R', '2', '読み', '店', null, '', ''],
    ['KJ195W', '2', '書き', 'みせ', null, '店', '広'],
    ['KJ196R', '2', '読み', '点', null, '', ''],
    ['KJ196W', '2', '書き', 'てん', null, '点', '店'],
    ['KJ197R', '2', '読み', '電', null, '', ''],
    ['KJ197W', '2', '書き', 'でん', null, '電', '雨'],
    ['KJ198R', '2', '読み', '刀', null, '', ''],
    ['KJ198W', '2', '書き', 'かたな', null, '刀', '力'],
    ['KJ199R', '2', '読み', '冬', null, '', ''],
    ['KJ199W', '2', '書き', 'ふゆ', null, '冬', '千'],
    ['KJ200R', '2', '読み', '当', null, '', ''],
    ['KJ200W', '2', '書き', 'あ', 'たる', '当', '光'],
    ['KJ201R', '2', '読み', '東', null, '', ''],
    ['KJ201W', '2', '書き', 'ひがし', null, '東', '京'],
    ['KJ202R', '2', '読み', '答', null, '', ''],
    ['KJ202W', '2', '書き', 'こた', 'える', '答', '合'],
    ['KJ203R', '2', '読み', '頭', null, '', ''],
    ['KJ203W', '2', '書き', 'あたま', null, '頭', '顔'],
    ['KJ204R', '2', '読み', '同', null, '', ''],
    ['KJ204W', '2', '書き', 'おな', 'じ', '同', '円'],
    ['KJ205R', '2', '読み', '道', null, '', ''],
    ['KJ205W', '2', '書き', 'みち', null, '道', '首'],
    ['KJ206R', '2', '読み', '読', null, '', ''],
    ['KJ206W', '2', '書き', 'よ', 'む', '読', '売'],
    ['KJ207R', '2', '読み', '内', null, '', ''],
    ['KJ207W', '2', '書き', 'うち', null, '内', '肉'],
    ['KJ208R', '2', '読み', '南', null, '', ''],
    ['KJ208W', '2', '書き', 'みなみ', null, '南', '男'],
    ['KJ209R', '2', '読み', '肉', null, '', ''],
    ['KJ209W', '2', '書き', 'にく', null, '肉', '内'],
    ['KJ210R', '2', '読み', '馬', null, '', ''],
    ['KJ210W', '2', '書き', 'うま', null, '馬', '鳥'],
    ['KJ211R', '2', '読み', '売', null, '', ''],
    ['KJ211W', '2', '書き', 'う', 'る', '売', '読'],
    ['KJ212R', '2', '読み', '買', null, '', ''],
    ['KJ212W', '2', '書き', 'か', 'う', '買', '貝'],
    ['KJ213R', '2', '読み', '麦', null, '', ''],
    ['KJ213W', '2', '書き', 'むぎ', null, '麦', '来'],
    ['KJ214R', '2', '読み', '半', null, '', ''],
    ['KJ214W', '2', '書き', 'なか', 'ば', '半', '平'],
    ['KJ215R', '2', '読み', '番', null, '', ''],
    ['KJ215W', '2', '書き', 'ばん', null, '番', '米'],
    ['KJ216R', '2', '読み', '父', null, '', ''],
    ['KJ216W', '2', '書き', 'ちち', null, '父', '交'],
    ['KJ217R', '2', '読み', '風', null, '', ''],
    ['KJ217W', '2', '書き', 'かぜ', null, '風', '虫'],
    ['KJ218R', '2', '読み', '分', null, '', ''],
    ['KJ218W', '2', '書き', 'わ', 'ける', '分', '聞'],
    ['KJ219R', '2', '読み', '聞', null, '', ''],
    ['KJ219W', '2', '書き', 'き', 'く', '聞', '間'],
    ['KJ220R', '2', '読み', '米', null, '', ''],
    ['KJ220W', '2', '書き', 'こめ', null, '米', '来'],
    ['KJ221R', '2', '読み', '歩', null, '', ''],
    ['KJ221W', '2', '書き', 'ある', 'く', '歩', '少'],
    ['KJ222R', '2', '読み', '母', null, '', ''],
    ['KJ222W', '2', '書き', 'はは', null, '母', '毎'],
    ['KJ223R', '2', '読み', '方', null, '', ''],
    ['KJ223W', '2', '書き', 'かた', null, '方', '万'],
    ['KJ224R', '2', '読み', '北', null, '', ''],
    ['KJ224W', '2', '書き', 'きた', null, '北', '比'],
    ['KJ225R', '2', '読み', '毎', null, '', ''],
    ['KJ225W', '2', '書き', 'まい', null, '毎', '母'],
    ['KJ226R', '2', '読み', '妹', null, '', ''],
    ['KJ226W', '2', '書き', 'いもうと', null, '妹', '姉'],
    ['KJ227R', '2', '読み', '万', null, '', ''],
    ['KJ227W', '2', '書き', 'まん', null, '万', '方'],
    ['KJ228R', '2', '読み', '明', null, '', ''],
    ['KJ228W', '2', '書き', 'あ', 'かるい', '明', '目'],
    ['KJ229R', '2', '読み', '鳴', null, '', ''],
    ['KJ229W', '2', '書き', 'な', 'く', '鳴', '鳥'],
    ['KJ230R', '2', '読み', '毛', null, '', ''],
    ['KJ230W', '2', '書き', 'け', null, '毛', '手'],
    ['KJ231R', '2', '読み', '門', null, '', ''],
    ['KJ231W', '2', '書き', 'もん', null, '門', '問'],
    ['KJ232R', '2', '読み', '夜', null, '', ''],
    ['KJ232W', '2', '書き', 'よる', null, '夜', '液'],
    ['KJ233R', '2', '読み', '野', null, '', ''],
    ['KJ233W', '2', '書き', 'の', null, '野', '里'],
    ['KJ234R', '2', '読み', '友', null, '', ''],
    ['KJ234W', '2', '書き', 'とも', null, '友', '反'],
    ['KJ235R', '2', '読み', '用', null, '', ''],
    ['KJ235W', '2', '書き', 'もち', 'いる', '用', '角'],
    ['KJ236R', '2', '読み', '曜', null, '', ''],
    ['KJ236W', '2', '書き', 'よう', null, '曜', '羽'],
    ['KJ237R', '2', '読み', '来', null, '', ''],
    ['KJ237W', '2', '書き', 'く', 'る', '来', '米'],
    ['KJ238R', '2', '読み', '里', null, '', ''],
    ['KJ238W', '2', '書き', 'さと', null, '里', '野'],
    ['KJ239R', '2', '読み', '理', null, '', ''],
    ['KJ239W', '2', '書き', 'り', null, '理', '里'],
    ['KJ240R', '2', '読み', '話', null, '', ''],
    ['KJ240W', '2', '書き', 'はな', 'す', '話', '舌']
        ];

        // '書き'問題のみを抽出・整形
        const KANJI_WRITING_PROBLEMS = [];
        RAW_PROBLEMS.forEach(row => {
            if (row[2] === '書き') {
                const yomi = (row[3] || '') + (row[4] || ''); 
                
                KANJI_WRITING_PROBLEMS.push({
                    id: row[0],
                    yomi: yomi, 
                    correct: row[5],   
                    distractor: row[6] 
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

        // 初期ロード時にスタート画面の表示に必要な処理を直接実行
        timerDisplay.textContent = formatTime(timeLeft); 
        readingDisplay.textContent = 'スタートを待っています';
            
        // 念のため、初期状態のオーバーレイを表示
        overlay.classList.remove('opacity-0', 'pointer-events-none');

    </script>

</body>
</html>
