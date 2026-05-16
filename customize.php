<?php
session_start();
require_once 'db.php';

$pageTitle  = "Create Teddy | Teddy Lap";
$isLoggedIn = !empty($_SESSION['logged_in']);
$userId     = $_SESSION['user_id'] ?? null;
$pdo        = getDB();

// ── جيبي تصاميم المجتمع من DB ────────────────────────────────
$communityDesigns = [];
try {
    $stmt = $pdo->query("
        SELECT custom_name, total_price, config_json
        FROM custom_teddies
        WHERE config_json IS NOT NULL
        ORDER BY custom_id DESC
        LIMIT 12
    ");
    $communityDesigns = $stmt->fetchAll();
} catch (Exception $e) {}

// ── AJAX handlers ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // ── Add to Cart ───────────────────────────────────────────
    if ($_POST['action'] === 'add_to_cart') {
        if (!$isLoggedIn) { echo json_encode(['success'=>false,'message'=>'login_required']); exit; }

        $configJson  = $_POST['config']      ?? '{}';
        $name        = trim($_POST['name']   ?? 'Custom Teddy');
        $totalPrice  = (float)($_POST['price'] ?? 20);
        $config      = json_decode($configJson, true);

        // استخرج القيم من الـ config
        $basePrice   = 20;
        $extrasPrice = $totalPrice - $basePrice;
        $customName  = $config['name'] ?? '';

        $pdo->prepare("
            INSERT INTO custom_teddies (user_id, custom_name, base_price, extras_price, total_price, config_json, is_saved)
            VALUES (?, ?, ?, ?, ?, ?, FALSE)
        ")->execute([$userId, $customName, $basePrice, $extrasPrice, $totalPrice, $configJson]);

        $stmt2 = $pdo->query("SELECT lastval()");
        $customId = $stmt2->fetchColumn();

        echo json_encode(['success' => true, 'custom_id' => $customId, 'price' => $totalPrice, 'config' => $config]);
        exit;
    }

    // ── Save Design (My Teddies) ──────────────────────────────
    if ($_POST['action'] === 'save_design') {
        if (!$isLoggedIn) { echo json_encode(['success'=>false,'message'=>'login_required']); exit; }

        $configJson = $_POST['config']    ?? '{}';
        $totalPrice = (float)($_POST['price'] ?? 20);
        $config     = json_decode($configJson, true);
        $basePrice  = 20;
        $extrasPrice = $totalPrice - $basePrice;
        $customName = $config['name'] ?? '';

        $pdo->prepare("
            INSERT INTO custom_teddies (user_id, custom_name, base_price, extras_price, total_price, config_json, is_saved)
            VALUES (?, ?, ?, ?, ?, ?, TRUE)
        ")->execute([$userId, $customName, $basePrice, $extrasPrice, $totalPrice, $configJson]);

        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;600&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg-shape { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3; animation: floatShapes 15s infinite alternate; }
        .shape-1 { top: 10%; left: 10%; width: 300px; height: 300px; background: var(--pink); }
        .shape-2 { bottom: 20%; right: 10%; width: 400px; height: 400px; background: var(--lavender); animation-delay: 5s; }
        @keyframes floatShapes { 0% { transform: translate(0, 0) rotate(0deg); } 100% { transform: translate(50px, 30px) rotate(20deg); } }
        .lab-container { padding: 100px 20px 50px; max-width: 1400px; margin: 0 auto; min-height: 100vh; display: flex; flex-direction: column; align-items: center; }
        .main-title { font-family: 'Playfair Display', serif; font-size: 48px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; text-align: center; background: linear-gradient(45deg, #ff6b81, #ff9a9e, #fbc2eb, #ff6b81); background-size: 300% 300%; -webkit-background-clip: text; -webkit-text-fill-color: transparent; animation: gradientMove 4s ease infinite; }
        .subtitle { font-family: 'Poppins', sans-serif; color: var(--secondary-text); font-size: 18px; margin-bottom: 50px; text-align: center; }
        @keyframes gradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .lab-grid { display: grid; grid-template-columns: 1fr 400px 1fr; grid-template-rows: auto auto auto; gap: 40px 40px; width: 100%; align-items: center; justify-items: center; }
        .widget-col { width: 240px; display: flex; flex-direction: column; align-items: center; }
        .widget-title { font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 600; color: var(--text-color); margin-bottom: 15px; display: block; }
        .creative-box { width: 100%; padding: 20px 15px; border-radius: 25px; position: relative; overflow: visible; box-sizing: border-box; border: 2px solid rgba(255, 255, 255, 0.6); animation: simpleFloat 6s ease-in-out infinite; }
        @keyframes simpleFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .acc-box { background: linear-gradient(145deg, #fff0f5, #ffd1dc); box-shadow: 0 10px 25px rgba(255, 182, 193, 0.3), inset 0 2px 10px rgba(255,255,255,0.8); }
        .outfit-box { background: linear-gradient(145deg, #ffffff, #fff0f5); box-shadow: 0 15px 35px rgba(255, 107, 129, 0.15), inset 0 2px 10px rgba(255,255,255,0.9); border: 2px solid rgba(255, 200, 210, 0.4); }
        .shoes-box { background: linear-gradient(145deg, #fff0f5, #ffe0e6); box-shadow: 0 8px 20px rgba(255, 107, 129, 0.25), inset 0 2px 5px rgba(255,255,255,0.8); border: 1px solid rgba(255, 107, 129, 0.1); }
        .cute-deco { position: absolute; z-index: 0; pointer-events: none; opacity: 0.8; animation: cuteFloat 4s ease-in-out infinite; }
        .cute-deco i { font-size: 12px; color: #ff9a9e; text-shadow: 0 0 5px rgba(255,255,255,0.8); }
        .cute-deco.d1 { top: 10px; left: 10px; animation-delay: 0s; }
        .cute-deco.d2 { top: 10px; right: 10px; animation-delay: 1s; }
        .cute-deco.d3 { bottom: 10px; right: 15px; animation-delay: 0.5s; }
        .cute-deco.d4 { bottom: 10px; left: 15px; animation-delay: 1.5s; }
        .cute-deco.d1 i { color: #ff6b81; }
        .cute-deco.d3 i { color: #a29bfe; }
        @keyframes cuteFloat { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-5px) rotate(10deg); } }
        .item-display { position: relative; width: 100%; height: 100px; display: flex; justify-content: center; align-items: flex-end; padding-bottom: 5px; z-index: 1; }
        .stand-bar { position: absolute; bottom: 5px; width: 80%; height: 8px; background: rgba(255, 255, 255, 0.6); border-radius: 4px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); z-index: 0; }
        .item-card { width: 55px; height: 75px; border-radius: 15px; overflow: hidden; position: relative; z-index: 1; margin: 0 3px; background: rgba(255, 255, 255, 0.6); border: 2px solid transparent; transition: all 0.3s; cursor: pointer; margin-bottom: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        .item-card img { width: 100%; height: 100%; object-fit: contain; background-color: #fff; }
        .item-card:hover { transform: scale(1.15) translateY(-15px); z-index: 10; background: #fff; border-color: #ff6b81; box-shadow: 0 15px 25px rgba(0,0,0,0.15); }
        .item-card.selected { background: #fff; border: 2px solid #ff6b81; box-shadow: 0 5px 15px rgba(255, 107, 129, 0.3); }
        .shoe-item { width: 45px; height: 45px; margin-bottom: 25px !important; border-radius: 10px; }
        .shoe-item img { object-fit: contain; }
        .color-palette-box { background: linear-gradient(145deg, #fff0f5, #ffd1dc); padding: 25px 15px 20px 15px; border-radius: 25px; box-shadow: 0 15px 35px rgba(255, 107, 129, 0.25), inset 0 2px 10px rgba(255,255,255,0.8); position: relative; overflow: visible; width: 100%; border: 2px solid rgba(255, 182, 193, 0.5); animation: simpleFloat 6s ease-in-out infinite; box-sizing: border-box; }
        .paint-drop { position: absolute; border-radius: 50%; opacity: 0.6; z-index: 0; animation: dropBounce 3s ease-in-out infinite; }
        .d1 { width: 10px; height: 10px; background: #8B4513; top: 10px; left: 15px; animation-delay: 0s; }
        .d2 { width: 8px; height: 8px; background: #ADD8E6; bottom: 20px; left: 25px; animation-delay: 1.5s; }
        .d3 { width: 12px; height: 12px; background: #f3bebe; top: 20px; right: 15px; animation-delay: 0.5s; }
        .d4 { width: 6px; height: 6px; background: #C0C0C0; bottom: 15px; right: 40px; animation-delay: 2s; }
        @keyframes dropBounce { 0%, 100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-5px) scale(1.1); } }
        .palette-sparkle { position: absolute; top: -10px; right: -5px; color: #ff6b81; font-size: 16px; animation: spinSparkle 4s linear infinite; }
        @keyframes spinSparkle { 0% { transform: rotate(0deg) scale(1); } 50% { transform: rotate(180deg) scale(1.2); } 100% { transform: rotate(360deg) scale(1); } }
        .color-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; justify-items: center; z-index: 2; position: relative; }
        .color-circle { width: 32px; height: 32px; border-radius: 50%; cursor: pointer; border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.15), inset 0 2px 5px rgba(255,255,255,0.5); transition: transform 0.2s, box-shadow 0.2s; position: relative; }
        .color-circle::after { content: ''; position: absolute; top: 4px; left: 6px; width: 8px; height: 5px; background: rgba(255,255,255,0.6); border-radius: 50%; transform: rotate(-30deg); }
        .color-circle:hover { transform: scale(1.2) translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        .color-circle.selected { box-shadow: 0 0 0 3px #fff, 0 0 0 5px #ff9a9e; transform: scale(1.1); }
        .paint-brush-deco { position: absolute; right: -20px; bottom: 20px; transform: rotate(-45deg); color: #ff6b81; font-size: 28px; z-index: 10; animation: brushPaint 3s ease-in-out infinite; }
        .paint-stroke { position: absolute; left: -25px; top: 10px; width: 30px; height: 10px; background: linear-gradient(90deg, #ff6b81, transparent); border-radius: 10px; opacity: 0.6; z-index: -1; }
        @keyframes brushPaint { 0%, 100% { transform: rotate(-45deg) translate(0, 0); } 50% { transform: rotate(-40deg) translate(5px, -5px); } }
        .name-craft-box { background: linear-gradient(145deg, #ffffff, #fff0f5); width: 100%; padding: 25px 15px; border-radius: 25px; position: relative; overflow: visible; box-shadow: 0 15px 35px rgba(255, 107, 129, 0.15), inset 0 2px 10px rgba(255,255,255,0.9); border: 2px solid rgba(255, 200, 210, 0.4); box-sizing: border-box; animation: simpleFloat 7s ease-in-out infinite; }
        .craft-deco { position: absolute; z-index: 1; pointer-events: none; opacity: 0.8; animation: cuteFloat 5s ease-in-out infinite; }
        .fly-heart { color: #ff6b81; }
        .fh1 { top: -10px; left: 10px; font-size: 14px; animation-delay: 0s; }
        .fh2 { bottom: -5px; right: 20px; font-size: 12px; animation-delay: 2s; color: #ff9a9e; }
        .fh3 { top: 40px; right: -10px; font-size: 10px; animation-delay: 1s; color: #a29bfe; }
        .fly-star { color: #ffeaa7; }
        .fs1 { top: 15px; right: 15px; font-size: 12px; animation-delay: 0.5s; }
        .fs2 { bottom: 20px; left: 20px; font-size: 10px; animation-delay: 1.5s; }
        .craft-label { background: #fff; width: 90%; padding: 15px 10px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; border: 1px solid #ffe0e6; transition: transform 0.3s; }
        .craft-label:hover { transform: scale(1.02); }
        .craft-label::before { content: ''; position: absolute; top: -8px; left: 50%; transform: translateX(-50%); width: 50px; height: 16px; background: rgba(255, 107, 129, 0.2); border-radius: 2px; border-left: 2px dashed rgba(255, 107, 129, 0.3); border-right: 2px dashed rgba(255, 107, 129, 0.3); }
        .craft-input { border: none; border-bottom: 2px dashed #ffd1dc; background: transparent; width: 100%; text-align: center; font-family: 'Dancing Script', cursive; font-size: 22px; color: #ff6b81; outline: none; padding-bottom: 5px; transition: all 0.3s; }
        .craft-input:focus { border-bottom-color: #ff6b81; }
        .craft-input::placeholder { color: #ffb8c0; opacity: 0.7; font-size: 16px; }
        .craft-btn { margin-top: 15px; background: linear-gradient(145deg, #ff6b81, #ff4757); color: #fff; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 14px; box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3); transition: all 0.3s; z-index: 2; display: flex; justify-content: center; align-items: center; }
        .craft-btn:hover { transform: scale(1.15) rotate(10deg); }
        .sound-device-container { position: relative; width: 100%; padding: 15px 10px; display: flex; flex-direction: column; align-items: center; }
        .music-note { position: absolute; color: var(--pink); opacity: 0.6; font-size: 12px; animation: floatNote 3s ease-in-out infinite; z-index: 0; }
        .note-1 { top: 0; left: 10px; animation-delay: 0s; color: #ff9a9e; }
        .note-2 { top: 5px; right: 10px; animation-delay: 1.5s; font-size: 14px; color: #a29bfe; }
        .note-3 { bottom: 5px; left: 20px; animation-delay: 0.8s; font-size: 10px; }
        @keyframes floatNote { 0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.6; } 50% { transform: translateY(-5px) rotate(15deg); opacity: 1; } }
        .sound-device { background: linear-gradient(145deg, #fff0f5, #ffe0e6); border-radius: 20px; padding: 12px; width: 100%; box-shadow: 0 8px 20px rgba(255, 107, 129, 0.25), inset 0 2px 5px rgba(255,255,255,0.8); display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; border: 1px solid rgba(255, 107, 129, 0.1); box-sizing: border-box; animation: simpleFloat 8s ease-in-out infinite; }
        .device-screen { width: 100%; height: 30px; background: #fff; border-radius: 8px; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); position: relative; overflow: hidden; }
        .wave-bars { display: flex; align-items: center; gap: 2px; height: 15px; }
        .bar { width: 2px; background: var(--pink); border-radius: 2px; opacity: 0.4; }
        .bar.active { opacity: 1; animation: waveAnim 0.5s ease infinite alternate; }
        .bar:nth-child(1) { height: 6px; } .bar:nth-child(2) { height: 10px; } .bar:nth-child(3) { height: 8px; } .bar:nth-child(4) { height: 12px; } .bar:nth-child(5) { height: 6px; }
        @keyframes waveAnim { 0% { height: 4px; } 100% { height: 14px; } }
        .device-controls { display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; }
        .btn-rec { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(145deg, #ff6b81, #ff4757); border: 3px solid #fff; box-shadow: 0 4px 10px rgba(255, 71, 87, 0.4); color: #fff; font-size: 14px; cursor: pointer; transition: transform 0.2s; display: flex; justify-content: center; align-items: center; }
        .btn-rec:hover { transform: scale(1.1); }
        .btn-action { background: none; border: none; color: #aaa; font-size: 16px; cursor: pointer; transition: all 0.2s; visibility: hidden; opacity: 0; }
        .btn-action.visible { visibility: visible; opacity: 1; }
        .btn-action:hover { color: var(--pink); transform: scale(1.2); }
        .btn-action.heart-btn { font-size: 18px; color: #ff6b81; }
        .saved-ui { display: none; flex-direction: column; align-items: center; width: 100%; }
        .saved-ui.visible { display: flex; }
        .audio-player-sm { width: 100%; height: 25px; margin-bottom: 6px; }
        .status-row { display: flex; align-items: center; justify-content: space-between; width: 100%; }
        .saved-txt { font-size: 10px; font-weight: 600; color: var(--pink); display: flex; align-items: center; gap: 4px; }
        .btn-reset { background: #f0f0f0; border: none; width: 20px; height: 20px; border-radius: 50%; color: #555; cursor: pointer; font-size: 9px; transition: all 0.2s; }
        .btn-reset:hover { background: var(--pink); color: #fff; transform: rotate(-360deg); }
        .teddy-area { grid-column: 2; grid-row: 1 / 4; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; }
        .teddy-stage { position: relative; width: 350px; height: 480px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .layer-base, .layer-item { position: absolute; top: 35%; left: 50%; transform: translate(-50%, -50%); width: 100%; height: 100%; object-fit: contain; z-index: 10; transition: all 0.4s ease; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15)); }
        .layer-base { width: 100%; height: 100%; object-fit: contain; object-position: center; animation: floatBear 4s ease-in-out infinite; }
        .layer-item { opacity: 0; pointer-events: none; }
        .layer-item.active { opacity: 1; }
        #layer-acc { width: 90px; left: 5%; top: 18%; transform: translate(-50%, -50%); z-index: 5; animation: floatBear 4s ease-in-out infinite; }
        #layer-outfit { width: 90%; height: auto; top: 46%; left: 50%; transform: translate(-50%, -50%); z-index: 15; animation: floatBear 4s ease-in-out infinite; }
        #layer-outfit.img-reddress { width: 90%; top: 46%; left: 50%; }
        #layer-outfit.img-pinkoutfit { width: 85%; top: 42%; left: 50%; }
        #layer-outfit.img-greenoutfit { width: 80%; top: 44%; left: 50%; }
        #layer-outfit.img-jeansoutfit { width: 74%; top: 45%; left: 50%; }
        #layer-shoes { width: 60%; height: auto; top: 80%; left: 48%; transform: translate(-50%, -50%); z-index: 16; animation: floatBear 4s ease-in-out infinite; }
        #layer-shoes.img-redshoes { width: 60%; top: 80%; left: 48%; }
        #layer-shoes.img-darkshoes { width: 67%; top: 77%; left: 47%; }
        #layer-shoes.img-pinkshoes { width: 65%; top: 78%; left: 49%; }
        #layer-shoes.img-conversshoes { width: 65%; top: 77%; left: 48%; }
        @keyframes floatBear { 0%, 100% { transform: translate(-50%, -50%) translateY(0); } 50% { transform: translate(-50%, -50%) translateY(-10px); } }
        .platform-container { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); width: 100%; display: flex; flex-direction: column; align-items: center; z-index: 5; }
        .platform-glow { width: 300px; height: 40px; background: radial-gradient(ellipse at center, rgba(255, 107, 129, 0.9) 0%, rgba(255, 107, 129, 0) 70%); border-radius: 50%; filter: blur(15px); animation: pulseGlow 2s infinite alternate; margin-bottom: -8px; }
        .platform-stand { width: 280px; height: 60px; background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%); border-radius: 50px; box-shadow: 0 15px 40px rgba(0,0,0,0.25), 0 0 30px rgba(255, 107, 129, 0.6); display: flex; align-items: center; justify-content: center; position: relative; border: 1px solid rgba(255, 107, 129, 0.2); }
        .stand-text { font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700; letter-spacing: 2px; z-index: 2; }
        .text-black { color: #333; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .text-pink { color: var(--pink); text-shadow: 0 0 10px rgba(255, 107, 129, 0.5); animation: textGlow 2s infinite alternate; }
        .stand-deco { position: absolute; width: 25px; height: 25px; opacity: 0.9; filter: drop-shadow(0 3px 3px rgba(0,0,0,0.2)); z-index: 1; animation: decoFloat 3s ease-in-out infinite; }
        .deco-1 { top: 5px; left: 20px; animation-delay: 0s; }
        .deco-2 { bottom: 5px; right: 25px; width: 22px; height: 22px; animation-delay: 1s; }
        .deco-3 { top: 10px; right: 30px; width: 18px; height: 18px; animation-delay: 0.5s; }
        @keyframes pulseGlow { 0% { opacity: 0.6; transform: scale(0.95); } 100% { opacity: 1; transform: scale(1.05); } }
        @keyframes textGlow { 0% { color: var(--pink); } 100% { color: #ff4757; text-shadow: 0 0 20px rgba(255, 107, 129, 1); } }
        @keyframes decoFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-3px); } }
        .text-overlay { position: absolute; top: 80px; right: 40px; font-family: 'Dancing Script', cursive; font-size: 22px; color: #000000; text-shadow: 0 1px 2px rgba(0,0,0,0.1); z-index: 20; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
        .text-overlay.active { opacity: 1; }
        .sound-indicator { position: absolute; top: 20px; right: 20px; font-size: 20px; color: var(--pink); background: #fff; border-radius: 50%; padding: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: none; z-index: 10; }
        .sound-indicator.active { display: block; animation: pulse 1s infinite; }
        .pos-acc { grid-column: 1; grid-row: 1; justify-self: end; }
        .pos-outfit { grid-column: 1; grid-row: 2; justify-self: end; }
        .pos-shoes { grid-column: 1; grid-row: 3; justify-self: end; }
        .pos-color { grid-column: 3; grid-row: 1; justify-self: start; }
        .pos-name { grid-column: 3; grid-row: 2; justify-self: start; }
        .pos-sound { grid-column: 3; grid-row: 3; justify-self: start; }
        .summary-container { margin-top: 60px; width: 100%; display: flex; flex-direction: column; align-items: center; padding-bottom: 50px; }
        .summary-actions-top { display: flex; gap: 20px; margin-bottom: 30px; }
        .summary-box { background: #fff; padding: 30px; border-radius: 25px; width: 100%; max-width: 600px; box-shadow: 0 15px 40px rgba(255, 107, 129, 0.15); border: 2px solid #ffd1dc; position: relative; overflow: hidden; }
        .summary-box::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 5px; background: linear-gradient(90deg, #ff6b81, #ff9a9e, #fad0c4); }
        .summary-title { text-align: center; font-family: 'Playfair Display', serif; font-size: 28px; color: #333; margin-bottom: 25px; }
        .summary-list { list-style: none; padding: 0; margin: 0; }
        .summary-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed #eee; font-family: 'Poppins', sans-serif; font-size: 15px; color: #666; }
        .summary-item span:last-child { font-weight: 600; color: #444; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 20px; font-size: 22px; font-weight: 700; color: #ff6b81; border-top: 2px solid #ffd1dc; padding-top: 15px; }
        .summary-actions { display: flex; gap: 20px; margin-top: 30px; }
        .btn-summary { flex: 1; padding: 15px; border-radius: 30px; border: none; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-cart { background: linear-gradient(45deg, #ff6b81, #ff4757); color: #fff; box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3); }
        .btn-cart:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(255, 71, 87, 0.4); }
        .btn-save { background: #fff; color: #ff6b81; border: 2px solid #ff6b81; }
        .btn-save:hover { background: #fff0f5; transform: translateY(-3px); }
        .gallery-section { width: 100%; max-width: 800px; margin-top: 20px; margin-bottom: 50px; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .gallery-card { background: #fff; border-radius: 20px; padding: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid #ffd1dc; transition: transform 0.3s; cursor: pointer; }
        .gallery-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(255, 107, 129, 0.15); }
        .gallery-card-preview { position: relative; width: 100%; height: 200px; background: #fff0f5; border-radius: 15px; margin-bottom: 10px; overflow: hidden; display: flex; justify-content: center; align-items: center; }
        .gallery-card-preview img { position: absolute; max-width: 100%; max-height: 100%; object-fit: contain; transition: all 0.3s; }
        .gallery-card-info { display: flex; justify-content: space-between; align-items: center; padding: 0 5px; }
        .gallery-card-name { font-weight: 600; color: #444; font-size: 14px; }
        .gallery-card-price { color: #ff6b81; font-weight: 700; }
        .section-subtitle { font-family: 'Poppins', sans-serif; font-size: 16px; color: #777; margin-bottom: 15px; margin-top: 30px; width: 100%; border-bottom: 1px dashed #ddd; padding-bottom: 5px; }
        .use-btn { background: #ff6b81; color: white; border: none; border-radius: 20px; padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; margin-left: 8px; white-space: nowrap; box-shadow: 0 2px 5px rgba(255, 107, 129, 0.3); }
        .use-btn:hover { background: #ff4757; transform: scale(1.05); }
        .gallery-card-preview .preview-base { width: 100%; height: 100%; object-fit: contain; z-index: 1; }
        .gallery-card-preview .preview-outfit { width: 70%; top: 45%; left: 50%; transform: translate(-50%, -50%); z-index: 2; }
        .gallery-card-preview .preview-outfit.img-reddress { width: 60%; top: 60%; left: 50%; }
        .gallery-card-preview .preview-outfit.img-pinkoutfit { width: 55%; top: 57%; left: 50%; }
        .gallery-card-preview .preview-outfit.img-greenoutfit { width: 50%; top: 57%; left: 50%; }
        .gallery-card-preview .preview-outfit.img-jeansoutfit { width: 50%; top: 60%; left: 50%; }
        .gallery-card-preview .preview-shoes { width: 60%; top: 85%; left: 48%; transform: translate(-50%, -50%); z-index: 3; }
        .gallery-card-preview .preview-shoes.img-redshoes { width: 40%; top: 95%; left: 49%; }
        .gallery-card-preview .preview-shoes.img-darkshoes { width: 45%; top: 91%; left: 49%; }
        .gallery-card-preview .preview-shoes.img-pinkshoes { width: 45%; top: 95%; left: 49%; }
        .gallery-card-preview .preview-shoes.img-conversshoes { width: 43%; top: 92%; left: 49%; }
        .gallery-card-preview .preview-acc { width: 26%; top: 18%; left: 15%; transform: translate(-50%, -50%); z-index: 4; }
        @media (max-width: 1100px) { .lab-grid { grid-template-columns: 1fr 300px 1fr; gap: 20px; } .teddy-stage { width: 280px; height: 350px; } }
        @media (max-width: 768px) { .lab-grid { display: flex; flex-direction: column; align-items: center; gap: 30px; } .teddy-area { order: 1; margin-bottom: 30px; } .widget-box, .widget-col { order: 2; width: 100%; max-width: 300px; } .summary-actions { flex-direction: column; } .summary-actions-top { flex-direction: column; width: 100%; max-width: 600px; } }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
</div>

<?php if (file_exists('navbar.php')) include 'navbar.php'; ?>

<div class="lab-container">
    <h1 class="main-title">Create Your Own Teddy</h1>
    <p class="subtitle">Customize every detail to build your perfect fluffy friend.</p>

    <div class="lab-grid">
        <div class="widget-col pos-acc">
            <span class="widget-title">Add Accessories</span>
            <div class="creative-box acc-box">
                <div class="cute-deco d1"><i class="fa-solid fa-heart"></i></div>
                <div class="cute-deco d2"><i class="fa-solid fa-star"></i></div>
                <div class="cute-deco d3"><i class="fa-solid fa-sparkles"></i></div>
                <div class="cute-deco d4"><i class="fa-solid fa-heart"></i></div>
                <div class="item-display">
                    <div class="stand-bar"></div>
                    <div class="item-card" onclick="toggleSelection('acc', 'images/redacc.png', this, 'Red Accessory')"><img src="images/redacc.png" alt="Acc 1"></div>
                    <div class="item-card" onclick="toggleSelection('acc', 'images/ball.png', this, 'Ball')"><img src="images/ball.png" alt="Acc 2"></div>
                    <div class="item-card" onclick="toggleSelection('acc', 'images/sunglasses.png', this, 'Sunglasses')"><img src="images/sunglasses.png" alt="Acc 3"></div>
                    <div class="item-card" onclick="toggleSelection('acc', 'images/camera.png', this, 'Camera')"><img src="images/camera.png" alt="Acc 4"></div>
                </div>
            </div>
        </div>

        <div class="widget-col pos-color">
            <span class="widget-title">Choose Color</span>
            <div class="color-palette-box">
                <div class="paint-drop d1"></div><div class="paint-drop d2"></div><div class="paint-drop d3"></div><div class="paint-drop d4"></div>
                <div class="palette-sparkle"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <div class="color-grid">
                    <div class="color-circle selected" style="background:#8B4513;" onclick="selectColor(this, 'brown', 'images/brown.png')"></div>
                    <div class="color-circle" style="background:#FFFFFF; border:1px solid #eee;" onclick="selectColor(this, 'white', 'images/white.png')"></div>
                    <div class="color-circle" style="background:#000000;" onclick="selectColor(this, 'black', 'images/black.png')"></div>
                    <div class="color-circle" style="background:#f3bebe;" onclick="selectColor(this, 'skin', 'images/pinkk.png')"></div>
                    <div class="color-circle" style="background:#C0C0C0;" onclick="selectColor(this, 'silver', 'images/gray.png')"></div>
                    <div class="color-circle" style="background:#ADD8E6;" onclick="selectColor(this, 'blue', 'images/blue.png')"></div>
                </div>
                <div class="paint-brush-deco"><div class="paint-stroke"></div><i class="fa-solid fa-paintbrush"></i></div>
            </div>
        </div>

        <div class="widget-col pos-outfit">
            <span class="widget-title">Select Outfit</span>
            <div class="creative-box outfit-box">
                <div class="cute-deco d1"><i class="fa-solid fa-shirt"></i></div>
                <div class="cute-deco d3"><i class="fa-solid fa-heart"></i></div>
                <div class="cute-deco d4"><i class="fa-solid fa-star"></i></div>
                <div class="item-display">
                    <div class="stand-bar"></div>
                    <div class="item-card" onclick="toggleSelection('outfit', 'images/reddress.png', this, 'Red Dress')"><img src="images/reddress.png" alt="Outfit 1"></div>
                    <div class="item-card" onclick="toggleSelection('outfit', 'images/pinkoutfit.png', this, 'Pink Outfit')"><img src="images/pinkoutfit.png" alt="Outfit 2"></div>
                    <div class="item-card" onclick="toggleSelection('outfit', 'images/greenoutfit.png', this, 'Casual Outfit')"><img src="images/greenoutfit.png" alt="Outfit 3"></div>
                    <div class="item-card" onclick="toggleSelection('outfit', 'images/jeansoutfit.png', this, 'Jeans Outfit')"><img src="images/jeansoutfit.png" alt="Outfit 4"></div>
                </div>
            </div>
        </div>

        <div class="teddy-area">
            <div class="teddy-stage">
                <img src="images/brown.png" class="layer-base" id="bearBase" alt="Teddy Bear">
                <img src="" class="layer-item" id="layer-outfit" alt="Outfit">
                <img src="" class="layer-item" id="layer-shoes" alt="Shoes">
                <img src="" class="layer-item" id="layer-acc" alt="Accessory">
                <div class="text-overlay" id="textName"></div>
                <div class="sound-indicator" id="soundIndicator"><i class="fa-solid fa-headphones"></i></div>
                <div class="platform-container">
                    <div class="platform-glow"></div>
                    <div class="platform-stand">
                        <span class="stand-text"><span class="text-black">Teddy</span> <span class="text-pink">Lab</span></span>
                        <img src="images/redacc.png" class="stand-deco deco-1" alt="deco">
                        <img src="images/ball.png" class="stand-deco deco-2" alt="deco">
                        <img src="images/sunglasses.png" class="stand-deco deco-3" alt="deco">
                    </div>
                </div>
            </div>
        </div>

        <div class="widget-col pos-name">
            <span class="widget-title">Choose Name</span>
            <div class="name-craft-box">
                <div class="craft-deco fly-heart fh1"><i class="fa-solid fa-heart"></i></div>
                <div class="craft-deco fly-heart fh2"><i class="fa-solid fa-heart"></i></div>
                <div class="craft-deco fly-heart fh3"><i class="fa-solid fa-heart"></i></div>
                <div class="craft-deco fly-star fs1"><i class="fa-solid fa-star"></i></div>
                <div class="craft-deco fly-star fs2"><i class="fa-solid fa-star"></i></div>
                <div class="craft-label">
                    <input type="text" class="craft-input" id="nameInput" placeholder="Type Name..." maxlength="10">
                </div>
                <button class="craft-btn" onclick="saveName()"><i class="fa-solid fa-heart"></i></button>
            </div>
        </div>

        <div class="widget-col pos-shoes">
            <span class="widget-title">Pick Shoes</span>
            <div class="creative-box shoes-box">
                <div class="cute-deco d2"><i class="fa-solid fa-shoe-prints"></i></div>
                <div class="cute-deco d1"><i class="fa-solid fa-star"></i></div>
                <div class="cute-deco d4"><i class="fa-solid fa-heart"></i></div>
                <div class="item-display">
                    <div class="stand-bar"></div>
                    <div class="item-card shoe-item" onclick="toggleSelection('shoes', 'images/redshoes.png', this, 'Red Shoes')"><img src="images/redshoes.png" alt="Shoes 1"></div>
                    <div class="item-card shoe-item" onclick="toggleSelection('shoes', 'images/darkshoes.png', this, 'Boots')"><img src="images/darkshoes.png" alt="Shoes 2"></div>
                    <div class="item-card shoe-item" onclick="toggleSelection('shoes', 'images/pinkshoes.png', this, 'Pink Shoes')"><img src="images/pinkshoes.png" alt="Shoes 3"></div>
                    <div class="item-card shoe-item" onclick="toggleSelection('shoes', 'images/conversshoes.png', this, 'Convers shoes')"><img src="images/conversshoes.png" alt="Shoes 4"></div>
                </div>
            </div>
        </div>

        <div class="widget-col pos-sound">
            <span class="widget-title">Add Sound</span>
            <div class="sound-device-container">
                <i class="fa-solid fa-music music-note note-1"></i>
                <i class="fa-solid fa-music music-note note-2"></i>
                <i class="fa-solid fa-music music-note note-3"></i>
                <div class="sound-device">
                    <div class="device-screen">
                        <div class="wave-bars" id="waveBars">
                            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                        </div>
                        <span class="screen-text" id="screenStatus"></span>
                    </div>
                    <div class="device-controls" id="controlsInitial">
                        <button class="btn-rec" id="btnRec" onclick="toggleRecording()"><i class="fa-solid fa-microphone" id="recIcon"></i></button>
                    </div>
                    <div class="device-controls" id="controlsRecorded" style="display:none;">
                        <button class="btn-action visible" onclick="deleteRecording()"><i class="fa-solid fa-trash"></i></button>
                        <button class="btn-rec" onclick="playPauseAudio()" style="width:35px;height:35px;"><i class="fa-solid fa-play" id="playIcon"></i></button>
                        <button class="btn-action visible heart-btn" onclick="saveRecording()"><i class="fa-solid fa-heart"></i></button>
                    </div>
                    <div class="saved-ui" id="savedUI">
                        <audio id="audioPlayback" class="audio-player-sm" controls></audio>
                        <div class="status-row">
                            <span class="saved-txt"><i class="fa-solid fa-check-circle"></i> Saved!</span>
                            <button class="btn-reset" onclick="resetRecorder()"><i class="fa-solid fa-rotate-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="summary-container">
        <div class="summary-actions-top">
            <button class="btn-summary btn-cart" style="background:linear-gradient(45deg,#a29bfe,#6c5ce7);" onclick="surpriseMe()">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Surprise Me
            </button>
            <button class="btn-summary btn-save" onclick="resetDesign()">
                <i class="fa-solid fa-redo"></i> Reset Design
            </button>
        </div>
        <div class="summary-box">
            <h2 class="summary-title">Order Summary</h2>
            <ul class="summary-list" id="summaryList"></ul>
            <div class="summary-total"><span>Total</span><span id="summaryTotal">$20</span></div>
            <div class="summary-actions">
                <button class="btn-summary btn-cart" onclick="addToCart(this)">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
                <button class="btn-summary btn-save" onclick="saveDesign(this)">
                    <i class="fa-solid fa-bookmark"></i> Save
                </button>
            </div>
        </div>
    </div>

    <div class="gallery-section">
        <p class="section-subtitle">Top Suggestions</p>
        <div class="gallery-grid" id="suggestionsGrid"></div>
    </div>
</div>

<script>
    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    const communityDesigns = <?= json_encode($communityDesigns) ?>;
    const prices = { base: 20, outfit: 5, shoes: 3, acc: 4, sound: 5 };
    const colorFiles = { brown:'brown.png', white:'white.png', black:'black.png', skin:'pinkk.png', silver:'gray.png', blue:'blue.png' };

    let currentConfig = {
        color: { name: 'Brown', img: 'images/brown.png' },
        outfit: null, shoes: null, acc: null, sound: null, name: ''
    };

    updateSummary();
    loadGallery();

    function selectColor(element, colorName, imgSrc) {
        document.querySelectorAll('.color-circle').forEach(c => c.classList.remove('selected'));
        if(element) element.classList.add('selected');
        const fileName = colorFiles[colorName] || (colorName + '.png');
        currentConfig.color = { name: colorName.charAt(0).toUpperCase() + colorName.slice(1), img: 'images/' + fileName };
        document.getElementById('bearBase').src = 'images/' + fileName;
        updateSummary();
    }

    function toggleSelection(type, imgSrc, element, itemName) {
        const isSelected = element && element.classList.contains('selected');
        const parentDisplay = element ? element.closest('.item-display') : null;
        if (parentDisplay) parentDisplay.querySelectorAll('.item-card').forEach(c => c.classList.remove('selected'));
        const layer = document.getElementById('layer-' + type);
        if (isSelected) {
            if(element) element.classList.remove('selected');
            layer.src = ''; layer.classList.remove('active'); currentConfig[type] = null;
        } else {
            if(element) element.classList.add('selected');
            layer.src = imgSrc; layer.classList.add('active');
            currentConfig[type] = { name: itemName, img: imgSrc };
            if (type === 'outfit' || type === 'shoes') {
                const fileName = imgSrc.split('/').pop().split('.').shift();
                layer.className = 'layer-item active';
                layer.classList.add('img-' + fileName);
            }
        }
        updateSummary();
    }

    function saveName() {
        const name = document.getElementById('nameInput').value;
        const nameDisplay = document.getElementById('textName');
        if (name) { nameDisplay.innerText = name; nameDisplay.classList.add('active'); currentConfig.name = name; }
        else { nameDisplay.classList.remove('active'); currentConfig.name = ''; }
        updateSummary();
    }

    let mediaRecorder, audioChunks = [], recordedBlob = null, recordedAudioBase64 = null;
    const btnRec = document.getElementById('btnRec');
    const recIcon = document.getElementById('recIcon');
    const waveBars = document.getElementById('waveBars');
    const screenStatus = document.getElementById('screenStatus');
    const controlsInitial = document.getElementById('controlsInitial');
    const controlsRecorded = document.getElementById('controlsRecorded');
    const savedUI = document.getElementById('savedUI');
    const audioPlayback = document.getElementById('audioPlayback');
    const soundIndicator = document.getElementById('soundIndicator');
    const playIcon = document.getElementById('playIcon');

    function toggleRecording() {
        if (mediaRecorder && mediaRecorder.state === 'recording') stopRecording();
        else startRecording();
    }

    async function startRecording() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
            mediaRecorder.onstop = () => {
                recordedBlob = new Blob(audioChunks, { type: 'audio/wav' });
                const reader = new FileReader();
                reader.readAsDataURL(recordedBlob);
                reader.onloadend = () => { recordedAudioBase64 = reader.result; };
                audioPlayback.src = URL.createObjectURL(recordedBlob);
                audioChunks = [];
                btnRec.classList.remove('recording');
                recIcon.className = 'fa-solid fa-microphone';
                waveBars.querySelectorAll('.bar').forEach(b => b.classList.remove('active'));
                screenStatus.innerText = "Recorded";
                controlsInitial.style.display = 'none';
                controlsRecorded.style.display = 'flex';
            };
            mediaRecorder.start();
            btnRec.classList.add('recording');
            recIcon.className = 'fa-solid fa-stop';
            waveBars.querySelectorAll('.bar').forEach(b => b.classList.add('active'));
        } catch(err) { alert("Could not access microphone."); }
    }

    function stopRecording() { if (mediaRecorder) mediaRecorder.stop(); }

    function playPauseAudio() {
        if (audioPlayback.src) {
            if (audioPlayback.paused) { audioPlayback.play(); playIcon.className = 'fa-solid fa-pause'; }
            else { audioPlayback.pause(); playIcon.className = 'fa-solid fa-play'; }
        }
    }
    audioPlayback.onended = () => { playIcon.className = 'fa-solid fa-play'; };
    function deleteRecording() { resetRecorder(); updateSummary(); }

    function saveRecording() {
        if (!recordedAudioBase64) { alert("No audio recorded yet."); return; }
        currentConfig.sound = recordedAudioBase64;
        soundIndicator.classList.add('active');
        controlsRecorded.style.display = 'none';
        savedUI.classList.add('visible');
        updateSummary();
    }

    function resetRecorder() {
        recordedBlob = null; recordedAudioBase64 = null; audioPlayback.src = "";
        currentConfig.sound = null; soundIndicator.classList.remove('active');
        savedUI.classList.remove('visible'); controlsRecorded.style.display = 'none';
        controlsInitial.style.display = 'flex'; screenStatus.innerText = ""; playIcon.className = 'fa-solid fa-play';
    }

    function updateSummary() {
        const list = document.getElementById('summaryList');
        let total = prices.base, html = '';
        html += `<li class="summary-item"><span>Base Teddy</span><span>$${prices.base}</span></li>`;
        if(currentConfig.color) html += `<li class="summary-item"><span>Color: ${currentConfig.color.name}</span><span><i class="fa-solid fa-gift"></i> Included</span></li>`;
        if(currentConfig.outfit) { total += prices.outfit; html += `<li class="summary-item"><span>Outfit: ${currentConfig.outfit.name}</span><span>$${prices.outfit}</span></li>`; }
        if(currentConfig.shoes)  { total += prices.shoes;  html += `<li class="summary-item"><span>Shoes: ${currentConfig.shoes.name}</span><span>$${prices.shoes}</span></li>`; }
        if(currentConfig.acc)    { total += prices.acc;    html += `<li class="summary-item"><span>Accessory: ${currentConfig.acc.name}</span><span>$${prices.acc}</span></li>`; }
        if(currentConfig.name)   html += `<li class="summary-item"><span>Name: "${currentConfig.name}"</span><span><i class="fa-solid fa-heart"></i> Free</span></li>`;
        if(currentConfig.sound)  { total += prices.sound;  html += `<li class="summary-item"><span>Custom Sound</span><span>$${prices.sound}</span></li>`; }
        list.innerHTML = html;
        document.getElementById('summaryTotal').innerText = '$' + total;
    }

    function generateDesignData() {
        let desc = [`Color: ${currentConfig.color.name}`];
        if(currentConfig.outfit) desc.push(`Outfit: ${currentConfig.outfit.name}`);
        if(currentConfig.shoes)  desc.push(`Shoes: ${currentConfig.shoes.name}`);
        if(currentConfig.acc)    desc.push(`Acc: ${currentConfig.acc.name}`);
        if(currentConfig.name)   desc.push(`Name: ${currentConfig.name}`);
        let total = prices.base;
        if(currentConfig.outfit) total += prices.outfit;
        if(currentConfig.shoes)  total += prices.shoes;
        if(currentConfig.acc)    total += prices.acc;
        if(currentConfig.sound)  total += prices.sound;
        return {
            id: 'CUSTOM_' + Date.now(),
            name: `Custom Teddy (${currentConfig.name || 'No Name'})`,
            price: total,
            description: desc.join(', '),
            config: JSON.parse(JSON.stringify(currentConfig))
        };
    }

    // ── Add to Cart — DB إذا مسجّل، localStorage إذا لا ────────
    function addToCart(btn) {
        const product = generateDesignData();
        const originalHTML = btn.innerHTML;

        if (!isLoggedIn) {
            // مش مسجّل → localStorage مؤقتاً
            let cart = JSON.parse(localStorage.getItem('teddy_cart')) || {};
            cart[product.id] = 1;
            localStorage.setItem('teddy_cart', JSON.stringify(cart));
            let customItems = JSON.parse(localStorage.getItem('teddy_custom_items')) || {};
            customItems[product.id] = product;
            localStorage.setItem('teddy_custom_items', JSON.stringify(customItems));
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
            btn.style.background = '#28a745';
            setTimeout(() => { btn.innerHTML = originalHTML; btn.style.background = ''; }, 1500);
            return;
        }

        // مسجّل → DB
        const configJson = JSON.stringify(product.config);
        const body = new URLSearchParams({
            action: 'add_to_cart',
            config: configJson,
            name: product.name,
            price: product.price,
            description: product.description
        });

        fetch('customize.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // احفظ custom_id في sessionStorage عشان الكارت يقدر يعرضه
                    let customCart = JSON.parse(sessionStorage.getItem('custom_cart') || '[]');
                    customCart.push({ custom_id: data.custom_id, name: product.name, price: data.price, config: product.config });
                    sessionStorage.setItem('custom_cart', JSON.stringify(customCart));

                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Added';
                    btn.style.background = '#28a745';
                    setTimeout(() => { btn.innerHTML = originalHTML; btn.style.background = ''; }, 1500);
                }
            });
    }

    // ── Save Design — DB إذا مسجّل، localStorage إذا لا ────────
    function saveDesign(btn) {
        const product = generateDesignData();
        const originalHTML = btn.innerHTML;

        if (!isLoggedIn) {
            let savedDesigns = JSON.parse(localStorage.getItem('teddy_saved_designs')) || [];
            savedDesigns.push(product);
            localStorage.setItem('teddy_saved_designs', JSON.stringify(savedDesigns));
            loadGallery();
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Saved';
            btn.style.background = '#28a745'; btn.style.borderColor = '#28a745'; btn.style.color = '#fff';
            setTimeout(() => { btn.innerHTML = originalHTML; btn.style.background = ''; btn.style.borderColor = ''; btn.style.color = ''; }, 1500);
            return;
        }

        const body = new URLSearchParams({
            action: 'save_design',
            config: JSON.stringify(product.config),
            name: product.name,
            price: product.price,
            description: product.description
        });

        fetch('customize.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    loadGallery();
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Saved';
                    btn.style.background = '#28a745'; btn.style.borderColor = '#28a745'; btn.style.color = '#fff';
                    setTimeout(() => { btn.innerHTML = originalHTML; btn.style.background = ''; btn.style.borderColor = ''; btn.style.color = ''; }, 1500);
                }
            });
    }

    function calculatePrice(config) {
        let total = prices.base;
        if(config.outfit) total += prices.outfit;
        if(config.shoes)  total += prices.shoes;
        if(config.acc)    total += prices.acc;
        if(config.sound)  total += prices.sound;
        return total;
    }

    function generateCardHTML(config, title) {
        const colorImgName = colorFiles[config.color.name.toLowerCase()] || 'brown.png';
        const baseImg = 'images/' + colorImgName;
        let outfitClass = config.outfit ? `preview-outfit img-${config.outfit.img.split('/').pop().split('.').shift()}` : '';
        let shoesClass  = config.shoes  ? `preview-shoes img-${config.shoes.img.split('/').pop().split('.').shift()}`   : '';
        let accClass    = config.acc    ? `preview-acc img-${config.acc.img.split('/').pop().split('.').shift()}`        : '';
        const configStr = encodeURIComponent(JSON.stringify(config));
        return `
        <div class="gallery-card">
            <div class="gallery-card-preview">
                <img src="${baseImg}" class="preview-base" alt="Base">
                ${config.outfit ? `<img src="${config.outfit.img}" class="${outfitClass}" alt="Outfit">` : ''}
                ${config.shoes  ? `<img src="${config.shoes.img}"  class="${shoesClass}"  alt="Shoes">` : ''}
                ${config.acc    ? `<img src="${config.acc.img}"    class="${accClass}"    alt="Accessory">` : ''}
            </div>
            <div class="gallery-card-info">
                <span class="gallery-card-name">${title}</span>
                <span class="gallery-card-price">$${calculatePrice(config)}</span>
                <button class="use-btn" onclick="event.stopPropagation(); applyPreset(decodeURIComponent('${configStr}'))">Use</button>
            </div>
        </div>`;
    }

    function loadGallery() {
        const suggestionsGrid = document.getElementById('suggestionsGrid');

        // الاقتراحات الثابتة
        const suggestions = [
            { title: "Sporty Star",   color:{name:'Brown'}, outfit:{img:'images/greenoutfit.png'},  shoes:{img:'images/conversshoes.png'}, acc:{img:'images/ball.png'} },
            { title: "Pink Princess", color:{name:'Skin'},  outfit:{img:'images/pinkoutfit.png'},   shoes:{img:'images/pinkshoes.png'},    acc:{img:'images/redacc.png'} },
            { title: "Cool Winter",   color:{name:'White'}, outfit:{img:'images/jeansoutfit.png'},  shoes:{img:'images/darkshoes.png'},    acc:{img:'images/sunglasses.png'} }
        ];

        // تصاميم المجتمع من DB
        const dbItems = communityDesigns
            .filter(d => d.config_json)
            .map(d => {
                try {
                    const cfg = JSON.parse(d.config_json);
                    return { title: d.custom_name || 'Custom Teddy', color: cfg.color, outfit: cfg.outfit, shoes: cfg.shoes, acc: cfg.acc };
                } catch(e) { return null; }
            })
            .filter(Boolean);

        const allItems = [...suggestions, ...dbItems];
        suggestionsGrid.innerHTML = allItems.map(item => generateCardHTML(item, item.title)).join('');
    }

    function applyPreset(configStr) {
        const preset = JSON.parse(decodeURIComponent(configStr));
        resetDesign(false);
        selectColor(null, preset.color.name.toLowerCase());
        if(preset.outfit) toggleSelection('outfit', preset.outfit.img, null, 'Outfit');
        if(preset.shoes)  toggleSelection('shoes',  preset.shoes.img,  null, 'Shoes');
        if(preset.acc)    toggleSelection('acc',    preset.acc.img,    null, 'Acc');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function surpriseMe() {
        const colors  = ['brown','white','black','skin','silver','blue'];
        const outfits = ['images/reddress.png','images/pinkoutfit.png','images/greenoutfit.png','images/jeansoutfit.png'];
        const shoes   = ['images/redshoes.png','images/darkshoes.png','images/pinkshoes.png','images/conversshoes.png'];
        const accs    = ['images/redacc.png','images/ball.png','images/sunglasses.png','images/camera.png'];
        const rand = arr => arr[Math.floor(Math.random() * arr.length)];
        applyPreset(encodeURIComponent(JSON.stringify({ color:{name:rand(colors)}, outfit:{img:rand(outfits)}, shoes:{img:rand(shoes)}, acc:{img:rand(accs)} })));
    }

    function resetDesign(scroll = true) {
        currentConfig = { color:{name:'Brown',img:'images/brown.png'}, outfit:null, shoes:null, acc:null, sound:null, name:'' };
        document.getElementById('bearBase').src = 'images/brown.png';
        ['outfit','shoes','acc'].forEach(type => { const l = document.getElementById('layer-'+type); l.src=''; l.className='layer-item'; });
        document.querySelectorAll('.item-card.selected').forEach(c => c.classList.remove('selected'));
        document.querySelectorAll('.color-circle').forEach((c,i) => { if(i===0) c.classList.add('selected'); else c.classList.remove('selected'); });
        document.getElementById('nameInput').value = '';
        document.getElementById('textName').classList.remove('active');
        resetRecorder();
        updateSummary();
        if(scroll) window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

<?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>