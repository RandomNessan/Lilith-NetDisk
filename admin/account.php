<?php
require __DIR__ . '/../lib/common.php';
require_login();

$auth = load_auth();
$curr_user = $auth['user'] ?? 'admin';

$msg = $err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) { $err = 'CSRF 校验失败'; }
    else {
        $old_pass = trim($_POST['old_pass'] ?? '');
        $new_user = trim($_POST['new_user'] ?? $curr_user);
        $new_pass = trim($_POST['new_pass'] ?? '');
        $new_pass2= trim($_POST['new_pass2'] ?? '');

        if ($old_pass === '') {
            $err = '请输入当前密码以确认此次修改';
        } elseif (!check_login_credential($curr_user, $old_pass)) {
            $err = '当前密码不正确';
        } else {
            if ($new_user === '') $new_user = $curr_user;
            if (mb_strlen($new_user) < 3) {
                $err = '用户名至少 3 个字符';
            } else {
                if ($new_pass !== '' || $new_pass2 !== '') {
                    if ($new_pass !== $new_pass2) {
                        $err = '两次输入的新密码不一致';
                    } elseif (strlen($new_pass) < 8) {
                        $err = '新密码至少 8 位';
                    }
                }
            }

            if (!$err) {
                $hash = $new_pass !== '' ? password_hash($new_pass, PASSWORD_DEFAULT) : ($auth['pass_hash'] ?? password_hash('admin123', PASSWORD_DEFAULT));
                save_auth($new_user, $hash);
                $_SESSION['admin_user'] = $new_user;
                $msg = '保存成功';
            }
        }
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>账户设置 · Dream Pro</title>
<style>
/* =================== Theme Vars =================== */
:root{
  --bg:#080b18;
  --txt:#eaf0ff;
  --muted:#b9c5ff;
  --stroke:rgba(98,117,255,.25);
  --accent:#7aa2ff;
  --accent-2:#a38bff;
  --danger:#ff5d7a;
  --success:#77e6b6;
  --shadow: 0 10px 40px rgba(0,0,0,.45);
  --radius:20px;

  --glass-1: linear-gradient(180deg, rgba(20,26,60,.6), rgba(17,22,52,.45));
  --glass-2: linear-gradient(180deg, rgba(18,22,55,.6), rgba(12,16,40,.45));
  --head-1:  linear-gradient(180deg, rgba(30,38,92,.55), rgba(22,28,72,.45));

  --blur-desktop: 85px;
  --blur-mobile: 60px;
}
.theme-nebula{
  --bg:#0b0d1b;
  --txt:#f0f6ff;
  --muted:#c7d2ff;
  --stroke:rgba(163,139,255,.28);
  --accent:#9bc7ff;
  --accent-2:#c09bff;
  --glass-1: linear-gradient(180deg, rgba(26,21,54,.55), rgba(19,16,46,.45));
  --glass-2: linear-gradient(180deg, rgba(24,19,50,.55), rgba(14,12,36,.45));
  --head-1:  linear-gradient(180deg, rgba(44,30,92,.55), rgba(28,22,72,.45));
}

/* =================== Base & Infinite Background =================== */
*{box-sizing:border-box}
html,body{min-height:100%; height:auto}
body{
  margin:0;
  font-family: ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial;
  color:var(--txt);
  -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
  background: transparent;
}
body::before{
  content:"";
  position:fixed; z-index:-2;
  top:0; left:0; right:0; bottom:0;
  background:
    radial-gradient(circle at 15% 20%, rgba(122,162,255,.22), transparent 70%),
    radial-gradient(circle at 85% 25%, rgba(163,139,255,.20), transparent 70%),
    radial-gradient(circle at 50% 90%, rgba(119,230,182,.16), transparent 80%),
    var(--bg);
  background-attachment: fixed;
}
.aurora{
  position:absolute; z-index:-1; pointer-events:none; left:0; right:0; top:0;
  min-height:100%; overflow:hidden;
}
.aurora::before, .aurora::after{
  content:"";
  position:absolute; width:120%; height:160%; top:-20%; left:-10%;
  filter: blur(var(--blur-desktop));
  background:
    radial-gradient(closest-side at 28% 32%, rgba(122,162,255,.18), transparent 60%),
    radial-gradient(closest-side at 68% 42%, rgba(163,139,255,.16), transparent 60%),
    radial-gradient(closest-side at 50% 75%, rgba(119,230,182,.12), transparent 60%);
  animation: float 26s ease-in-out infinite;
}
.theme-nebula .aurora::before, .theme-nebula .aurora::after{
  background:
    radial-gradient(closest-side at 32% 35%, rgba(155,199,255,.18), transparent 60%),
    radial-gradient(closest-side at 66% 45%, rgba(192,155,255,.16), transparent 60%),
    radial-gradient(closest-side at 46% 72%, rgba(255,209,102,.12), transparent 60%);
}
.aurora::after{ animation-duration:32s; mix-blend-mode:screen; opacity:.85; transform: rotate(10deg) }
@keyframes float{ 0%{transform:translate(0,0) scale(1)} 50%{transform:translate(4%,3%) scale(1.04)} 100%{transform:translate(0,0) scale(1)} }
@media (prefers-reduced-motion: reduce){ .aurora::before,.aurora::after{ animation:none } }
@media (max-width: 768px){ .aurora::before,.aurora::after{ filter: blur(var(--blur-mobile)); } }

/* =================== Nav =================== */
nav{
  position:sticky; top:0; z-index:10;
  display:flex; align-items:center; justify-content:space-between;
  padding: max(12px, env(safe-area-inset-top)) 16px 12px 16px;
  background: linear-gradient(to bottom, rgba(10,14,30,.85), rgba(10,14,30,.35));
  backdrop-filter: blur(10px);
  border-bottom:1px solid var(--stroke);
}
.brand{display:flex; align-items:center; gap:12px; font-weight:800; letter-spacing:.4px}
.brand .logo{
  width:28px; height:28px; border-radius:8px;
  background: linear-gradient(135deg, var(--accent), var(--accent-2));
  box-shadow: 0 0 24px rgba(122,162,255,.45);
}
nav .actions{display:flex; gap:10px; flex-wrap:wrap}

/* =================== Buttons =================== */
.btn{
  appearance:none; border:1px solid var(--stroke);
  background: linear-gradient(180deg, rgba(34,42,104,.6), rgba(25,31,78,.52));
  color:var(--txt); padding:10px 14px; border-radius:12px; text-decoration:none; font-weight:600;
  box-shadow: 0 6px 16px rgba(0,0,0,.18);
  transition: transform .16s ease, box-shadow .2s ease, filter .2s ease, background .2s ease;
}
.btn:hover{ filter:brightness(1.06); transform: translateY(-1px); box-shadow: 0 10px 26px rgba(0,0,0,.28); }
.btn:active{ transform: translateY(0px) scale(.99); }
.btn-ghost{ background: transparent; border-color: rgba(122,162,255,.35)}
.btn-accent{
  border-color: rgba(122,162,255,.6);
  background: linear-gradient(180deg, rgba(122,162,255,.35), rgba(163,139,255,.28));
  box-shadow: 0 8px 24px rgba(122,162,255,.25);
}

/* =================== Layout & Card =================== */
.container{ max-width:720px; margin:26px auto 90px; padding:0 16px }
.card{
  background: var(--glass-1); border:1px solid var(--stroke);
  border-radius: var(--radius); padding:18px; box-shadow: var(--shadow); margin-top:16px;
}
h1{margin:0 0 8px; font-size:22px; font-weight:900; letter-spacing:.3px}
h2{margin:0 0 12px; font-size:18px; font-weight:900}
label{display:block; margin:10px 0 6px}
input[type=text], input[type=password]{
  width:100%; padding:12px; border-radius:12px; border:1px solid var(--stroke);
  background: linear-gradient(180deg, rgba(12,16,40,.9), rgba(10,14,36,.9));
  color:var(--txt); outline:none; transition: border-color .2s ease, box-shadow .2s ease;
}
input[type=text]:focus, input[type=password]:focus{ border-color: rgba(122,162,255,.75); box-shadow: 0 0 0 6px rgba(122,162,255,.12) }
.help{font-size:12px; color:var(--muted)}
.msg{color:var(--success)}
.err{color:#ff9aa2}

/* =================== Toast =================== */
.toast{
  position:fixed; left:50%; bottom: max(20px, env(safe-area-inset-bottom)); transform: translateX(-50%) translateY(20px);
  background: rgba(16,22,54,.9); backdrop-filter: blur(10px); color:var(--txt); border:1px solid var(--stroke);
  padding:10px 14px; border-radius:12px; box-shadow: var(--shadow); opacity:0; pointer-events:none;
  transition: opacity .2s ease, transform .2s ease; z-index: 50;
}
.toast.show{opacity:1; transform: translateX(-50%) translateY(0)}
</style>
<script>
(function(){
  const THEME_KEY='dream_theme';
  function applyTheme(t){
    document.documentElement.classList.toggle('theme-nebula', t==='nebula');
    localStorage.setItem(THEME_KEY, t);
    const btn=document.getElementById('themeToggle');
    if(btn) btn.innerText = t==='nebula' ? '切换 Aurora' : '切换 Nebula';
  }
  window.toggleTheme=function(){
    const cur=localStorage.getItem(THEME_KEY)||'aurora';
    applyTheme(cur==='aurora'?'nebula':'aurora');
  };
  document.addEventListener('DOMContentLoaded', ()=>{
    applyTheme(localStorage.getItem(THEME_KEY)||'aurora');
    // 成功/失败提示
    const msg = <?= json_encode($msg ?? null, JSON_UNESCAPED_UNICODE) ?>;
    const err = <?= json_encode($err ?? null, JSON_UNESCAPED_UNICODE) ?>;
    if (msg) showToast(msg);
    if (err) showToast(err);
  });

  window.showToast=function(msg){
    let el=document.querySelector('.toast');
    if(!el){ el=document.createElement('div'); el.className='toast'; document.body.appendChild(el); }
    el.textContent=msg; el.classList.add('show');
    setTimeout(()=> el.classList.remove('show'), 1800);
  };
})();
</script>
</head>
<body>
<div class="aurora"></div>

<nav>
  <div class="brand">
    <div class="logo"></div>
    <div>账户设置</div>
  </div>
  <div class="actions">
    <button id="themeToggle" class="btn btn-ghost" type="button" onclick="toggleTheme()">切换 Nebula</button>
    <a class="btn btn-ghost" href="/admin/index.php">返回管理</a>
    <a class="btn btn-accent" href="/admin/logout.php">退出登录</a>
  </div>
</nav>

<div class="container">
  <h1>安全中心</h1>
  <p class="help">修改管理员用户名与密码。为保护安全，变更需验证当前密码。</p>

  <div class="card">
    <h2>修改管理员账号 / 密码</h2>
    <?php if($msg):?><div class="msg" role="status"><?=e($msg)?></div><?php endif;?>
    <?php if($err):?><div class="err" role="alert"><?=e($err)?></div><?php endif;?>
    <form method="post" style="margin-top:8px">
      <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">

      <label>当前用户名</label>
      <input type="text" value="<?=e($curr_user)?>" disabled>

      <label>新用户名（可选）</label>
      <input type="text" name="new_user" placeholder="留空则不修改" value="<?=e($curr_user)?>">
      <div class="help">建议 3 个字符以上，易记且不易被猜测。</div>

      <label>新密码（可选，至少 8 位）</label>
      <input type="password" name="new_pass" placeholder="留空则不修改">

      <label>确认新密码</label>
      <input type="password" name="new_pass2" placeholder="再次输入新密码">

      <label>当前密码（必填，用于确认）</label>
      <input type="password" name="old_pass" required>

      <p style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn btn-accent" type="submit">保存</button>
        <a class="btn" href="/admin/index.php">返回</a>
      </p>
    </form>
  </div>
</div>

<div class="toast" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
