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
    --red-500:#ef4444; --red-50:#fef2f2; --green-500:#22c55e; --green-50:#f0fdf4;
    --shadow-md:0 4px 20px rgba(0,0,0,0.08); --shadow-lg:0 10px 40px rgba(0,0,0,0.12);
    --radius-sm:8px; --radius-md:12px; --radius-lg:16px;
    --transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
  }
  body {
    font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;
    background: linear-gradient(135deg, var(--slate-50) 0%, var(--teal-50) 100%);
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    padding:2rem 1.5rem; -webkit-font-smoothing:antialiased;
  }
  .signup-wrap {
    width:100%; max-width:680px; background:var(--white);
    border-radius:var(--radius-md); box-shadow:var(--shadow-lg); overflow:hidden;
  }
  .signup-header {
    background:linear-gradient(135deg, var(--teal-900), var(--teal-800));
    padding:2rem 2rem 1.5rem; text-align:center;
  }
  .signup-header .logo-link { display:inline-flex; align-items:center; gap:0.75rem; text-decoration:none; margin-bottom:0.5rem; }
  .signup-header h1 { color:var(--white); font-size:1.4rem; }
  .signup-header p { color:rgba(255,255,255,0.75); font-size:0.85rem; margin-top:0.2rem; }

  /* Steps indicator */
  .steps-bar { display:flex; align-items:center; justify-content:center; gap:0; padding:1.5rem 2rem 0; }
  .step-dot { display:flex; align-items:center; gap:0.5rem; font-size:0.78rem; font-weight:600; color:var(--slate-400); }
  .step-dot .num { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; background:var(--slate-100); color:var(--slate-500); transition:var(--transition); }
  .step-dot.active .num { background:var(--teal-600); color:var(--white); box-shadow:0 2px 8px rgba(13,148,136,0.3); }
  .step-dot.active { color:var(--teal-700); }
  .step-dot.done .num { background:var(--teal-100); color:var(--teal-700); }
  .step-line { width:40px; height:1px; background:var(--slate-200); margin:0 0.5rem; }
  .step-line.done { background:var(--teal-400); }

  .signup-body { padding:2rem; }
  .error-msg {
    background:var(--red-50); border:1px solid #fecaca; color:var(--red-500);
    padding:0.75rem 1rem; border-radius:var(--radius-sm); font-size:0.85rem;
    font-weight:500; display:none; margin-bottom:1.5rem; text-align:center;
  }
  .error-msg.show { display:block; }
  .step-panel { display:none; }
  .step-panel.active { display:block; }
  .step-panel h2 { font-size:1.2rem; font-weight:700; color:var(--slate-900); margin-bottom:0.25rem; }
  .step-panel .step-desc { font-size:0.85rem; color:var(--slate-500); margin-bottom:1.5rem; }

  form { display:flex; flex-direction:column; gap:1rem; }
  .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
  .form-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; }
  label { display:block; font-size:0.82rem; font-weight:600; color:var(--slate-700); margin-bottom:0.3rem; }
  label .req { color:var(--red-500); }
  input, select {
    width:100%; background:var(--white); border:1.5px solid var(--slate-200);
    border-radius:var(--radius-sm); padding:0.75rem 1rem; font-size:0.92rem;
    color:var(--slate-800); transition:var(--transition); font-family:inherit;
  }
  input::placeholder { color:var(--slate-400); }
  input:focus, select:focus { outline:none; border-color:var(--teal-500); box-shadow:0 0 0 3px rgba(20,184,166,0.1); }
  select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 1rem center; cursor:pointer; }
  .file-upload { position:relative; }
  .file-upload input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; z-index:2; }
  .file-upload .file-label { display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border:1.5px dashed var(--slate-300); border-radius:var(--radius-sm); color:var(--slate-500); font-size:0.85rem; transition:var(--transition); }
  .file-upload .file-label svg { width:18px; height:18px; stroke:var(--slate-400); fill:none; stroke-width:2; flex-shrink:0; }
  .file-upload.has-file .file-label { border-color:var(--teal-400); background:var(--teal-50); color:var(--teal-700); }
  .file-upload.has-file .file-label svg { stroke:var(--teal-600); }
  .checkbox-row { display:flex; align-items:flex-start; gap:0.75rem; }
  .checkbox-row input[type=checkbox] { width:18px; height:18px; margin-top:0.15rem; flex-shrink:0; accent-color:var(--teal-600); border-radius:3px; cursor:pointer; }
  .checkbox-row label { font-size:0.82rem; color:var(--slate-600); font-weight:400; cursor:pointer; }
  .checkbox-row label a { color:var(--teal-600); text-decoration:none; font-weight:500; }
  .btn-row { display:flex; gap:1rem; margin-top:1rem; }
  .btn {
    padding:0.85rem 1.5rem; border-radius:var(--radius-sm); font-weight:600; font-size:0.95rem;
    border:none; cursor:pointer; transition:var(--transition); font-family:inherit; display:inline-flex;
    align-items:center; justify-content:center; gap:0.5rem; flex:1;
  }
  .btn-primary { background:var(--teal-600); color:var(--white); box-shadow:0 4px 12px rgba(13,148,136,0.25); }
  .btn-primary:hover { background:var(--teal-700); transform:translateY(-1px); box-shadow:0 6px 20px rgba(13,148,136,0.35); }
  .btn-primary:disabled { opacity:0.6; cursor:not-allowed; transform:none; }
  .btn-outline { background:transparent; color:var(--slate-600); border:1.5px solid var(--slate-200); }
  .btn-outline:hover { border-color:var(--slate-400); }
  .btn-back { flex:0.5; }
  .terms-note { font-size:0.75rem; color:var(--slate-400); text-align:center; margin-top:1.5rem; }
  .login-link { text-align:center; margin-top:1.25rem; font-size:0.9rem; color:var(--slate-500); }
  .login-link a { color:var(--teal-600); font-weight:600; text-decoration:none; }
  .login-link a:hover { text-decoration:underline; }
  .success-screen { text-align:center; padding:3rem 1rem; display:none; }
  .success-screen.show { display:block; }
  .success-screen .check { width:64px; height:64px; border-radius:50%; background:var(--green-50); display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; }
  .success-screen .check svg { width:32px; height:32px; stroke:var(--green-500); fill:none; stroke-width:2; }
  .success-screen h2 { font-size:1.5rem; font-weight:700; color:var(--slate-900); margin-bottom:0.5rem; }
  .success-screen p { color:var(--slate-500); font-size:0.9rem; margin-bottom:2rem; line-height:1.6; }
  .success-screen .btn { display:inline-flex; width:auto; }

  @media(max-width:600px){
    .form-row, .form-row-3 { grid-template-columns:1fr; }
    .signup-body { padding:1.5rem; }
    .step-line { width:24px; }
    .btn-row { flex-direction:column; }
    .btn-back { flex:1; }
  }
</style>
</head>
<body>

<div class="signup-wrap">
  <div class="signup-header">
    <a href="../index.html" class="logo-link">
      <img src="../images/legacy-logo-green.png" alt="Legacy" style="height:28px">
    </a>
    <h1>Open Your Account</h1>
    <p>Complete all steps to activate your banking relationship</p>
  </div>

  <!-- Steps -->
  <div class="steps-bar">
    <div class="step-dot active" data-step="1"><span class="num">1</span> Personal</div>
    <div class="step-line" data-step="1"></div>
    <div class="step-dot" data-step="2"><span class="num">2</span> Identity</div>
    <div class="step-line" data-step="2"></div>
    <div class="step-dot" data-step="3"><span class="num">3</span> Security</div>
  </div>

  <div class="signup-body">
    <div class="error-msg" id="signupError"></div>

    <!-- SUCCESS SCREEN -->
    <div class="success-screen" id="successScreen">
      <div class="check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
      <h2>Application Submitted</h2>
      <p>Your account has been created. Please check your email for verification instructions. You'll receive your account and routing numbers once your identity is verified.</p>
      <a href="../login.php" class="btn btn-primary" style="width:auto;padding:0.85rem 2.5rem;">Sign In to Your Account</a>
    </div>

    <!-- STEP 1: Personal Information -->
    <div class="step-panel active" id="step1">
      <h2>Personal Information</h2>
      <p class="step-desc">Please provide your legal name and contact details as they appear on your government-issued ID.</p>
      <form id="step1Form">
        <div class="form-row">
          <div>
            <label>First Name <span class="req">*</span></label>
            <input type="text" id="first_name" placeholder="John" required>
          </div>
          <div>
            <label>Last Name <span class="req">*</span></label>
            <input type="text" id="last_name" placeholder="Doe" required>
          </div>
        </div>
        <div class="form-row">
          <div>
            <label>Date of Birth <span class="req">*</span></label>
            <input type="date" id="date_of_birth" required>
          </div>
          <div>
            <label>Email Address <span class="req">*</span></label>
            <input type="email" id="email" placeholder="you@example.com" required>
          </div>
        </div>
        <div>
          <label>Phone Number <span class="req">*</span></label>
          <input type="tel" id="phone" placeholder="(555) 123-4567" required>
        </div>
        <div>
          <label>Street Address <span class="req">*</span></label>
          <input type="text" id="address_street" placeholder="123 Main Street, Apt 4B" required>
        </div>
        <div class="form-row-3">
          <div>
            <label>City <span class="req">*</span></label>
            <input type="text" id="address_city" placeholder="New York" required>
          </div>
          <div>
            <label>State <span class="req">*</span></label>
            <select id="address_state" required>
              <option value="">Select</option>
              <option value="AL">Alabama</option><option value="AK">Alaska</option><option value="AZ">Arizona</option>
              <option value="AR">Arkansas</option><option value="CA">California</option><option value="CO">Colorado</option>
              <option value="CT">Connecticut</option><option value="DE">Delaware</option><option value="FL">Florida</option>
              <option value="GA">Georgia</option><option value="HI">Hawaii</option><option value="ID">Idaho</option>
              <option value="IL">Illinois</option><option value="IN">Indiana</option><option value="IA">Iowa</option>
              <option value="KS">Kansas</option><option value="KY">Kentucky</option><option value="LA">Louisiana</option>
              <option value="ME">Maine</option><option value="MD">Maryland</option><option value="MA">Massachusetts</option>
              <option value="MI">Michigan</option><option value="MN">Minnesota</option><option value="MS">Mississippi</option>
              <option value="MO">Missouri</option><option value="MT">Montana</option><option value="NE">Nebraska</option>
              <option value="NV">Nevada</option><option value="NH">New Hampshire</option><option value="NJ">New Jersey</option>
              <option value="NM">New Mexico</option><option value="NY">New York</option><option value="NC">North Carolina</option>
              <option value="ND">North Dakota</option><option value="OH">Ohio</option><option value="OK">Oklahoma</option>
              <option value="OR">Oregon</option><option value="PA">Pennsylvania</option><option value="RI">Rhode Island</option>
              <option value="SC">South Carolina</option><option value="SD">South Dakota</option><option value="TN">Tennessee</option>
              <option value="TX">Texas</option><option value="UT">Utah</option><option value="VT">Vermont</option>
              <option value="VA">Virginia</option><option value="WA">Washington</option><option value="WV">West Virginia</option>
              <option value="WI">Wisconsin</option><option value="WY">Wyoming</option>
            </select>
          </div>
          <div>
            <label>ZIP Code <span class="req">*</span></label>
            <input type="text" id="address_zip" placeholder="10001" required pattern="[0-9]{5}(-[0-9]{4})?">
          </div>
        </div>
        <div class="btn-row">
          <button type="button" class="btn btn-outline btn-back" onclick="history.back()" style="flex:0.5">Back</button>
          <button type="submit" class="btn btn-primary">Continue →</button>
        </div>
      </form>
    </div>

    <!-- STEP 2: Identity & Employment -->
    <div class="step-panel" id="step2">
      <h2>Identity Verification</h2>
      <p class="step-desc">We are required by federal regulation to verify your identity. Your information is encrypted and securely stored.</p>
      <form id="step2Form">
        <div class="form-row">
          <div>
            <label>ID Type <span class="req">*</span></label>
            <select id="id_type" required>
              <option value="">Select ID type</option>
              <option value="drivers_license">Driver's License</option>
              <option value="state_id">State ID Card</option>
              <option value="passport">U.S. Passport</option>
              <option value="passport_card">Passport Card</option>
              <option value="military_id">Military ID</option>
            </select>
          </div>
          <div>
            <label>ID Number <span class="req">*</span></label>
            <input type="text" id="id_number" placeholder="Enter ID number" required>
          </div>
        </div>
        <div>
          <label>Social Security Number / ITIN <span class="req">*</span></label>
          <input type="text" id="ssn" placeholder="XXX-XX-XXXX" required pattern="^\d{3}-?\d{2}-?\d{4}$|^\d{2}-?\d{7}$" inputmode="numeric" oninput="autoFormatSSN(this)">
          <div style="font-size:0.72rem;color:var(--slate-400);margin-top:0.25rem;">Enter your 9-digit SSN or ITIN. Hyphens optional — we'll format it.</div>
        </div>
        <div class="form-row">
          <div class="file-upload" id="idFrontUpload">
            <input type="file" id="id_front" accept="image/*,.pdf" required>
            <div class="file-label">
              <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <span>Upload front of ID</span>
            </div>
          </div>
          <div class="file-upload" id="idBackUpload">
            <input type="file" id="id_back" accept="image/*,.pdf">
            <div class="file-label">
              <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <span>Upload back of ID (optional)</span>
            </div>
          </div>
        </div>
        <div>
          <label>Employment Status <span class="req">*</span></label>
          <select id="employment_status" required>
            <option value="">Select status</option>
            <option value="employed">Employed (Full-time)</option>
            <option value="part_time">Employed (Part-time)</option>
            <option value="self_employed">Self-Employed</option>
            <option value="retired">Retired</option>
            <option value="student">Student</option>
            <option value="unemployed">Unemployed</option>
          </select>
        </div>
        <div class="form-row" id="employmentFields">
          <div>
            <label>Occupation / Job Title</label>
            <input type="text" id="occupation" placeholder="e.g. Software Engineer">
          </div>
          <div>
            <label>Employer Name</label>
            <input type="text" id="employer_name" placeholder="Company name">
          </div>
        </div>
        <div>
          <label>Purpose of Account <span class="req">*</span></label>
          <select id="account_purpose" required>
            <option value="">Select purpose</option>
            <option value="personal">Personal Banking</option>
            <option value="savings">Savings & Inheritance Planning</option>
            <option value="business">Business</option>
            <option value="trust">Trust / Estate Management</option>
            <option value="investment">Investment</option>
          </select>
        </div>
        <div class="btn-row">
          <button type="button" class="btn btn-outline btn-back" onclick="goToStep(1)">← Back</button>
          <button type="submit" class="btn btn-primary">Continue →</button>
        </div>
      </form>
    </div>

    <!-- STEP 3: Security & Agreement -->
    <div class="step-panel" id="step3">
      <h2>Security &amp; Agreement</h2>
      <p class="step-desc">Create your login credentials and review the account agreement.</p>
      <form id="step3Form">
        <div class="form-row">
          <div>
            <label>Security Question <span class="req">*</span></label>
            <select id="security_question" required>
              <option value="">Select a question</option>
              <option value="mother_maiden">What is your mother's maiden name?</option>
              <option value="pet">What was the name of your first pet?</option>
              <option value="school">What city were you born in?</option>
              <option value="street">What street did you grow up on?</option>
              <option value="teacher">What was your favorite teacher's name?</option>
              <option value="company">What company did you first work for?</option>
            </select>
          </div>
          <div>
            <label>Security Answer <span class="req">*</span></label>
            <input type="text" id="security_answer" placeholder="Your answer" required>
          </div>
        </div>
        <div class="form-row">
          <div>
            <label>Create Password <span class="req">*</span></label>
            <input type="password" id="password" placeholder="At least 8 characters" required minlength="8">
          </div>
          <div>
            <label>Confirm Password <span class="req">*</span></label>
            <input type="password" id="confirm_password" placeholder="Repeat password" required>
          </div>
        </div>
        <div>
          <div class="checkbox-row">
            <input type="checkbox" id="agreed_tos" required>
            <label for="agreed_tos">I agree to the <a href="#">Terms of Service</a>, <a href="#">Privacy Policy</a>, and <a href="#">Electronic Fund Transfer Agreement</a>. I understand that I am opening a deposit account subject to verification. <span class="req">*</span></label>
          </div>
        </div>
        <div>
          <div class="checkbox-row">
            <input type="checkbox" id="agreed_electronic">
            <label for="agreed_electronic">I consent to receive account disclosures, statements, and communications electronically instead of paper copies.</label>
          </div>
        </div>
        <div class="btn-row">
          <button type="button" class="btn btn-outline btn-back" onclick="goToStep(2)">← Back</button>
          <button type="submit" class="btn btn-primary" id="submitBtn">Submit Application</button>
        </div>
      </form>
    </div>

    <p class="terms-note">By creating an account, you certify that all information provided is accurate and complete. Fraudulent information may result in account closure and legal action.</p>
    <p class="login-link">Already have an account? <a href="../login.php">Sign in</a></p>
  </div>
</div>

<script>
// Auto-format SSN/ITIN as user types
function autoFormatSSN(input) {
  let v = input.value.replace(/[^0-9]/g, '');
  if (v.length > 3 && v.length <= 5) input.value = v.slice(0,3) + '-' + v.slice(3);
  else if (v.length > 5) input.value = v.slice(0,3) + '-' + v.slice(3,5) + '-' + v.slice(5,9);
  else input.value = v;
}
// Step navigation
let currentStep = 1;
const stepDots = document.querySelectorAll('.step-dot');
const stepLines = document.querySelectorAll('.step-line');
const stepPanels = [null, document.getElementById('step1'), document.getElementById('step2'), document.getElementById('step3')];

function goToStep(n) {
  currentStep = n;
  stepPanels.forEach((p, i) => { if (p) p.classList.toggle('active', i === n); });
  stepDots.forEach((d, i) => {
    d.classList.toggle('active', i + 1 === n);
    d.classList.toggle('done', i + 1 < n);
  });
  stepLines.forEach((l, i) => l.classList.toggle('done', i + 1 < n));
  document.getElementById('signupError').classList.remove('show');
}

// File upload UI
document.querySelectorAll('.file-upload input[type=file]').forEach(input => {
  input.addEventListener('change', function() {
    const wrap = this.closest('.file-upload');
    const label = wrap.querySelector('span');
    if (this.files.length > 0) {
      wrap.classList.add('has-file');
      label.textContent = this.files[0].name;
    } else {
      wrap.classList.remove('has-file');
      label.textContent = wrap.id === 'idFrontUpload' ? 'Upload front of ID' : 'Upload back of ID (optional)';
    }
  });
});

// Collect all form data across steps
function collectFormData() {
  const fd = new FormData();
  fd.append('first_name', document.getElementById('first_name').value.trim());
  fd.append('last_name', document.getElementById('last_name').value.trim());
  fd.append('date_of_birth', document.getElementById('date_of_birth').value);
  fd.append('email', document.getElementById('email').value.trim());
  fd.append('phone', document.getElementById('phone').value.trim());
  fd.append('address_street', document.getElementById('address_street').value.trim());
  fd.append('address_city', document.getElementById('address_city').value.trim());
  fd.append('address_state', document.getElementById('address_state').value);
  fd.append('address_zip', document.getElementById('address_zip').value.trim());
  fd.append('ssn', document.getElementById('ssn').value.trim().replace(/-/g, ''));
  fd.append('id_type', document.getElementById('id_type').value);
  fd.append('id_number', document.getElementById('id_number').value.trim());
  fd.append('employment_status', document.getElementById('employment_status').value);
  fd.append('occupation', document.getElementById('occupation').value.trim());
  fd.append('employer_name', document.getElementById('employer_name').value.trim());
  fd.append('account_purpose', document.getElementById('account_purpose').value);
  fd.append('security_question', document.getElementById('security_question').value);
  fd.append('security_answer', document.getElementById('security_answer').value.trim());
  fd.append('password', document.getElementById('password').value);
  fd.append('agreed_tos', document.getElementById('agreed_tos').checked ? '1' : '0');
  fd.append('agreed_electronic', document.getElementById('agreed_electronic').checked ? '1' : '0');

  const idFront = document.getElementById('id_front').files[0];
  const idBack = document.getElementById('id_back').files[0];
  if (idFront) fd.append('id_front', idFront);
  if (idBack) fd.append('id_back', idBack);

  return fd;
}

// Step 1 validation
document.getElementById('step1Form').addEventListener('submit', function(e) {
  e.preventDefault();
  const errorEl = document.getElementById('signupError');
  errorEl.classList.remove('show');

  const email = document.getElementById('email').value.trim();
  const dob = document.getElementById('date_of_birth').value;
  const age = dob ? Math.floor((Date.now() - new Date(dob).getTime()) / 31557600000) : 0;
  
  if (age < 18) {
    errorEl.textContent = 'You must be at least 18 years old to open an account.';
    errorEl.classList.add('show');
    return;
  }

  goToStep(2);
});

// Step 2 validation
document.getElementById('step2Form').addEventListener('submit', function(e) {
  e.preventDefault();
  goToStep(3);
});

// Step 3 submit
document.getElementById('step3Form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const errorEl = document.getElementById('signupError');
  const btn = document.getElementById('submitBtn');
  errorEl.classList.remove('show');

  if (document.getElementById('password').value !== document.getElementById('confirm_password').value) {
    errorEl.textContent = 'Passwords do not match.';
    errorEl.classList.add('show');
    return;
  }

  if (!document.getElementById('agreed_tos').checked) {
    errorEl.textContent = 'You must agree to the Terms of Service to open an account.';
    errorEl.classList.add('show');
    return;
  }

  btn.disabled = true;
  btn.textContent = 'Submitting application...';

  try {
    const res = await fetch('../api/register.php', {
      method: 'POST',
      body: collectFormData()
    });
    const data = await res.json();
    if (!res.ok) {
      errorEl.textContent = data.error || 'Registration failed';
      errorEl.classList.add('show');
      btn.disabled = false;
      btn.textContent = 'Submit Application';
      return;
    }

    // Hide forms, show success
    document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('successScreen').classList.add('show');
  } catch (err) {
    errorEl.textContent = 'Connection error. Please try again.';
    errorEl.classList.add('show');
    btn.disabled = false;
    btn.textContent = 'Submit Application';
  }
});
</script>

</body>
</html>
