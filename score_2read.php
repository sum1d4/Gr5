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
            <h2 id="modal-title" class="text-3xl font-bold text-gray-800 mb-4">漢字の読み スコアアタック！</h2>
            <p id="modal-message" class="text-gray-600 mb-6 text-lg">
                3分でどれだけせいかいできるかな？
            </p>
            
            <button id="start-button" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-full text-xl shadow-lg transition duration-200" onclick="startGame()">
                ゲームスタート
            </button>
            
        </div>
    </div>

    <script>
        // --- 漢字問題リスト（ご提供いただいた新しいデータ） ---
        const RAW_KANJI_DATA = [
            { kanji: '引', yomi: 'いん' }, { kanji: '引', yomi: 'ひ' },
            { kanji: '羽', yomi: 'う' }, { kanji: '羽', yomi: 'は' }, { kanji: '羽', yomi: 'はね' },
            { kanji: '雲', yomi: 'うん' }, { kanji: '雲', yomi: 'くも' },
            { kanji: '園', yomi: 'えん' }, { kanji: '園', yomi: 'その' },
            { kanji: '遠', yomi: 'えん' }, { kanji: '遠', yomi: 'とお' },
            { kanji: '何', yomi: 'か' }, { kanji: '何', yomi: 'なに' }, { kanji: '何', yomi: 'なん' },
            { kanji: '科', yomi: 'か' },
            { kanji: '夏', yomi: 'か' }, { kanji: '夏', yomi: 'なつ' },
            { kanji: '家', yomi: 'か' }, { kanji: '家', yomi: 'け' }, { kanji: '家', yomi: 'いえ' }, { kanji: '家', yomi: 'や' },
            { kanji: '歌', yomi: 'か' }, { kanji: '歌', yomi: 'うた' },
            { kanji: '画', yomi: 'が' }, { kanji: '画', yomi: 'かく' },
            { kanji: '回', yomi: 'かい' }, { kanji: '回', yomi: 'まわ' },
            { kanji: '会', yomi: 'かい' }, { kanji: '会', yomi: 'え' }, { kanji: '会', yomi: 'あ' },
            { kanji: '海', yomi: 'かい' }, { kanji: '海', yomi: 'うみ' },
            { kanji: '絵', yomi: 'かい' }, { kanji: '絵', yomi: 'え' },
            { kanji: '外', yomi: 'がい' }, { kanji: '外', yomi: 'げ' }, { kanji: '外', yomi: 'そと' }, { kanji: '外', yomi: 'ほか' }, { kanji: '外', yomi: 'はず' },
            { kanji: '角', yomi: 'かく' }, { kanji: '角', yomi: 'かど' }, { kanji: '角', yomi: 'つの' },
            { kanji: '楽', yomi: 'がく' }, { kanji: '楽', yomi: 'らく' }, { kanji: '楽', yomi: 'たの' },
            { kanji: '活', yomi: 'かつ' },
            { kanji: '間', yomi: 'かん' }, { kanji: '間', yomi: 'けん' }, { kanji: '間', yomi: 'あいだ' }, { kanji: '間', yomi: 'ま' },
            { kanji: '丸', yomi: 'がん' }, { kanji: '丸', yomi: 'まる' },
            { kanji: '岩', yomi: 'がん' }, { kanji: '岩', yomi: 'いわ' },
            { kanji: '顔', yomi: 'がん' }, { kanji: '顔', yomi: 'かお' },
            { kanji: '汽', yomi: 'き' },
            { kanji: '記', yomi: 'き' }, { kanji: '記', yomi: 'しる' },
            { kanji: '帰', yomi: 'き' }, { kanji: '帰', yomi: 'かえ' },
            { kanji: '弓', yomi: 'きゅう' }, { kanji: '弓', yomi: 'ゆみ' },
            { kanji: '牛', yomi: 'ぎゅう' }, { kanji: '牛', yomi: 'うし' },
            { kanji: '魚', yomi: 'ぎょ' }, { kanji: '魚', yomi: 'うお' }, { kanji: '魚', yomi: 'さかな' },
            { kanji: '京', yomi: 'きょう' }, { kanji: '京', yomi: 'けい' },
            { kanji: '強', yomi: 'きょう' }, { kanji: '強', yomi: 'ごう' }, { kanji: '強', yomi: 'つよ' },
            { kanji: '教', yomi: 'きょう' }, { kanji: '教', yomi: 'おし' }, { kanji: '教', yomi: 'おそ' },
            { kanji: '近', yomi: 'きん' }, { kanji: '近', yomi: 'ちか' },
            { kanji: '兄', yomi: 'けい' }, { kanji: '兄', yomi: 'きょう' }, { kanji: '兄', yomi: 'あに' },
            { kanji: '形', yomi: 'けい' }, { kanji: '形', yomi: 'ぎょう' }, { kanji: '形', yomi: 'かた' }, { kanji: '形', yomi: 'かたち' },
            { kanji: '計', yomi: 'けい' }, { kanji: '計', yomi: 'はか' },
            { kanji: '元', yomi: 'げん' }, { kanji: '元', yomi: 'がん' }, { kanji: '元', yomi: 'もと' },
            { kanji: '言', yomi: 'げん' }, { kanji: '言', yomi: 'ごん' }, { kanji: '言', yomi: 'い' }, { kanji: '言', yomi: 'こと' },
            { kanji: '原', yomi: 'げん' }, { kanji: '原', yomi: 'はら' },
            { kanji: '戸', yomi: 'こ' }, { kanji: '戸', yomi: 'と' },
            { kanji: '古', yomi: 'こ' }, { kanji: '古', yomi: 'ふる' },
            { kanji: '午', yomi: 'ご' },
            { kanji: '後', yomi: 'ご' }, { kanji: '後', yomi: 'こう' }, { kanji: '後', yomi: 'のち' }, { kanji: '後', yomi: 'うし' }, { kanji: '後', yomi: 'あと' }, { kanji: '後', yomi: 'おく' },
            { kanji: '語', yomi: 'ご' }, { kanji: '語', yomi: 'かた' },
            { kanji: '工', yomi: 'こう' }, { kanji: '工', yomi: 'く' },
            { kanji: '公', yomi: 'こう' }, { kanji: '公', yomi: 'おおやけ' },
            { kanji: '広', yomi: 'こう' }, { kanji: '広', yomi: 'ひろ' },
            { kanji: '交', yomi: 'こう' }, { kanji: '交', yomi: 'まじ' }, { kanji: '交', yomi: 'か' },
            { kanji: '光', yomi: 'こう' }, { kanji: '光', yomi: 'ひかり' }, { kanji: '光', yomi: 'ひか' },
            { kanji: '考', yomi: 'こう' }, { kanji: '考', yomi: 'かんが' },
            { kanji: '行', yomi: 'こう' }, { kanji: '行', yomi: 'ぎょう' }, { kanji: '行', yomi: 'あん' }, { kanji: '行', yomi: 'い' }, { kanji: '行', yomi: 'ゆ' }, { kanji: '行', yomi: 'おこな' },
            { kanji: '高', yomi: 'こう' }, { kanji: '高', yomi: 'たか' },
            { kanji: '黄', yomi: 'こう' }, { kanji: '黄', yomi: 'おう' }, { kanji: '黄', yomi: 'き' },
            { kanji: '合', yomi: 'ごう' }, { kanji: '合', yomi: 'がっ' }, { kanji: '合', yomi: 'あ' },
            { kanji: '谷', yomi: 'こく' }, { kanji: '谷', yomi: 'たに' },
            { kanji: '国', yomi: 'こく' }, { kanji: '国', yomi: 'くに' },
            { kanji: '黒', yomi: 'こく' }, { kanji: '黒', yomi: 'くろ' },
            { kanji: '今', yomi: 'こん' }, { kanji: '今', yomi: 'きん' }, { kanji: '今', yomi: 'いま' },
            { kanji: '才', yomi: 'さい' },
            { kanji: '細', yomi: 'さい' }, { kanji: '細', yomi: 'ほそ' }, { kanji: '細', yomi: 'こま' },
            { kanji: '作', yomi: 'さく' }, { kanji: '作', yomi: 'さ' }, { kanji: '作', yomi: 'つく' },
            { kanji: '算', yomi: 'さん' },
            { kanji: '止', yomi: 'し' }, { kanji: '止', yomi: 'と' }, { kanji: '止', yomi: 'や' },
            { kanji: '市', yomi: 'し' }, { kanji: '市', yomi: 'いち' },
            { kanji: '矢', yomi: 'し' }, { kanji: '矢', yomi: 'や' },
            { kanji: '姉', yomi: 'し' }, { kanji: '姉', yomi: 'あね' },
            { kanji: '思', yomi: 'し' }, { kanji: '思', yomi: 'おも' },
            { kanji: '紙', yomi: 'し' }, { kanji: '紙', yomi: 'かみ' },
            { kanji: '寺', yomi: 'じ' }, { kanji: '寺', yomi: 'てら' },
            { kanji: '自', yomi: 'じ' }, { kanji: '自', yomi: 'し' }, { kanji: '自', yomi: 'みずか' },
            { kanji: '時', yomi: 'じ' }, { kanji: '時', yomi: 'とき' },
            { kanji: '室', yomi: 'しつ' }, { kanji: '室', yomi: 'むろ' },
            { kanji: '社', yomi: 'しゃ' }, { kanji: '社', yomi: 'やしろ' },
            { kanji: '弱', yomi: 'じゃく' }, { kanji: '弱', yomi: 'よわ' },
            { kanji: '首', yomi: 'しゅ' }, { kanji: '首', yomi: 'くび' },
            { kanji: '秋', yomi: 'しゅう' }, { kanji: '秋', yomi: 'あき' },
            { kanji: '週', yomi: 'しゅう' },
            { kanji: '春', yomi: 'しゅん' }, { kanji: '春', yomi: 'はる' },
            { kanji: '書', yomi: 'しょ' }, { kanji: '書', yomi: 'か' },
            { kanji: '少', yomi: 'しょう' }, { kanji: '少', yomi: 'すく' }, { kanji: '少', yomi: 'すこ' },
            { kanji: '場', yomi: 'じょう' }, { kanji: '場', yomi: 'ば' },
            { kanji: '色', yomi: 'しょく' }, { kanji: '色', yomi: 'しき' }, { kanji: '色', yomi: 'いろ' },
            { kanji: '食', yomi: 'しょく' }, { kanji: '食', yomi: 'じき' }, { kanji: '食', yomi: 'く' }, { kanji: '食', yomi: 'た' },
            { kanji: '心', yomi: 'しん' }, { kanji: '心', yomi: 'こころ' },
            { kanji: '新', yomi: 'しん' }, { kanji: '新', yomi: 'あたら' }, { kanji: '新', yomi: 'あら' },
            { kanji: '親', yomi: 'しん' }, { kanji: '親', yomi: 'おや' }, { kanji: '親', yomi: 'した' },
            { kanji: '図', yomi: 'ず' }, { kanji: '図', yomi: 'と' }, { kanji: '図', yomi: 'はか' },
            { kanji: '数', yomi: 'すう' }, { kanji: '数', yomi: 'かず' }, { kanji: '数', yomi: 'かぞ' },
            { kanji: '西', yomi: 'せい' }, { kanji: '西', yomi: 'さい' }, { kanji: '西', yomi: 'にし' },
            { kanji: '声', yomi: 'せい' }, { kanji: '声', yomi: 'しょう' }, { kanji: '声', yomi: 'こえ' },
            { kanji: '星', yomi: 'せい' }, { kanji: '星', yomi: 'しょう' }, { kanji: '星', yomi: 'ほし' },
            { kanji: '晴', yomi: 'せい' }, { kanji: '晴', yomi: 'は' },
            { kanji: '切', yomi: 'せつ' }, { kanji: '切', yomi: 'さい' }, { kanji: '切', yomi: 'き' },
            { kanji: '雪', yomi: 'せつ' }, { kanji: '雪', yomi: 'ゆき' },
            { kanji: '船', yomi: 'せん' }, { kanji: '船', yomi: 'ふね' }, { kanji: '船', yomi: 'ふな' },
            { kanji: '線', yomi: 'せん' },
            { kanji: '前', yomi: 'ぜん' }, { kanji: '前', yomi: 'まえ' },
            { kanji: '組', yomi: 'そ' }, { kanji: '組', yomi: 'くみ' },
            { kanji: '走', yomi: 'そう' }, { kanji: '走', yomi: 'はし' },
            { kanji: '多', yomi: 'た' }, { kanji: '多', yomi: 'おお' },
            { kanji: '太', yomi: 'たい' }, { kanji: '太', yomi: 'た' }, { kanji: '太', yomi: 'ふと' },
            { kanji: '体', yomi: 'たい' }, { kanji: '体', yomi: 'てい' }, { kanji: '体', yomi: 'からだ' },
            { kanji: '台', yomi: 'だい' }, { kanji: '台', yomi: 'たい' },
            { kanji: '地', yomi: 'ち' }, { kanji: '地', yomi: 'じ' },
            { kanji: '池', yomi: 'ち' }, { kanji: '池', yomi: 'いけ' },
            { kanji: '知', yomi: 'ち' }, { kanji: '知', yomi: 'し' },
            { kanji: '茶', yomi: 'ちゃ' }, { kanji: '茶', yomi: 'さ' },
            { kanji: '昼', yomi: 'ちゅう' }, { kanji: '昼', yomi: 'ひる' },
            { kanji: '長', yomi: 'ちょう' }, { kanji: '長', yomi: 'なが' },
            { kanji: '鳥', yomi: 'ちょう' }, { kanji: '鳥', yomi: 'とり' },
            { kanji: '朝', yomi: 'ちょう' }, { kanji: '朝', yomi: 'あさ' },
            { kanji: '直', yomi: 'ちょく' }, { kanji: '直', yomi: 'じき' }, { kanji: '直', yomi: 'なお' }, { kanji: '直', yomi: 'ただ' },
            { kanji: '通', yomi: 'つう' }, { kanji: '通', yomi: 'とお' }, { kanji: '通', yomi: 'かよ' },
            { kanji: '弟', yomi: 'てい' }, { kanji: '弟', yomi: 'だい' }, { kanji: '弟', yomi: 'おとうと' },
            { kanji: '店', yomi: 'てん' }, { kanji: '店', yomi: 'みせ' },
            { kanji: '点', yomi: 'てん' },
            { kanji: '電', yomi: 'でん' },
            { kanji: '刀', yomi: 'とう' }, { kanji: '刀', yomi: 'かたな' },
            { kanji: '冬', yomi: 'とう' }, { kanji: '冬', yomi: 'ふゆ' },
            { kanji: '当', yomi: 'とう' }, { kanji: '当', yomi: 'あ' },
            { kanji: '東', yomi: 'とう' }, { kanji: '東', yomi: 'ひがし' },
            { kanji: '答', yomi: 'とう' }, { kanji: '答', yomi: 'こた' },
            { kanji: '頭', yomi: 'とう' }, { kanji: '頭', yomi: 'ず' }, { kanji: '頭', yomi: 'あたま' }, { kanji: '頭', yomi: 'かしら' },
            { kanji: '同', yomi: 'どう' }, { kanji: '同', yomi: 'おな' },
            { kanji: '道', yomi: 'どう' }, { kanji: '道', yomi: 'とう' }, { kanji: '道', yomi: 'みち' },
            { kanji: '読', yomi: 'どく' }, { kanji: '読', yomi: 'とく' }, { kanji: '読', yomi: 'とう' }, { kanji: '読', yomi: 'よ' },
            { kanji: '内', yomi: 'ない' }, { kanji: '内', yomi: 'だい' }, { kanji: '内', yomi: 'うち' },
            { kanji: '南', yomi: 'なん' }, { kanji: '南', yomi: 'な' }, { kanji: '南', yomi: 'みなみ' },
            { kanji: '肉', yomi: 'にく' },
            { kanji: '馬', yomi: 'ば' }, { kanji: '馬', yomi: 'うま' },
            { kanji: '売', yomi: 'ばい' }, { kanji: '売', yomi: 'う' },
            { kanji: '買', yomi: 'ばい' }, { kanji: '買', yomi: 'か' },
            { kanji: '麦', yomi: 'ばく' }, { kanji: '麦', yomi: 'むぎ' },
            { kanji: '半', yomi: 'はん' }, { kanji: '半', yomi: 'なか' },
            { kanji: '番', yomi: 'ばん' },
            { kanji: '父', yomi: 'ふ' }, { kanji: '父', yomi: 'ちち' },
            { kanji: '風', yomi: 'ふう' }, { kanji: '風', yomi: 'ふ' }, { kanji: '風', yomi: 'かぜ' },
            { kanji: '分', yomi: 'ぶん' }, { kanji: '分', yomi: 'ふん' }, { kanji: '分', yomi: 'ぶ' }, { kanji: '分', yomi: 'わ' },
            { kanji: '聞', yomi: 'ぶん' }, { kanji: '聞', yomi: 'もん' }, { kanji: '聞', yomi: 'き' },
            { kanji: '米', yomi: 'べい' }, { kanji: '米', yomi: 'まい' }, { kanji: '米', yomi: 'こめ' },
            { kanji: '歩', yomi: 'ほ' }, { kanji: '歩', yomi: 'ぶ' }, { kanji: '歩', yomi: 'ふ' }, { kanji: '歩', yomi: 'ある' }, { kanji: '歩', yomi: 'あゆ' },
            { kanji: '母', yomi: 'ぼ' }, { kanji: '母', yomi: 'はは' },
            { kanji: '方', yomi: 'ほう' }, { kanji: '方', yomi: 'かた' },
            { kanji: '北', yomi: 'ほく' }, { kanji: '北', yomi: 'きた' },
            { kanji: '毎', yomi: 'まい' },
            { kanji: '妹', yomi: 'まい' }, { kanji: '妹', yomi: 'いもうと' },
            { kanji: '万', yomi: 'まん' }, { kanji: '万', yomi: 'ばん' },
            { kanji: '明', yomi: 'めい' }, { kanji: '明', yomi: 'みょう' }, { kanji: '明', yomi: 'あ' },
            { kanji: '鳴', yomi: 'めい' }, { kanji: '鳴', yomi: 'な' },
            { kanji: '毛', yomi: 'もう' }, { kanji: '毛', yomi: 'け' },
            { kanji: '門', yomi: 'もん' }, { kanji: '門', yomi: 'かど' },
            { kanji: '夜', yomi: 'や' }, { kanji: '夜', yomi: 'よ' }, { kanji: '夜', yomi: 'よる' },
            { kanji: '野', yomi: 'や' }, { kanji: '野', yomi: 'の' },
            { kanji: '友', yomi: 'ゆう' }, { kanji: '友', yomi: 'とも' },
            { kanji: '用', yomi: 'よう' }, { kanji: '用', yomi: 'もち' },
            { kanji: '曜', yomi: 'よう' },
            { kanji: '来', yomi: 'らい' }, { kanji: '来', yomi: 'く' }, { kanji: '来', yomi: 'き' },
            { kanji: '里', yomi: 'り' }, { kanji: '里', yomi: 'さと' },
            { kanji: '理', yomi: 'り' },
            { kanji: '話', yomi: 'わ' }, { kanji: '話', yomi: 'はな' }, { kanji: '話', yomi: 'はなし' }
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
                feedbackDisplay.textContent = 'よみを入力してください！';
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
