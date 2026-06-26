<?php require_once __DIR__ . '/includes/config.php'; if (isLoggedIn()) { header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/' : 'user/')); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Legacy National Bank</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
  :root {
    --teal-900:#0a2e2a; --teal-800:#0d5555; --teal-700:#0f766e; --teal-600:#0d9488;
    --teal-500:#14b8a6; --teal-400:#2dd4bf; --teal-50:#f0fdfa;
    --slate-900:#0f172a; --slate-800:#1e293b; --slate-700:#334155; --slate-600:#475569;
    --slate-500:#64748b; --slate-400:#94a3b8; --slate-300:#cbd5e1; --slate-200:#e2e8f0;
    --slate-100:#f1f5f9; --slate-50:#f8fafc; --white:#ffffff;
    --shadow-md:0 4px 20px rgba(0,0,0,0.08); --shadow-lg:0 10px 40px rgba(0,0,0,0.12);
    --radius-sm:8px; --radius-md:12px; --transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
  }
  body {
    font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;
    background: var(--slate-50); color: var(--slate-700);
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    padding:1.5rem; -webkit-font-smoothing:antialiased;
  }
  .login-wrap {
    display:grid; width:100%; max-width:1000px;
    background:var(--white); border-radius:var(--radius-md);
    box-shadow:var(--shadow-lg); overflow:hidden;
    grid-template-columns:1fr;
    min-height:600px;
  }
  @media(min-width:768px){ .login-wrap { grid-template-columns:1fr 1fr; } }

  .login-left {
    padding:3rem 2.5rem; display:flex; flex-direction:column;
  }
  .logo-link { display:inline-flex; align-items:center; gap:0.75rem; text-decoration:none; margin-bottom:3rem; }
  .logo { height:32px; }
  

  .login-left h1 { font-size:1.8rem; margin-bottom:0.5rem; color:var(--slate-900); }
  .login-left .subtitle { font-size:0.95rem; color:var(--slate-500); margin-bottom:2rem; }

  .error-msg {
    background:#fef2f2; border:1px solid #fecaca; color:#dc2626;
    padding:0.75rem 1rem; border-radius:var(--radius-sm); font-size:0.85rem;
    font-weight:500; display:none; margin-bottom:1rem; text-align:center;
  }
  .error-msg.show { display:block; }

  form { display:flex; flex-direction:column; gap:1.25rem; }
  label { display:block; font-size:0.85rem; font-weight:600; color:var(--slate-700); margin-bottom:0.4rem; }
  input {
    width:100%; background:var(--white); border:1.5px solid var(--slate-200);
    border-radius:var(--radius-sm); padding:0.85rem 1rem; font-size:0.95rem;
    color:var(--slate-800); transition:var(--transition); font-family:inherit;
  }
  input::placeholder { color:var(--slate-400); }
  input:focus { outline:none; border-color:var(--teal-500); box-shadow:0 0 0 3px rgba(20,184,166,0.1); }
  .password-wrap { position:relative; }
  .toggle-eye {
    position:absolute; right:1rem; top:50%; transform:translateY(-50%);
    cursor:pointer; width:22px; height:22px; display:flex; align-items:center; justify-content:center;
  }
  .toggle-eye svg { width:18px; height:18px; stroke:var(--slate-400); fill:none; stroke-width:2; transition:var(--transition); }
  .toggle-eye:hover svg { stroke:var(--teal-600); }

  .forgot { text-align:right; margin-top:-0.5rem; }
  .forgot a { color:var(--teal-600); font-size:0.85rem; font-weight:600; text-decoration:none; }
  .forgot a:hover { text-decoration:underline; }

  .btn {
    padding:0.9rem; border-radius:var(--radius-sm); font-weight:600; font-size:1rem;
    border:none; cursor:pointer; transition:var(--transition); font-family:inherit;
    display:inline-flex; align-items:center; justify-content:center; gap:0.5rem;
  }
  .btn-primary {
    background:var(--teal-600); color:var(--white);
    box-shadow:0 4px 12px rgba(13,148,136,0.25);
  }
  .btn-primary:hover { background:var(--teal-700); transform:translateY(-1px); box-shadow:0 6px 20px rgba(13,148,136,0.35); }
  .btn-primary:disabled { opacity:0.6; cursor:not-allowed; transform:none; }

  .divider { text-align:center; color:var(--slate-400); font-size:0.85rem; margin:0.5rem 0; position:relative; }
  .divider::before, .divider::after {
    content:''; position:absolute; top:50%; width:42%; height:1px; background:var(--slate-200);
  }
  .divider::before { left:0; } .divider::after { right:0; }

  .signup-cta {
    text-align:center; padding:1rem; background:var(--teal-50);
    border-radius:var(--radius-sm); margin-top:0.5rem;
  }
  .signup-cta p { font-size:0.9rem; color:var(--slate-600); margin-bottom:0.25rem; }
  .signup-cta a { color:var(--teal-600); font-weight:700; text-decoration:none; }
  .signup-cta a:hover { text-decoration:underline; }

  .demo-hint { text-align:center; font-size:0.75rem; color:var(--slate-400); margin-top:0.75rem; }

  footer { text-align:center; padding-top:1.5rem; border-top:1px solid var(--slate-100); font-size:0.8rem; color:var(--slate-400); margin-top:auto; }

  .login-right {
    display:none; background:linear-gradient(135deg, var(--teal-900), var(--teal-800));
    padding:3rem; color:var(--white); flex-direction:column; justify-content:center; position:relative; overflow:hidden;
  }
  .login-right::before {
    content:''; position:absolute; width:400px; height:400px; border-radius:50%;
    background:radial-gradient(circle, rgba(45,212,191,0.15) 0%, transparent 70%);
    top:-100px; right:-100px;
  }
  @media(min-width:768px){ .login-right { display:flex; } }

  .login-right-content { position:relative; z-index:1; }
  .login-right-badge {
    display:inline-flex; align-items:center; gap:0.5rem;
    background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12);
    padding:0.5rem 1rem; border-radius:100px; font-size:0.8rem; font-weight:600; margin-bottom:2rem;
  }
  .login-right h2 {
    font-family:'Playfair Display',serif; font-size:2rem; line-height:1.2; margin-bottom:1.5rem;
  }
  .login-right ul { list-style:none; display:flex; flex-direction:column; gap:1.25rem; margin-bottom:2.5rem; }
  .login-right li { display:flex; align-items:flex-start; gap:0.75rem; font-size:0.95rem; line-height:1.6; color:rgba(255,255,255,0.85); }
  .login-right li svg { width:20px; height:20px; stroke:var(--teal-400); fill:none; stroke-width:2; flex-shrink:0; margin-top:2px; }
  .trust-badges { display:flex; gap:1.5rem; padding-top:1.5rem; border-top:1px solid rgba(255,255,255,0.1); }
  .trust-item { display:flex; flex-direction:column; gap:0.25rem; }
  .trust-item span:first-child { font-weight:700; font-size:0.8rem; }
  .trust-item span:last-child { font-size:0.75rem; opacity:0.6; }

  @media(max-width:480px){ .login-left { padding:2rem 1.5rem; } h1 { font-size:1.5rem; } }
</style>
</head>
<body>

<div class="login-wrap">
  <div class="login-left">
    <a href="index.html" class="logo-link">
      <img src="images/legacy-logo-green.png" alt="Legacy National Bank" class="logo" />

    </a>
    <h1>Welcome back</h1>
    <p class="subtitle">Sign in to your account to continue</p>

    <div class="error-msg" id="loginError"></div>

    <form id="loginForm">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-wrap">
          <input type="password" id="password" placeholder="Enter your password" required>
          <div class="toggle-eye" id="toggleEye">
            <svg id="eyeClosed" viewBox="0 0 24 24">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
              <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
            <svg id="eyeOpen" viewBox="0 0 24 24" style="display:none;">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </div>
        </div>
      </div>
      <div class="forgot"><a href="#">Forgot password?</a></div>
      <button type="submit" class="btn btn-primary" id="loginBtn">Sign In</button>
    </form>

    <div class="divider">or</div>

    <div class="signup-cta">
      <p>Don't have an account?</p>
      <a href="signup/">Create your account →</a>
    </div>

    <div class="demo-hint">Demo: admin@legacy.com / admin123</div>

    <footer>&copy; 2026 Legacy National Bank. Member FDIC.</footer>
  </div>

  <div class="login-right">
    <div class="login-right-content">
      <div class="login-right-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Secure & Confidential
      </div>
      <h2>Banking that puts you first</h2>
      <ul>
        <li>
          <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Quick, secure access to all your accounts
        </li>
        <li>
          <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          FDIC insured up to $250,000 per account
        </li>
        <li>
          <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          24/7 dedicated support from real bankers
        </li>
      </ul>
      <div class="trust-badges">
        <div class="trust-item">
          <span>Member FDIC</span>
          <span>Deposits insured</span>
        </div>
        <div class="trust-item">
          <span>SSL Secured</span>
          <span>256-bit encryption</span>
        </div>
        <div class="trust-item">
          <span>BBB Rated</span>
          <span>A+ rating</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const toggleEye = document.getElementById('toggleEye');
  const passwordInput = document.getElementById('password');
  const eyeOpen = document.getElementById('eyeOpen');
  const eyeClosed = document.getElementById('eyeClosed');

  toggleEye.addEventListener('click', () => {
    const isVisible = passwordInput.type === 'text';
    passwordInput.type = isVisible ? 'password' : 'text';
    eyeOpen.style.display = isVisible ? 'none' : 'block';
    eyeClosed.style.display = isVisible ? 'block' : 'none';
  });

  document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const errorEl = document.getElementById('loginError');
    const btn = document.getElementById('loginBtn');
    errorEl.classList.remove('show');

    const formData = new URLSearchParams();
    formData.append('email', email);
    formData.append('password', password);

    btn.disabled = true;
    btn.textContent = 'Signing in...';

    try {
      const res = await fetch('api/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
      });
      const data = await res.json();
      if (!res.ok) {
        errorEl.textContent = data.error || 'Login failed';
        errorEl.classList.add('show');
        return;
      }
      window.location.href = data.redirect;
    } catch (err) {
      errorEl.textContent = 'Connection error. Please try again.';
      errorEl.classList.add('show');
    } finally {
      btn.disabled = false;
      btn.textContent = 'Sign In';
    }
  });
</script>

</body>
</html>
