<?php
session_start();
require_once 'db.php';

$pageTitle  = "Custom Teddy Details | Teddy Lap";
$pdo        = getDB();
$isLoggedIn = !empty($_SESSION['logged_in']);
$userId     = $_SESSION['user_id'] ?? null;

$customId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teddy    = null;

if ($customId) {
    $stmt = $pdo->prepare("
        SELECT custom_id, custom_name AS name, total_price AS price, config_json, created_at
        FROM custom_teddies
        WHERE custom_id = ?
    ");
    $stmt->execute([$customId]);
    $teddy = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        @keyframes floatShapes { 0% { transform: translate(0,0) rotate(0deg); } 100% { transform: translate(50px,30px) rotate(20deg); } }
        .details-container { padding: 120px 20px 50px; max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.2fr; gap: 50px; align-items: center; opacity: 0; animation: fadeIn 0.8s forwards; }
        @media (max-width: 900px) { .details-container { grid-template-columns: 1fr; padding-top: 100px; gap: 30px; } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .product-image-section { position: relative; background: var(--card-bg); border-radius: 30px; padding: 40px; box-shadow: 0 15px 40px var(--shadow); display: flex; justify-content: center; align-items: center; min-height: 450px; }
        .image-layer-container { position: relative; width: 100%; max-width: 350px; height: 400px; }
        .layer-img { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15)); }
        .customized-preview { position: relative; width: 100%; height: 100%; background: #f8f8f8; border-radius: 10px; overflow: hidden; }
        .customized-preview img { position: absolute; max-width: 100%; max-height: 100%; object-fit: contain; transition: all 0.3s; }
        .customized-preview .preview-base { width: 100%; height: 100%; object-fit: contain; z-index: 1; }
        .customized-preview .preview-outfit { width: 70%; top: 45%; left: 50%; transform: translate(-50%,-50%); z-index: 2; }
        .customized-preview .preview-shoes { width: 60%; top: 85%; left: 48%; transform: translate(-50%,-50%); z-index: 3; }
        .customized-preview .preview-acc { width: 26%; top: 18%; left: 15%; transform: translate(-50%,-50%); z-index: 4; }
        .customized-preview .preview-outfit.img-reddress { width: 65%; top: 57%; }
        .customized-preview .preview-outfit.img-pinkoutfit { width: 65%; top: 55%; }
        .customized-preview .preview-outfit.img-greenoutfit { width: 65%; top: 59%; }
        .customized-preview .preview-outfit.img-jeansoutfit { width: 60%; top: 59%; }
        .customized-preview .preview-shoes.img-redshoes { width: 50%; top: 93%; left: 48%; }
        .customized-preview .preview-shoes.img-darkshoes { width: 55%; top: 91%; left: 47%; }
        .customized-preview .preview-shoes.img-pinkshoes { width: 56%; top: 93%; left: 49%; }
        .customized-preview .preview-shoes.img-conversshoes { width: 53%; top: 92%; left: 48%; }
        .custom-badge-detail { position: absolute; top: 20px; left: 20px; background: linear-gradient(45deg, #a29bfe, #6c5ce7); color: #fff; padding: 8px 20px; border-radius: 20px; font-weight: 600; font-size: 14px; z-index: 10; box-shadow: 0 5px 15px rgba(108,92,231,0.3); }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--secondary-text); text-decoration: none; font-weight: 500; margin-bottom: 20px; transition: color 0.2s; }
        .back-link:hover { color: var(--pink); }
        .product-name { font-family: 'Playfair Display', serif; font-size: 36px; color: var(--text-color); margin: 0 0 10px; line-height: 1.2; }
        .product-price { font-size: 28px; color: var(--pink); font-weight: 700; margin-bottom: 25px; }
        .section-divider { height: 1px; background: #eee; margin: 20px 0; }
        body.dark-mode .section-divider { background: #444; }
        .specs-title { font-size: 18px; color: var(--text-color); margin-bottom: 15px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .specs-title i { color: var(--pink); }
        .specs-list { list-style: none; padding: 0; margin: 0 0 30px 0; }
        .spec-item { display: flex; align-items: center; padding: 12px 0; border-bottom: 1px dashed #f0f0f0; }
        body.dark-mode .spec-item { border-bottom-color: #333; }
        .spec-icon { width: 40px; height: 40px; background: var(--bg-color); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--pink); margin-right: 15px; font-size: 16px; }
        .spec-info { flex: 1; }
        .spec-label { font-size: 12px; color: var(--secondary-text); margin-bottom: 2px; }
        .spec-value { font-weight: 600; color: var(--text-color); font-size: 15px; }
        .error-state { grid-column: 1/-1; text-align: center; padding: 80px 20px; }
        .error-state i { font-size: 60px; color: var(--pink); margin-bottom: 20px; }
        .voice-player-container { display: flex; align-items: center; gap: 12px; }
        .play-voice-btn { background: linear-gradient(45deg, #ff9a9e, #fad0c4); color: #fff; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: 0 3px 8px rgba(255,154,158,0.4); transition: transform 0.2s; }
        .play-voice-btn:hover { transform: scale(1.1); }
        .play-voice-btn.playing { background: linear-gradient(45deg, #a29bfe, #6c5ce7); animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(162,155,254,0.4); } 70% { box-shadow: 0 0 0 10px rgba(162,155,254,0); } 100% { box-shadow: 0 0 0 0 rgba(162,155,254,0); } }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<?php if (!$teddy): ?>
    <div class="details-container">
        <div class="error-state">
            <i class="fa-solid fa-box-open"></i>
            <h2>Teddy Not Found</h2>
            <p style="color:var(--secondary-text);">This custom design might have been deleted.</p>
            <a href="shop.php" style="display:inline-flex;align-items:center;gap:8px;background:var(--pink);color:#fff;padding:12px 25px;border-radius:25px;text-decoration:none;font-weight:600;margin-top:15px;">
                Back to Shop
            </a>
        </div>
    </div>

<?php else:
    $cfg  = $teddy['config_json'] ? json_decode($teddy['config_json'], true) : null;
    $name = htmlspecialchars($teddy['name'] ?: 'Custom Teddy');
    ?>
    <div class="details-container">

        <!-- صورة الدب -->
        <div class="product-image-section">
            <div class="custom-badge-detail"><i class="fa-solid fa-magic"></i> Custom Made</div>
            <div class="image-layer-container">
                <?php if ($cfg && isset($cfg['color'])): ?>
                    <div class="customized-preview">
                        <img src="<?= htmlspecialchars($cfg['color']['img'] ?? 'images/brown.png') ?>"
                             class="preview-base" alt="Base">
                        <?php if (!empty($cfg['outfit'])): ?>
                            <?php $outfitFile = basename($cfg['outfit']['img'], '.png'); ?>
                            <img src="<?= htmlspecialchars($cfg['outfit']['img']) ?>"
                                 class="preview-outfit img-<?= $outfitFile ?>" alt="Outfit">
                        <?php endif; ?>
                        <?php if (!empty($cfg['shoes'])): ?>
                            <?php $shoesFile = basename($cfg['shoes']['img'], '.png'); ?>
                            <img src="<?= htmlspecialchars($cfg['shoes']['img']) ?>"
                                 class="preview-shoes img-<?= $shoesFile ?>" alt="Shoes">
                        <?php endif; ?>
                        <?php if (!empty($cfg['acc'])): ?>
                            <img src="<?= htmlspecialchars($cfg['acc']['img']) ?>"
                                 class="preview-acc" alt="Accessory">
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <img src="images/brown.png" class="layer-img" alt="Custom Teddy">
                <?php endif; ?>
            </div>
        </div>

        <!-- معلومات الدب -->
        <div class="product-info-section">
            <a href="javascript:history.back()" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>

            <h1 class="product-name"><?= $name ?></h1>
            <div class="product-price">$<?= number_format($teddy['price'], 2) ?></div>

            <div class="section-divider"></div>

            <div class="specs-title">
                <i class="fa-solid fa-list-check"></i> Customization Details
            </div>

            <ul class="specs-list">
                <?php if ($cfg): ?>
                    <?php if (!empty($cfg['color'])): ?>
                        <li class="spec-item">
                            <div class="spec-icon"><i class="fa-solid fa-palette"></i></div>
                            <div class="spec-info">
                                <span class="spec-label">Color</span>
                                <span class="spec-value"><?= htmlspecialchars($cfg['color']['name'] ?? '') ?></span>
                            </div>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($cfg['outfit'])): ?>
                        <li class="spec-item">
                            <div class="spec-icon"><i class="fa-solid fa-shirt"></i></div>
                            <div class="spec-info">
                                <span class="spec-label">Outfit</span>
                                <span class="spec-value"><?= htmlspecialchars($cfg['outfit']['name'] ?? '') ?></span>
                            </div>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($cfg['shoes'])): ?>
                        <li class="spec-item">
                            <div class="spec-icon"><i class="fa-solid fa-shoe-prints"></i></div>
                            <div class="spec-info">
                                <span class="spec-label">Shoes</span>
                                <span class="spec-value"><?= htmlspecialchars($cfg['shoes']['name'] ?? '') ?></span>
                            </div>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($cfg['acc'])): ?>
                        <li class="spec-item">
                            <div class="spec-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                            <div class="spec-info">
                                <span class="spec-label">Accessory</span>
                                <span class="spec-value"><?= htmlspecialchars($cfg['acc']['name'] ?? '') ?></span>
                            </div>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($cfg['name'])): ?>
                        <li class="spec-item">
                            <div class="spec-icon"><i class="fa-solid fa-signature"></i></div>
                            <div class="spec-info">
                                <span class="spec-label">Name</span>
                                <span class="spec-value"><?= htmlspecialchars($cfg['name']) ?></span>
                            </div>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($cfg['sound'])): ?>
                        <li class="spec-item">
                            <div class="spec-icon"><i class="fa-solid fa-microphone-lines"></i></div>
                            <div class="spec-info">
                                <span class="spec-label">Voice Message</span>
                                <div class="voice-player-container">
                                    <span class="spec-value">Recorded Audio</span>
                                    <button class="play-voice-btn" onclick="toggleVoice(this)"
                                            data-src="<?= htmlspecialchars($cfg['sound']) ?>">
                                        <i class="fa-solid fa-headphones"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <li class="spec-item">
                    <div class="spec-icon"><i class="fa-regular fa-calendar"></i></div>
                    <div class="spec-info">
                        <span class="spec-label">Created</span>
                        <span class="spec-value"><?= date('M d, Y', strtotime($teddy['created_at'])) ?></span>
                    </div>
                </li>
            </ul>
        </div>

    </div>
<?php endif; ?>

<audio id="audioPlayer" style="display:none;"></audio>

<script>
    const audioPlayer = document.getElementById('audioPlayer');

    function toggleVoice(btn) {
        const src = btn.dataset.src;
        if (!src) return;

        if (audioPlayer.getAttribute('data-src') === src && !audioPlayer.paused) {
            audioPlayer.pause();
            btn.classList.remove('playing');
            btn.innerHTML = '<i class="fa-solid fa-headphones"></i>';
        } else {
            audioPlayer.src = src;
            audioPlayer.setAttribute('data-src', src);
            audioPlayer.play();
            btn.classList.add('playing');
            btn.innerHTML = '<i class="fa-solid fa-pause"></i>';
        }
    }

    audioPlayer.onended = function() {
        const btn = document.querySelector('.play-voice-btn.playing');
        if (btn) { btn.classList.remove('playing'); btn.innerHTML = '<i class="fa-solid fa-headphones"></i>'; }
    };
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>