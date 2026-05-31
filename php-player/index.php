<?php
require_once __DIR__ . '/config.php';
$preUrl = isset($_GET['url']) ? htmlspecialchars(urldecode($_GET['url'])) : '';
$preKey = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : DEFAULT_KEY;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title><?= htmlspecialchars(PLAYER_TITLE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Rajdhani:wght@300;400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
:root{
  --bg0:#010508;--bg1:#060d16;--bg2:#0b1624;--bg3:#101e30;
  --gold:#c8a84b;--gold2:#e8c66a;--gold3:#7a6530;
  --cyan:#00d4ff;--cyan2:#00aacc;--cyan3:#004455;
  --red:#ff453a;--grn:#30d158;--ylw:#ffd60a;
  --t1:#e8f0fe;--t2:#8fa8c0;--t3:#405060;
  --bdr:rgba(200,168,75,.18);--bdrc:rgba(0,212,255,.14);
  --r:10px;--r2:6px;
  --fh:'Orbitron',monospace;--fb:'Rajdhani',sans-serif;--fm:'Share Tech Mono',monospace;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{font-size:16px;scroll-behavior:smooth}
body{
  font-family:var(--fb);background:var(--bg0);color:var(--t1);
  min-height:100vh;overflow-x:hidden;
}
/* === BG EFFECTS === */
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background:
    radial-gradient(ellipse 90% 60% at 50% -5%,rgba(0,212,255,.05) 0%,transparent 55%),
    radial-gradient(ellipse 70% 50% at 90% 100%,rgba(200,168,75,.04) 0%,transparent 50%),
    radial-gradient(ellipse 50% 40% at 10% 60%,rgba(0,212,255,.03) 0%,transparent 50%);
}
body::after{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:
    linear-gradient(rgba(0,212,255,.025) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,212,255,.025) 1px,transparent 1px);
  background-size:56px 56px;
}
/* === LAYOUT === */
.wrap{position:relative;z-index:1;max-width:1140px;margin:0 auto;padding:0 18px 56px}
/* === HEADER === */
.hdr{
  display:flex;align-items:center;justify-content:space-between;
  padding:22px 0 18px;border-bottom:1px solid var(--bdr);margin-bottom:28px;
  flex-wrap:wrap;gap:12px;
}
.hdr-logo{display:flex;align-items:center;gap:14px}
.logo-box{
  width:46px;height:46px;border:2px solid var(--gold);border-radius:9px;
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 0 18px rgba(200,168,75,.2);background:rgba(200,168,75,.05);
  font-size:20px;
}
.logo-name{font-family:var(--fh);font-size:20px;font-weight:900;letter-spacing:4px;
  color:var(--gold);text-shadow:0 0 16px rgba(200,168,75,.4);line-height:1.1}
.logo-sub{font-size:11px;letter-spacing:3px;color:var(--t2);font-weight:300;margin-top:2px}
.hdr-status{display:flex;align-items:center;gap:8px;font-family:var(--fm);font-size:12px;color:var(--t2)}
.dot{width:8px;height:8px;border-radius:50%;background:var(--t3);box-shadow:0 0 6px var(--t3);animation:blink 2s infinite}
.dot.ok{background:var(--grn);box-shadow:0 0 8px var(--grn)}
.dot.err{background:var(--red);box-shadow:0 0 8px var(--red)}
.dot.chk{background:var(--ylw);box-shadow:0 0 8px var(--ylw)}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
/* === CARDS === */
.grid4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
@media(max-width:900px){.grid4{grid-template-columns:repeat(2,1fr)}}
@media(max-width:500px){.grid4{grid-template-columns:1fr 1fr}}
.card{
  background:var(--bg1);border:1px solid var(--bdr);border-radius:var(--r);
  padding:14px 16px;transition:border-color .2s;
}
.card:hover{border-color:rgba(200,168,75,.35)}
.card-lbl{font-family:var(--fm);font-size:10px;color:var(--t3);letter-spacing:2px;text-transform:uppercase;margin-bottom:5px}
.card-val{font-family:var(--fm);font-size:12px;color:var(--t1);word-break:break-all;line-height:1.4}
.card-val.g{color:var(--gold)}.card-val.c{color:var(--cyan)}.card-val.gr{color:var(--grn)}
/* === INPUT PANEL === */
.panel{background:var(--bg1);border:1px solid var(--bdr);border-radius:var(--r);overflow:hidden;margin-bottom:20px}
.panel-hdr{
  display:flex;align-items:center;gap:10px;padding:13px 18px;
  background:rgba(200,168,75,.04);border-bottom:1px solid var(--bdr);
  font-family:var(--fh);font-size:10px;letter-spacing:3px;color:var(--gold);font-weight:700;
}
.panel-body{padding:18px}
/* type tabs */
.tabs{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.tab{
  background:var(--bg2);border:1px solid var(--bdr);color:var(--t2);
  font-family:var(--fh);font-size:9px;letter-spacing:2px;
  padding:8px 18px;border-radius:var(--r2);cursor:pointer;transition:all .2s;
}
.tab.on{border-color:var(--gold);color:var(--gold);background:rgba(200,168,75,.07);box-shadow:0 0 12px rgba(200,168,75,.1)}
.tab:hover:not(.on){border-color:var(--t2);color:var(--t1)}
/* inputs */
.row{display:grid;grid-template-columns:1fr auto;gap:12px;margin-bottom:14px;align-items:end}
@media(max-width:580px){.row{grid-template-columns:1fr}}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:4px}
@media(max-width:480px){.row2{grid-template-columns:1fr}}
.fg{display:flex;flex-direction:column;gap:5px}
.lbl{font-family:var(--fm);font-size:10px;color:var(--t2);letter-spacing:1px;text-transform:uppercase}
.inp{
  background:var(--bg2);border:1px solid var(--bdrc);color:var(--t1);
  font-family:var(--fm);font-size:13px;padding:10px 13px;border-radius:var(--r2);
  outline:none;width:100%;transition:border-color .2s,box-shadow .2s;
}
.inp::placeholder{color:var(--t3)}.inp:focus{border-color:var(--cyan);box-shadow:0 0 0 2px rgba(0,212,255,.1)}
.inp.g{border-color:var(--bdr);color:var(--gold)}.inp.g:focus{border-color:var(--gold);box-shadow:0 0 0 2px rgba(200,168,75,.1)}
/* load button */
.btn-load{
  background:linear-gradient(135deg,var(--gold) 0%,#9a7228 100%);
  color:#000;border:none;font-family:var(--fh);font-size:10px;
  letter-spacing:3px;font-weight:700;padding:11px 22px;border-radius:var(--r2);
  cursor:pointer;transition:all .2s;white-space:nowrap;
  box-shadow:0 3px 18px rgba(200,168,75,.25);
}
.btn-load:hover{transform:translateY(-1px);box-shadow:0 5px 28px rgba(200,168,75,.45)}
.btn-load:active{transform:translateY(0)}
/* history */
.hist{border-top:1px solid var(--bdr);padding-top:14px;margin-top:10px}
.hist-lbl{font-family:var(--fm);font-size:10px;color:var(--t3);letter-spacing:1px;margin-bottom:8px}
.hist-list{display:flex;flex-wrap:wrap;gap:7px}
.hist-item{
  background:var(--bg2);border:1px solid var(--bdrc);color:var(--cyan);
  font-family:var(--fm);font-size:11px;padding:5px 12px;border-radius:20px;
  cursor:pointer;transition:all .15s;max-width:260px;
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.hist-item:hover{background:rgba(0,212,255,.07);box-shadow:0 0 8px rgba(0,212,255,.12)}
/* === VIDEO PLAYER === */
.vplayer{background:var(--bg1);border:1px solid var(--bdr);border-radius:var(--r);overflow:hidden;margin-bottom:20px;box-shadow:0 4px 36px rgba(0,0,0,.55)}
.vplayer-hdr{display:flex;align-items:center;justify-content:space-between;padding:12px 18px;background:rgba(200,168,75,.04);border-bottom:1px solid var(--bdr);gap:10px;flex-wrap:wrap}
.vplayer-hdr-l{display:flex;align-items:center;gap:8px;font-family:var(--fh);font-size:10px;letter-spacing:3px;color:var(--gold);font-weight:700}
.vplayer-hdr-l::before{content:'';width:3px;height:15px;background:var(--gold);border-radius:2px;box-shadow:0 0 6px var(--gold)}
.vnow{font-family:var(--fm);font-size:12px;color:var(--cyan);max-width:380px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
@media(max-width:600px){.vnow{display:none}}
/* video wrap */
.vwrap{position:relative;background:#000;aspect-ratio:16/9}
.vwrap::before{
  content:'NO CONTENT LOADED';position:absolute;inset:0;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fh);font-size:12px;letter-spacing:4px;
  color:var(--t3);pointer-events:none;z-index:1;
}
.vwrap.loaded::before{display:none}
#vid{width:100%;height:100%;display:block;background:#000;position:relative;z-index:2}
/* loading overlay */
.voverlay{
  position:absolute;inset:0;background:rgba(0,0,0,.75);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:14px;z-index:5;opacity:0;pointer-events:none;transition:opacity .25s;
}
.voverlay.show{opacity:1;pointer-events:all}
.spinner{width:38px;height:38px;border:2px solid rgba(255,255,255,.1);border-top-color:var(--gold);border-right-color:var(--cyan);border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.spin-txt{font-family:var(--fh);font-size:10px;letter-spacing:4px;color:var(--gold)}
/* controls bar */
.cbar{display:flex;align-items:center;gap:10px;padding:11px 18px;background:var(--bg2);border-top:1px solid var(--bdrc);flex-wrap:wrap}
.cb{
  background:transparent;border:1px solid var(--bdrc);color:var(--cyan);
  font-family:var(--fh);font-size:8px;letter-spacing:2px;
  padding:7px 13px;border-radius:4px;cursor:pointer;transition:all .2s;white-space:nowrap;
}
.cb:hover{background:rgba(0,212,255,.1);border-color:var(--cyan);box-shadow:0 0 10px rgba(0,212,255,.15)}
.vsel{
  background:var(--bg3);border:1px solid var(--bdrc);color:var(--cyan);
  font-family:var(--fm);font-size:12px;padding:6px 10px;border-radius:4px;cursor:pointer;outline:none;
}
.vsel option{background:var(--bg2)}
.vsel.g{border-color:var(--bdr);color:var(--gold)}
.vsel.g option{background:var(--bg2)}
.vol-wrap{display:flex;align-items:center;gap:7px;flex:1;min-width:100px}
.vol-lbl{font-family:var(--fm);font-size:10px;color:var(--t2);white-space:nowrap}
input[type=range].vol{
  flex:1;-webkit-appearance:none;height:3px;background:var(--bdrc);border-radius:2px;outline:none;cursor:pointer;
}
input[type=range].vol::-webkit-slider-thumb{-webkit-appearance:none;width:13px;height:13px;border-radius:50%;background:var(--cyan);box-shadow:0 0 5px var(--cyan);cursor:pointer}
.timedsp{font-family:var(--fm);font-size:11px;color:var(--t2);margin-left:auto;white-space:nowrap}
/* progress bar */
.prog-wrap{position:relative;height:4px;background:var(--bdrc);cursor:pointer;overflow:hidden}
.prog-bar{height:100%;background:linear-gradient(90deg,var(--cyan),var(--gold));width:0%;pointer-events:none;transition:width .1s linear}
/* === LOG === */
.logwrap{background:var(--bg1);border:1px solid var(--bdr);border-radius:var(--r);overflow:hidden;margin-bottom:20px}
.logbody{padding:12px 18px;font-family:var(--fm);font-size:12px;min-height:72px;max-height:140px;overflow-y:auto;line-height:1.9}
.logbody::-webkit-scrollbar{width:3px}.logbody::-webkit-scrollbar-track{background:transparent}.logbody::-webkit-scrollbar-thumb{background:var(--bdr);border-radius:2px}
.ll{display:flex;gap:10px}.lt{color:var(--t3);min-width:66px}
.ok{color:var(--grn)}.er{color:var(--red)}.in{color:var(--cyan)}.wn{color:var(--gold)}
/* === TOAST === */
.toasts{position:fixed;top:18px;right:18px;z-index:9999;display:flex;flex-direction:column;gap:7px;pointer-events:none}
.toast{
  background:var(--bg2);border:1px solid var(--bdr);border-radius:var(--r2);
  padding:10px 16px;font-family:var(--fm);font-size:12px;color:var(--t1);
  box-shadow:0 4px 20px rgba(0,0,0,.5);animation:tin .3s ease;max-width:300px;pointer-events:all;
}
.toast.ok{border-color:var(--grn);color:var(--grn)}.toast.er{border-color:var(--red);color:var(--red)}.toast.in{border-color:var(--cyan);color:var(--cyan)}
@keyframes tin{from{transform:translateX(110%);opacity:0}to{transform:translateX(0);opacity:1}}
/* === FOOTER === */
.ftr{border-top:1px solid var(--bdr);padding-top:18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
.ftr-brand{font-family:var(--fh);font-size:10px;letter-spacing:3px;color:var(--t3)}.ftr-brand span{color:var(--gold)}
.ftr-links{display:flex;gap:14px}
.ftr-lnk{font-family:var(--fm);font-size:11px;color:var(--t3);cursor:pointer;transition:color .2s}.ftr-lnk:hover{color:var(--cyan)}
/* === SCROLLBAR === */
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:var(--bg0)}
::-webkit-scrollbar-thumb{background:var(--bdr);border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:var(--gold3)}
</style>
</head>
<body>
<div class="toasts" id="tc"></div>
<div class="wrap">

<!-- HEADER -->
<header class="hdr">
  <div class="hdr-logo">
    <div class="logo-box">▶</div>
    <div>
      <div class="logo-name"><?= htmlspecialchars(APP_NAME) ?></div>
      <div class="logo-sub">SECURE MEDIA PLAYER v<?= APP_VERSION ?></div>
    </div>
  </div>
  <div class="hdr-status">
    <div class="dot chk" id="sDot"></div>
    <span id="sTxt">CHECKING SERVER...</span>
  </div>
</header>

<!-- INFO CARDS -->
<div class="grid4">
  <div class="card"><div class="card-lbl">RENDER SERVER</div><div class="card-val g"><?= htmlspecialchars(PROXY_SERVER) ?></div></div>
  <div class="card"><div class="card-lbl">DEFAULT KEY</div><div class="card-val c"><?= htmlspecialchars(DEFAULT_KEY) ?></div></div>
  <div class="card"><div class="card-lbl">PHP PROXY</div><div class="card-val">proxy.php</div></div>
  <div class="card"><div class="card-lbl">PHP VERSION</div><div class="card-val gr"><?= PHP_VERSION ?></div></div>
</div>

<!-- INPUT PANEL -->
<div class="panel">
  <div class="panel-hdr">⚡ LOAD CONTENT</div>
  <div class="panel-body">
    <div class="tabs">
      <div class="tab on" id="tVid" onclick="setType('video')">▶ VIDEO</div>
      <div class="tab" id="tPdf" onclick="setType('pdf')">📄 PDF</div>
    </div>
    <div class="row">
      <div class="fg" style="flex:1">
        <label class="lbl">ENCRYPTED URL</label>
        <input type="url" class="inp" id="urlInp" placeholder="Paste AppX encrypted URL here..." value="<?= $preUrl ?>">
      </div>
      <button class="btn-load" onclick="loadContent()">LOAD →</button>
    </div>
    <div class="row2">
      <div class="fg">
        <label class="lbl">DECRYPTION KEY</label>
        <input type="text" class="inp g" id="keyInp" placeholder="appx-pdf-keyset" value="<?= $preKey ?>">
      </div>
      <div class="fg">
        <label class="lbl">QUALITY HINT</label>
        <select class="inp" id="qualInp" style="color:var(--cyan)">
          <option value="">— AUTO —</option>
          <option value="720">720p HD</option>
          <option value="480">480p SD</option>
          <option value="360">360p LQ</option>
          <option value="1080">1080p FHD</option>
        </select>
      </div>
    </div>
    <div class="hist" id="histSec" style="display:none">
      <div class="hist-lbl">RECENT URLS ▾</div>
      <div class="hist-list" id="histList"></div>
    </div>
  </div>
</div>

<!-- VIDEO PLAYER -->
<div class="vplayer">
  <div class="vplayer-hdr">
    <div class="vplayer-hdr-l">VIDEO PLAYER</div>
    <div class="vnow" id="vnow">No content loaded</div>
  </div>
  <!-- Progress bar -->
  <div class="prog-wrap" id="progWrap" onclick="seekClick(event)">
    <div class="prog-bar" id="progBar"></div>
  </div>
  <!-- Video -->
  <div class="vwrap" id="vwrap">
    <div class="voverlay" id="vover">
      <div class="spinner"></div>
      <div class="spin-txt">LOADING STREAM</div>
    </div>
    <video id="vid" controls playsinline preload="none"></video>
  </div>
  <!-- Controls -->
  <div class="cbar">
    <button class="cb" onclick="v.play()">▶ PLAY</button>
    <button class="cb" onclick="v.pause()">⏸ PAUSE</button>
    <button class="cb" onclick="sk(-10)">⏪ -10s</button>
    <button class="cb" onclick="sk(10)">+10s ⏩</button>
    <button class="cb" onclick="goFull()">⛶ FULL</button>
    <button class="cb" onclick="goPip()">⊡ PIP</button>
    <div class="vol-wrap">
      <span class="vol-lbl">VOL</span>
      <input type="range" class="vol" id="volR" min="0" max="1" step="0.05" value="1" oninput="v.volume=this.value">
    </div>
    <select class="vsel g" onchange="v.playbackRate=parseFloat(this.value)">
      <option value="0.5">0.5×</option><option value="0.75">0.75×</option>
      <option value="1" selected>1×</option><option value="1.25">1.25×</option>
      <option value="1.5">1.5×</option><option value="2">2×</option>
    </select>
    <div class="timedsp" id="tdsp">0:00 / 0:00</div>
  </div>
</div>

<!-- LOG -->
<div class="logwrap">
  <div class="panel-hdr">
    📋 ACTIVITY LOG
    <button class="cb" style="margin-left:auto;font-size:8px;padding:4px 10px" onclick="clearLog()">CLEAR</button>
  </div>
  <div class="logbody" id="lg"></div>
</div>

<!-- FOOTER -->
<footer class="ftr">
  <div class="ftr-brand"><span><?= htmlspecialchars(APP_NAME) ?></span> · SECURE MEDIA PLAYER</div>
  <div class="ftr-links">
    <div class="ftr-lnk" onclick="checkServer()">CHECK SERVER</div>
    <div class="ftr-lnk" onclick="clearHist()">CLEAR HISTORY</div>
    <div class="ftr-lnk" onclick="window.open('<?= htmlspecialchars(PROXY_SERVER) ?>/health','_blank')">NODE HEALTH</div>
  </div>
</footer>
</div>

<script>
const RENDER = '<?= PROXY_SERVER ?>';
const DEFKEY = '<?= DEFAULT_KEY ?>';
const v      = document.getElementById('vid');

let curType = 'video';
let hist    = [];
try { hist = JSON.parse(localStorage.getItem('su_hist') || '[]'); } catch(e){}

// ---- init ----
renderHist();
checkServer();
<?php if($preUrl): ?>window.addEventListener('load',()=>setTimeout(loadContent,400));<?php endif; ?>

// ---- type toggle ----
function setType(t){
  curType = t;
  document.getElementById('tVid').classList.toggle('on', t==='video');
  document.getElementById('tPdf').classList.toggle('on', t==='pdf');
  lg_('Switched to '+t.toUpperCase(), 'in');
}

// ---- load ----
function loadContent(){
  const url = document.getElementById('urlInp').value.trim();
  const key = document.getElementById('keyInp').value.trim() || DEFKEY;
  if(!url){ toast('Please enter a URL','er'); return; }
  const px = 'proxy.php?type='+curType+'&url='+encodeURIComponent(url)+'&key='+encodeURIComponent(key);
  if(curType==='video') doVideo(px, url);
  else doPdf(px, url);
  saveHist(url, key, curType);
}

// ---- video ----
function doVideo(src, raw){
  showOver(true);
  document.getElementById('vwrap').classList.remove('loaded');
  v.pause();
  v.removeAttribute('src');
  v.load();
  v.src = src;
  v.load();
  lg_('Loading video...','in');
  v.onloadeddata = ()=>{
    showOver(false);
    document.getElementById('vwrap').classList.add('loaded');
    document.getElementById('vnow').textContent = shortUrl(raw);
    lg_('Video ready ✓','ok');
    toast('Video loaded!','ok');
    v.play().catch(()=>{});
  };
  v.onerror = ()=>{
    showOver(false);
    const codes={1:'ABORTED',2:'NETWORK',3:'DECODE',4:'NOT_SUPPORTED'};
    const c = v.error ? (codes[v.error.code]||'UNKNOWN') : 'UNKNOWN';
    lg_('Video error: '+c,'er');
    toast('Error: '+c,'er');
  };
  v.onwaiting = ()=>showOver(true);
  v.oncanplay = ()=>showOver(false);
  v.onplaying = ()=>showOver(false);
}

// ---- pdf ----
function doPdf(src, raw){
  lg_('Opening PDF in new tab...','in');
  toast('Opening PDF...','in');
  window.open(src, '_blank');
}

// ---- progress bar ----
v.addEventListener('timeupdate',()=>{
  const pct = v.duration ? (v.currentTime/v.duration)*100 : 0;
  document.getElementById('progBar').style.width = pct+'%';
  document.getElementById('tdsp').textContent = fmt(v.currentTime)+' / '+fmt(v.duration||0);
});

function seekClick(e){
  if(!v.duration) return;
  const r = e.currentTarget.getBoundingClientRect();
  v.currentTime = ((e.clientX - r.left)/r.width)*v.duration;
}

function fmt(s){
  if(!s||isNaN(s)) return '0:00';
  const m=Math.floor(s/60), sec=Math.floor(s%60);
  return m+':'+(sec<10?'0':'')+sec;
}

// ---- controls ----
function sk(s){ v.currentTime=Math.max(0,v.currentTime+s); }
function goFull(){
  if(!document.fullscreenElement) document.getElementById('vwrap').requestFullscreen().catch(()=>v.requestFullscreen().catch(()=>{}));
  else document.exitFullscreen();
}
async function goPip(){
  if(document.pictureInPictureElement) await document.exitPictureInPicture();
  else { try{ await v.requestPictureInPicture(); } catch(e){ toast('PiP not supported','er'); } }
}
function showOver(b){ document.getElementById('vover').classList.toggle('show',b); }

// ---- server health ----
function checkServer(){
  const dot=document.getElementById('sDot'), txt=document.getElementById('sTxt');
  dot.className='dot chk'; txt.textContent='CHECKING...';
  fetch('proxy.php?type=health',{signal:AbortSignal.timeout(10000)})
    .then(r=>r.json()).then(d=>{
      if(d.node_server==='ok'){
        dot.className='dot ok'; txt.textContent='ALL SYSTEMS ONLINE';
        lg_('Server online — Node: '+RENDER,'ok');
      } else {
        dot.className='dot err'; txt.textContent='NODE SERVER ERROR';
        lg_('Node server error: '+JSON.stringify(d),'er');
      }
    }).catch(e=>{
      dot.className='dot err'; txt.textContent='PHP PROXY ERROR';
      lg_('Health check failed: '+e.message,'er');
    });
}

// ---- history ----
function saveHist(url,key,type){
  hist=[{url,key,type,ts:Date.now()},...hist.filter(h=>h.url!==url)].slice(0,12);
  try{ localStorage.setItem('su_hist',JSON.stringify(hist)); }catch(e){}
  renderHist();
}
function renderHist(){
  const el=document.getElementById('histList'), sec=document.getElementById('histSec');
  el.innerHTML='';
  if(!hist.length){ sec.style.display='none'; return; }
  sec.style.display='block';
  hist.forEach(h=>{
    const d=document.createElement('div');
    d.className='hist-item'; d.title=h.url;
    d.textContent=(h.type==='pdf'?'📄 ':'▶ ')+shortUrl(h.url);
    d.onclick=()=>{ document.getElementById('urlInp').value=h.url; document.getElementById('keyInp').value=h.key; setType(h.type); };
    el.appendChild(d);
  });
}
function clearHist(){
  hist=[]; try{localStorage.removeItem('su_hist');}catch(e){} renderHist();
  lg_('History cleared','in'); toast('History cleared','in');
}

// ---- log ----
function lg_(msg,type='in'){
  const t=new Date().toTimeString().slice(0,8);
  const d=document.createElement('div'); d.className='ll';
  d.innerHTML='<span class="lt">'+t+'</span><span class="'+type+'">'+esc(msg)+'</span>';
  const lg=document.getElementById('lg'); lg.appendChild(d); lg.scrollTop=lg.scrollHeight;
}
function clearLog(){ document.getElementById('lg').innerHTML=''; }

// ---- toast ----
function toast(msg,type='in'){
  const t=document.createElement('div'); t.className='toast '+type; t.textContent=msg;
  document.getElementById('tc').appendChild(t); setTimeout(()=>t.remove(),3500);
}

// ---- helpers ----
function shortUrl(u){ try{ return new URL(u).pathname.split('/').pop()||u; }catch(e){ return u.slice(0,50); } }
function esc(s){ return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// ---- keyboard ----
document.getElementById('urlInp').addEventListener('keydown',e=>{ if(e.key==='Enter') loadContent(); });
document.addEventListener('keydown',e=>{
  if(document.activeElement.tagName==='INPUT') return;
  if(e.key===' '){ e.preventDefault(); v.paused?v.play():v.pause(); }
  if(e.key==='ArrowRight') sk(10);
  if(e.key==='ArrowLeft') sk(-10);
});

// ---- startup log ----
lg_('<?= htmlspecialchars(APP_NAME) ?> Player initialized','ok');
lg_('Node Server: '+RENDER,'in');
lg_('Press Space=Play/Pause, Arrow=±10s','in');
</script>
</body>
</html>
