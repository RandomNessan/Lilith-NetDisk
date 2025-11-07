<?php
require __DIR__ . '/../lib/common.php';
require_login();

$files = load_files($config);

// 统计汇总
$total_files = 0;
$total_size  = 0;
$total_dl    = 0;
foreach ($files as $f) {
  $name = $f['name'] ?? '';
  if (!is_string($name) || $name==='') continue;
  $total_files++;
  $total_size += (int)($f['size'] ?? 0);
  $total_dl   += (int)($f['downloads'] ?? 0);
}
function human_bytes($bytes){
  $u=['B','KB','MB','GB','TB']; $i=0;
  while($bytes>=1024 && $i<count($u)-1){$bytes/=1024;$i++;}
  return sprintf('%.2f %s',$bytes,$u[$i]);
}
$just_synced = isset($_GET['synced']) && $_GET['synced'] === '1';
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>文件管理 · Dream Pro</title>
<style>
/* —— 样式同你现版，省略未变部分 —— */
:root{ --bg:#080b18; --txt:#eaf0ff; --muted:#b9c5ff; --stroke:rgba(98,117,255,.25); --accent:#7aa2ff; --accent-2:#a38bff; --danger:#ff5d7a; --success:#77e6b6; --warn:#ffd166; --shadow:0 10px 40px rgba(0,0,0,.45); --radius:20px; --glass-1:linear-gradient(180deg, rgba(20,26,60,.6), rgba(17,22,52,.45)); --glass-2:linear-gradient(180deg, rgba(18,22,55,.6), rgba(12,16,40,.45)); --head-1:linear-gradient(180deg, rgba(30,38,92,.55), rgba(22,28,72,.45)); --blur-desktop:85px; --blur-mobile:60px;}
.theme-nebula{ --bg:#0b0d1b; --txt:#f0f6ff; --muted:#c7d2ff; --stroke:rgba(163,139,255,.28); --accent:#9bc7ff; --accent-2:#c09bff; --glass-1:linear-gradient(180deg, rgba(26,21,54,.55), rgba(19,16,46,.45)); --glass-2:linear-gradient(180deg, rgba(24,19,50,.55), rgba(14,12,36,.45)); --head-1:linear-gradient(180deg, rgba(44,30,92,.55), rgba(28,22,72,.45)); }
*{box-sizing:border-box}
html,body{min-height:100%; height:auto}
body{ margin:0; font-family: ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial; color:var(--txt); background:transparent;}
body::before{ content:""; position:fixed; z-index:-2; top:0; left:0; right:0; bottom:0; background:
  radial-gradient(circle at 15% 20%, rgba(122,162,255,.22), transparent 70%),
  radial-gradient(circle at 85% 25%, rgba(163,139,255,.20), transparent 70%),
  radial-gradient(circle at 50% 90%, rgba(119,230,182,.16), transparent 80%),
  var(--bg); background-attachment:fixed; }

.aurora{ position:absolute; z-index:-1; pointer-events:none; left:0; right:0; top:0; min-height:100%; overflow:hidden; }
.aurora::before,.aurora::after{ content:""; position:absolute; width:120%; height:160%; top:-20%; left:-10%; filter:blur(var(--blur-desktop));
  background: radial-gradient(closest-side at 28% 32%, rgba(122,162,255,.18), transparent 60%),
              radial-gradient(closest-side at 68% 42%, rgba(163,139,255,.16), transparent 60%),
              radial-gradient(closest-side at 50% 75%, rgba(119,230,182,.12), transparent 60%);
  animation: float 26s ease-in-out infinite; }
.theme-nebula .aurora::before,.theme-nebula .aurora::after{ background:
  radial-gradient(closest-side at 32% 35%, rgba(155,199,255,.18), transparent 60%),
  radial-gradient(closest-side at 66% 45%, rgba(192,155,255,.16), transparent 60%),
  radial-gradient(closest-side at 46% 72%, rgba(255,209,102,.12), transparent 60%);}
.aurora::after{ animation-duration:32s; mix-blend-mode:screen; opacity:.85; transform: rotate(10deg) }
@keyframes float{ 0%{transform:translate(0,0) scale(1)} 50%{transform:translate(4%,3%) scale(1.04)} 100%{transform:translate(0,0) scale(1)} }
@media (prefers-reduced-motion: reduce){ .aurora::before,.aurora::after{ animation:none } }
@media (max-width:768px){ .aurora::before,.aurora::after{ filter:blur(var(--blur-mobile)); } }

nav{ position:sticky; top:0; z-index:10; display:flex; align-items:center; justify-content:space-between;
  padding:max(12px, env(safe-area-inset-top)) 16px 12px; background:linear-gradient(to bottom, rgba(10,14,30,.85), rgba(10,14,30,.35));
  backdrop-filter:blur(10px); border-bottom:1px solid var(--stroke);}
.brand{display:flex; align-items:center; gap:12px; font-weight:800; letter-spacing:.4px}
.brand .logo{ width:28px; height:28px; border-radius:8px; background:linear-gradient(135deg, var(--accent), var(--accent-2)); box-shadow:0 0 24px rgba(122,162,255,.45)}
nav .actions{display:flex; gap:10px; flex-wrap:wrap}

.btn{ appearance:none; border:1px solid var(--stroke); background:linear-gradient(180deg, rgba(34,42,104,.6), rgba(25,31,78,.52));
  color:var(--txt); padding:10px 14px; border-radius:12px; text-decoration:none; font-weight:600;
  box-shadow:0 6px 16px rgba(0,0,0,.18); transition: transform .16s ease, box-shadow .2s ease, filter .2s ease, background .2s ease; }
.btn:hover{ filter:brightness(1.06); transform:translateY(-1px); box-shadow:0 10px 26px rgba(0,0,0,.28) }
.btn:active{ transform: translateY(0) scale(.99) }
.btn-ghost{ background:transparent; border-color:rgba(122,162,255,.35) }
.btn-accent{ border-color:rgba(122,162,255,.6); background:linear-gradient(180deg, rgba(122,162,255,.35), rgba(163,139,255,.28)); box-shadow:0 8px 24px rgba(122,162,255,.25) }
.btn-danger{ border-color:rgba(255,93,122,.5); background:linear-gradient(180deg, rgba(255,93,122,.25), rgba(255,93,122,.15)) }

.container{ max-width:1180px; margin:26px auto 90px; padding:0 20px; }
.hero{ display:grid; grid-template-columns:1fr auto; gap:14px; align-items:center; margin:12px 0 18px; padding:16px;
  border:1px solid var(--stroke); background:var(--glass-1); backdrop-filter:blur(12px); border-radius:var(--radius); box-shadow:var(--shadow); }
.hero h1{margin:0; font-size:22px; font-weight:900; letter-spacing:.3px}
.hero p{margin:2px 0 0; color:var(--muted); font-size:14px}
.toolbar{display:flex; gap:8px; flex-wrap:wrap}
@media (max-width:900px){ .hero{grid-template-columns:1fr} }

.stats{display:grid; grid-template-columns: repeat(3,1fr); gap:12px; margin:0 0 16px}
.stat{ border:1px solid var(--stroke); background:var(--glass-2); border-radius:16px; padding:14px; box-shadow:var(--shadow); display:flex; align-items:center; gap:12px; }
.stat .ico{width:28px; height:28px; border-radius:10px; display:grid; place-items:center; background:linear-gradient(135deg, rgba(122,162,255,.25), rgba(163,139,255,.22)); border:1px solid rgba(122,162,255,.35)}
.stat .k{font-size:12px; color:var(--muted); margin-bottom:4px}
.stat .v{font-size:18px; font-weight:900}
@media (max-width:900px){ .stats{grid-template-columns:1fr} }

.card{ background:var(--glass-1); border:1px solid var(--stroke); border-radius:var(--radius); padding:18px; box-shadow:var(--shadow); margin-bottom:18px }
.card h2{margin:0 0 12px; font-size:18px; font-weight:900; letter-spacing:.2px}
.small{font-size:12px;color:var(--muted)}
input[type=file], input[type=text]{ width:100%; padding:12px 12px; border-radius:12px; border:1px solid var(--stroke);
  background:linear-gradient(180deg, rgba(12,16,40,.9), rgba(10,14,36,.9)); color:var(--txt); outline:none; transition:border-color .2s ease, box-shadow .2s ease; }
input[type=file]:focus, input[type=text]:focus{ border-color: rgba(122,162,255,.75); box-shadow: 0 0 0 6px rgba(122,162,255,.12) }
.row{display:grid; grid-template-columns:2fr 1fr; gap:16px}
@media (max-width:900px){ .row{grid-template-columns:1fr} }

.drop{ border:1px dashed rgba(122,162,255,.4); border-radius:14px; padding:14px; margin-top:10px; display:flex; align-items:center; justify-content:center; gap:10px; color:var(--muted); transition:border-color .2s ease, background .2s ease;}
.drop.drag{ border-color:var(--accent); background:rgba(122,162,255,.08) }

.table-wrap{ border:1px solid var(--stroke); border-radius:calc(var(--radius) + 2px); overflow:auto; background:var(--glass-2); box-shadow:var(--shadow) }
table{ width:100%; border-collapse:collapse; min-width:840px }
thead th{ background:var(--head-1); text-align:left; font-weight:900; letter-spacing:.2px; font-size:13px; color:var(--muted); padding:12px 14px; border-bottom:1px solid var(--stroke); position:sticky; top:0; z-index:1;}
tbody td{ padding:12px 14px; font-size:14px; border-bottom:1px solid rgba(65,78,160,.18) }
tbody tr{ transition: background .18s ease }
tbody tr:hover{ background: linear-gradient(90deg, rgba(122,162,255,.08), transparent 60%) }
.badge{ display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:999px; font-size:12px; line-height:1;
  background:linear-gradient(180deg, rgba(122,162,255,.2), rgba(163,139,255,.18)); border:1px solid rgba(122,162,255,.35); box-shadow: inset 0 0 20px rgba(122,162,255,.08)}
.right{text-align:right} .nowrap{white-space:nowrap} .notice{color:var(--success)}
.actions{display:flex; gap:8px; flex-wrap:wrap}

.search{display:flex; gap:10px; align-items:center}
.search input{ width:280px; max-width:60vw }
@media (max-width:900px){ .search{flex-wrap:wrap} .search input{ width:100% }}

.toast{ position:fixed; left:50%; bottom:max(20px, env(safe-area-inset-bottom)); transform: translateX(-50%) translateY(20px);
  background:rgba(16,22,54,.9); backdrop-filter:blur(10px); color:var(--txt); border:1px solid var(--stroke);
  padding:10px 14px; border-radius:12px; box-shadow:var(--shadow); opacity:0; pointer-events:none; transition: opacity .2s ease, transform .2s ease; z-index:50; }
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

    const q=document.getElementById('q');
    if(q){
      q.addEventListener('input', ()=>{
        const term=q.value.trim().toLowerCase();
        document.querySelectorAll('tbody tr[data-name]').forEach(tr=>{
          const name=tr.getAttribute('data-name');
          const remark=tr.getAttribute('data-remark');
          const hit = (!term) || name.includes(term) || remark.includes(term);
          tr.style.display = hit ? '' : 'none';
        });
      });
    }

    <?php if ($just_synced): ?>
      showToast('同步完成：已扫描 /dl/ 并更新清单');
    <?php endif; ?>
  });

  window.copyText=function(t){
    navigator.clipboard.writeText(t).then(()=>{ showToast('已复制到剪贴板'); })
      .catch(()=> alert('复制失败'));
  };
  window.showToast=function(msg){
    let el=document.querySelector('.toast');
    if(!el){ el=document.createElement('div'); el.className='toast'; document.body.appendChild(el); }
    el.textContent=msg; el.classList.add('show');
    setTimeout(()=> el.classList.remove('show'), 1600);
  };
})();
</script>
</head>
<body>
<div class="aurora"></div>

<nav>
  <div class="brand">
    <div class="logo"></div>
    <div>Dream Storage · 控制台</div>
  </div>
  <div class="actions">
    <button id="themeToggle" class="btn btn-ghost" type="button" onclick="toggleTheme()">切换 Nebula</button>
    <a class="btn btn-ghost" href="/admin/account.php">账户设置</a>
    <a class="btn btn-accent" href="/admin/logout.php">退出登录</a>
  </div>
</nav>

<div class="container">
  <section class="hero">
    <div>
      <h1>文件管理</h1>
      <p>上传到 <code>/dl/</code>，自动生成直链与隐匿链接。</p>
    </div>
    <div class="toolbar search">
      <input id="q" type="text" placeholder="搜索文件名 / 备注…（前端过滤）" aria-label="搜索">
      <a class="btn" href="/admin/index.php" title="刷新">刷新</a>

      <!-- 新增：文件同步按钮（POST + CSRF） -->
      <form action="/admin/sync.php" method="post" style="display:inline">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <button class="btn" type="submit" title="扫描 /dl/ 并更新清单">文件同步</button>
      </form>
    </div>
  </section>

  <section class="stats">
    <div class="stat">
      <div class="ico">📦</div>
      <div>
        <div class="k">文件数</div>
        <div class="v"><?= (int)$total_files ?></div>
      </div>
    </div>
    <div class="stat">
      <div class="ico">💾</div>
      <div>
        <div class="k">总大小</div>
        <div class="v"><?= e(human_bytes($total_size)) ?></div>
      </div>
    </div>
    <div class="stat">
      <div class="ico">⬇️</div>
      <div>
        <div class="k">总下载</div>
        <div class="v"><?= (int)$total_dl ?></div>
      </div>
    </div>
  </section>

  <div class="card">
    <h2>上传文件</h2>
    <div class="small">可点击或将文件拖拽至下方区域，上传后保存到 <code>/dl/</code> 并加入清单。</div>
    <form method="post" action="/admin/upload.php" enctype="multipart/form-data" style="margin-top:12px">
      <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
      <div class="row">
        <div>
          <input type="file" name="file" required>
          <div class="drop" role="button" tabindex="0" aria-label="拖拽到此上传">将文件拖到这里，或点击选择</div>
        </div>
        <div>
          <input type="text" name="remark" placeholder="备注（可选，如：版本/用途说明）">
        </div>
      </div>
      <p style="margin-top:12px">
        <button class="btn btn-accent" type="submit">开始上传</button>
      </p>
    </form>
  </div>

  <div class="card">
    <h2>文件列表</h2>
    <?php if (empty($files)): ?>
      <div class="notice" style="margin-top:4px">当前暂无文件。</div>
    <?php else: ?>
      <div class="table-wrap" style="margin-top:10px">
        <table>
          <thead>
            <tr>
              <th>文件名</th>
              <th>备注</th>
              <th class="right">大小</th>
              <th class="nowrap">上传时间</th>
              <th class="right nowrap">下载数</th>
              <th class="nowrap">最后下载</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($files as $f):
            $name = $f['name'] ?? '';
            if (!is_string($name) || $name === '') continue;
            $hidden = build_hidden_link($config, $name);
            $public = build_public_link($name);
            $remark = (string)($f['remark'] ?? '');
          ?>
            <tr data-name="<?=e(mb_strtolower($name))?>" data-remark="<?=e(mb_strtolower($remark))?>">
              <td><span class="badge">/dl</span>&nbsp;<?=e($name)?></td>
              <td><?=e($remark)?></td>
              <td class="right"><?=e($f['size_human'] ?? '')?></td>
              <td class="nowrap"><?=e($f['created_at'] ?? '')?></td>
              <td class="right nowrap"><?=e($f['downloads'] ?? 0)?></td>
              <td class="nowrap"><?=e($f['last_download_at'] ?? '-')?></td>
              <td>
                <div class="actions">
                  <a class="btn btn-ghost" href="<?=e($public)?>" target="_blank">直链</a>
                  <a class="btn btn-ghost" href="<?=e($hidden)?>" target="_blank">隐匿链接</a>
                  <button class="btn" type="button" onclick="copyText('<?=e($hidden)?>')">复制隐匿</button>
                  <a class="btn" href="/admin/file_edit.php?name=<?=rawurlencode($name)?>">修改信息</a>
                  <a class="btn btn-danger" href="/admin/file_delete.php?name=<?=rawurlencode($name)?>"
                     onclick="return confirm('确认删除该文件及记录？')">删除</a>
                </div>
              </td>
            </tr>
          <?php endforeach;?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="toast" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
