<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Register – APAO Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --green-dark:   #2D5741;
      --green-mid:    #3A6B4F;
      --green-light:  #4E8A65;
      --green-accent: #3D7A55;
      --green-glow:   rgba(61,122,85,0.25);
      --white:        #ffffff;
      --text-dark:    #1A2A22;
      --text-muted:   #6B7B72;
      --border:       #E2EAE5;
      --input-bg:     #F8FAF9;
      --error-bg:     #FFF0EE;
      --error-text:   #C0392B;
      --success-text: #2D5741;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body { font-family: 'DM Sans', system-ui, sans-serif; background: #EEF2EE; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px; }
    .card { width: min(100%, 960px); background: var(--white); border-radius: 20px; box-shadow: 0 24px 64px rgba(0,0,0,0.12), 0 4px 16px rgba(0,0,0,0.06); display: grid; grid-template-columns: 2fr 3fr; overflow: hidden; animation: cardIn .55s cubic-bezier(.22,.68,0,1.2) both; }
    @keyframes cardIn { from{opacity:0;transform:translateY(20px) scale(0.97)} to{opacity:1;transform:translateY(0) scale(1)} }
    .panel-left { background: linear-gradient(170deg, var(--green-dark) 0%, var(--green-mid) 55%, var(--green-light) 100%); padding: 48px 32px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; position: relative; overflow: hidden; }
    .panel-left::before, .panel-left::after { content: ''; position: absolute; border-radius: 50%; background: rgba(255,255,255,0.06); pointer-events: none; }
    .panel-left::before { width: 300px; height: 300px; top: -80px; left: -80px; }
    .panel-left::after  { width: 220px; height: 220px; bottom: -60px; right: -60px; }
    .logo-wrap { width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.95); border: 3px solid rgba(255,255,255,0.5); display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; z-index: 1; box-shadow: 0 8px 28px rgba(0,0,0,0.3); padding: 4px; }
    .logo-wrap img { width: 108px; height: 108px; object-fit: cover; border-radius: 50%; }
    .brand-name { color: var(--white); font-size: 22px; font-weight: 700; letter-spacing: .5px; text-align: center; position: relative; z-index: 1; }
    .brand-sub  { color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 500; text-align: center; position: relative; z-index: 1; margin-top: -12px; }
    .brand-divider { width: 40px; height: 2px; background: rgba(255,255,255,0.3); border-radius: 2px; position: relative; z-index: 1; }
    .panel-right { padding: 52px; display: flex; align-items: center; justify-content: center; background: var(--white); }
    .form-wrap { width: 100%; max-width: 380px; }
    .form-title { font-size: 26px; font-weight: 700; color: var(--text-dark); margin-bottom: 6px; }
    .form-desc  { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 28px; }
    .error-msg   { display: none; background: var(--error-bg); color: var(--error-text); border-radius: 10px; padding: 11px 14px; font-size: 14px; margin-bottom: 18px; }
    .success-msg { display: none; background: #f0faf4; color: var(--success-text); font-size: 14px; text-align: center; margin-bottom: 16px; border-radius: 10px; padding: 11px 14px; border: 1px solid #b7dfc8; }
    .field { margin-bottom: 16px; }
    .label { display: block; font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 7px; }
    .input { width: 100%; background: var(--input-bg); border: 1.5px solid var(--border); border-radius: 10px; padding: 13px 14px; font-size: 15px; font-family: inherit; color: var(--text-dark); outline: none; transition: border-color .2s, box-shadow .2s; }
    .input::placeholder { color: #B0BCB6; }
    .input:focus { border-color: var(--green-accent); background: var(--white); box-shadow: 0 0 0 3.5px var(--green-glow); }
    /* Password strength */
    .strength-bar-wrap { height: 5px; border-radius: 4px; background: #e2eae5; margin-top: 7px; overflow: hidden; }
    .strength-bar      { height: 100%; border-radius: 4px; width: 0%; transition: width .3s, background .3s; }
    .strength-text     { font-size: 12px; margin-top: 5px; font-weight: 600; min-height: 16px; }
    .strength-weak     { background: #e53e3e; }
    .strength-fair     { background: #ecc94b; }
    .strength-good     { background: #3ec6ff; }
    .strength-strong   { background: #33b481; }
    .strength-hint     { font-size: 11px; color: #94a3b8; margin-top: 4px; line-height: 1.5; }
    .password-wrapper { position: relative; }
    .password-wrapper .input { padding-right: 44px; }
    .toggle-password {
      position: absolute; right: 12px; top: 13px;
      background: none; border: none; cursor: pointer;
      padding: 4px; display: flex; align-items: center; justify-content: center;
      color: var(--text-muted);
    }
    .toggle-password:hover { color: var(--green-accent); }
    .toggle-password svg { width: 19px; height: 19px; fill: currentColor; }
    .btn { width: 100%; background: linear-gradient(135deg, var(--green-dark), var(--green-mid)); color: var(--white); border: none; border-radius: 10px; padding: 14px; font-size: 15px; font-weight: 700; font-family: inherit; cursor: pointer; letter-spacing: .3px; box-shadow: 0 8px 20px rgba(45,87,65,0.35); transition: filter .2s, transform .1s; margin-top: 4px; }
    .btn:hover  { filter: brightness(1.08); transform: translateY(-1px); }
    .btn:active { transform: translateY(0); }
    .btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
    .back-link { margin-top: 18px; font-size: 14px; color: var(--text-muted); text-align: center; }
    .back-link a { color: var(--green-dark); font-weight: 600; text-decoration: none; }
    .back-link a:hover { text-decoration: underline; }
    @media (max-width: 700px) {
      .card { grid-template-columns: 1fr; }
      .panel-left { padding: 32px 24px; flex-direction: row; gap: 16px; justify-content: flex-start; }
      .brand-divider { display: none; }
      .panel-right { padding: 36px 28px; }
    }
  </style>
</head>
<body>
  <main class="card">
    <section class="panel-left">
      <div class="logo-wrap">
        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="APAO Logo" onerror="this.style.display='none'" />
      </div>
      <p class="brand-name">APAO Portal</p>
      <p class="brand-sub">Secure Access System</p>
      <div class="brand-divider"></div>
    </section>

    <section class="panel-right">
      <div class="form-wrap">
        <h2 class="form-title">Create Account</h2>
        <p class="form-desc">Register a new staff account for the APAO Renewal System.</p>

        <div class="error-msg"   id="errorMsg"   role="alert"></div>
        <div class="success-msg" id="successMsg" role="status"></div>

        <div class="field">
          <label class="label" for="name">Full Name</label>
          <input class="input" type="text" id="name" placeholder="Juan Dela Cruz" autocomplete="name" />
        </div>
        <div class="field">
          <label class="label" for="email">Email Address</label>
          <input class="input" type="email" id="email" placeholder="you@example.com" autocomplete="email" />
        </div>
        <div class="field">
          <label class="label" for="password">Password</label>
          <div class="password-wrapper">
            <input class="input" type="password" id="password" placeholder="Min 8 chars, uppercase, number, symbol" autocomplete="new-password" />
            <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
              <svg class="icon-eye" viewBox="0 0 24 24" style="display:none;"><path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>
              <svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M2 4.27 3.28 3 21 20.72 19.73 22l-3.08-3.08A11.6 11.6 0 0 1 12 20C5 20 2 13 2 13a17.6 17.6 0 0 1 4.06-5.94L2 4.27zM12 6c7 0 10 7 10 7a17.9 17.9 0 0 1-2.19 3.36l-2.16-2.16A4 4 0 0 0 12 8a3.95 3.95 0 0 0-1.29.22L8.36 5.86A11.6 11.6 0 0 1 12 6zm-3.29 4.29 5 5A4 4 0 0 1 8.71 10.29z"/></svg>
            </button>
          </div>
          <div class="strength-bar-wrap"><div class="strength-bar" id="strengthBar"></div></div>
          <div class="strength-text" id="strengthText"></div>
          <div class="strength-hint">Must contain: uppercase letter, number, and special character (@#!$%)</div>
        </div>
       <div class="field">
          <label class="label" for="password_confirm">Confirm Password</label>
          <div class="password-wrapper">
            <input class="input" type="password" id="password_confirm" placeholder="Repeat your password" autocomplete="new-password" />
            <button type="button" class="toggle-password" id="toggleConfirmPassword" aria-label="Show password">
              <svg class="icon-eye" viewBox="0 0 24 24" style="display:none;"><path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>
              <svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M2 4.27 3.28 3 21 20.72 19.73 22l-3.08-3.08A11.6 11.6 0 0 1 12 20C5 20 2 13 2 13a17.6 17.6 0 0 1 4.06-5.94L2 4.27zM12 6c7 0 10 7 10 7a17.9 17.9 0 0 1-2.19 3.36l-2.16-2.16A4 4 0 0 0 12 8a3.95 3.95 0 0 0-1.29.22L8.36 5.86A11.6 11.6 0 0 1 12 6zm-3.29 4.29 5 5A4 4 0 0 1 8.71 10.29z"/></svg>
            </button>
          </div>
        </div>

        <button type="button" class="btn" id="registerBtn">Create Account</button>
        <p class="back-link"><a href="<?php echo e(route('login')); ?>">← Back to Login</a></p>
      </div>
    </section>
  </main>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const csrf     = document.querySelector('meta[name="csrf-token"]').content;
    const errorMsg = document.getElementById('errorMsg');
    const successMsg = document.getElementById('successMsg');
    const btn      = document.getElementById('registerBtn');

    function showError(msg)   { errorMsg.textContent=msg; errorMsg.style.display='block'; successMsg.style.display='none'; }
    function showSuccess(msg) { successMsg.textContent=msg; successMsg.style.display='block'; errorMsg.style.display='none'; }
    function hide()           { errorMsg.style.display='none'; successMsg.style.display='none'; }

    // Show/hide password toggle (reusable for both fields)
    function setupToggle(buttonId, inputId) {
      const button = document.getElementById(buttonId);
      const input  = document.getElementById(inputId);
      if (!button || !input) return;
      const eyeIcon    = button.querySelector('.icon-eye');
      const eyeOffIcon = button.querySelector('.icon-eye-off');
      button.addEventListener('click', function () {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        eyeIcon.style.display    = isPassword ? 'block' : 'none';
        eyeOffIcon.style.display = isPassword ? 'none' : 'block';
        button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      });
    }
    setupToggle('togglePassword', 'password');
    setupToggle('toggleConfirmPassword', 'password_confirm');

    // Password strength checker
    document.getElementById('password').addEventListener('input', function () {
      const val  = this.value;
      const bar  = document.getElementById('strengthBar');
      const text = document.getElementById('strengthText');
      let score = 0;
      if (val.length >= 8)            score++;
      if (/[A-Z]/.test(val))          score++;
      if (/[0-9]/.test(val))          score++;
      if (/[^A-Za-z0-9]/.test(val))   score++;
      const levels = [
        { pct:'25%', cls:'strength-weak',   label:'Weak',   color:'#e53e3e' },
        { pct:'50%', cls:'strength-fair',   label:'Fair',   color:'#ecc94b' },
        { pct:'75%', cls:'strength-good',   label:'Good',   color:'#3ec6ff' },
        { pct:'100%',cls:'strength-strong', label:'Strong', color:'#33b481' },
      ];
      if (!val) { bar.style.width='0'; text.textContent=''; return; }
      const lvl = levels[Math.max(0, score - 1)];
      bar.style.width  = lvl.pct;
      bar.className    = 'strength-bar ' + lvl.cls;
      text.textContent = lvl.label;
      text.style.color = lvl.color;
    });

    btn.addEventListener('click', async function () {
      hide();
      const name     = document.getElementById('name').value.trim();
      const email    = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirm  = document.getElementById('password_confirm').value;

      if (!name)    { showError('Please enter your full name.'); return; }
      if (!email)   { showError('Please enter your email address.'); return; }
      if (!password){ showError('Please enter a password.'); return; }

      // Enforce password rules
      if (password.length < 8)              { showError('Password must be at least 8 characters.'); return; }
      if (!/[A-Z]/.test(password))          { showError('Password must contain at least one uppercase letter.'); return; }
      if (!/[0-9]/.test(password))          { showError('Password must contain at least one number.'); return; }
      if (!/[^A-Za-z0-9]/.test(password))   { showError('Password must contain at least one special character (@, #, !, etc.).'); return; }
      if (password !== confirm)             { showError('Passwords do not match.'); return; }

      btn.disabled = true;
      btn.textContent = 'Creating account...';

      try {
        const res  = await fetch('/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
          body: JSON.stringify({ name, email, password })
        });
        const data = await res.json();
        if (data.success) {
          showSuccess('✓ Account created! Redirecting to login...');
          btn.textContent = '✓ Done!';
          setTimeout(() => { window.location.href = '/login'; }, 2000);
        } else {
          showError(data.errors?.email?.[0] || data.message || 'Registration failed. Please try again.');
          btn.disabled = false;
          btn.textContent = 'Create Account';
        }
      } catch (err) {
        showError('Something went wrong. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Create Account';
      }
    });
  });
  </script>
</body>
</html>
