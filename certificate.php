<?php
session_start();

// Gate: bounce anyone without a verified session back to the login form
if (empty($_SESSION['verified_email'])) {
    header('Location: login.php');
    exit;
}

$attendeeEmail = $_SESSION['verified_email'];
$attendeeName  = $_SESSION['attendee_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SQA Festival 2026 — Certificate Generator</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --navy:#0a1628;--navy-mid:#112040;--navy-light:#1a3060;
  --gold:#c9a84c;--gold-light:#e8c96a;--gold-pale:#f5e9c8;
  --white:#ffffff;--gray-100:#f0f4f8;--gray-300:#c8d0dc;--gray-500:#6b7a94;
  --radius:12px;--radius-sm:6px;
}
body{background:var(--navy);font-family:'Inter',sans-serif;min-height:100vh;display:flex;flex-direction:column;color:var(--white);}

/* ── Header ── */
.header{background:var(--navy-mid);border-bottom:1px solid rgba(201,168,76,.25);padding:14px 20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.header-badge{background:linear-gradient(135deg,var(--gold),var(--gold-light));color:var(--navy);font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;padding:4px 10px;border-radius:20px;white-space:nowrap;}
.header h1{font-family:'Playfair Display',serif;color:var(--white);font-size:16px;font-weight:600;}
.header-sub{color:var(--gold);font-size:11px;font-weight:500;letter-spacing:.05em;margin-left:auto;}
.header-account{display:flex;align-items:center;gap:10px;margin-left:auto;font-size:11.5px;color:var(--gray-300);}
.header-account a{color:var(--gold);text-decoration:none;border-bottom:1px solid rgba(201,168,76,.4);}
.header-account a:hover{color:var(--gold-light);}

/* ── Mobile tab bar ── */
.mob-tabs{display:none;background:var(--navy-mid);border-bottom:1px solid rgba(201,168,76,.15);}
.mob-tab{flex:1;padding:12px 8px;background:none;border:none;color:var(--gray-500);font-family:'Inter',sans-serif;font-size:12px;font-weight:500;cursor:pointer;border-bottom:2px solid transparent;transition:all .2s;}
.mob-tab.active{color:var(--gold);border-bottom-color:var(--gold);}

/* ── Workspace ── */
.workspace{display:flex;flex:1;overflow:hidden;}

/* ── Sidebar ── */
.sidebar{width:300px;min-width:300px;background:var(--navy-mid);border-right:1px solid rgba(201,168,76,.15);display:flex;flex-direction:column;overflow-y:auto;}
.sidebar-section{padding:18px 18px 0;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:18px;}
.sidebar-section:last-of-type{border-bottom:none;}
.section-label{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--gold);margin-bottom:12px;}
.field{margin-bottom:12px;}
.field label{display:block;font-size:12px;font-weight:500;color:var(--gray-300);margin-bottom:5px;}
.field input[type="text"],.field input[type="number"],.field select{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius-sm);color:var(--white);font-family:'Inter',sans-serif;font-size:13px;padding:9px 12px;outline:none;transition:border-color .2s;}
.field input:focus,.field select:focus{border-color:var(--gold);}
.field select option{background:var(--navy-mid);color:var(--white);}
.field input[type="file"]{width:100%;color:var(--gray-300);font-size:12px;cursor:pointer;}
.field input[type="file"]::file-selector-button{background:rgba(201,168,76,.15);border:1px solid var(--gold);color:var(--gold);padding:6px 12px;border-radius:var(--radius-sm);font-size:12px;cursor:pointer;margin-right:10px;font-family:'Inter',sans-serif;}
.color-row{display:flex;align-items:center;gap:8px;}
.color-row input[type="color"]{width:40px;height:36px;padding:2px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius-sm);cursor:pointer;}
.color-hex{flex:1;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius-sm);color:var(--white);font-family:'Inter',sans-serif;font-size:13px;padding:9px 12px;outline:none;}
.color-hex:focus{border-color:var(--gold);}
.size-row{display:flex;align-items:center;gap:8px;}
.size-row input[type="range"]{flex:1;accent-color:var(--gold);}
.size-val{font-size:12px;color:var(--gold);font-weight:600;width:36px;text-align:right;}
.cert-types{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.cert-type-btn{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius-sm);color:var(--gray-300);font-family:'Inter',sans-serif;font-size:11px;font-weight:500;padding:8px 6px;cursor:pointer;text-align:center;transition:all .2s;}
.cert-type-btn:hover{border-color:var(--gold);color:var(--gold);}
.cert-type-btn.active{background:rgba(201,168,76,.15);border-color:var(--gold);color:var(--gold);}
.actions{padding:14px 18px;display:flex;flex-direction:column;gap:8px;border-top:1px solid rgba(201,168,76,.15);}
.btn-primary{background:linear-gradient(135deg,var(--gold),var(--gold-light));color:var(--navy);border:none;border-radius:var(--radius-sm);font-family:'Inter',sans-serif;font-size:13px;font-weight:700;padding:12px;cursor:pointer;letter-spacing:.04em;transition:opacity .2s;width:100%;}
.btn-primary:hover{opacity:.9;}
.btn-secondary{background:transparent;color:var(--gray-300);border:1px solid rgba(255,255,255,.15);border-radius:var(--radius-sm);font-family:'Inter',sans-serif;font-size:13px;font-weight:500;padding:10px;cursor:pointer;transition:all .2s;width:100%;}
.btn-secondary:hover{border-color:var(--gray-300);color:var(--white);}

/* ── Canvas area ── */
.canvas-area{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:24px 16px;background:repeating-conic-gradient(rgba(255,255,255,.03) 0% 25%,transparent 0% 50%) 0 0/24px 24px;overflow:auto;}
.canvas-hint{font-size:11px;color:var(--gray-500);margin-bottom:14px;letter-spacing:.05em;text-align:center;line-height:1.6;}
.cert-scaler{transform-origin:top center;}
#certificate{position:relative;width:1056px;height:748px;background:var(--white);box-shadow:0 24px 80px rgba(0,0,0,.5),0 0 0 1px rgba(201,168,76,.3);overflow:hidden;flex-shrink:0;}
#cert-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:none;}
.cert-design{position:absolute;inset:0;width:100%;height:100%;}
#cert-name{position:absolute;left:50%;top:52%;transform:translate(-50%,-50%);font-size:52px;font-weight:700;font-family:'Playfair Display',serif;color:var(--navy);cursor:move;user-select:none;white-space:nowrap;z-index:10;text-shadow:0 1px 3px rgba(0,0,0,.1);}
#cert-name:hover{outline:2px dashed rgba(201,168,76,.6);outline-offset:6px;}

/* Touch drag indicator */
.touch-hint{display:none;font-size:11px;color:var(--gold);margin-top:10px;text-align:center;}

/* ── RESPONSIVE ── */
@media(max-width:768px){
  .header-sub{display:none;}
  .header h1{font-size:14px;}
  .mob-tabs{display:flex;}
  .workspace{flex-direction:column;overflow:visible;}
  .sidebar{
    width:100%;min-width:unset;border-right:none;
    border-bottom:1px solid rgba(201,168,76,.15);
    display:none; /* hidden by default, shown via tab */
  }
  .sidebar.mob-visible{display:flex;}
  .canvas-area{
    display:none;
    padding:16px 12px 24px;
    min-height:auto;
  }
  .canvas-area.mob-visible{display:flex;}
  .canvas-hint{font-size:10px;}
  .touch-hint{display:block;}
  .actions{flex-direction:row;flex-wrap:wrap;}
  .actions .btn-primary,.actions .btn-secondary{flex:1;min-width:120px;}
}

@media(max-width:480px){
  .header{padding:10px 14px;gap:8px;}
  .header-badge{font-size:9px;padding:3px 8px;}
  .header h1{font-size:13px;}
  .sidebar-section{padding:14px 14px 0;padding-bottom:14px;}
  .cert-types{grid-template-columns:1fr 1fr;}
  .actions{padding:12px 14px;}
}
</style>
</head>
<body>

<div class="header">
  <div class="header-badge">SQA Festival 2026</div>
  <h1>Certificate Generator</h1>
  <div class="header-account">
    <span><?php echo htmlspecialchars($attendeeEmail, ENT_QUOTES); ?></span>
    <a href="logout.php">Log out</a>
  </div>
</div>

<!-- Mobile tab navigation -->
<div class="mob-tabs">
  <button class="mob-tab active" onclick="showTab('settings')">⚙ Settings</button>
  <button class="mob-tab" onclick="showTab('preview')">👁 Preview</button>
</div>

<div class="workspace">

  <!-- Sidebar -->
  <aside class="sidebar mob-visible" id="sidebar">

    <div class="sidebar-section">
      <div class="section-label">Certificate Type</div>
      <div class="cert-types">
        <button class="cert-type-btn active" onclick="setCertType('attendance','#fff')" id="btn-attendance">Attendance</button>
        <!-- <button class="cert-type-btn" onclick="setCertType('participation','#fff')" id="btn-participation">Participation</button>
        <button class="cert-type-btn" onclick="setCertType('speaker','#e8c96a')" id="btn-speaker">Speaker</button>
        <button class="cert-type-btn" onclick="setCertType('custom','#0a1628')" id="btn-custom">Custom</button> -->
      </div>
    </div>

    <div class="sidebar-section">
      <div class="section-label">Recipient</div>
      <div class="field">
        <label>Full Name</label>
        <input type="text" id="recipient" placeholder="e.g. Jane Mwangi" oninput="updateName()" value="<?php echo htmlspecialchars($attendeeName, ENT_QUOTES); ?>">
      </div>
    </div>

    <div class="sidebar-section">
      <div class="section-label">Typography</div>
      <div class="field">
        <label>Font Family</label>
        <select id="font" onchange="updateFont()">
          <option value="'Playfair Display', serif">Playfair Display</option>
          <option value="Georgia, serif">Georgia</option>
          <option value="'Times New Roman', serif">Times New Roman</option>
          <option value="'Inter', sans-serif">Inter</option>
          <option value="Verdana, sans-serif">Verdana</option>
        </select>
      </div>
      <div class="field">
        <label>Font Size</label>
        <div class="size-row">
          <input type="range" id="size-slider" min="20" max="100" value="52" oninput="updateSize(this.value)">
          <span class="size-val" id="size-display">52px</span>
        </div>
      </div>
      <div class="field">
        <label>Name Color</label>
        <div class="color-row">
          <input type="color" id="color" value="#ffffff" oninput="updateColor(this.value)">
          <input type="text" class="color-hex" id="color-hex" value="#ffffff" oninput="updateColorFromHex(this.value)">
        </div>
      </div>
    </div>

    <div class="sidebar-section">
      <div class="section-label">Background</div>
      <div class="field">
        <label>Upload Certificate Template</label>
        <input type="file" id="upload" accept="image/*" onchange="uploadBg()">
      </div>
    </div>

    <div class="actions">
      <button class="btn-primary" onclick="downloadPNG()">⬇ Download PNG</button>
      <button class="btn-secondary" onclick="window.print()">🖨 Print</button>
    </div>

  </aside>

  <!-- Canvas -->
  <main class="canvas-area" id="canvas-area">
    <p class="canvas-hint">Drag the name to reposition · Scroll over name to resize</p>
    <p class="touch-hint">Long-press &amp; drag the name to reposition</p>

    <div class="cert-scaler" id="cert-scaler">
      <div id="certificate">
        <img id="cert-bg" src="" alt="">
        <svg class="cert-design" id="cert-svg" viewBox="0 0 1056 748" xmlns="http://www.w3.org/2000/svg"></svg>
        <div id="cert-name">Recipient Name</div>
      </div>
    </div>
  </main>

</div>

<script>
// ── Mobile tabs ──
function showTab(tab){
  const sidebar=document.getElementById('sidebar');
  const canvas=document.getElementById('canvas-area');
  document.querySelectorAll('.mob-tab').forEach(b=>b.classList.remove('active'));
  if(tab==='settings'){
    sidebar.classList.add('mob-visible');
    canvas.classList.remove('mob-visible');
    document.querySelector('.mob-tab:nth-child(1)').classList.add('active');
  } else {
    sidebar.classList.remove('mob-visible');
    canvas.classList.add('mob-visible');
    document.querySelector('.mob-tab:nth-child(2)').classList.add('active');
    setTimeout(scaleCert,50);
  }
}

// ── Scale certificate ──
function scaleCert(){
  const area=document.getElementById('canvas-area');
  const scaler=document.getElementById('cert-scaler');
  const padding=window.innerWidth<=768?24:80;
  const availW=area.clientWidth-padding;
  const availH=Math.max(area.clientHeight-80,300);
  const scale=Math.min(availW/1056,availH/748,1);
  scaler.style.transform=`scale(${scale})`;
  scaler.style.marginBottom=`${-(748*(1-scale))}px`;
  scaler.style.marginRight=`${-(1056*(1-scale))/2}px`;
  scaler.style.marginLeft=`${-(1056*(1-scale))/2}px`;
}
window.addEventListener('resize',scaleCert);
window.addEventListener('load',scaleCert);

// ── Designs ──
const designs={
  attendance:()=>`
    <rect width="1056" height="748" fill="#0a1628"/>
    <rect x="0" y="0" width="8" height="748" fill="#c9a84c"/>
    <rect x="1048" y="0" width="8" height="748" fill="#c9a84c"/>
    <rect x="0" y="0" width="1056" height="5" fill="#c9a84c"/>
    <rect x="0" y="743" width="1056" height="5" fill="#c9a84c"/>
    <rect x="28" y="28" width="1000" height="692" fill="none" stroke="#c9a84c" stroke-width="1" opacity="0.4"/>
    <rect x="36" y="36" width="984" height="676" fill="none" stroke="#c9a84c" stroke-width="0.5" opacity="0.25"/>
    <polygon points="0,0 220,0 0,180" fill="#112040"/>
    <polygon points="1056,748 836,748 1056,568" fill="#112040"/>
    <line x1="50" y1="50" x2="130" y2="50" stroke="#c9a84c" stroke-width="1.5"/>
    <line x1="50" y1="50" x2="50" y2="130" stroke="#c9a84c" stroke-width="1.5"/>
    <circle cx="50" cy="50" r="4" fill="#c9a84c"/>
    <line x1="1006" y1="50" x2="926" y2="50" stroke="#c9a84c" stroke-width="1.5"/>
    <line x1="1006" y1="50" x2="1006" y2="130" stroke="#c9a84c" stroke-width="1.5"/>
    <circle cx="1006" cy="50" r="4" fill="#c9a84c"/>
    <line x1="50" y1="698" x2="130" y2="698" stroke="#c9a84c" stroke-width="1.5"/>
    <line x1="50" y1="698" x2="50" y2="618" stroke="#c9a84c" stroke-width="1.5"/>
    <circle cx="50" cy="698" r="4" fill="#c9a84c"/>
    <line x1="1006" y1="698" x2="926" y2="698" stroke="#c9a84c" stroke-width="1.5"/>
    <line x1="1006" y1="698" x2="1006" y2="618" stroke="#c9a84c" stroke-width="1.5"/>
    <circle cx="1006" cy="698" r="4" fill="#c9a84c"/>
    <text x="528" y="110" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" font-weight="600" letter-spacing="6" fill="#c9a84c" opacity="0.9">KIWAMI TECH SOLUTIONS</text>
    <line x1="228" y1="122" x2="828" y2="122" stroke="#c9a84c" stroke-width="0.8" opacity="0.4"/>
    <text x="528" y="175" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="300" letter-spacing="5" fill="#e8c96a" opacity="0.85">CERTIFICATE OF</text>
    <text x="528" y="240" text-anchor="middle" font-family="Georgia,serif" font-size="56" font-weight="700" letter-spacing="8" fill="#ffffff">ATTENDANCE</text>
    <line x1="378" y1="262" x2="678" y2="262" stroke="#c9a84c" stroke-width="1" opacity="0.6"/>
    <circle cx="528" cy="262" r="3" fill="#c9a84c"/>
    <text x="528" y="310" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="400" letter-spacing="3" fill="#c8d0dc" opacity="0.7">PRESENTED TO</text>
    <line x1="278" y1="430" x2="778" y2="430" stroke="#c9a84c" stroke-width="1" opacity="0.5"/>
    <text x="528" y="470" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="300" letter-spacing="2" fill="#c8d0dc" opacity="0.65">for attending</text>
    <text x="528" y="512" text-anchor="middle" font-family="Georgia,serif" font-size="26" font-weight="700" fill="#c9a84c">Software Quality Assurance Festival 2026</text>
    <text x="528" y="538" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="300" letter-spacing="1" fill="#c8d0dc" opacity="0.65">10–11 April 2026  ·  124 Manyani E Rd, Nairobi, Kenya</text>
    <line x1="180" y1="640" x2="380" y2="640" stroke="#c9a84c" stroke-width="0.8" opacity="0.5"/>
    <text x="280" y="658" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" letter-spacing="1" fill="#c8d0dc" opacity="0.5">ORGANISER</text>
    <line x1="676" y1="640" x2="876" y2="640" stroke="#c9a84c" stroke-width="0.8" opacity="0.5"/>
    <text x="776" y="658" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" letter-spacing="1" fill="#c8d0dc" opacity="0.5">DATE</text>
    <text x="528" y="708" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" font-weight="300" letter-spacing="4" fill="#c9a84c" opacity="0.45">TEST · BREAK · LEARN · CONNECT</text>
  `,
  participation:()=>`
    <rect width="1056" height="748" fill="#0f1f3d"/>
    <defs><linearGradient id="gG" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#c9a84c" stop-opacity="0.3"/><stop offset="50%" stop-color="#e8c96a"/><stop offset="100%" stop-color="#c9a84c" stop-opacity="0.3"/></linearGradient><radialGradient id="rG" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#c9a84c" stop-opacity="0.08"/><stop offset="100%" stop-color="#c9a84c" stop-opacity="0"/></radialGradient></defs>
    <rect width="1056" height="748" fill="url(#rG)"/>
    <rect x="0" y="0" width="1056" height="6" fill="url(#gG)"/>
    <rect x="0" y="742" width="1056" height="6" fill="url(#gG)"/>
    <rect x="24" y="24" width="1008" height="700" fill="none" stroke="#c9a84c" stroke-width="1" opacity="0.3"/>
    <g opacity="0.07" fill="none" stroke="#c9a84c" stroke-width="1">
      <polygon points="900,50 930,67 930,101 900,118 870,101 870,67"/>
      <polygon points="960,50 990,67 990,101 960,118 930,101 930,67"/>
      <polygon points="930,0 960,17 960,51 930,68 900,51 900,17"/>
    </g>
    <text x="528" y="105" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" font-weight="600" letter-spacing="6" fill="#c9a84c">KIWAMI TECH SOLUTIONS</text>
    <line x1="228" y1="118" x2="828" y2="118" stroke="#c9a84c" stroke-width="0.5" opacity="0.35"/>
    <text x="528" y="168" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="300" letter-spacing="5" fill="#e8c96a" opacity="0.8">CERTIFICATE OF</text>
    <text x="528" y="235" text-anchor="middle" font-family="Georgia,serif" font-size="54" font-weight="700" letter-spacing="6" fill="#ffffff">PARTICIPATION</text>
    <line x1="378" y1="257" x2="678" y2="257" stroke="#c9a84c" stroke-width="1" opacity="0.6"/>
    <circle cx="528" cy="257" r="3" fill="#c9a84c"/>
    <text x="528" y="305" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="400" letter-spacing="3" fill="#c8d0dc" opacity="0.65">THIS CERTIFIES THAT</text>
    <line x1="278" y1="430" x2="778" y2="430" stroke="#c9a84c" stroke-width="1" opacity="0.5"/>
    <text x="528" y="470" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="300" letter-spacing="2" fill="#c8d0dc" opacity="0.65">actively participated in</text>
    <text x="528" y="512" text-anchor="middle" font-family="Georgia,serif" font-size="26" font-weight="700" fill="#c9a84c">Software Quality Assurance Festival 2026</text>
    <text x="528" y="538" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="300" letter-spacing="1" fill="#c8d0dc" opacity="0.65">10–11 April 2026  ·  124 Manyani E Rd, Nairobi, Kenya</text>
    <line x1="180" y1="640" x2="380" y2="640" stroke="#c9a84c" stroke-width="0.8" opacity="0.5"/>
    <text x="280" y="658" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" letter-spacing="1" fill="#c8d0dc" opacity="0.5">ORGANISER</text>
    <line x1="676" y1="640" x2="876" y2="640" stroke="#c9a84c" stroke-width="0.8" opacity="0.5"/>
    <text x="776" y="658" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" letter-spacing="1" fill="#c8d0dc" opacity="0.5">DATE</text>
    <text x="528" y="708" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" font-weight="300" letter-spacing="4" fill="#c9a84c" opacity="0.4">TEST · BREAK · LEARN · CONNECT</text>
  `,
  speaker:()=>`
    <rect width="1056" height="748" fill="#050e1f"/>
    <defs>
      <linearGradient id="sG" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#c9a84c"/><stop offset="100%" stop-color="#f5e9c8"/></linearGradient>
      <radialGradient id="sglow" cx="50%" cy="40%" r="60%"><stop offset="0%" stop-color="#1a3060" stop-opacity="0.8"/><stop offset="100%" stop-color="#050e1f" stop-opacity="0"/></radialGradient>
    </defs>
    <rect width="1056" height="748" fill="url(#sglow)"/>
    <rect x="16" y="16" width="1024" height="716" fill="none" stroke="url(#sG)" stroke-width="2"/>
    <rect x="24" y="24" width="1008" height="700" fill="none" stroke="#c9a84c" stroke-width="0.5" opacity="0.3"/>
    <g fill="#c9a84c" opacity="0.5">
      <circle cx="528" cy="72" r="2.5"/>
      <circle cx="500" cy="72" r="1.5"/>
      <circle cx="556" cy="72" r="1.5"/>
      <circle cx="472" cy="72" r="1"/>
      <circle cx="584" cy="72" r="1"/>
    </g>
    <text x="528" y="108" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" font-weight="600" letter-spacing="6" fill="#c9a84c">KIWAMI TECH SOLUTIONS</text>
    <line x1="228" y1="122" x2="828" y2="122" stroke="#c9a84c" stroke-width="0.5" opacity="0.4"/>
    <text x="528" y="170" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="300" letter-spacing="5" fill="#e8c96a" opacity="0.8">HONOURS</text>
    <text x="528" y="238" text-anchor="middle" font-family="Georgia,serif" font-size="54" font-weight="700" letter-spacing="6" fill="url(#sG)">SPEAKER</text>
    <text x="528" y="272" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="300" letter-spacing="5" fill="#e8c96a" opacity="0.7">RECOGNITION AWARD</text>
    <line x1="378" y1="288" x2="678" y2="288" stroke="#c9a84c" stroke-width="1" opacity="0.6"/>
    <circle cx="528" cy="288" r="3" fill="#c9a84c"/>
    <text x="528" y="332" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="400" letter-spacing="3" fill="#c8d0dc" opacity="0.65">IN RECOGNITION OF</text>
    <line x1="278" y1="444" x2="778" y2="444" stroke="#c9a84c" stroke-width="1" opacity="0.5"/>
    <text x="528" y="480" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="300" letter-spacing="2" fill="#c8d0dc" opacity="0.65">for their outstanding contribution as a speaker at</text>
    <text x="528" y="522" text-anchor="middle" font-family="Georgia,serif" font-size="26" font-weight="700" fill="#c9a84c">Software Quality Assurance Festival 2026</text>
    <text x="528" y="548" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="300" letter-spacing="1" fill="#c8d0dc" opacity="0.65">10–11 April 2026  ·  Nairobi, Kenya</text>
    <line x1="180" y1="646" x2="380" y2="646" stroke="#c9a84c" stroke-width="0.8" opacity="0.5"/>
    <text x="280" y="664" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" letter-spacing="1" fill="#c8d0dc" opacity="0.5">ORGANISER</text>
    <line x1="676" y1="646" x2="876" y2="646" stroke="#c9a84c" stroke-width="0.8" opacity="0.5"/>
    <text x="776" y="664" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" letter-spacing="1" fill="#c8d0dc" opacity="0.5">DATE</text>
    <text x="528" y="710" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" font-weight="300" letter-spacing="4" fill="#c9a84c" opacity="0.4">TEST · BREAK · LEARN · CONNECT</text>
  `,
  custom:()=>`
    <rect width="1056" height="748" fill="#f0f4f8"/>
    <rect x="0" y="0" width="8" height="748" fill="#0a1628"/>
    <rect x="1048" y="0" width="8" height="748" fill="#0a1628"/>
    <rect x="0" y="0" width="1056" height="6" fill="#0a1628"/>
    <rect x="0" y="742" width="1056" height="6" fill="#0a1628"/>
    <rect x="28" y="28" width="1000" height="692" fill="none" stroke="#0a1628" stroke-width="1" opacity="0.15"/>
    <text x="528" y="105" text-anchor="middle" font-family="Inter,sans-serif" font-size="11" font-weight="600" letter-spacing="6" fill="#0a1628" opacity="0.6">KIWAMI TECH SOLUTIONS</text>
    <line x1="228" y1="118" x2="828" y2="118" stroke="#0a1628" stroke-width="0.5" opacity="0.2"/>
    <text x="528" y="170" text-anchor="middle" font-family="Inter,sans-serif" font-size="13" font-weight="300" letter-spacing="5" fill="#0a1628" opacity="0.5">CERTIFICATE OF</text>
    <text x="528" y="235" text-anchor="middle" font-family="Georgia,serif" font-size="56" font-weight="700" letter-spacing="6" fill="#0a1628">ACHIEVEMENT</text>
    <line x1="378" y1="257" x2="678" y2="257" stroke="#c9a84c" stroke-width="1.5"/>
    <circle cx="528" cy="257" r="3" fill="#c9a84c"/>
    <text x="528" y="305" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="400" letter-spacing="3" fill="#0a1628" opacity="0.5">PRESENTED TO</text>
    <line x1="278" y1="430" x2="778" y2="430" stroke="#c9a84c" stroke-width="1.5"/>
    <text x="528" y="470" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="300" letter-spacing="2" fill="#0a1628" opacity="0.5">in recognition of their contribution to</text>
    <text x="528" y="512" text-anchor="middle" font-family="Georgia,serif" font-size="26" font-weight="700" fill="#0a1628">Software Quality Assurance Festival 2026</text>
    <text x="528" y="538" text-anchor="middle" font-family="Inter,sans-serif" font-size="12" font-weight="300" fill="#0a1628" opacity="0.5">10–11 April 2026  ·  Nairobi, Kenya</text>
    <line x1="180" y1="640" x2="380" y2="640" stroke="#0a1628" stroke-width="0.8" opacity="0.3"/>
    <text x="280" y="658" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" letter-spacing="1" fill="#0a1628" opacity="0.4">ORGANISER</text>
    <line x1="676" y1="640" x2="876" y2="640" stroke="#0a1628" stroke-width="0.8" opacity="0.3"/>
    <text x="776" y="658" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" letter-spacing="1" fill="#0a1628" opacity="0.4">DATE</text>
    <text x="528" y="708" text-anchor="middle" font-family="Inter,sans-serif" font-size="10" font-weight="300" letter-spacing="4" fill="#c9a84c" opacity="0.7">TEST · BREAK · LEARN · CONNECT</text>
  `
};

function renderDesign(type){document.getElementById('cert-svg').innerHTML=designs[type]();}

function setCertType(type,color){
  document.querySelectorAll('.cert-type-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('btn-'+type).classList.add('active');
  const bg=document.getElementById('cert-bg');
  if(bg.style.display==='block') return;
  renderDesign(type);
  const n=document.getElementById('cert-name');
  n.style.color=color;
  document.getElementById('color').value=color;
  document.getElementById('color-hex').value=color;
}

function updateName(){
  const v=document.getElementById('recipient').value;
  document.getElementById('cert-name').innerText=v||'Recipient Name';
}
function updateFont(){document.getElementById('cert-name').style.fontFamily=document.getElementById('font').value;}
function updateSize(v){document.getElementById('cert-name').style.fontSize=v+'px';document.getElementById('size-display').innerText=v+'px';}
function updateColor(v){document.getElementById('cert-name').style.color=v;document.getElementById('color-hex').value=v;}
function updateColorFromHex(v){if(/^#[0-9a-fA-F]{6}$/.test(v)){document.getElementById('cert-name').style.color=v;document.getElementById('color').value=v;}}

function uploadBg(){
  const file=document.getElementById('upload').files[0];
  if(!file) return;
  const reader=new FileReader();
  reader.onload=e=>{
    const bg=document.getElementById('cert-bg');
    bg.src=e.target.result;bg.style.display='block';
    document.getElementById('cert-svg').style.display='none';
  };
  reader.readAsDataURL(file);
}

// ── Mouse drag ──
const nameEl=document.getElementById('cert-name');
const certEl=document.getElementById('certificate');
let isDragging=false,dragOffX=0,dragOffY=0;

nameEl.addEventListener('mousedown',e=>{
  isDragging=true;
  const rect=nameEl.getBoundingClientRect();
  dragOffX=e.clientX-rect.left;
  dragOffY=e.clientY-rect.top;
  nameEl.style.transform='';
  e.preventDefault();
});
document.addEventListener('mouseup',()=>{isDragging=false;});
document.addEventListener('mousemove',e=>{
  if(!isDragging) return;
  const cr=certEl.getBoundingClientRect();
  const sx=1056/cr.width,sy=748/cr.height;
  nameEl.style.left=((e.clientX-cr.left-dragOffX)*sx)+'px';
  nameEl.style.top=((e.clientY-cr.top-dragOffY)*sy)+'px';
  nameEl.style.transform='';
});

// ── Touch drag ──
nameEl.addEventListener('touchstart',e=>{
  isDragging=true;
  const t=e.touches[0];
  const rect=nameEl.getBoundingClientRect();
  dragOffX=t.clientX-rect.left;
  dragOffY=t.clientY-rect.top;
  nameEl.style.transform='';
  e.preventDefault();
},{passive:false});
document.addEventListener('touchend',()=>{isDragging=false;});
document.addEventListener('touchmove',e=>{
  if(!isDragging) return;
  const t=e.touches[0];
  const cr=certEl.getBoundingClientRect();
  const sx=1056/cr.width,sy=748/cr.height;
  nameEl.style.left=((t.clientX-cr.left-dragOffX)*sx)+'px';
  nameEl.style.top=((t.clientY-cr.top-dragOffY)*sy)+'px';
  nameEl.style.transform='';
  e.preventDefault();
},{passive:false});

// ── Scroll resize ──
nameEl.addEventListener('wheel',e=>{
  e.preventDefault();
  let s=parseInt(window.getComputedStyle(nameEl).fontSize);
  s=e.deltaY<0?Math.min(s+2,100):Math.max(s-2,14);
  nameEl.style.fontSize=s+'px';
  document.getElementById('size-slider').value=s;
  document.getElementById('size-display').innerText=s+'px';
},{passive:false});

// ── Download ──
function downloadPNG(){
  const scaler=document.getElementById('cert-scaler');
  const prev=scaler.style.transform;
  scaler.style.transform='scale(1)';
  scaler.style.marginBottom='0';
  scaler.style.marginLeft='0';
  scaler.style.marginRight='0';
  html2canvas(certEl,{scale:3,useCORS:true,logging:false}).then(canvas=>{
    const link=document.createElement('a');
    const n=document.getElementById('recipient').value||'certificate';
    link.download=`SQA-Festival-2026-${n.replace(/\s+/g,'-')}.png`;
    link.href=canvas.toDataURL('image/png');
    link.click();
    scaler.style.transform=prev;
    setTimeout(scaleCert,100);
  });
}

// ── Init ──
renderDesign('attendance');
document.getElementById('cert-name').style.color='#ffffff';
updateName();
</script>
</body>
</html>