<?php require_once __DIR__ . '/../includes/config.php'; if (isLoggedIn()) { header('Location: ' . ($_SESSION['role'] === 'admin' ? '../admin/' : '../user/')); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Open an Account — Legacy National Bank</title>
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
    background: linear-gradient(135deg, var(--slate-50) 0%, var(--teal-50) 100%);
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    padding:1.5rem; -webkit-font-smoothing:antialiased;
  }
  .signup-wrap {
    width:100%; max-width:520px; background:var(--white);
    border-radius:var(--radius-md); box-shadow:var(--shadow-lg); overflow:hidden;
  }
  .signup-header {
    background:linear-gradient(135deg, var(--teal-900), var(--teal-800));
    padding:2rem 2rem 1.5rem; text-align:center;
  }
  .signup-header .logo-link { display:inline-flex; align-items:center; gap:0.75rem; text-decoration:none; margin-bottom:0.75rem; }

  .signup-header h1 { color:var(--white); font-size:1.5rem; }
  .signup-header p { color:rgba(255,255,255,0.8); font-size:0.9rem; margin-top:0.25rem; }
  .signup-body { padding:2rem; }
  .error-msg {
    background:#fef2f2; border:1px solid #fecaca; color:#dc2626;
    padding:0.75rem 1rem; border-radius:var(--radius-sm); font-size:0.85rem;
    font-weight:500; display:none; margin-bottom:1.5rem; text-align:center;
  }
  .error-msg.show { display:block; }
  .success-msg {
    background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a;
    padding:0.75rem 1rem; border-radius:var(--radius-sm); font-size:0.85rem;
    font-weight:500; display:none; margin-bottom:1.5rem; text-align:center;
  }
  .success-msg.show { display:block; }
  form { display:flex; flex-direction:column; gap:1rem; }
  .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
  label { display:block; font-size:0.85rem; font-weight:600; color:var(--slate-700); margin-bottom:0.35rem; }
  input {
    width:100%; background:var(--white); border:1.5px solid var(--slate-200);
    border-radius:var(--radius-sm); padding:0.8rem 1rem; font-size:0.95rem;
    color:var(--slate-800); transition:var(--transition); font-family:inherit;
  }
  input::placeholder { color:var(--slate-400); }
  input:focus { outline:none; border-color:var(--teal-500); box-shadow:0 0 0 3px rgba(20,184,166,0.1); }
  .btn {
    padding:0.9rem; border-radius:var(--radius-sm); font-weight:600; font-size:1rem;
    border:none; cursor:pointer; transition:var(--transition); font-family:inherit;
  }
  .btn-primary {
    background:var(--teal-600); color:var(--white);
    box-shadow:0 4px 12px rgba(13,148,136,0.25);
  }
  .btn-primary:hover { background:var(--teal-700); transform:translateY(-1px); box-shadow:0 6px 20px rgba(13,148,136,0.35); }
  .btn-primary:disabled { opacity:0.6; cursor:not-allowed; transform:none; }
  .terms { font-size:0.8rem; color:var(--slate-500); text-align:center; margin-top:1rem; }
  .terms a { color:var(--teal-600); text-decoration:none; }
  .login-link { text-align:center; margin-top:1.25rem; font-size:0.9rem; color:var(--slate-500); }
  .login-link a { color:var(--teal-600); font-weight:600; text-decoration:none; }
  .login-link a:hover { text-decoration:underline; }
  @media(max-width:480px){ .form-row { grid-template-columns:1fr; } .signup-body { padding:1.5rem; } }
</style>
</head>
<body>

<div class="signup-wrap">
  <div class="signup-header">
    <a href="../index.html" class="logo-link">
      <img src="../images/legacy-logo-green.png" alt="Legacy" style="height:28px">

    </a>
    <h1>Open Your Account</h1>
    <p>Free checking with premium benefits</p>
  </div>
  <div class="signup-body">
    <div class="error-msg" id="signupError"></div>
    <div class="success-msg" id="signupSuccess"></div>

    <form id="signupForm">
      <div class="form-row">
        <div>
          <label for="first_name">First Name</label>
          <input type="text" id="first_name" placeholder="John" required>
        </div>
        <div>
          <label for="last_name">Last Name</label>
          <input type="text" id="last_name" placeholder="Doe" required>
        </div>
      </div>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="you@example.com" required>
      </div>
      <div>
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" placeholder="(555) 123-4567">
      </div>
      <div>
        <label for="password">Password</label>
        <input type="password" id="password" placeholder="Create a strong password" required minlength="8">
      </div>
      <div>
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" placeholder="Repeat your password" required>
      </div>
      <button type="submit" class="btn btn-primary" id="signupBtn">Create Account</button>
    </form>

    <p class="terms">By creating an account, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>
    <p class="login-link">Already have an account? <a href="../login.php">Sign in</a></p>
  </div>
</div>

<script>
  document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errorEl = document.getElementById('signupError');
    const successEl = document.getElementById('signupSuccess');
    const btn = document.getElementById('signupBtn');
    errorEl.classList.remove('show');
    successEl.classList.remove('show');

    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;

    if (password !== confirm) {
      errorEl.textContent = 'Passwords do not match';
      errorEl.classList.add('show');
      return;
    }

    const formData = new URLSearchParams();
    formData.append('first_name', document.getElementById('first_name').value.trim());
    formData.append('last_name', document.getElementById('last_name').value.trim());
    formData.append('email', document.getElementById('email').value.trim());
    formData.append('password', password);
    formData.append('phone', document.getElementById('phone').value.trim());

    btn.disabled = true;
    btn.textContent = 'Creating account...';

    try {
      const res = await fetch('../api/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
      });
      const data = await res.json();
      if (!res.ok) {
        errorEl.textContent = data.error || 'Registration failed';
        errorEl.classList.add('show');
        return;
      }
      successEl.textContent = 'Account created successfully! Redirecting to login...';
      successEl.classList.add('show');
      setTimeout(() => { window.location.href = '../login.php'; }, 2000);
    } catch (err) {
      errorEl.textContent = 'Connection error. Please try again.';
      errorEl.classList.add('show');
    } finally {
      btn.disabled = false;
      btn.textContent = 'Create Account';
    }
  });
</script>

</body>
</html>
