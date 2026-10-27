<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#09090b">
    <title>Student Check-in — QRCAMv2</title>
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link rel="stylesheet" href="styles.css?v=liquid_glass_v1">
    <script defer src="assets/html5-qrcode.min.js"></script>
    <script defer src="checkin.js?v=liquid_glass_v1"></script>
</head>
<body class="checkin-page">
    <main class="checkin-wrap">
        <a class="brand checkin-brand" href="index.php"><span class="brand-mark small"><img class="brand-logo-icon theme-logo-dark" src="assets/logo-white.png" alt="QRCAM" width="22" height="22"><img class="brand-logo-icon theme-logo-light" src="assets/logo.png" alt="QRCAM" width="22" height="22"></span><span>QRCAM<span class="brand-light">v2</span></span></a>
        <section class="checkin-card">
            <p class="eyebrow">STUDENT ATTENDANCE</p>
            <h1>Check in to class</h1>
            <p class="muted">Use this phone's camera to scan your personal attendance QR code.</p>
            <div id="checkin-reader" class="reader"></div>
            <p id="checkin-message" class="scan-result" role="status">Preparing secure check-in…</p>
            <button id="checkin-start" class="button primary wide" disabled>Start camera</button>
            <button id="checkin-rescan" class="button secondary wide" hidden>Scan again</button>
        </section>
        <a class="checkin-admin" href="index.php">Adviser? Sign in to the management console</a>
    </main>
</body>
</html>
