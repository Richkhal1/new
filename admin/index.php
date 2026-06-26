<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — Legacy National Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--t9:#0a2e2a;--t8:#0d5555;--t7:#0f766e;--t6:#0d9488;--t5:#14b8a6;--t4:#2dd4bf;--t0:#f0fdfa;--s9:#0f172a;--s8:#1e293b;--s7:#334155;--s6:#475569;--s5:#64748b;--s4:#94a3b8;--s3:#cbd5e1;--s2:#e2e8f0;--s1:#f1f5f9;--s0:#f8fafc;--wh:#fff;--sm:0 1px 3px rgba(0,0,0,0.06);--md:0 4px 20px rgba(0,0,0,0.08);--lg:0 10px 40px rgba(0,0,0,0.12);--r8:8px;--r12:12px;--r16:16px;--tr:all .3s cubic-bezier(.4,0,.2,1)}
body{font-family:Inter,sans-serif;background:var(--s0);color:var(--s6);-webkit-font-smoothing:antialiased}
.app{display:flex;min-height:100vh}
.sb{width:250px;background:var(--s9);color:var(--wh);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50;transition:var(--tr)}
.sbb{padding:1.25rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.08);font-family:'Playfair Display',serif;font-size:1.25rem;font-weight:700}
.sbb span{color:var(--t4)}
.sbn{padding:.25rem 0;flex:1;overflow-y:auto}
.sbn .lb{font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:var(--s5);padding:.75rem 1.5rem .25rem}
.sbn a{display:flex;align-items:center;gap:.7rem;padding:.6rem 1.5rem;color:rgba(255,255,255,.6);text-decoration:none;font-size:.85rem;font-weight:500;transition:var(--tr);border-left:3px solid transparent}
.sbn a:hover,.sbn a.act{background:rgba(255,255,255,.05);color:var(--wh)}
.sbn a.act{border-left-color:var(--t5);color:var(--wh)}
.sbn a svg{width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}
.sbf{padding:.75rem 1.5rem;border-top:1px solid rgba(255,255,255,.08)}
.sbf .u{display:flex;align-items:center;gap:.7rem}
.sbf .av{width:34px;height:34px;border-radius:50%;background:var(--t6);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem}
.sbf .nm{font-size:.82rem;font-weight:600}
.sbf .rl{font-size:.72rem;color:var(--t4)}
.sbf .lo{margin-top:.6rem;display:flex;align-items:center;gap:.4rem;color:var(--s4);font-size:.82rem;text-decoration:none;transition:var(--tr)}
.sbf .lo:hover{color:#ef4444}
.mn{margin-left:250px;flex:1;padding:1.5rem 2rem}
.tp{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--s2)}
.tp h1{font-size:1.5rem;color:var(--s9)}
.tp .dt{font-size:.85rem;color:var(--s5)}
.tpr{display:flex;align-items:center;gap:.75rem}
.sg{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.85rem;margin-bottom:1.5rem}
.sc{background:var(--wh);border-radius:var(--r12);padding:1.1rem 1.25rem;box-shadow:var(--sm);border:1px solid var(--s1)}
.sc .sl{font-size:.72rem;font-weight:600;color:var(--s5);text-transform:uppercase;letter-spacing:.03em}
.sc .sv{font-size:1.6rem;font-weight:800;color:var(--s9);margin-top:.15rem}
.sc .ss{font-size:.75rem;color:var(--s4);margin-top:.05rem}
.sc .sv .t{font-size:.85rem;font-weight:600}
.sec{background:var(--wh);border-radius:var(--r12);box-shadow:var(--sm);border:1px solid var(--s1);margin-bottom:1.25rem;overflow:hidden}
.sh{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;border-bottom:1px solid var(--s1);flex-wrap:wrap;gap:.6rem}
.sh h2{font-size:1rem;color:var(--s9);font-weight:700}
.sh .bdg{background:var(--t0);color:var(--t7);font-size:.72rem;font-weight:600;padding:.2rem .65rem;border-radius:999px}
.ct{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap}
.ct input,.ct select{padding:.45rem .7rem;border:1.5px solid var(--s2);border-radius:var(--r8);font-size:.82rem;font-family:inherit;color:var(--s8);background:var(--wh)}
.ct input:focus,.ct select:focus{outline:none;border-color:var(--t5);box-shadow:0 0 0 3px rgba(20,184,166,.1)}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:.6rem 1.1rem;font-size:.85rem;border-bottom:1px solid var(--s1)}
th{font-weight:600;color:var(--s5);font-size:.75rem;text-transform:uppercase;letter-spacing:.03em;background:var(--s0)}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--s0)}
.st{display:inline-flex;align-items:center;gap:.25rem;font-size:.75rem;font-weight:600;padding:.15rem .55rem;border-radius:999px}
.st.act,.st.cmp,.st.app,.st.paid{background:#f0fdf4;color:#16a34a}
.st.pen{background:#fefce8;color:#ca8a04}
.st.sus,.st.fail,.st.rej,.st.cls,.st.def{background:#fef2f2;color:#dc2626}
.st.urg{background:#fef2f2;color:#dc2626;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.6}}
.btn{padding:.45rem .9rem;border-radius:var(--r8);font-weight:600;font-size:.82rem;border:none;cursor:pointer;transition:var(--tr);font-family:inherit;display:inline-flex;align-items:center;gap:.35rem}
.bsm{padding:.3rem .65rem;font-size:.75rem}
.bp{background:var(--t6);color:var(--wh)}
.bp:hover{background:var(--t7)}
.bd{background:#ef4444;color:var(--wh)}
.bd:hover{background:#dc2626}
.bs{background:#16a34a;color:var(--wh)}
.bs:hover{background:#15803d}
.bw{background:#f59e0b;color:var(--wh)}
.bw:hover{background:#d97706}
.bg{background:transparent;color:var(--s6);border:1px solid var(--s2)}
.bg:hover{background:var(--s0)}
.bi{background:var(--s8);color:var(--wh)}
.bi:hover{background:var(--s9)}
.btn:disabled{opacity:.5;cursor:not-allowed}
.mo{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;align-items:center;justify-content:center;padding:1rem}
.mo.sho{display:flex}
.mod{background:var(--wh);border-radius:var(--r16);width:100%;max-width:560px;max-height:85vh;overflow-y:auto;box-shadow:var(--lg)}
.mh{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;border-bottom:1px solid var(--s1);position:sticky;top:0;background:var(--wh);z-index:1}
.mh h3{font-size:1.05rem;color:var(--s9)}
.mc{background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--s4);line-height:1;padding:.2rem}
.mc:hover{color:var(--s6)}
.mb{padding:1.25rem}
.mf{display:flex;justify-content:flex-end;gap:.6rem;padding:.85rem 1.25rem;border-top:1px solid var(--s1);position:sticky;bottom:0;background:var(--wh)}
.fg{margin-bottom:.85rem}
.fg label{display:block;font-size:.8rem;font-weight:600;color:var(--s7);margin-bottom:.25rem}
.fg input,.fg select,.fg textarea{width:100%;background:var(--wh);border:1.5px solid var(--s2);border-radius:var(--r8);padding:.55rem .8rem;font-size:.85rem;color:var(--s8);transition:var(--tr);font-family:inherit}
.fg input:focus,.fg select:focus,.fg textarea:focus{outline:none;border-color:var(--t5);box-shadow:0 0 0 3px rgba(20,184,166,.1)}
.fg textarea{resize:vertical;min-height:55px}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:.85rem}
.toast{position:fixed;bottom:1.5rem;right:1.5rem;background:var(--s9);color:var(--wh);padding:.75rem 1.15rem;border-radius:var(--r8);font-size:.85rem;box-shadow:var(--lg);z-index:200;transform:translateY(100px);opacity:0;transition:var(--tr)}
.toast.sho{transform:translateY(0);opacity:1}
.toast.scs{background:#16a34a}
.toast.err{background:#dc2626}
.em{text-align:center;padding:2rem;color:var(--s4);font-size:.85rem}
.tbs{display:flex;gap:0;border-bottom:1px solid var(--s2);margin-bottom:1rem}
.tbb{padding:.55rem 1.1rem;font-size:.82rem;font-weight:600;border:none;background:none;cursor:pointer;color:var(--s5);border-bottom:2px solid transparent;transition:var(--tr);font-family:inherit}
.tbb:hover{color:var(--s7)}
.tbb.act{color:var(--t6);border-bottom-color:var(--t6)}
.pill{display:inline-flex;align-items:center;gap:.25rem;padding:.1rem .45rem;border-radius:999px;font-size:.7rem;font-weight:600}
.chart-c{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem}
.chart{background:var(--wh);border-radius:var(--r12);padding:1.25rem;box-shadow:var(--sm);border:1px solid var(--s1)}
.chart h3{font-size:.9rem;color:var(--s9);margin-bottom:.75rem;font-weight:600}
.chart .ch{height:120px;display:flex;align-items:flex-end;gap:3px;padding:0 4px}
.chart .ch .bar{flex:1;background:var(--t5);border-radius:3px 3px 0 0;min-height:2px;transition:var(--tr);position:relative}
.chart .ch .bar:hover{background:var(--t6)}
.chart .ch .bar .tip{position:absolute;bottom:100%;left:50%;transform:translateX(-50%);background:var(--s9);color:var(--wh);font-size:.65rem;padding:2px 6px;border-radius:4px;white-space:nowrap;opacity:0;pointer-events:none;transition:var(--tr);margin-bottom:4px}
.chart .ch .bar:hover .tip{opacity:1}
.chart .xl{display:flex;justify-content:space-between;font-size:.65rem;color:var(--s4);margin-top:.5rem}
.chart .note{font-size:.7rem;color:var(--s4);margin-top:.35rem}
.qag{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.6rem;margin-bottom:1.5rem}
.qa{background:var(--wh);border-radius:var(--r8);padding:.85rem 1rem;box-shadow:var(--sm);border:1px solid var(--s1);text-align:center;cursor:pointer;transition:var(--tr);text-decoration:none;color:var(--s6)}
.qa:hover{transform:translateY(-2px);box-shadow:var(--md);border-color:var(--t4)}
.qa svg{width:22px;height:22px;stroke:var(--t6);fill:none;stroke-width:2;margin-bottom:.35rem}
.qa .ql{font-size:.8rem;font-weight:600;color:var(--s8)}
.widget-c{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem}
.widget{background:var(--wh);border-radius:var(--r12);padding:1.1rem 1.25rem;box-shadow:var(--sm);border:1px solid var(--s1)}
.widget h3{font-size:.82rem;color:var(--s9);margin-bottom:.6rem;font-weight:600;display:flex;justify-content:space-between;align-items:center}
.widget .wi{font-size:.82rem;color:var(--s6);padding:.3rem 0;border-bottom:1px solid var(--s1);display:flex;justify-content:space-between}
.widget .wi:last-child{border-bottom:none}
.widget .wi .wl{font-weight:500}
.widget .wi .wv{font-weight:600;color:var(--s8);font-size:.8rem}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem}
@media(max-width:1024px){.chart-c,.widget-c,.grid-3{grid-template-columns:1fr}}
@media(max-width:768px){
  .sb{transform:translateX(-100%)}
  .sb.op{transform:translateX(0)}
  .mn{margin-left:0;padding:1.25rem}
  .sg{grid-template-columns:1fr 1fr}
  .tw{overflow-x:auto}
  .fr{grid-template-columns:1fr}
  .ct{flex-direction:column;align-items:stretch}
  .ct input,.ct select{width:100%}
}
@media(max-width:480px){.sg{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="app">
  <aside class="sb" id="sb">
    <div class="sbb" style="display:flex;align-items:center;gap:10px"><img src="../images/legacy-logo-green.png" style="height:28px"></div>
    <nav class="sbn">
      <div class="lb">Admin</div>
      <a href="#" class="act" data-t="dash"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Dashboard</a>
      <a href="#" data-t="users"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Users</a>
      <a href="#" data-t="loans"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>Loans</a>
      <a href="#" data-t="transfers"><svg viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>Transfers</a>
      <a href="#" data-t="tx"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Transactions</a>
      <a href="#" data-t="announce"><svg viewBox="0 0 24 24"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>Announcements</a>
      <a href="#" data-t="activity"><svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Activity Log</a>
      <a href="#" data-t="settings"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>Settings</a>
    </nav>
    <div class="sbf">
      <div class="u"><div class="av"><?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?></div><div><div class="nm"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div><div class="rl">Administrator</div></div></div>
      <a href="../api/logout.php" class="lo"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Sign Out</a>
    </div>
  </aside>

  <main class="mn">
    <div class="tp">
      <div><h1 id="pageTitle">Dashboard</h1><div class="dt" id="cdate"></div></div>
      <div class="tpr">
        <span id="notifBell" style="cursor:pointer;position:relative" onclick="switchTab('transfers')">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--s5)" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span id="notifDot" style="display:none;position:absolute;top:-4px;right:-4px;width:10px;height:10px;background:#ef4444;border-radius:50%;border:2px solid var(--s0)"></span>
        </span>
        <button class="btn bg bsm" onclick="refreshAll()">⟳ Refresh</button>
        <button class="btn bp bsm" onclick="document.getElementById('sb').classList.toggle('op')">☰</button>
      </div>
    </div>

    <!-- ═══ DASHBOARD ═══ -->
    <div id="t-dash" class="tc">
      <!-- Quick Actions -->
      <div class="qag">
        <div class="qa" onclick="switchTab('users');setTimeout(()=>openModal('createUser'),200)"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg><div class="ql">New User</div></div>
        <div class="qa" onclick="switchTab('loans')"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg><div class="ql">Loans</div></div>
        <div class="qa" onclick="switchTab('transfers')"><svg viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg><div class="ql">Transfers</div></div>
        <div class="qa" onclick="switchTab('tx')"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><div class="ql">Transactions</div></div>
        <div class="qa" onclick="switchTab('announce')"><svg viewBox="0 0 24 24"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg><div class="ql">Announce</div></div>
        <div class="qa" onclick="refreshAll()"><svg viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg><div class="ql">Refresh</div></div>
      </div>
      <!-- Stats -->
      <div class="sg" id="statsGrid">
        <div class="sc"><div class="sl">Total Users</div><div class="sv" id="sUsers">—</div><div class="ss" id="sUsersSub"></div></div>
        <div class="sc"><div class="sl">Active Accounts</div><div class="sv" id="sActive">—</div></div>
        <div class="sc"><div class="sl">Total Balance</div><div class="sv" id="sBalance">—</div></div>
        <div class="sc"><div class="sl">Pending Transfers</div><div class="sv" id="sPending">—</div><div class="ss" id="sPendingAmt"></div></div>
        <div class="sc"><div class="sl">Pending Loans</div><div class="sv" id="sLoans">—</div><div class="ss" id="sLoansAmt"></div></div>
        <div class="sc"><div class="sl">Active Loans</div><div class="sv" id="sActiveLoans">—</div><div class="ss" id="sActiveLoansAmt"></div></div>
        <div class="sc"><div class="sl">30d Volume</div><div class="sv" id="sVolume">—</div></div>
        <div class="sc"><div class="sl">Total Transfers</div><div class="sv" id="sTotalTrf">—</div></div>
      </div>
      <!-- Charts -->
      <div class="chart-c">
        <div class="chart"><h3>User Registrations (14 days)</h3><div class="ch" id="regChart"></div><div class="xl" id="regLabels"></div></div>
        <div class="chart"><h3>Transaction Volume (14 days)</h3><div class="ch" id="txChart"></div><div class="xl" id="txLabels"></div></div>
      </div>
      <!-- Widgets -->
      <div class="widget-c">
        <div class="widget"><h3>Recent Registrations</h3><div id="recentRegWidget"><div class="em">Loading...</div></div></div>
        <div class="widget"><h3>Recent Transfers</h3><div id="recentTrfWidget"><div class="em">Loading...</div></div></div>
      </div>
    </div>

    <!-- ═══ USERS ═══ -->
    <div id="t-users" class="tc" style="display:none">
      <div class="sec">
        <div class="sh">
          <h2>Manage Users</h2>
          <div class="ct">
            <input type="text" id="userSearch" placeholder="Search name/email/acct..." oninput="loadUsers()" style="width:200px">
            <button class="btn bp" onclick="openModal('createUser')">+ New User</button>
          </div>
        </div>
        <div class="tw"><table><thead><tr><th>Name</th><th>Email</th><th>Account</th><th>Balance</th><th>Loans</th><th>Status</th><th style="width:200px">Actions</th></tr></thead><tbody id="usersTb"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ LOANS ═══ -->
    <div id="t-loans" class="tc" style="display:none">
      <div class="tbs" id="loanTabs">
        <button class="tbb act" data-ls="pending" onclick="switchLoanTab('pending',this)">Pending</button>
        <button class="tbb" data-ls="active" onclick="switchLoanTab('active',this)">Active</button>
        <button class="tbb" data-ls="approved" onclick="switchLoanTab('approved',this)">Approved</button>
        <button class="tbb" data-ls="rejected" onclick="switchLoanTab('rejected',this)">Rejected</button>
        <button class="tbb" data-ls="paid" onclick="switchLoanTab('paid',this)">Paid</button>
        <button class="tbb" data-ls="all" onclick="switchLoanTab('all',this)">All</button>
      </div>
      <div class="sec">
        <div class="sh"><h2 id="loanTitle">Pending Loans</h2><span class="bdg" id="loanBadge">0</span></div>
        <div class="tw"><table><thead><tr><th>Borrower</th><th>Amount</th><th>Purpose</th><th>Interest</th><th>Term</th><th>Monthly</th><th>Status</th><th style="width:140px">Actions</th></tr></thead><tbody id="loansTb"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ TRANSFERS ═══ -->
    <div id="t-transfers" class="tc" style="display:none">
      <div class="tbs" id="trfTabs">
        <button class="tbb act" data-ts="pending" onclick="switchTrfTab('pending',this)">Pending</button>
        <button class="tbb" data-ts="approved" onclick="switchTrfTab('approved',this)">Approved</button>
        <button class="tbb" data-ts="rejected" onclick="switchTrfTab('rejected',this)">Rejected</button>
        <button class="tbb" data-ts="all" onclick="switchTrfTab('all',this)">All</button>
      </div>
      <div class="sec">
        <div class="sh"><h2 id="trfTitle">Pending Transfers</h2><span class="bdg" id="trfBadge">0</span></div>
        <div class="tw"><table><thead><tr><th>From</th><th>To Account</th><th>Amount</th><th>Description</th><th>Date</th><th>Status</th><th style="width:140px">Actions</th></tr></thead><tbody id="trfTb"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ TRANSACTIONS ═══ -->
    <div id="t-tx" class="tc" style="display:none">
      <div class="sec">
        <div class="sh">
          <h2>All Transactions</h2>
          <div class="ct">
            <select id="txType" onchange="loadTx()"><option value="">All Types</option><option value="deposit">Deposit</option><option value="withdrawal">Withdrawal</option><option value="transfer_in">Transfer In</option><option value="transfer_out">Transfer Out</option><option value="credit">Credit</option><option value="debit">Debit</option></select>
            <select id="txSt" onchange="loadTx()"><option value="">All Statuses</option><option value="completed">Completed</option><option value="pending">Pending</option><option value="failed">Failed</option><option value="flagged">Flagged</option></select>
            <input type="text" id="txSearch" placeholder="Account #..." style="width:130px" oninput="loadTx()">
            <button class="btn bg bsm" onclick="exportCSV()">CSV</button>
          </div>
        </div>
        <div class="tw"><table><thead><tr><th>Account</th><th>Name</th><th>Type</th><th>Amount</th><th>Description</th><th>Ref</th><th>Date</th><th>Status</th></tr></thead><tbody id="txTb"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ ANNOUNCEMENTS ═══ -->
    <div id="t-announce" class="tc" style="display:none">
      <div class="sec">
        <div class="sh"><h2>Announcements</h2><button class="btn bp" onclick="openModal('announce')">+ New</button></div>
        <div id="announceList"></div>
      </div>
    </div>

    <!-- ═══ ACTIVITY ═══ -->
    <div id="t-activity" class="tc" style="display:none">
      <div class="sec">
        <div class="sh"><h2>Admin Activity Log</h2></div>
        <div class="tw"><table><thead><tr><th>Admin</th><th>Action</th><th>Date</th></tr></thead><tbody id="actTb"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ SETTINGS ═══ -->
    <div id="t-settings" class="tc" style="display:none">
      <div class="sec">
        <div class="sh"><h2>Bank Settings</h2></div>
        <div style="padding:1.25rem">
          <form id="settingsForm">
            <div class="fr">
              <div class="fg"><label>Bank Name</label><input name="bank_name" id="s_bank_name"></div>
              <div class="fg"><label>Currency</label><select name="currency" id="s_currency"><option value="USD">USD ($)</option><option value="EUR">EUR (€)</option><option value="GBP">GBP (£)</option></select></div>
            </div>
            <div class="fr">
              <div class="fg"><label>Support Email</label><input type="email" name="support_email" id="s_support_email"></div>
              <div class="fg"><label>Support Phone</label><input name="support_phone" id="s_support_phone"></div>
            </div>
            <div class="fr">
              <div class="fg"><label>Default Interest Rate (%)</label><input type="number" step="0.01" name="default_interest_rate" id="s_default_interest_rate"></div>
              <div class="fg"><label>Max Loan Term (months)</label><input type="number" name="max_loan_term" id="s_max_loan_term"></div>
            </div>
            <div class="fr">
              <div class="fg"><label>Require Transfer Auth</label><select name="transfer_auth_required" id="s_transfer_auth_required"><option value="false">No</option><option value="true">Yes</option></select></div>
              <div class="fg"><label>Auth Type</label><select name="auth_type" id="s_auth_type"><option value="none">None</option><option value="imf">IMF Code</option><option value="swift">SWIFT Code</option><option value="cot">COT Code</option></select></div>
            </div>
            <div class="fg"><label>Authorization Code</label><input name="auth_code" id="s_auth_code" placeholder="Leave blank if not used"></div>
            <button type="submit" class="btn bp">Save Settings</button>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- ─── MODALS ─── -->
<div class="mo" id="m-createUser"><div class="mod">
  <div class="mh"><h3>Create New User</h3><button class="mc" onclick="closeModal('createUser')">×</button></div>
  <div class="mb">
    <form id="createUserForm" onsubmit="return false">
      <div class="fr"><div class="fg"><label>First Name</label><input name="first_name" required></div><div class="fg"><label>Last Name</label><input name="last_name" required></div></div>
      <div class="fg"><label>Email</label><input type="email" name="email" required></div>
      <div class="fr"><div class="fg"><label>Password</label><input type="password" name="password" required minlength="8"></div><div class="fg"><label>Phone</label><input name="phone"></div></div>
      <div class="fg"><label>Initial Deposit ($)</label><input type="number" step="0.01" min="0" name="initial_deposit" value="0"></div>
    </form>
  </div>
  <div class="mf"><button class="btn bg" onclick="closeModal('createUser')">Cancel</button><button class="btn bp" id="createUserBtn" onclick="createUser()">Create User</button></div>
</div></div>

<div class="mo" id="m-fund"><div class="mod" style="max-width:440px">
  <div class="mh"><h3 id="fundTitle">Manage Balance</h3><button class="mc" onclick="closeModal('fund')">×</button></div>
  <div class="mb">
    <input type="hidden" id="fundUserId">
    <div class="fg"><label>Amount ($)</label><input type="number" step="0.01" min="0.01" id="fundAmount" required></div>
    <div class="fg"><label>Reason</label><textarea id="fundDesc" placeholder="e.g. Salary, fee, adjustment"></textarea></div>
  </div>
  <div class="mf"><button class="btn bg" onclick="closeModal('fund')">Cancel</button><button class="btn bs" onclick="fundAccount('fund')">+ Credit</button><button class="btn bd" onclick="fundAccount('debit')">− Debit</button></div>
</div></div>

<div class="mo" id="m-userDetail"><div class="mod" style="max-width:700px">
  <div class="mh"><h3 id="udName">User Details</h3><button class="mc" onclick="closeModal('userDetail')">×</button></div>
  <div class="mb" id="udBody"></div>
</div></div>

<div class="mo" id="m-rejectTransfer"><div class="mod" style="max-width:400px">
  <div class="mh"><h3>Reject Transfer</h3><button class="mc" onclick="closeModal('rejectTransfer')">×</button></div>
  <div class="mb">
    <input type="hidden" id="rejectTransferId">
    <div class="fg"><label>Reason</label><textarea id="rejectReason" placeholder="Why is this being rejected?"></textarea></div>
  </div>
  <div class="mf"><button class="btn bg" onclick="closeModal('rejectTransfer')">Cancel</button><button class="btn bd" onclick="rejectTrfConfirm()">Reject</button></div>
</div></div>

<div class="mo" id="m-rejectLoan"><div class="mod" style="max-width:400px">
  <div class="mh"><h3>Reject Loan</h3><button class="mc" onclick="closeModal('rejectLoan')">×</button></div>
  <div class="mb">
    <input type="hidden" id="rejectLoanId">
    <div class="fg"><label>Note to applicant</label><textarea id="rejectLoanNote" placeholder="Reason for rejection"></textarea></div>
  </div>
  <div class="mf"><button class="btn bg" onclick="closeModal('rejectLoan')">Cancel</button><button class="btn bd" onclick="rejectLoanConfirm()">Reject Loan</button></div>
</div></div>

<div class="mo" id="m-loanDetail"><div class="mod" style="max-width:700px">
  <div class="mh"><h3 id="ldTitle">Loan Details</h3><button class="mc" onclick="closeModal('loanDetail')">×</button></div>
  <div class="mb" id="ldBody"></div>
</div></div>

<div class="mo" id="m-announce"><div class="mod" style="max-width:500px">
  <div class="mh"><h3>New Announcement</h3><button class="mc" onclick="closeModal('announce')">×</button></div>
  <div class="mb">
    <form id="announceForm" onsubmit="return false">
      <div class="fg"><label>Title</label><input name="title" id="aTitle" required></div>
      <div class="fg"><label>Message</label><textarea id="aMessage" rows="4" required></textarea></div>
      <div class="fg"><label>Priority</label><select id="aPriority"><option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
    </form>
  </div>
  <div class="mf"><button class="btn bg" onclick="closeModal('announce')">Cancel</button><button class="btn bp" onclick="createAnnouncement()">Publish</button></div>
</div></div>

<div class="toast" id="toast"></div>

<script>
const API = {
  async get(a,p={}){const q=new URLSearchParams({action:a,...p}).toString();const r=await fetch('../api/admin.php?'+q);return r.json()},
  async post(d){const fd=new URLSearchParams(d);const r=await fetch('../api/admin.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:fd.toString()});return r.json()}
};
function tst(m,t){const e=document.getElementById('toast');e.textContent=m;e.className='toast '+t;setTimeout(()=>e.classList.add('sho'),10);setTimeout(()=>e.classList.remove('sho'),3500)}
function openModal(id){document.getElementById('m-'+id).classList.add('sho')}
function closeModal(id){document.getElementById('m-'+id).classList.remove('sho')}
document.querySelectorAll('.mo').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('sho')}));
function esc(s){if(!s)return '—';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}
function $(n){return '$'+parseFloat(n||0).toLocaleString('en-US',{minimumFractionDigits:2})}

document.getElementById('cdate').textContent=new Date().toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ─── Tab switching ───
const tabLoaders = { dash:()=>{loadStats();loadCharts();loadWidgets()}, users:()=>loadUsers(), loans:()=>loadLoans(), transfers:()=>loadTrf(), tx:()=>loadTx(), announce:()=>loadAnnounce(), activity:()=>loadActivity(), settings:()=>loadSettings() };
function switchTab(tab) {
  document.querySelectorAll('.sbn a').forEach(x=>x.classList.remove('act'));
  document.querySelector(`.sbn a[data-t="${tab}"]`).classList.add('act');
  document.querySelectorAll('.tc').forEach(t=>t.style.display='none');
  const el=document.getElementById('t-'+tab);if(el)el.style.display='block';
  document.getElementById('pageTitle').textContent=document.querySelector(`.sbn a[data-t="${tab}"]`)?.textContent.trim()||'Dashboard';
  if(window.innerWidth<=768)document.getElementById('sb').classList.remove('op');
  if(tabLoaders[tab])tabLoaders[tab]();
}
document.querySelectorAll('.sbn a[data-t]').forEach(a=>{a.addEventListener('click',e=>{e.preventDefault();switchTab(a.dataset.t)})});
function refreshAll(){Object.values(tabLoaders).forEach(f=>f());tst('Refreshed!','scs')}

// ─── DASHBOARD ───
async function loadStats(){
  const d=await API.get('stats');
  document.getElementById('sUsers').textContent=d.users.total;
  document.getElementById('sUsersSub').textContent=d.recent.cnt+' new this week';
  document.getElementById('sActive').textContent=d.users.active;
  document.getElementById('sBalance').textContent=$(d.accounts.total_balance);
  document.getElementById('sPending').textContent=d.pending.total;
  document.getElementById('sPendingAmt').textContent=$(d.pending.total_amount)+' pending';
  document.getElementById('sLoans').textContent=d.pendingLoans.total;
  document.getElementById('sLoansAmt').textContent=$(d.pendingLoans.total_amount)+' pending';
  document.getElementById('sActiveLoans').textContent=d.activeLoans.total;
  document.getElementById('sActiveLoansAmt').textContent=$(d.activeLoans.total_amount)+' active';
  document.getElementById('sVolume').textContent=$(d.txVolume.vol);
  document.getElementById('sTotalTrf').textContent=d.totalTrf.total;
  // Notification dot
  const totalPending = d.pending.total + d.pendingLoans.total;
  document.getElementById('notifDot').style.display = totalPending > 0 ? 'block' : 'none';
}

async function loadCharts(){
  const reg=await API.get('chart_registrations',{days:14});
  const tx=await API.get('chart_transactions',{days:14});
  max=(a)=>Math.max(1,...a.map(i=>i.count));
  const rMax=max(reg),tMax=max(tx);
  document.getElementById('regChart').innerHTML=reg.map(d=>`<div class="bar" style="height:${(d.count/rMax*100)}%"><span class="tip">${d.count}</span></div>`).join('');
  document.getElementById('regLabels').innerHTML=reg.map(d=>`<span>${d.date.slice(5)}</span>`).join('');
  document.getElementById('txChart').innerHTML=tx.map(d=>`<div class="bar" style="height:${(d.count/tMax*100)}%"><span class="tip">${d.count} (${$(d.volume)})</span></div>`).join('');
  document.getElementById('txLabels').innerHTML=tx.map(d=>`<span>${d.date.slice(5)}</span>`).join('');
}

async function loadWidgets(){
  const d=await API.get('recent_activity');
  const rw=document.getElementById('recentRegWidget');
  rw.innerHTML=d.recent.slice(0,5).map(u=>`<div class="wi"><span class="wl">${esc(u.first_name+' '+u.last_name)}</span><span class="wv">${new Date(u.date).toLocaleDateString()}</span></div>`).join('')||'<div class="em">No recent registrations</div>';
  const tw=document.getElementById('recentTrfWidget');
  tw.innerHTML=d.transfers.slice(0,5).map(t=>`<div class="wi"><span class="wl">${esc(t.first_name+' '+t.last_name)} → ${t.to_account_number}</span><span class="wv">${$(t.amount)}</span></div>`).join('')||'<div class="em">No recent transfers</div>';
}

// ─── USERS ───
async function loadUsers(){
  const s=document.getElementById('userSearch').value.trim();
  const u=await API.get('users',s?{search:s}:{});
  const tb=document.getElementById('usersTb');
  if(!u.length){tb.innerHTML='<tr><td colspan="7" class="em">No users found</td></tr>';return}
  tb.innerHTML=u.map(u=>{
    const n=esc(u.first_name+' '+u.last_name),e=esc(u.email);
    const a=u.accounts&&u.accounts[0]||{},ac=a.account_number||'—',bal=$(a.balance);
    const st=u.status,isS=st==='suspended';
    return `<tr><td><strong>${n}</strong><br><span style="font-size:.75rem;color:var(--s4)">${e}</span></td>
    <td style="font-size:.82rem">${e}</td>
    <td style="font-family:monospace;font-size:.8rem">${ac}</td>
    <td><strong>${bal}</strong></td>
    <td>${u.loans?u.loans.cnt:0}</td>
    <td><span class="st ${st}">${st}</span></td>
    <td style="white-space:nowrap">
      <button class="btn bs bsm" onclick="openFund('${u.id}','${n.replace(/'/g,"\\'")}')">$$</button>
      <button class="btn ${isS?'bs':'bw'} bsm" onclick="toggleStatus('${u.id}','${st}')">${isS?'Activate':'Suspend'}</button>
      <button class="btn bg bsm" onclick="viewUser('${u.id}')">👁</button>
    </td></tr>`
  }).join('');
}
async function toggleStatus(id,cur){
  const ns=cur==='suspended'?'active':'suspended';
  const r=await API.post({action:'update_user_status',user_id:id,status:ns});
  if(r.error){tst(r.error,'err');return}
  tst(r.message,'scs');loadUsers();loadStats()
}
async function createUser(){
  const f=document.getElementById('createUserForm'),d=new FormData(f);d.set('action','create_user');
  const btn=document.getElementById('createUserBtn');btn.disabled=true;btn.textContent='Creating...';
  const r=await API.post(Object.fromEntries(d));
  if(r.error){tst(r.error,'err');btn.disabled=false;btn.textContent='Create User';return}
  tst('User created! Acct: '+r.account_number,'scs');closeModal('createUser');f.reset();loadUsers();loadStats();
  btn.disabled=false;btn.textContent='Create User';
}
let fundUserId='';
function openFund(id,nm){fundUserId=id;document.getElementById('fundUserId').value=id;document.getElementById('fundTitle').textContent='Balance — '+nm;document.getElementById('fundAmount').value='';document.getElementById('fundDesc').value='';openModal('fund')}
async function fundAccount(act){
  const amt=document.getElementById('fundAmount').value,desc=document.getElementById('fundDesc').value.trim()||(act==='fund'?'Admin credit':'Admin debit');
  if(!amt||parseFloat(amt)<=0){tst('Enter valid amount','err');return}
  const r=await API.post({action:act,user_id:fundUserId,amount:amt,description:desc});
  if(r.error){tst(r.error,'err');return}
  tst(r.message,'scs');closeModal('fund');loadUsers();loadStats()
}

async function viewUser(uid){
  const d=await API.get('user_detail',{user_id:uid});
  if(d.error){tst(d.error,'err');return}
  const n=esc(d.first_name+' '+d.last_name),e=esc(d.email);
  const accts=(d.accounts||[]).map(a=>`<tr><td style="font-family:monospace">${a.account_number}</td><td>${a.account_type}</td><td><strong>${$(a.balance)}</strong></td><td><span class="st ${a.status}">${a.status}</span></td></tr>`).join('');
  const txs=(d.transactions||[]).slice(0,10).map(t=>`<tr><td><span class="st ${t.status}">${t.type.replace(/_/g,' ')}</span></td><td><strong>${$(t.amount)}</strong></td><td>${esc(t.description)}</td><td>${new Date(t.created_at).toLocaleDateString()}</td><td><span class="st ${t.status}">${t.status}</span></td></tr>`).join('');
  const loans=(d.loans||[]).map(l=>`<tr><td><strong>${$(l.amount)}</strong></td><td>${l.interest_rate}%</td><td>${l.term_months}mo</td><td>${$(l.monthly_payment)}</td><td><span class="st ${l.status}">${l.status}</span></td></tr>`).join('');
  const notes=(d.notes||[]).map(n=>`<div style="padding:.5rem 0;border-bottom:1px solid var(--s1);font-size:.82rem"><strong>${esc(n.first_name+' '+n.last_name)}</strong> — ${esc(n.note)}<br><span style="font-size:.72rem;color:var(--s4)">${new Date(n.created_at).toLocaleString()}</span></div>`).join('');
  document.getElementById('udName').textContent=n;
  document.getElementById('udBody').innerHTML=`
    <div class="fr" style="margin-bottom:1rem"><div class="fg"><label>Email</label><input value="${e}" disabled></div><div class="fg"><label>Phone</label><input value="${esc(d.phone||'—')}" disabled></div></div>
    <div class="fr" style="margin-bottom:1rem"><div class="fg"><label>Status</label><input value="${d.status}" disabled></div><div class="fg"><label>Member Since</label><input value="${new Date(d.created_at).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'})}" disabled></div></div>
    <h4 style="margin:.75rem 0 .4rem;font-size:.9rem;color:var(--s8)">Accounts</h4>
    <table style="margin-bottom:1rem"><thead><tr><th>Account #</th><th>Type</th><th>Balance</th><th>Status</th></tr></thead><tbody>${accts||'<tr><td colspan="4" class="em">No accounts</td></tr>'}</tbody></table>
    <h4 style="margin:.75rem 0 .4rem;font-size:.9rem;color:var(--s8)">Loans</h4>
    <table style="margin-bottom:1rem"><thead><tr><th>Amount</th><th>Rate</th><th>Term</th><th>Monthly</th><th>Status</th></tr></thead><tbody>${loans||'<tr><td colspan="5" class="em">No loans</td></tr>'}</tbody></table>
    <h4 style="margin:.75rem 0 .4rem;font-size:.9rem;color:var(--s8)">Recent Transactions</h4>
    <table style="margin-bottom:1rem"><thead><tr><th>Type</th><th>Amount</th><th>Description</th><th>Date</th><th>Status</th></tr></thead><tbody>${txs||'<tr><td colspan="5" class="em">No transactions</td></tr>'}</tbody></table>
    <h4 style="margin:.75rem 0 .4rem;font-size:.9rem;color:var(--s8)">Admin Notes <button class="btn bg bsm" onclick="addNotePrompt(${d.id})">+ Add Note</button></h4>
    <div>${notes||'<div class="em">No notes</div>'}</div>
  `;
  openModal('userDetail')
}
async function addNotePrompt(uid){
  const note=prompt('Enter note:');
  if(!note||!note.trim())return;
  const r=await API.post({action:'add_note',user_id:uid,note:note.trim()});
  if(r.success){tst('Note added','scs');viewUser(uid)}else tst(r.error||'Error','err')
}

// ─── LOANS ───
let curLoanTab='pending';
function switchLoanTab(s,btn){
  curLoanTab=s;
  document.querySelectorAll('#loanTabs .tbb').forEach(b=>b.classList.remove('act'));
  btn.classList.add('act');loadLoans()
}
async function loadLoans(){
  const s=curLoanTab;
  const loans=await API.get('loans',s!=='all'?{status:s}:{status:'all'});
  const tb=document.getElementById('loansTb');
  document.getElementById('loanTitle').textContent=(s==='all'?'All':s.charAt(0).toUpperCase()+s.slice(1))+' Loans';
  const pc=(await API.get('stats')).pendingLoans.total;
  document.getElementById('loanBadge').textContent=pc;
  if(!loans.length){tb.innerHTML='<tr><td colspan="8" class="em">No loans found</td></tr>';return}
  tb.innerHTML=loans.map(l=>{
    const pen=l.status==='pending';
    return `<tr>
      <td>${esc(l.first_name+' '+l.last_name)}<br><span style="font-size:.72rem;color:var(--s4)">${esc(l.email)}</span></td>
      <td><strong>${$(l.amount)}</strong></td>
      <td>${esc(l.purpose||'—')}</td>
      <td>${l.interest_rate}%</td>
      <td>${l.term_months}mo</td>
      <td>${$(l.monthly_payment)}</td>
      <td><span class="st ${l.status}">${l.status}</span></td>
      <td style="white-space:nowrap">
        ${pen?`<button class="btn bs bsm" onclick="approveLoan(${l.id})">Approve</button><button class="btn bd bsm" onclick="openRejectLoan(${l.id})">Reject</button>`:''}
        <button class="btn bg bsm" onclick="viewLoan(${l.id})">View</button>
      </td>
    </tr>`
  }).join('');
}
async function approveLoan(id){
  const r=await API.post({action:'approve_loan',loan_id:id});
  if(r.error){tst(r.error,'err');return}
  tst('Loan approved! Monthly: '+$(r.monthly_payment)+' Total: '+$(r.total_repayment),'scs')
  loadLoans();loadStats()
}
function openRejectLoan(id){document.getElementById('rejectLoanId').value=id;document.getElementById('rejectLoanNote').value='';openModal('rejectLoan')}
async function rejectLoanConfirm(){
  const id=document.getElementById('rejectLoanId').value,note=document.getElementById('rejectLoanNote').value.trim()||'Not approved';
  const r=await API.post({action:'reject_loan',loan_id:id,note});
  if(r.success){tst('Loan rejected','scs');closeModal('rejectLoan');loadLoans()}else tst(r.error||'Error','err')
}
async function viewLoan(id){
  const d=await API.get('loan_detail',{loan_id:id});
  if(d.error){tst(d.error,'err');return}
  const n=esc(d.first_name+' '+d.last_name);
  let pmts='';
  (d.payments||[]).forEach(p=>{
    const isOverdue=p.status==='pending'&&new Date(p.due_date)<new Date();
    const st=isOverdue?'overdue':p.status;
    pmts+=`<tr><td>#${p.id}</td><td>${$(p.amount)}</td><td>${p.due_date}</td><td>${p.paid_date||'—'}</td><td><span class="st ${st}">${isOverdue?'overdue':p.status}</span></td>
    ${p.status==='pending'?`<td><button class="btn bs bsm" onclick="recordPayment(${p.id})">Record</button></td>`:'<td>—</td>'}</tr>`
  });
  document.getElementById('ldTitle').textContent='Loan #'+d.id+' — '+n;
  document.getElementById('ldBody').innerHTML=`
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:1rem">
      <div class="fg"><label>Amount</label><input value="${$(d.amount)}" disabled></div>
      <div class="fg"><label>Interest Rate</label><input value="${d.interest_rate}%" disabled></div>
      <div class="fg"><label>Term</label><input value="${d.term_months} months" disabled></div>
      <div class="fg"><label>Monthly Payment</label><input value="${$(d.monthly_payment)}" disabled></div>
      <div class="fg"><label>Total Repayment</label><input value="${$(d.total_repayment)}" disabled></div>
      <div class="fg"><label>Paid So Far</label><input value="${$(d.paid_so_far)}" disabled></div>
    </div>
    <div class="fg"><label>Purpose</label><input value="${esc(d.purpose||'—')}" disabled></div>
    ${d.admin_note?`<div class="fg"><label>Admin Note</label><textarea disabled>${esc(d.admin_note)}</textarea></div>`:''}
    <h4 style="margin:.75rem 0 .4rem;font-size:.9rem;color:var(--s8)">Payment Schedule</h4>
    <table><thead><tr><th>#</th><th>Amount</th><th>Due</th><th>Paid</th><th>Status</th><th>Action</th></tr></thead><tbody>${pmts||'<tr><td colspan="6" class="em">No payments</td></tr>'}</tbody></table>
  `;
  openModal('loanDetail')
}
async function recordPayment(pid){
  if(!confirm('Record this payment as paid?'))return;
  const r=await API.post({action:'record_loan_payment',payment_id:pid});
  if(r.success){tst('Payment recorded!','scs');viewLoan(document.querySelector('#ldTitle').textContent.match(/\d+/)[0]||0);loadLoans()}else tst(r.error||'Error','err')
}

// ─── TRANSFERS ───
let curTrfTab='pending';
function switchTrfTab(s,btn){curTrfTab=s;document.querySelectorAll('#trfTabs .tbb').forEach(b=>b.classList.remove('act'));btn.classList.add('act');loadTrf()}
async function loadTrf(){
  const s=curTrfTab;
  const tfs=s==='all'?await API.get('all_transfers'):await API.get('transfers',{status:s});
  const tb=document.getElementById('trfTb');
  document.getElementById('trfTitle').textContent=(s==='all'?'All':s.charAt(0).toUpperCase()+s.slice(1))+' Transfers';
  const pc=(await API.get('stats')).pending.total;
  document.getElementById('trfBadge').textContent=pc;
  if(!tfs.length){tb.innerHTML='<tr><td colspan="7" class="em">No transfers</td></tr>';return}
  tb.innerHTML=tfs.map(t=>{
    const pen=t.status==='pending';
    return `<tr><td>${esc(t.first_name+' '+t.last_name)}</td>
    <td style="font-family:monospace;font-size:.8rem">${t.to_account_number}</td>
    <td><strong>${$(t.amount)}</strong></td>
    <td>${esc(t.description||'—')}</td>
    <td>${new Date(t.created_at).toLocaleDateString()}</td>
    <td><span class="st ${t.status}">${t.status}</span></td>
    <td style="white-space:nowrap">
      ${pen?`<button class="btn bs bsm" onclick="approveTrf(${t.id})">✓</button><button class="btn bd bsm" onclick="openRejectTrf(${t.id})">✗</button>`:'<span style="color:var(--s4);font-size:.78rem">—</span>'}
    </td></tr>`
  }).join('');
}
async function approveTrf(id){const r=await API.post({action:'approve_transfer',transfer_id:id});if(r.error){tst(r.error,'err');return}tst(r.message,'scs');loadTrf();loadStats()}
function openRejectTrf(id){document.getElementById('rejectTransferId').value=id;document.getElementById('rejectReason').value='';openModal('rejectTransfer')}
async function rejectTrfConfirm(){const id=document.getElementById('rejectTransferId').value,reason=document.getElementById('rejectReason').value.trim()||'Rejected';const r=await API.post({action:'reject_transfer',transfer_id:id,reason});tst(r.message,'scs');closeModal('rejectTransfer');loadTrf()}

// ─── TRANSACTIONS ───
async function loadTx(){
  const type=document.getElementById('txType').value,st=document.getElementById('txSt').value,acct=document.getElementById('txSearch').value.trim();
  const p={};if(type)p.type=type;if(st)p.status=st;if(acct)p.account=acct;
  const txs=await API.get('transactions',p);
  const tb=document.getElementById('txTb');
  if(!txs.length){tb.innerHTML='<tr><td colspan="8" class="em">No transactions</td></tr>';return}
  tb.innerHTML=txs.map(t=>`<tr><td style="font-family:monospace;font-size:.8rem">${t.account_number}</td><td>${esc(t.first_name+' '+t.last_name)}</td><td><span class="st ${t.status}">${t.type.replace(/_/g,' ')}</span></td><td><strong>${$(t.amount)}</strong></td><td>${esc(t.description||'—')}</td><td style="font-family:monospace;font-size:.75rem">${t.reference||'—'}</td><td style="font-size:.8rem">${new Date(t.created_at).toLocaleString()}</td><td><span class="st ${t.status}">${t.status}</span></td></tr>`).join('')
}
function exportCSV(){
  const r=[['Account','Name','Type','Amount','Description','Reference','Date','Status']];
  document.querySelectorAll('#txTb tr').forEach(tr=>{const c=tr.querySelectorAll('td');if(c.length>=8)r.push([...c].map(x=>x.innerText))});
  const csv=r.map(r=>r.map(c=>'"'+c.replace(/"/g,'""')+'"').join(',')).join('\n');
  const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));a.download='transactions.csv';a.click();
  tst('CSV exported!','scs')
}

// ─── ANNOUNCEMENTS ───
async function loadAnnounce(){
  const a=await API.get('announcements');
  const el=document.getElementById('announceList');
  if(!a.length){el.innerHTML='<div style="padding:2rem;text-align:center;color:var(--s4)">No announcements yet</div>';return}
  el.innerHTML=a.map(x=>`
    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--s1);display:flex;justify-content:space-between;align-items:center">
      <div><strong style="color:var(--s8)">${esc(x.title)}</strong>
      ${x.priority==='urgent'?'<span class="st urg">URGENT</span>':x.priority==='high'?'<span class="st rej">HIGH</span>':''}
      <br><span style="font-size:.82rem;color:var(--s6)">${esc(x.message)}</span>
      <br><span style="font-size:.72rem;color:var(--s4)">by ${esc(x.first_name+' '+x.last_name)} · ${new Date(x.created_at).toLocaleDateString()}</span></div>
      <div style="display:flex;gap:.5rem;align-items:center">
        <span class="st ${x.is_active?'act':'sus'}">${x.is_active?'Active':'Inactive'}</span>
        <button class="btn bg bsm" onclick="toggleAnnounce(${x.id})">Toggle</button>
        <button class="btn bd bsm" onclick="deleteAnnounce(${x.id})">×</button>
      </div>
    </div>`).join('');
}
async function toggleAnnounce(id){const r=await API.post({action:'toggle_announcement',announcement_id:id});if(r.success){tst('Toggled','scs');loadAnnounce()}}
async function deleteAnnounce(id){if(!confirm('Delete this announcement?'))return;const r=await API.post({action:'delete_announcement',announcement_id:id});if(r.success){tst('Deleted','scs');loadAnnounce()}}
async function createAnnouncement(){
  const title=document.getElementById('aTitle').value.trim(),message=document.getElementById('aMessage').value.trim(),priority=document.getElementById('aPriority').value;
  if(!title||!message){tst('Title and message required','err');return}
  const r=await API.post({action:'create_announcement',title,message,priority});
  if(r.success){tst('Announcement published!','scs');closeModal('announce');document.getElementById('announceForm').reset();loadAnnounce()}else tst(r.error||'Error','err')
}

// ─── ACTIVITY ───
async function loadActivity(){
  const l=await API.get('activity');
  const tb=document.getElementById('actTb');
  if(!l.length){tb.innerHTML='<tr><td colspan="3" class="em">No activity</td></tr>';return}
  tb.innerHTML=l.map(x=>`<tr><td>${esc(x.first_name+' '+x.last_name)}</td><td>${esc(x.description)}</td><td style="font-size:.8rem">${new Date(x.created_at).toLocaleString()}</td></tr>`).join('')
}

// ─── SETTINGS ───
async function loadSettings(){
  const s=await API.get('settings');
  document.getElementById('s_bank_name').value=s.bank_name||'';
  document.getElementById('s_currency').value=s.currency||'USD';
  document.getElementById('s_support_email').value=s.support_email||'';
  document.getElementById('s_support_phone').value=s.support_phone||'';
  document.getElementById('s_default_interest_rate').value=s.default_interest_rate||'5.00';
  document.getElementById('s_max_loan_term').value=s.max_loan_term||'12';
  document.getElementById('s_transfer_auth_required').value=s.transfer_auth_required||'false';
  document.getElementById('s_auth_type').value=s.auth_type||'none';
  const k=(s.auth_type||'none')+'_code';
  document.getElementById('s_auth_code').value=s[k]||'';
}
document.getElementById('settingsForm').addEventListener('submit',async e=>{
  e.preventDefault();
  const at=document.getElementById('s_auth_type').value;
  const sets={bank_name:document.getElementById('s_bank_name').value,currency:document.getElementById('s_currency').value,support_email:document.getElementById('s_support_email').value,support_phone:document.getElementById('s_support_phone').value,default_interest_rate:document.getElementById('s_default_interest_rate').value,max_loan_term:document.getElementById('s_max_loan_term').value,transfer_auth_required:document.getElementById('s_transfer_auth_required').value,auth_type:at};
  sets[at+'_code']=document.getElementById('s_auth_code').value;
  const d=new URLSearchParams();d.set('action','update_settings');
  for(const[k,v]of Object.entries(sets))d.append('settings['+k+']',v);
  const r=await fetch('../api/admin.php',{method:'POST',body:d});const j=await r.json();
  if(j.error){tst(j.error,'err');return}tst('Settings saved!','scs')
});

// ─── INIT ───
loadStats();loadCharts();loadWidgets();
</script>
</body>
</html>
