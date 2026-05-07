<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session expirée</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: #0b0f1a;
            color: #f9fafb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #111827;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 48px 32px;
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .icon {
            width: 72px;
            height: 72px;
            background: rgba(245,158,11,0.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 32px;
        }
        h1 { font-size: 22px; font-weight: 700; margin-bottom: 12px; }
        p { color: #9ca3af; font-size: 15px; line-height: 1.6; }
        .hint {
            margin-top: 24px;
            background: rgba(245,158,11,0.08);
            border: 1px solid rgba(245,158,11,0.2);
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 13px;
            color: #fcd34d;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⏱</div>
        <h1>Session expirée</h1>
        <p>Votre session a expiré après 15 minutes d'inactivité.</p>
        <div class="hint">
            Scannez à nouveau le QR code de votre table pour continuer.
        </div>
    </div>
</body>
</html>
