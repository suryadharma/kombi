<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?>Verifikasi <?= htmlspecialchars(AppSettings::getShortName()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/public/images/favicon.ico">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <link href="/assets/css/custom.css?v=20250207a" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .verification-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .verification-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .verification-header h1 {
            color: #667eea;
            font-weight: 700;
        }
        .verification-header .subtitle {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status-valid {
            background: linear-gradient(135deg, #00b09b, #96c93d);
            color: white;
        }
        .status-invalid {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: white;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .info-card .label {
            font-weight: 600;
            color: #495057;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-card .value {
            color: #212529;
            font-size: 1rem;
            margin-top: 5px;
        }
        .verification-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 0.85rem;
        }
        .verification-footer a {
            color: #667eea;
            text-decoration: none;
        }
        .verification-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-header">
            <i class="fas fa-shield-alt fa-3x" style="color: #667eea; margin-bottom: 15px;"></i>
            <h1>Verifikasi Dokumen</h1>
            <p class="subtitle"><?= htmlspecialchars(AppSettings::getFullName()) ?> (<?= htmlspecialchars(AppSettings::getShortName()) ?>)</p>
            <p class="subtitle"><?= htmlspecialchars(AppSettings::getFullOrganizationName()) ?></p>
        </div>

        <?php if (isset($content)): ?>
            <?= $content ?>
        <?php else: ?>
            <?= $viewContent ?? '' ?>
        <?php endif; ?>

        <div class="verification-footer">
            <p>Dokumen ini diverifikasi melalui <?= htmlspecialchars(AppSettings::getFullName()) ?></p>
            <p><?= AppSettings::getCopyrightText() ?></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
