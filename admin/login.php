<?php
require __DIR__ . '/../lib/common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) { die('CSRF invalid'); }
    $user = trim($_POST['user'] ?? '');
    $pass = trim($_POST['pass'] ?? '');

    if (check_login_credential($user, $pass)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user;
        header('Location: /admin/index.php'); exit;
    } else {
        $err = '账号或密码错误';
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>管理员登录 · Dream Pro</title>
<style>
:root{
  --bg:#080b18;
  --txt:#eaf0ff;
  --muted:#b9c5ff;
  --stroke:rgba(98,117,255,.25);
  --accent:#7aa2ff;
  --accent-2:#a38bff;
  --danger:#ff5d7a;
  --shadow:0 10px 40px rgba(0,0,0,.45);
  --radius:20px;
  --glass-1:linear-gradient(180deg,rgba(20,26,60,.6),rgba(17,22,52,.45));
  --blur-desktop:85px;
  --blur-mobile:60px;
}
.theme-nebula{
  --bg:#0b0d1b;
  --txt:#f0f6ff;
  --muted:#c7d2ff;
  --stroke:rgba(163,139,255,.28);
  --accent:#9bc7ff;
  --accent-2:#c09bff;
  --glass-1:linear-gradient(180deg,rgba(26,21,54,.55),rgba(19,16,46,.45));
}

/* 基础与背景 */
*{box-sizing:border-box}
html,body{min-height:100%;height:auto}
body{
  margin:0;display:flex;align-items:center;justify-content:center;
  background:transparent;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial;
  color:var(--txt);padding:max(24px,env(safe-area-inset-top)) 16px max(24px,env(safe-area-inset-bottom));
}
body::before{
  content:"";position:fixed;z-index:-2;inset:0;
  background:
    radial-gradient(circle at 15% 20%,rgba(122,162,255,.22),transparent 70%),
    radial-gradient(circle at 85% 25%,rgba(163,139,255,.20),transparent 70%),
    radial-gradient(circle at 50% 90%,rgba(119,230,182,.16),transparent 80%),
    var(--bg);
  background-attachment:fixed;
}
.aurora{
  position:absolute;z-index:-1;pointer-events:none;left:0;right:0;top:0;bottom:0;overflow:hidden;
}
.aurora::before,.aurora::after{
  content:"";position:absolute;width:120%;height:160%;top:-20%;left:-10%;
  filter:blur(var(--blur-desktop));
  background:
    radial-gradient(closest-side at 28% 32%,rgba(122,162,255,.18),transparent 60%),
    radial-gradient(closest-side at 68% 42%,rgba(163,139,255,.16),transparent 60%),
    radial-gradient(closest-side at 50% 75%,rgba(119,230,182,.12),transparent 60%);
  animation:float 26s ease-in-out infinite;
}
.theme-nebula .aurora::before,.theme-nebula .aurora::after{
  background:
    radial-gradient(closest-side at 32% 35%,rgba(155,199,255,.18),transparent 60%),
    radial-gradient(closest-side at 66% 45%,rgba(192,155,255,.16),transparent 60%),
    radial-gradient(closest-side at 46% 72%,rgba(255,209,102,.12),transparent 60%);
}
.aurora::after{animation-duration:32s;mix-blend-mode:screen;opacity:.85;transform:rotate(10deg)}
@keyframes float{0%{transform:translate(0,0) scale(1)}50%{transform:translate(4%,3%) scale(1.04)}100%{transform:translate(0,0) scale(1)}}
@media (max-width:768px){.aurora::before,.aurora::after{filter:blur(var(--blur-mobile));}}

/* 登录卡片 */
.card{
  width:min(360px,100%);
  background:var(--glass-1);
  border:1px solid var(--stroke);
  border-radius:var(--radius);
  padding:22px 18px 18px;
  box-shadow:var(--shadow);
  position:relative;
}
.logo{
  width:42px;height:42px;border-radius:12px;margin:0 auto 10px;
  background:linear-gradient(135deg,var(--accent),var(--accent-2));
  box-shadow:0 0 28px rgba(122,162,255,.45);
}
h1{text-align:center;margin:4px 0 14px;font-size:20px;font-weight:900}

/* 输入与按钮 */
label{display:block;margin:8px 0 6px}
input[type=text],input[type=password]{
  width:100%;padding:12px;border-radius:12px;border:1px solid var(--stroke);
  background:linear-gradient(180deg,rgba(12,16,40,.9),rgba(10,14,36,.9));
  color:var(--txt);outline:none;transition:border-color .2s ease,box-shadow .2s ease;
}
input[type=text]:focus,input[type=password]:focus{
  border-color:rgba(122,162,255,.75);
  box-shadow:0 0 0 6px rgba(122,162,255,.12);
}
button[type=submit]{
  appearance:none;border:1px solid rgba(122,162,255,.6);
  background:linear-gradient(180deg,rgba(122,162,255,.35),rgba(163,139,255,.28));
  color:var(--txt);width:100%;padding:12px;border-radius:12px;font-weight:700;cursor:pointer;
  box-shadow:0 8px 24px rgba(122,162,255,.25);
  transition:transform .16s ease,box-shadow .2s ease,filter .2s ease;
}
button[type=submit]:hover{filter:brightness(1.06);transform:translateY(-1px);box-shadow:0 12px 30px rgba(0,0,0,.28)}
button[type=submit]:active{transform:translateY(0) scale(.99)}

/* 错误提示 */
.err{
  color:#ff9aa2;margin:8px 0 0;text-align:center;font-weight:700;
  background:rgba(255,154,162,.12);border:1px solid rgba(255,154,162,.35);
  border-radius:10px;padding:8px;
}

/* Toast */
.toast{
  position:fixed;left:50%;bottom:max(20px,env(safe-area-inset-bottom));
  transform:translateX(-50%) translateY(20px);
  background:rgba(16,22,54,.9);backdrop-filter:blur(10px);
  color:var(--txt);border:1px solid var(--stroke);
  padding:10px 14px;border-radius:12px;box-shadow:var(--shadow);
  opacity:0;pointer-events:none;transition:opacity .2s,transform .2s;z-index:50;
}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

/* 右上角主题开关 */
.theme-switch{
  position:fixed;top:20px;right:20px;z-index:100;
  width:54px;height:54px;border-radius:50%;
  background:linear-gradient(135deg,var(--accent),var(--accent-2));
  border:1px solid var(--stroke);
  box-shadow:0 0 25px rgba(122,162,255,.45),inset 0 0 15px rgba(255,255,255,.1);
  cursor:pointer;display:flex;align-items:center;justify-content:center;
  transition:transform .2s,box-shadow .3s,filter .2s;
}
.theme-switch:hover{transform:rotate(15deg) scale(1.05);filter:brightness(1.2);}
.theme-switch span{font-size:22px;text-shadow:0 0 8px rgba(255,255,255,.6);}
.theme-switch span::before{content:"🌌";}
.theme-nebula .theme-switch{background:linear-gradient(135deg,var(--accent-2),#ffd166);box-shadow:0 0 25px rgba(255,209,102,.45);}
.theme-nebula .theme-switch span::before{content:"🌠";}
</style>
<script>
(function(){
  const THEME_KEY='dream_theme';
  function applyTheme(t){
    document.documentElement.classList.toggle('theme-nebula',t==='nebula');
    localStorage.setItem(THEME_KEY,t);
  }
  window.toggleTheme=function(){
    const cur=localStorage.getItem(THEME_KEY)||'aurora';
    applyTheme(cur==='aurora'?'nebula':'aurora');
  };
  document.addEventListener('DOMContentLoaded',()=>{
    applyTheme(localStorage.getItem(THEME_KEY)||'aurora');
    const err=<?=json_encode($err??null,JSON_UNESCAPED_UNICODE)?>;
    if(err)showToast(err);
  });
  window.showToast=function(msg){
    let el=document.querySelector('.toast');
    if(!el){el=document.createElement('div');el.className='toast';document.body.appendChild(el);}
    el.textContent=msg;el.classList.add('show');
    setTimeout(()=>el.classList.remove('show'),1800);
  };
})();
</script>
</head>
<body>
<div class="aurora"></div>

<!-- 固定右上角主题切换开关 -->
<div class="theme-switch" onclick="toggleTheme()" title="切换主题"><span></span></div>

<div class="card" role="form" aria-label="管理员登录">
  <div class="logo"></div>
  <h1>管理员登录</h1>

  <?php if(!empty($err)):?><div class="err"><?=e($err)?></div><?php endif;?>

  <form method="post" style="margin-top:10px">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <label>账号</label>
    <input type="text" name="user" autocomplete="username" required>
    <label>密码</label>
    <input type="password" name="pass" autocomplete="current-password" required>
    <div style="margin-top:12px"><button type="submit">登录</button></div>
  </form>
</div>

<div class="toast" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
