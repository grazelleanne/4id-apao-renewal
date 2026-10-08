<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Forgot Password – APAO Portal</title>
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
      body {
        font-family: 'DM Sans', system-ui, sans-serif;
        background: #EEF2EE;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 24px;
      }
      .card {
        width: min(100%, 960px);
        background: var(--white);
        border-radius: 20px;
        box-shadow: 0 24px 64px rgba(0,0,0,0.12), 0 4px 16px rgba(0,0,0,0.06);
        display: grid;
        grid-template-columns: 2fr 3fr;
        overflow: hidden;
        animation: cardIn .55s cubic-bezier(.22,.68,0,1.2) both;
      }
      @keyframes cardIn {
        from { opacity: 0; transform: translateY(20px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
      }
      .panel-left {
        background: linear-gradient(170deg, var(--green-dark) 0%, var(--green-mid) 55%, var(--green-light) 100%);
        padding: 48px 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 20px;
        position: relative;
        overflow: hidden;
      }
      .panel-left::before, .panel-left::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
        pointer-events: none;
      }
      .panel-left::before { width: 300px; height: 300px; top: -80px; left: -80px; }
      .panel-left::after  { width: 220px; height: 220px; bottom: -60px; right: -60px; }
      .logo-wrap {
        width: 120px; height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.95);
        border: 3px solid rgba(255,255,255,0.5);
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
        position: relative; z-index: 1;
        box-shadow: 0 8px 28px rgba(0,0,0,0.3);
        padding: 4px;
      }
      .logo-wrap img { width: 108px; height: 108px; object-fit: cover; border-radius: 50%; }
      .brand-name {
        color: var(--white);
        font-size: 22px;
        font-weight: 700;
        letter-spacing: .5px;
        text-align: center;
        position: relative; z-index: 1;
      }
      .brand-sub {
        color: rgba(255,255,255,0.65);
        font-size: 13px;
        font-weight: 500;
        letter-spacing: .3px;
        text-align: center;
        position: relative; z-index: 1;
        margin-top: -12px;
      }
      .brand-divider {
        width: 40px; height: 2px;
        background: rgba(255,255,255,0.3);
        border-radius: 2px;
        position: relative; z-index: 1;
      }
      .panel-right {
        padding: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--white);
      }
      .form-wrap { width: 100%; max-width: 380px; }
      .form-title {
        font-size: 26px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 6px;
      }
      .form-desc {
        font-size: 14px;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 28px;
      }
      .error-msg {
        display: none;
        background: var(--error-bg);
        color: var(--error-text);
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 14px;
        margin-bottom: 18px;
      }
      .success-msg {
        display: none;
        background: #f0faf4;
        color: var(--success-text);
        font-size: 14px;
        text-align: center;
        margin-bottom: 16px;
        border-radius: 10px;
        padding: 11px 14px;
        border: 1px solid #b7dfc8;
      }
      .field { margin-bottom: 18px; }
      .label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 7px;
      }
      .input {
        width: 100%;
        background: var(--input-bg);
        border: 1.5px solid var(--border);
        border-radius: 10px;
        padding: 13px 14px;
        font-size: 15px;
        font-family: inherit;
        color: var(--text-dark);
        outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
      }
      .input::placeholder { color: #B0BCB6; }
      .input:focus {
        border-color: var(--green-accent);
        background: var(--white);
        box-shadow: 0 0 0 3.5px var(--green-glow);
      }
      .code-field      { display: none; }
      .password-fields { display: none; }
      .btn {
        width: 100%;
        background: linear-gradient(135deg, var(--green-dark), var(--green-mid));
        color: var(--white);
        border: none;
        border-radius: 10px;
        padding: 14px;
        font-size: 15px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        letter-spacing: .3px;
        box-shadow: 0 8px 20px rgba(45,87,65,0.35);
        transition: filter .2s, transform .1s, box-shadow .2s;
        margin-top: 4px;
      }
      .btn:hover  { filter: brightness(1.08); transform: translateY(-1px); }
      .btn:active { transform: translateY(0); }
      .btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
      .back-link {
        margin-top: 18px;
        font-size: 14px;
        color: var(--text-muted);
        text-align: center;
      }
      .back-link a {
        color: var(--green-dark);
        font-weight: 600;
        text-decoration: none;
      }
      .back-link a:hover { text-decoration: underline; }
      .strength-bar-wrap { height: 5px; border-radius: 4px; background: #e2e8e5; margin-top: 6px; overflow: hidden; }
      .strength-bar { height: 100%; border-radius: 4px; width: 0%; transition: width 0.3s, background 0.3s; }
      .strength-text { font-size: 12px; margin-top: 4px; font-weight: 600; min-height: 16px; }
      .strength-weak   { background: #e53e3e; }
      .strength-fair   { background: #ecc94b; }
      .strength-good   { background: #3ec6ff; }
      .strength-strong { background: #33b481; }
      @media (max-width: 700px) {
        .card { grid-template-columns: 1fr; }
        .panel-left { padding: 32px 24px; flex-direction: row; gap: 16px; justify-content: flex-start; }
        .brand-divider { display: none; }
        .panel-right { padding: 36px 28px; }
        .form-title { font-size: 22px; }
      }
    </style>
    <link rel="stylesheet" href="/css/typography.css">
</head>
  <body>
    <main class="card" role="main" aria-label="Forgot password">

      <section class="panel-left" aria-label="APAO Portal brand">
        <div class="logo-wrap">
          <img src="<?php echo e(asset('images/logo.png')); ?>" alt="APAO Portal Logo" onerror="this.style.display='none'" />
        </div>
        <p class="brand-name">APAO Portal</p>
        <p class="brand-sub">Secure Access System</p>
        <div class="brand-divider"></div>
      </section>

      <section class="panel-right" aria-label="Reset password form">
        <div class="form-wrap">
          <h2 class="form-title">Forgot Password</h2>
          <p class="form-desc" id="step-desc">
            Enter your email address and we'll send you a 6-digit code to reset your password.
          </p>

          <div class="error-msg"   id="errorMsg"   role="alert"></div>
          <div class="success-msg" id="successMsg" role="status"></div>

          <form id="forgotForm" autocomplete="on">

            <!-- Step 1: Email -->
            <div class="field" id="email-field">
              <label class="label" for="email">Email address</label>
              <input class="input" type="email" id="email" name="email"
                     placeholder="you@example.com" autocomplete="email" />
            </div>

            <!-- Step 2: OTP Code -->
            <div class="field code-field" id="code-field">
              <label class="label" for="code">6-digit verification code</label>
              <input class="input" type="text" id="code" name="code"
                     placeholder="Enter code from your email" maxlength="6"
                     autocomplete="one-time-code" inputmode="numeric" />
            </div>

            <!-- Step 3: New Password -->
            <div class="password-fields" id="password-fields">
              <div class="field">
                <label class="label" for="new-password">New Password</label>
                <input class="input" type="password" id="new-password" name="new-password"
                       placeholder="Enter a strong password (min. 8 characters)" minlength="8"
                       autocomplete="new-password" />
              </div>
              <div class="field">
                <label class="label" for="confirm-password">Confirm New Password</label>
                <input class="input" type="password" id="confirm-password" name="confirm-password"
                       placeholder="Confirm new password" minlength="8"
                       autocomplete="new-password" />
              </div>
            </div>

            <button type="button" class="btn" id="submitBtn">Send Reset Code</button>

          </form>

          <p class="back-link">
            <a href="<?php echo e(route('login')); ?>">← Back to Login</a>
          </p>
        </div>
      </section>

    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

      const submitBtn      = document.getElementById('submitBtn');
      const errorMsg       = document.getElementById('errorMsg');
      const successMsg     = document.getElementById('successMsg');
      const stepDesc       = document.getElementById('step-desc');
      const emailField     = document.getElementById('email-field');
      const codeField      = document.getElementById('code-field');
      const passwordFields = document.getElementById('password-fields');
      const emailInput     = document.getElementById('email');
      const codeInput      = document.getElementById('code');
      const newPassword    = document.getElementById('new-password');
      const confirmPass    = document.getElementById('confirm-password');
      const csrf           = document.querySelector('meta[name="csrf-token"]').content;

      let step = 1;
      let userEmail = '';

      function showError(msg) {
        errorMsg.textContent = msg;
        errorMsg.style.display = 'block';
        successMsg.style.display = 'none';
      }

      function showSuccess(msg) {
        successMsg.textContent = msg;
        successMsg.style.display = 'block';
        errorMsg.style.display = 'none';
      }

      function hideMessages() {
        errorMsg.style.display = 'none';
        successMsg.style.display = 'none';
      }

      function setLoading(loading, label) {
        submitBtn.disabled = loading;
        submitBtn.textContent = loading ? 'Please wait...' : label;
      }

      // ── Step 1: Send OTP ──
      async function sendOtp() {
        const email = emailInput.value.trim();
        if (!email) { showError('Please enter your email address.'); return; }

        setLoading(true, 'Send Reset Code');
        hideMessages();

        try {
          const res  = await fetch('/forgot-password/send-otp', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify({ email })
          });
          const data = await res.json();

          if (!res.ok) {
            showError(data.errors?.email?.[0] || data.message || 'Email not found. Please check and try again.');
            setLoading(false, 'Send Reset Code');
            return;
          }

          userEmail = email;
          step = 2;
          emailField.style.display = 'none';
          codeField.style.display  = 'block';
          stepDesc.textContent = 'A 6-digit verification code has been sent to ' + email + '. Check your inbox and spam folder.';
          setLoading(false, 'Verify Code');
          showSuccess('✓ Code sent! Check your email inbox.');

        } catch (err) {
          showError('Something went wrong. Please try again.');
          setLoading(false, 'Send Reset Code');
        }
      }

      // ── Step 2: Verify OTP ──
      async function verifyOtp() {
        const code = codeInput.value.trim();
        if (!code || code.length !== 6) { showError('Please enter the 6-digit code sent to your email.'); return; }

        setLoading(true, 'Verify Code');
        hideMessages();

        try {
          const res  = await fetch('/forgot-password/verify-otp', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify({ email: userEmail, code })
          });
          const data = await res.json();

          if (!res.ok) {
            showError(data.error || 'Invalid or expired code. Please request a new one.');
            setLoading(false, 'Verify Code');
            return;
          }

          step = 3;
          codeField.style.display      = 'none';
          passwordFields.style.display = 'block';
          stepDesc.textContent = 'Code verified! Enter your new password below.';
          setLoading(false, 'Reset Password');
          showSuccess('✓ Code verified! Now set your new password.');

        } catch (err) {
          showError('Something went wrong. Please try again.');
          setLoading(false, 'Verify Code');
        }
      }

      // ── Step 3: Reset Password ──
      async function resetPassword() {
        const password              = newPassword.value;
        const password_confirmation = confirmPass.value;

        if (!password || password.length < 8) { showError('Password must be at least 8 characters.'); return; }
        if (password !== password_confirmation) { showError('Passwords do not match. Please try again.'); return; }

        setLoading(true, 'Reset Password');
        hideMessages();

        try {
          const res  = await fetch('/forgot-password/reset', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify({ email: userEmail, password, password_confirmation })
          });
          const data = await res.json();

          if (!res.ok) {
            showError(data.message || 'Could not reset password. Please try again.');
            setLoading(false, 'Reset Password');
            return;
          }

          showSuccess('✓ Password reset successful! Redirecting to login...');
          submitBtn.style.display = 'none';
          setTimeout(() => { window.location.href = '/login'; }, 2000);

        } catch (err) {
          showError('Something went wrong. Please try again.');
          setLoading(false, 'Reset Password');
        }
      }

      // ── Button handler ──
      submitBtn.addEventListener('click', function () {
        if (step === 1) sendOtp();
        else if (step === 2) verifyOtp();
        else if (step === 3) resetPassword();
      });

    });

    // PASSWORD STRENGTH CHECKER
    document.getElementById('new-password').addEventListener('input', function () {
      const val = this.value;
      const bar  = document.getElementById('strengthBar');
      const text = document.getElementById('strengthText');
      let score = 0;
      if (val.length >= 8)               score++;
      if (/[A-Z]/.test(val))             score++;
      if (/[0-9]/.test(val))             score++;
      if (/[^A-Za-z0-9]/.test(val))      score++;
      const levels = [
        { pct:'25%', cls:'strength-weak',   label:'Weak',   color:'#e53e3e' },
        { pct:'50%', cls:'strength-fair',   label:'Fair',   color:'#ecc94b' },
        { pct:'75%', cls:'strength-good',   label:'Good',   color:'#3ec6ff' },
        { pct:'100%',cls:'strength-strong', label:'Strong', color:'#33b481' },
      ];
      if (!val) { bar.style.width='0'; text.textContent=''; return; }
      const lvl = levels[Math.max(0, score-1)];
      bar.style.width = lvl.pct;
      bar.className   = 'strength-bar ' + lvl.cls;
      text.textContent = lvl.label;
      text.style.color = lvl.color;
    });
    </script>
  </body>
</html>
