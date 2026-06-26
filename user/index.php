<?php
require_once __DIR__ . '/../includes/config.php';
requireAuth();
if ($_SESSION['role'] !== 'user') { header('Location: ../admin/'); exit; }
$user = getCurrentUser();
$account = getUserAccount($user['id']);
if (!$account) { die('Account not found. Contact support.'); }

$db = getDB();
// Get legacy plan
$plan = $db->prepare("SELECT status FROM legacy_plans WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$plan->execute([$user['id']]); $hasPlan = $plan->fetch();
// Count beneficiaries
$bens = $db->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(percentage),0) as total FROM beneficiaries WHERE user_id = ? AND status='active'");
$bens->execute([$user['id']]); $benData = $bens->fetch();
// Count loan accounts
$laCnt = $db->prepare("SELECT COUNT(*) as cnt FROM loan_accounts WHERE user_id = ?");
$laCnt->execute([$user['id']]); $loanAcctCount = $laCnt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Legacy National Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--t9:#0a2e2a;--t8:#0d5555;--t7:#0f766e;--t6:#0d9488;--t5:#14b8a6;--t4:#2dd4bf;--t0:#f0fdfa;--s9:#0f172a;--s8:#1e293b;--s7:#334155;--s6:#475569;--s5:#64748b;--s4:#94a3b8;--s3:#cbd5e1;--s2:#e2e8f0;--s1:#f1f5f9;--s0:#f8fafc;--wh:#fff;--sm:0 1px 3px rgba(0,0,0,.06);--md:0 4px 20px rgba(0,0,0,.08);--lg:0 10px 40px rgba(0,0,0,.12);--r8:8px;--r12:12px;--r16:16px;--tr:all .3s cubic-bezier(.4,0,.2,1)}
body{font-family:Inter,sans-serif;background:var(--s0);color:var(--s6);-webkit-font-smoothing:antialiased}
.app{display:flex;min-height:100vh}
.sb{width:250px;background:var(--wh);border-right:1px solid var(--s2);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50;transition:var(--tr)}
.sbb{padding:1.25rem 1.5rem;border-bottom:1px solid var(--s1);font-family:'Playfair Display',serif;font-size:1.25rem;font-weight:700;color:var(--s9)}
.sbb span{color:var(--t6)}
.sbn{padding:.25rem 0;flex:1;overflow-y:auto}
.sbn .lb{font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:var(--s4);padding:.75rem 1.5rem .25rem}
.sbn a{display:flex;align-items:center;gap:.7rem;padding:.6rem 1.5rem;color:var(--s6);text-decoration:none;font-size:.85rem;font-weight:500;transition:var(--tr);border-left:3px solid transparent}
.sbn a:hover{background:var(--s0);color:var(--s9)}
.sbn a.act{border-left-color:var(--t5);background:var(--t0);color:var(--t7)}
.sbn a svg{width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:2;flex-shrink:0}
.sbf{padding:.75rem 1.5rem;border-top:1px solid var(--s1)}
.sbf .u{display:flex;align-items:center;gap:.7rem}
.sbf .av{width:34px;height:34px;border-radius:50%;background:var(--t6);color:var(--wh);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem}
.sbf .nm{font-size:.82rem;font-weight:600;color:var(--s9)}
.sbf .em{font-size:.72rem;color:var(--s5)}
.sbf .lo{margin-top:.6rem;display:flex;align-items:center;gap:.4rem;color:var(--s4);font-size:.82rem;text-decoration:none;transition:var(--tr)}
.sbf .lo:hover{color:#ef4444}
.mn{margin-left:250px;flex:1;padding:1.5rem 2rem}
.tp{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--s2)}
.tp h1{font-size:1.5rem;color:var(--s9)}
.tp .dt{font-size:.85rem;color:var(--s5)}
/* Balance card */
.bc{background:linear-gradient(135deg,var(--t9),var(--t8));border-radius:var(--r16);padding:1.75rem 2rem;color:var(--wh);margin-bottom:1.5rem;position:relative;overflow:hidden}
.bc::before{content:'';position:absolute;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(45,212,191,.12) 0%,transparent 70%);top:-100px;right:-50px}
.bc .bl{font-size:.82rem;opacity:.8;margin-bottom:.2rem}
.bc .ba{font-size:2.25rem;font-weight:800;font-family:'Playfair Display',serif}
.bc .bi{display:flex;gap:1.5rem;margin-top:1.25rem;font-size:.82rem;opacity:.85}
.bc .bi span{display:flex;flex-direction:column}
.bc .bi .al{font-size:.68rem;text-transform:uppercase;letter-spacing:.03em;opacity:.6}
/* Quick actions */
.qag{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.6rem;margin-bottom:1.5rem}
.qa{background:var(--wh);border-radius:var(--r8);padding:.75rem 1rem;box-shadow:var(--sm);border:1px solid var(--s1);text-align:center;cursor:pointer;transition:var(--tr);text-decoration:none;color:var(--s6)}
.qa:hover{transform:translateY(-2px);box-shadow:var(--md);border-color:var(--t4)}
.qa svg{width:22px;height:22px;stroke:var(--t6);fill:none;stroke-width:2;margin-bottom:.3rem}
.qa .ql{font-size:.78rem;font-weight:600;color:var(--s8)}
.qa .qs{font-size:.68rem;color:var(--s4)}
/* Inheritance summary strip */
.is{background:var(--wh);border-radius:var(--r12);padding:1.15rem 1.5rem;box-shadow:var(--sm);border:1px solid var(--s1);margin-bottom:1.5rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:1rem}
.is .ii{text-align:center}
.is .ii .iv{font-size:1.3rem;font-weight:800;color:var(--s9)}
.is .ii .il{font-size:.72rem;color:var(--s5);text-transform:uppercase;letter-spacing:.03em}
/* Sections */
.sec{background:var(--wh);border-radius:var(--r12);box-shadow:var(--sm);border:1px solid var(--s1);margin-bottom:1.25rem;overflow:hidden}
.sh{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;border-bottom:1px solid var(--s1);flex-wrap:wrap;gap:.6rem}
.sh h2{font-size:1rem;color:var(--s9);font-weight:700}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:.55rem 1.1rem;font-size:.84rem;border-bottom:1px solid var(--s1)}
th{font-weight:600;color:var(--s5);font-size:.74rem;text-transform:uppercase;letter-spacing:.03em;background:var(--s0)}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--s0)}
.st{display:inline-flex;align-items:center;gap:.25rem;font-size:.74rem;font-weight:600;padding:.15rem.55rem;border-radius:999px}
.st.act,.st.cmp,.st.paid{background:#f0fdf4;color:#16a34a}
.st.pen{background:#fefce8;color:#ca8a04}
.st.sus,.st.fail,.st.rej{background:#fef2f2;color:#dc2626}
.cr{color:#16a34a}.db{color:#dc2626}
.btn{padding:.4rem.85rem;border-radius:var(--r8);font-weight:600;font-size:.82rem;border:none;cursor:pointer;transition:var(--tr);font-family:inherit;display:inline-flex;align-items:center;gap:.35rem}
.bsm{padding:.3rem.65rem;font-size:.74rem}
.bp{background:var(--t6);color:var(--wh)}.bp:hover{background:var(--t7)}
.bd{background:#ef4444;color:var(--wh)}.bd:hover{background:#dc2626}
.bs{background:#16a34a;color:var(--wh)}.bs:hover{background:#15803d}
.bg{background:transparent;color:var(--s6);border:1px solid var(--s2)}.bg:hover{background:var(--s0)}
.btn:disabled{opacity:.5;cursor:not-allowed}
.mo{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:100;align-items:center;justify-content:center;padding:1rem}
.mo.sho{display:flex}
.mod{background:var(--wh);border-radius:var(--r16);width:100%;max-width:540px;max-height:85vh;overflow-y:auto;box-shadow:var(--lg)}
.mh{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.25rem;border-bottom:1px solid var(--s1);position:sticky;top:0;background:var(--wh);z-index:1}
.mh h3{font-size:1.05rem;color:var(--s9)}
.mc{background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--s4);line-height:1}
.mc:hover{color:var(--s6)}
.mb{padding:1.25rem}
.mf{display:flex;justify-content:flex-end;gap:.6rem;padding:.85rem 1.25rem;border-top:1px solid var(--s1)}
.fg{margin-bottom:.85rem}
.fg label{display:block;font-size:.8rem;font-weight:600;color:var(--s7);margin-bottom:.25rem}
.fg input,.fg select,.fg textarea{width:100%;background:var(--wh);border:1.5px solid var(--s2);border-radius:var(--r8);padding:.5rem.75rem;font-size:.84rem;color:var(--s8);transition:var(--tr);font-family:inherit}
.fg input:focus,.fg select:focus,.fg textarea:focus{outline:none;border-color:var(--t5);box-shadow:0 0 0 3px rgba(20,184,166,.1)}
.fg textarea{resize:vertical;min-height:50px}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.ib{background:var(--t0);border:1px solid var(--t4);border-radius:var(--r8);padding:.65rem 1rem;font-size:.82rem;color:var(--t8);margin-bottom:1rem}
.toast{position:fixed;bottom:1.5rem;right:1.5rem;background:var(--s9);color:var(--wh);padding:.75rem 1.15rem;border-radius:var(--r8);font-size:.85rem;box-shadow:var(--lg);z-index:200;transform:translateY(100px);opacity:0;transition:var(--tr)}
.toast.sho{transform:translateY(0);opacity:1}
.toast.scs{background:#16a34a}.toast.err{background:#dc2626}
.em{text-align:center;padding:2rem;color:var(--s4);font-size:.85rem}
.tbs{display:flex;gap:0;border-bottom:1px solid var(--s2);margin-bottom:1rem}
.tbb{padding:.5rem 1rem;font-size:.8rem;font-weight:600;border:none;background:none;cursor:pointer;color:var(--s5);border-bottom:2px solid transparent;transition:var(--tr);font-family:inherit}
.tbb:hover{color:var(--s7)}
.tbb.act{color:var(--t6);border-bottom-color:var(--t6)}
@media(max-width:768px){
  .sb{transform:translateX(-100%)}
  .sb.op{transform:translateX(0)}
  .mn{margin-left:0;padding:1.25rem}
  .bc .bi{flex-direction:column;gap:.5rem}
  .qag{grid-template-columns:1fr 1fr}
  .fr{grid-template-columns:1fr}
  .tw{overflow-x:auto}
}
</style>
</head>
<body>

<div class="app">
  <aside class="sb" id="sb">
    <div class="sbb" style="display:flex;align-items:center;gap:10px"><img src="../images/legacy-logo-green.png" style="height:26px"></div>
    <nav class="sbn">
      <div class="lb">My Account</div>
      <a href="#" class="act" data-t="overview"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Overview</a>
      <a href="#" data-t="transfer"><svg viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>Transfer</a>
      <a href="#" data-t="history"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>History</a>
      <div class="lb">Legacy Planning</div>
      <a href="#" data-t="beneficiaries"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>Beneficiaries</a>
      <a href="#" data-t="legacy"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Legacy Plan</a>
      <div class="lb">Lending</div>
      <a href="#" data-t="loans"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>Loan Accounts</a>
      <div class="lb">Settings</div>
      <a href="#" data-t="profile"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Profile</a>
    </nav>
    <div class="sbf">
      <div class="u"><div class="av"><?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?></div><div><div class="nm"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div><div class="em"><?= htmlspecialchars($user['email']) ?></div></div></div>
      <a href="../api/logout.php" class="lo"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Sign Out</a>
    </div>
  </aside>

  <main class="mn">
    <div class="tp">
      <div><h1 id="pgTitle">Overview</h1><div class="dt" id="cdate"></div></div>
      <button class="btn bg bsm" onclick="document.getElementById('sb').classList.toggle('op')">☰</button>
    </div>

    <!-- ═══ OVERVIEW ═══ -->
    <div id="t-overview" class="tc">
      <div class="bc">
        <div class="bl">Available Balance</div>
        <div class="ba">$<?= number_format($account['balance'],2) ?></div>
        <div class="bi">
          <span><span class="al">Account</span><?= htmlspecialchars($account['account_number']) ?></span>
          <span><span class="al">Type</span><?= htmlspecialchars($account['account_type']) ?></span>
          <span><span class="al">Routing</span><?= htmlspecialchars($account['routing_number']) ?></span>
        </div>
      </div>
      <!-- Inheritance Summary -->
      <div class="is">
        <div class="ii"><div class="iv"><?= $benData['cnt'] ?: 0 ?></div><div class="il">Beneficiaries</div></div>
        <div class="ii"><div class="iv"><?= $hasPlan ? 'Active' : 'Not Set' ?></div><div class="il">Legacy Plan</div></div>
        <div class="ii"><div class="iv"><?= $loanAcctCount ?: 0 ?></div><div class="il">Loan Accounts</div></div>
        <div class="ii"><div class="iv">$<?= number_format($account['balance'],0) ?></div><div class="il">Inheritance Value</div></div>
      </div>
      <div class="qag">
        <div class="qa" onclick="switchTab('transfer')"><svg viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg><div class="ql">Send Money</div></div>
        <div class="qa" onclick="openModal('deposit')"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><div class="ql">Deposit</div></div>
        <div class="qa" onclick="switchTab('beneficiaries')"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><div class="ql">Beneficiaries</div></div>
        <div class="qa" onclick="switchTab('loans')"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg><div class="ql">Loan Accounts</div></div>
      </div>
      <div class="sec">
        <div class="sh"><h2>Recent Transactions</h2></div>
        <div class="tw"><table><thead><tr><th>Type</th><th>Amount</th><th>Description</th><th>Date</th><th>Status</th></tr></thead><tbody id="recentTx"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ TRANSFER ═══ -->
    <div id="t-transfer" class="tc" style="display:none">
      <div class="sec">
        <div class="sh"><h2>Send Money</h2></div>
        <div style="padding:1.25rem">
          <div class="ib">Transfers require admin approval before processing.</div>
          <form id="transferForm" onsubmit="return false">
            <div class="fr">
              <div class="fg"><label>Recipient Account</label><input id="t_to_account" placeholder="10XXXXXXXXXX" required></div>
              <div class="fg"><label>Recipient Name</label><input id="t_to_name" placeholder="John Doe"></div>
            </div>
            <div class="fr">
              <div class="fg"><label>Amount ($)</label><input type="number" step="0.01" min="0.01" id="t_amount" required></div>
              <div class="fg"><label>Description</label><input id="t_description" placeholder="What is this for?"></div>
            </div>
            <button type="submit" class="btn bp" id="transferBtn">Submit Transfer</button>
          </form>
        </div>
      </div>
      <div class="sec">
        <div class="sh"><h2>Pending Transfers</h2></div>
        <div class="tw"><table><thead><tr><th>To Account</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody id="pendingTrf"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ HISTORY ═══ -->
    <div id="t-history" class="tc" style="display:none">
      <div class="sec">
        <div class="sh"><h2>Transaction History</h2></div>
        <div class="tw"><table><thead><tr><th>Type</th><th>Amount</th><th>Description</th><th>Ref</th><th>Date</th><th>Status</th></tr></thead><tbody id="allTx"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ BENEFICIARIES ═══ -->
    <div id="t-beneficiaries" class="tc" style="display:none">
      <div class="sec">
        <div class="sh"><h2>Your Beneficiaries</h2><button class="btn bp bsm" onclick="openModal('addBen')">+ Add</button></div>
        <div class="ib" style="margin:1rem 1.25rem">Designate who inherits from your accounts. Set percentage splits for each person.</div>
        <div class="tw"><table><thead><tr><th>Name</th><th>Relationship</th><th>Allocation</th><th>Account</th><th>Status</th><th>Action</th></tr></thead><tbody id="benTb"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ LEGACY PLAN ═══ -->
    <div id="t-legacy" class="tc" style="display:none">
      <div class="sec">
        <div class="sh"><h2>Your Legacy Plan</h2><button class="btn bp bsm" id="legacyEditBtn" onclick="toggleLegacyForm()">Edit Plan</button></div>
        <div style="padding:1.25rem">
          <div id="legacyDisplay"></div>
          <form id="legacyForm" style="display:none" onsubmit="return false">
            <div class="fg"><label>Plan Name</label><input id="lp_name" value="My Legacy Plan"></div>
            <div class="fg"><label>Distribution Instructions</label><textarea id="lp_instructions" rows="3" placeholder="How should your assets be distributed? Any special conditions?"></textarea></div>
            <div class="fr">
              <div class="fg"><label>Executor Name</label><input id="lp_exec_name" placeholder="Person who will execute your will"></div>
              <div class="fg"><label>Executor Email</label><input id="lp_exec_email" type="email"></div>
            </div>
            <div class="fg"><label>Executor Phone</label><input id="lp_exec_phone"></div>
            <div class="fg"><label>Funeral/Memorial Wishes</label><textarea id="lp_funeral" rows="2" placeholder="Optional: any wishes for your memorial service"></textarea></div>
            <button type="submit" class="btn bp" onclick="saveLegacyPlan()">Save Legacy Plan</button>
          </form>
        </div>
      </div>
    </div>

    <!-- ═══ LOANS ═══ -->
    <div id="t-loans" class="tc" style="display:none">
      <div class="sec">
        <div class="sh"><h2>Loan Accounts</h2><button class="btn bp bsm" onclick="openModal('createLoanAcct')">+ New Loan</button></div>
        <div class="ib" style="margin:1rem 1.25rem">Loan accounts are separate from your main account. Apply for a loan, get approved by admin, and receive funds.</div>
        <div class="tw"><table><thead><tr><th>Account #</th><th>Type</th><th>Principal</th><th>Rate</th><th>Balance</th><th>Monthly</th><th>Paid</th><th>Status</th><th>Action</th></tr></thead><tbody id="loanAcctTb"></tbody></table></div>
      </div>
      <div class="sec">
        <div class="sh"><h2>Loan Applications</h2></div>
        <div class="tw"><table><thead><tr><th>Amount</th><th>Purpose</th><th>Rate</th><th>Term</th><th>Monthly</th><th>Status</th></tr></thead><tbody id="userLoanApps"></tbody></table></div>
      </div>
    </div>

    <!-- ═══ PROFILE ═══ -->
    <div id="t-profile" class="tc" style="display:none">
      <div class="sec">
        <div class="sh"><h2>Profile</h2></div>
        <div style="padding:1.25rem">
          <div class="fr">
            <div class="fg"><label>First Name</label><input value="<?= htmlspecialchars($user['first_name']) ?>" disabled></div>
            <div class="fg"><label>Last Name</label><input value="<?= htmlspecialchars($user['last_name']) ?>" disabled></div>
          </div>
          <div class="fg"><label>Email</label><input value="<?= htmlspecialchars($user['email']) ?>" disabled></div>
          <div class="fg"><label>Phone</label><input value="<?= htmlspecialchars($user['phone'] ?? 'Not provided') ?>" disabled></div>
          <div class="fg"><label>Member Since</label><input value="<?= date('F j, Y',strtotime($user['created_at'])) ?>" disabled></div>
        </div>
      </div>
      <div class="sec">
        <div class="sh"><h2>Account Details</h2></div>
        <div style="padding:1.25rem">
          <div class="fr">
            <div class="fg"><label>Account Number</label><input value="<?= htmlspecialchars($account['account_number']) ?>" disabled style="font-family:monospace"></div>
            <div class="fg"><label>Routing Number</label><input value="<?= htmlspecialchars($account['routing_number']) ?>" disabled style="font-family:monospace"></div>
          </div>
          <div class="fg"><label>Account Type</label><input value="<?= htmlspecialchars($account['account_type']) ?>" disabled></div>
          <div class="fg"><label>Balance</label><input value="$<?= number_format($account['balance'],2) ?>" disabled style="font-weight:700;color:var(--t6)"></div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- MODALS -->
<div class="mo" id="m-deposit"><div class="mod" style="max-width:420px">
  <div class="mh"><h3>Make a Deposit</h3><button class="mc" onclick="closeModal('deposit')">×</button></div>
  <div class="mb">
    <div class="fg"><label>Amount ($)</label><input type="number" step="0.01" min="0.01" id="d_amount" required></div>
    <div class="fg"><label>Description</label><input id="d_description" placeholder="e.g. Direct deposit"></div>
  </div>
  <div class="mf"><button class="btn bg" onclick="closeModal('deposit')">Cancel</button><button class="btn bp" onclick="submitDeposit()">Deposit</button></div>
</div></div>

<div class="mo" id="m-addBen"><div class="mod" style="max-width:480px">
  <div class="mh"><h3>Add Beneficiary</h3><button class="mc" onclick="closeModal('addBen')">×</button></div>
  <div class="mb">
    <form id="benForm" onsubmit="return false">
      <div class="fr">
        <div class="fg"><label>Full Name *</label><input id="ben_name" required></div>
        <div class="fg"><label>Relationship</label><input id="ben_rel" placeholder="e.g. Child, Spouse"></div>
      </div>
      <div class="fr">
        <div class="fg"><label>Email</label><input type="email" id="ben_email"></div>
        <div class="fg"><label>Phone</label><input id="ben_phone"></div>
      </div>
      <div class="fr">
        <div class="fg"><label>Allocation (%) *</label><input type="number" step="0.01" min="1" max="100" id="ben_pct" required></div>
        <div class="fg"><label>Age (if minor)</label><input type="number" id="ben_age" placeholder="Optional"></div>
      </div>
      <div class="fg"><label>Account</label><select id="ben_account"></select></div>
    </form>
  </div>
  <div class="mf"><button class="btn bg" onclick="closeModal('addBen')">Cancel</button><button class="btn bs" onclick="addBeneficiary()">Add Beneficiary</button></div>
</div></div>

<div class="mo" id="m-createLoanAcct"><div class="mod" style="max-width:480px">
  <div class="mh"><h3>Apply for a Loan</h3><button class="mc" onclick="closeModal('createLoanAcct')">×</button></div>
  <div class="mb">
    <div class="ib">Loan applications are reviewed by admin. Once approved, funds are disbursed to your main account.</div>
    <form id="loanAcctForm" onsubmit="return false">
      <div class="fg"><label>Loan Type</label><select id="la_type"><option value="Personal">Personal</option><option value="Family">Family</option><option value="Education">Education</option><option value="Business">Business</option><option value="Emergency">Emergency</option></select></div>
      <div class="fr">
        <div class="fg"><label>Amount ($)</label><input type="number" step="0.01" min="1" id="la_amount" required></div>
        <div class="fg"><label>Term (months)</label><select id="la_term"><option value="3">3</option><option value="6">6</option><option value="12" selected>12</option><option value="24">24</option><option value="36">36</option><option value="48">48</option><option value="60">60</option></select></div>
      </div>
      <div class="fg"><label>Purpose</label><textarea id="la_purpose" rows="2" placeholder="What is this loan for?" required></textarea></div>
      <button type="submit" class="btn bp" id="laBtn" onclick="createLoanAccount()">Submit Application</button>
    </form>
  </div>
</div></div>

<div class="toast" id="toast"></div>

<script>
const API_BASE = '../api';
function apiFetch(endpoint, data = null) {
  const opts = {};
  if (data) { opts.method = 'POST'; opts.headers = { 'Content-Type': 'application/x-www-form-urlencoded' }; opts.body = new URLSearchParams(data).toString(); }
  return fetch(`${API_BASE}/${endpoint}`, opts).then(r => r.json());
}
function tst(m,t){const e=document.getElementById('toast');e.textContent=m;e.className='toast '+t;setTimeout(()=>e.classList.add('sho'),10);setTimeout(()=>e.classList.remove('sho'),3500)}
function openModal(id){document.getElementById('m-'+id).classList.add('sho')}
function closeModal(id){document.getElementById('m-'+id).classList.remove('sho')}
document.querySelectorAll('.mo').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('sho')}));
function esc(s){if(!s)return'—';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}
function $(n){return'$'+parseFloat(n||0).toLocaleString('en-US',{minimumFractionDigits:2})}
function switchTab(t){
  document.querySelectorAll('.sbn a').forEach(x=>x.classList.remove('act'));
  document.querySelector(`.sbn a[data-t="${t}"]`).classList.add('act');
  document.querySelectorAll('.tc').forEach(x=>x.style.display='none');
  document.getElementById('t-'+t).style.display='block';
  document.getElementById('pgTitle').textContent=document.querySelector(`.sbn a[data-t="${t}"]`)?.textContent.trim()||'Overview';
  if(window.innerWidth<=768)document.getElementById('sb').classList.remove('op');
  const loaders={overview:()=>{},transfer:()=>{},history:()=>{loadTx()},beneficiaries:()=>{loadBeneficiaries();loadBenAccounts()},legacy:()=>{loadLegacyPlan()},loans:()=>{loadLoanAccounts();loadUserLoanApps()}};
  if(loaders[t])loaders[t]();
}
document.querySelectorAll('.sbn a[data-t]').forEach(a=>{a.addEventListener('click',e=>{e.preventDefault();switchTab(a.dataset.t)})});
document.getElementById('cdate').textContent=new Date().toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});

// ─── TRANSACTIONS ───
async function loadTx(){
  const txs=await apiFetch('transactions.php');
  const ren=(tb,lim)=>{const its=lim?txs.slice(0,lim):txs;tb.innerHTML=its.map(t=>`<tr><td><span class="${t.type==='deposit'||t.type==='credit'||t.type==='transfer_in'?'cr':'db'}">${t.type.replace(/_/g,' ')}</span></td><td><strong class="${t.type==='deposit'||t.type==='credit'||t.type==='transfer_in'?'cr':'db'}">${(t.type==='deposit'||t.type==='credit'||t.type==='transfer_in'?'+':'-')+$(t.amount)}</strong></td><td>${esc(t.description)}</td>${lim?'':`<td style="font-family:monospace;font-size:.78rem">${t.reference||'—'}</td>`}<td>${new Date(t.created_at).toLocaleDateString()}</td><td><span class="st ${t.status}">${t.status}</span></td></tr>`).join('')};
  ren(document.getElementById('recentTx'),5);ren(document.getElementById('allTx'));
}

// ─── TRANSFER ───
async function loadPendingTrf(){const t=await apiFetch('transfers.php?type=pending');document.getElementById('pendingTrf').innerHTML=t.map(x=>`<tr><td style="font-family:monospace">${x.to_account_number}</td><td><strong>${$(x.amount)}</strong></td><td><span class="st ${x.status}">${x.status}</span></td><td>${new Date(x.created_at).toLocaleDateString()}</td></tr>`).join('')||'<tr><td colspan="4" class="em">No pending transfers</td></tr>'}

document.getElementById('transferForm').addEventListener('submit',async e=>{e.preventDefault();const btn=document.getElementById('transferBtn');btn.disabled=true;btn.textContent='Submitting...';const r=await apiFetch('transactions.php',{type:'transfer',to_account:document.getElementById('t_to_account').value.trim(),to_name:document.getElementById('t_to_name').value.trim(),amount:document.getElementById('t_amount').value,description:document.getElementById('t_description').value.trim()});if(r.error)tst(r.error,'err');else{tst('Transfer submitted!','scs');document.getElementById('transferForm').reset();loadPendingTrf()}btn.disabled=false;btn.textContent='Submit Transfer'});

async function submitDeposit(){const amt=document.getElementById('d_amount').value,desc=document.getElementById('d_description').value;if(!amt||parseFloat(amt)<=0){tst('Enter valid amount','err');return}const r=await apiFetch('transactions.php',{type:'deposit',amount:amt,description:desc});if(r.error){tst(r.error,'err');return}tst('Deposit successful!','scs');closeModal('deposit');location.reload()}

// ─── BENEFICIARIES ───
async function loadBeneficiaries(){
  const d=await apiFetch('legacy.php?action=beneficiaries');
  const tb=document.getElementById('benTb');
  if(!d.beneficiaries||!d.beneficiaries.length){tb.innerHTML='<tr><td colspan="6" class="em">No beneficiaries added yet</td></tr>';return}
  tb.innerHTML=d.beneficiaries.map(b=>`<tr><td><strong>${esc(b.full_name)}</strong>${b.email?'<br><span style="font-size:.74rem;color:var(--s4)">'+esc(b.email)+'</span>':''}</td><td>${esc(b.relationship||'—')}</td><td><strong>${b.percentage}%</strong></td><td style="font-family:monospace;font-size:.8rem">${b.account_number||'—'}</td><td><span class="st ${b.status}">${b.status}</span></td><td><button class="btn bd bsm" onclick="removeBen(${b.id})">Remove</button></td></tr>`).join('');
}
async function loadBenAccounts(){
  const sel=document.getElementById('ben_account');
  const u=await apiFetch('user.php');
  const accts=u.account?[u.account]:[];
  sel.innerHTML=accts.map(a=>`<option value="${a.id}">${a.account_number} (${$(a.balance)})</option>`).join('')||'<option value="">No accounts</option>';
}
async function addBeneficiary(){
  const name=document.getElementById('ben_name').value.trim(),rel=document.getElementById('ben_rel').value.trim(),email=document.getElementById('ben_email').value.trim(),phone=document.getElementById('ben_phone').value.trim(),pct=document.getElementById('ben_pct').value,age=document.getElementById('ben_age').value,acct=document.getElementById('ben_account').value;
  if(!name||!pct||!acct){tst('Name, percentage, and account required','err');return}
  const r=await apiFetch('legacy.php',{action:'add_beneficiary',account_id:acct,full_name:name,relationship:rel,email,phone,percentage:pct,age});
  if(r.error)tst(r.error,'err');else{tst(r.message,'scs');closeModal('addBen');document.getElementById('benForm').reset();loadBeneficiaries()}
}
async function removeBen(id){if(!confirm('Remove this beneficiary?'))return;const r=await apiFetch('legacy.php',{action:'remove_beneficiary',beneficiary_id:id});if(r.success){tst('Removed','scs');loadBeneficiaries()}}

// ─── LEGACY PLAN ───
let legacyLoaded=false;
async function loadLegacyPlan(){
  const p=await apiFetch('legacy.php?action=legacy_plan');
  const disp=document.getElementById('legacyDisplay');
  if(!p||p.status==='no_plan'){
    disp.innerHTML=`<div class="em">You haven't created a legacy plan yet. Click "Edit Plan" to get started.</div>`;
    document.getElementById('legacyEditBtn').textContent='Create Plan';
    return
  }
  document.getElementById('legacyEditBtn').textContent='Edit Plan';
  disp.innerHTML=`
    <div class="is" style="margin-bottom:1rem">
      <div class="ii"><div class="iv">${esc(p.plan_name)}</div><div class="il">Plan Name</div></div>
      <div class="ii"><div class="iv"><span class="st ${p.status}">${p.status}</span></div><div class="il">Status</div></div>
      <div class="ii"><div class="iv">${p.beneficiaries_summary?.count||0}</div><div class="il">Beneficiaries</div></div>
      <div class="ii"><div class="iv">${$(p.inheritance_value||0)}</div><div class="il">Inheritance Value</div></div>
    </div>
    ${p.instructions?`<div class="fg"><label>Distribution Instructions</label><textarea disabled rows="3">${esc(p.instructions)}</textarea></div>`:''}
    <div class="fr">
      ${p.executor_name?`<div class="fg"><label>Executor</label><input value="${esc(p.executor_name)}" disabled></div>`:''}
      ${p.executor_email?`<div class="fg"><label>Executor Email</label><input value="${esc(p.executor_email)}" disabled></div>`:''}
    </div>
    ${p.funeral_wishes?`<div class="fg"><label>Funeral Wishes</label><textarea disabled rows="2">${esc(p.funeral_wishes)}</textarea></div>`:''}
  `;
  document.getElementById('lp_name').value=p.plan_name||'My Legacy Plan';
  document.getElementById('lp_instructions').value=p.instructions||'';
  document.getElementById('lp_exec_name').value=p.executor_name||'';
  document.getElementById('lp_exec_email').value=p.executor_email||'';
  document.getElementById('lp_exec_phone').value=p.executor_phone||'';
  document.getElementById('lp_funeral').value=p.funeral_wishes||'';
}
function toggleLegacyForm(){
  const f=document.getElementById('legacyForm'),d=document.getElementById('legacyDisplay');
  if(f.style.display==='none'||!f.style.display){f.style.display='block';d.style.display='none'}else{f.style.display='none';d.style.display='block'}
}
async function saveLegacyPlan(){
  const r=await apiFetch('legacy.php',{action:'save_legacy_plan',plan_name:document.getElementById('lp_name').value,instructions:document.getElementById('lp_instructions').value,executor_name:document.getElementById('lp_exec_name').value,executor_email:document.getElementById('lp_exec_email').value,executor_phone:document.getElementById('lp_exec_phone').value,funeral_wishes:document.getElementById('lp_funeral').value});
  if(r.error)tst(r.error,'err');else{tst('Legacy plan saved!','scs');toggleLegacyForm();loadLegacyPlan()}
}

// ─── LOANS ───
async function loadLoanAccounts(){
  const las=await apiFetch('legacy.php?action=loan_accounts');
  const tb=document.getElementById('loanAcctTb');
  if(!las||!las.length){tb.innerHTML='<tr><td colspan="9" class="em">No loan accounts yet</td></tr>';return}
  tb.innerHTML=las.map(l=>{
    const ps=l.payments_summary||{};
    return `<tr><td style="font-family:monospace;font-size:.8rem">${l.account_number}</td><td>${esc(l.loan_type)}</td><td><strong>${$(l.principal)}</strong></td><td>${l.interest_rate}%</td><td><strong>${$(l.balance)}</strong></td><td>${$(l.monthly_payment)}</td><td>${$(ps.total_paid||0)}</td><td><span class="st ${l.status}">${l.status}</span></td>
    <td>${l.status==='pending'?`<button class="btn bs bsm" onclick="requestDisbursement(${l.id})">Disburse</button>`:'<span style="color:var(--s4);font-size:.75rem">—</span>'}</td></tr>`
  }).join('');
}
async function loadUserLoanApps(){
  const apps=await apiFetch('user_loan.php?action=list');
  const tb=document.getElementById('userLoanApps');
  if(!apps||!apps.length){tb.innerHTML='<tr><td colspan="6" class="em">No loan applications</td></tr>';return}
  tb.innerHTML=apps.map(l=>`<tr><td><strong>${$(l.amount)}</strong></td><td>${esc(l.purpose||'—')}</td><td>${l.interest_rate}%</td><td>${l.term_months}mo</td><td>${$(l.monthly_payment)}</td><td><span class="st ${l.status}">${l.status}</span></td></tr>`).join('');
}
async function createLoanAccount(){
  const type=document.getElementById('la_type').value,amt=document.getElementById('la_amount').value,term=document.getElementById('la_term').value,purpose=document.getElementById('la_purpose').value.trim();
  if(!amt||parseFloat(amt)<=0||!purpose){tst('Amount and purpose required','err');return}
  const btn=document.getElementById('laBtn');btn.disabled=true;btn.textContent='Submitting...';
  const r=await apiFetch('legacy.php',{action:'create_loan_account',loan_type:type,principal:amt,term_months:term,purpose});
  if(r.error)tst(r.error,'err');else{tst('Loan application submitted!','scs');closeModal('createLoanAcct');document.getElementById('loanAcctForm').reset();loadLoanAccounts();loadUserLoanApps()}
  btn.disabled=false;btn.textContent='Submit Application';
}
async function requestDisbursement(id){
  if(!confirm('Request disbursement of this loan to your main account?'))return;
  const r=await apiFetch('legacy.php',{action:'request_disbursement',loan_account_id:id});
  if(r.error)tst(r.error,'err');else{tst(r.message,'scs');loadLoanAccounts()}
}

// ─── INIT ───
loadTx();loadPendingTrf();
</script>
</body>
</html>
