<?php
// 自动识别当前域名并拼接跳转链接
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$login_url = "https://{$domain}/admin/login.php";
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Lilith Storage · 文件管理系统</title>
<style>
:root{
  --bg:#080b18;
  --txt:#eaf0ff;
  --muted:#b9c5ff;
  --stroke:rgba(98,117,255,.25);
  --accent:#7aa2ff;
  --accent-2:#a38bff;
  --shadow:0 10px 40px rgba(0,0,0,.45);
  --radius:20px;
}
.theme-nebula{
  --bg:#0b0d1b;
  --txt:#f0f6ff;
  --muted:#c7d2ff;
  --stroke:rgba(163,139,255,.28);
  --accent:#9bc7ff;
  --accent-2:#c09bff;
}
*{box-sizing:border-box}
html,body{min-height:100%;margin:0;padding:0;background:transparent;color:var(--txt);font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial;}
body::before{
  content:"";
  position:fixed;z-index:-2;inset:0;
  background:
    radial-gradient(circle at 15% 20%, rgba(122,162,255,.22), transparent 70%),
    radial-gradient(circle at 85% 25%, rgba(163,139,255,.20), transparent 70%),
    radial-gradient(circle at 50% 90%, rgba(119,230,182,.16), transparent 80%),
    var(--bg);
  background-attachment:fixed;
}
.aurora{
  position:absolute;z-index:-1;pointer-events:none;left:0;right:0;top:0;bottom:0;overflow:hidden;
}
.aurora::before,.aurora::after{
  content:"";position:absolute;width:120%;height:160%;top:-20%;left:-10%;
  filter:blur(85px);
  background:
    radial-gradient(closest-side at 28% 32%, rgba(122,162,255,.18), transparent 60%),
    radial-gradient(closest-side at 68% 42%, rgba(163,139,255,.16), transparent 60%),
    radial-gradient(closest-side at 50% 75%, rgba(119,230,182,.12), transparent 60%);
  animation:float 26s ease-in-out infinite;
}
.aurora::after{animation-duration:32s;mix-blend-mode:screen;opacity:.85;transform:rotate(10deg)}
@keyframes float{0%{transform:translate(0,0) scale(1)}50%{transform:translate(4%,3%) scale(1.04)}100%{transform:translate(0,0) scale(1)}}
.container{
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  min-height:100vh;text-align:center;padding:20px;
}
.card{
  background:linear-gradient(180deg,rgba(20,26,60,.6),rgba(17,22,52,.45));
  border:1px solid var(--stroke);border-radius:var(--radius);
  box-shadow:var(--shadow);
  max-width:600px;padding:32px 24px;margin:0 auto;
}
h1{margin:0 0 12px;font-size:28px;font-weight:900;letter-spacing:.5px}
p{margin:0 0 20px;color:var(--muted);line-height:1.6}
.btn{
  appearance:none;display:inline-block;padding:14px 28px;
  background:linear-gradient(180deg,rgba(122,162,255,.35),rgba(163,139,255,.28));
  border:1px solid rgba(122,162,255,.6);
  border-radius:12px;color:var(--txt);font-weight:700;font-size:16px;text-decoration:none;
  box-shadow:0 8px 24px rgba(122,162,255,.25);
  transition:transform .16s ease,box-shadow .2s ease,filter .2s ease;
}
.btn:hover{filter:brightness(1.06);transform:translateY(-1px);box-shadow:0 12px 30px rgba(0,0,0,.28)}
footer{margin-top:30px;font-size:13px;color:var(--muted);}

/* ====== 主题切换开关按钮 ====== */
.theme-switch{
  position:fixed;top:20px;right:20px;z-index:20;
  width:54px;height:54px;border-radius:50%;
  background:linear-gradient(135deg,var(--accent),var(--accent-2));
  border:1px solid var(--stroke);
  box-shadow:0 0 25px rgba(122,162,255,.45),inset 0 0 15px rgba(255,255,255,.1);
  cursor:pointer;display:flex;align-items:center;justify-content:center;
  transition:transform .2s ease,box-shadow .3s ease,filter .2s ease;
}
.theme-switch:hover{transform:rotate(15deg) scale(1.05);filter:brightness(1.2);}
.theme-switch span{
  font-size:22px;user-select:none;
  text-shadow:0 0 8px rgba(255,255,255,.6);
  transition:opacity .3s ease;
}
.theme-nebula .theme-switch{background:linear-gradient(135deg,var(--accent-2),#ffd166);box-shadow:0 0 25px rgba(255,209,102,.45);}
.theme-nebula .theme-switch span::before{content:"🌠";}
.theme-switch span::before{content:"🌌";}
</style>
<script>
(function(){
  const THEME_KEY='dream_theme';
  function applyTheme(t){
    document.documentElement.classList.toggle('theme-nebula', t==='nebula');
    localStorage.setItem(THEME_KEY, t);
  }
  window.toggleTheme=function(){
    const cur=localStorage.getItem(THEME_KEY)||'aurora';
    applyTheme(cur==='aurora'?'nebula':'aurora');
  };
  document.addEventListener('DOMContentLoaded',()=>applyTheme(localStorage.getItem(THEME_KEY)||'aurora'));
})();
</script>
</head>
<body>
<div class="aurora"></div>

<!-- 固定右上角主题切换开关 -->
<div class="theme-switch" onclick="toggleTheme()" title="切换主题">
  <span></span>
</div>

<div class="container">
  <div class="card">
    <h1>🌌 Lilith Storage</h1>
    <p>一个轻量、安全、梦幻风格的文件管理系统。<br>
    支持文件上传、隐匿链接生成与下载统计。<br>
    后台采用玻璃拟态设计与极光动画视觉效果。</p>
    <a class="btn" href="<?=$login_url?>">进入后台</a>
  </div>
  <footer>© <?=date('Y')?> Lilith Storage · All Rights Reserved.</footer>
</div>
</body>
</html>
