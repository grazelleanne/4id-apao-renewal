<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --forest: #2d6a4f;
      --forest-light: #40916c;
      --forest-dark: #1b4332;
      --bg: #f0f4f8;
      --card-bg: #ffffff;
      --input-bg: #f8fafc;
      --input-border: #d1d5db;
      --text-main: #1a202c;
      --text-muted: #6b7280;
      --error: #dc2626;
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: linear-gradient(105deg, rgba(7,28,21,.82), rgba(7,28,21,.32)), url('/images/4idapao2-clean.png') center center / cover no-repeat;
      background-color: #123c2c;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 28px 18px;
    }

    .card {
      display: flex;
      width: min(900px, 95vw);
      min-height: 520px;
      border-radius: 24px;
      border: 1px solid rgba(255,255,255,.3);
      overflow: hidden;
      box-shadow: 0 28px 80px rgba(0,0,0,.4);
    }

    /* LEFT */
    .side.left {
      background: linear-gradient(160deg, rgba(12,48,33,.95), rgba(36,94,66,.9));
      width: 40%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem 2rem;
      position: relative;
      overflow: hidden;
    }

    .side.left::before {
      content: '';
      position: absolute;
      width: 300px; height: 300px;
      background: rgba(255,255,255,0.05);
      border-radius: 50%;
      top: -80px; left: -80px;
    }

    .side.left::after {
      content: '';
      position: absolute;
      width: 200px; height: 200px;
      background: rgba(255,255,255,0.04);
      border-radius: 50%;
      bottom: -50px; right: -50px;
    }

    .brand {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      z-index: 1;
    }

    .brand-logo {
      width: min(240px, 85%);
      height: auto;
      max-height: 420px;
      border-radius: 50%;
      object-fit: contain;
      border: 3px solid rgba(255,255,255,0.3);
      background: rgba(255,255,255,0.1);
      box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }

    .brand-title {
      color: #fff;
      font-size: 1.5rem;
      font-weight: 700;
      text-align: center;
    }

    .brand-subtitle {
      color: rgba(255,255,255,0.7);
      font-size: 0.85rem;
      text-align: center;
      margin-top: -0.5rem;
    }

    .divider {
      width: 50px; height: 3px;
      background: rgba(255,255,255,0.3);
      border-radius: 99px;
      margin-top: 0.5rem;
    }

    /* RIGHT */
    .side.right {
      background: rgba(255,255,255,.97);
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 3rem 2.5rem;
    }

    .title {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--text-main);
      margin-bottom: 0.35rem;
    }

    .subtitle {
      font-size: 0.9rem;
      color: var(--text-muted);
      margin-bottom: 2rem;
    }

    /* ERROR BOX */
    .error-message {
      background: #fef2f2;
      color: var(--error);
      border: 1px solid #fecaca;
      border-radius: 8px;
      padding: 0.65rem 1rem;
      font-size: 0.875rem;
      margin-bottom: 1.25rem;
      display: none;
    }

    .error-message.visible { display: block; }

    /* FIELDS */
    .field { margin-bottom: 1.25rem; }

    .label {
      display: block;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--text-main);
      margin-bottom: 0.4rem;
    }

    .input {
      width: 100%;
      padding: 0.7rem 1rem;
      border: 1.5px solid var(--input-border);
      border-radius: 10px;
      background: var(--input-bg);
      color: var(--text-main);
      font-size: 0.95rem;
      font-family: inherit;
      transition: border-color 0.2s, box-shadow 0.2s;
      outline: none;
    }

    .input:focus {
      border-color: var(--forest-light);
      box-shadow: 0 0 0 3px rgba(64,145,108,0.15);
    }

    .input.error { border-color: var(--error); }

    .password-wrapper {
      position: relative;
    }

    .password-wrapper .input {
      padding-right: 2.75rem;
    }

    .toggle-password {
      position: absolute;
      right: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      padding: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-muted);
    }

    .toggle-password:hover { color: var(--forest); }

    .toggle-password svg {
      width: 20px;
      height: 20px;
      fill: currentColor;
    }

    /* ROW */
    .row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.5rem;
    }

    .checkbox {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      color: var(--text-muted);
      cursor: pointer;
    }

    .checkbox input[type="checkbox"] {
      width: 16px; height: 16px;
      accent-color: var(--forest);
      cursor: pointer;
    }

    .forgot-link {
      font-size: 0.875rem;
      color: var(--forest);
      text-decoration: none;
      font-weight: 600;
    }

    .forgot-link:hover { text-decoration: underline; }

    /* BUTTON */
    .btn {
      width: 100%;
      padding: 0.8rem;
      background: linear-gradient(135deg, var(--forest) 0%, var(--forest-light) 100%);
      color: #fff;
      font-size: 1rem;
      font-weight: 600;
      font-family: inherit;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: opacity 0.2s, transform 0.1s, box-shadow 0.2s;
      box-shadow: 0 4px 14px rgba(45,106,79,0.35);
      margin-bottom: 1.25rem;
    }

    .btn:hover:not(:disabled) {
      opacity: 0.92;
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(45,106,79,0.4);
    }

    .btn:disabled {
      opacity: 0.65;
      cursor: not-allowed;
      transform: none;
    }

    /* SUBTLE */
    .subtle {
      font-size: 0.875rem;
      color: var(--text-muted);
      text-align: center;
    }

    .subtle a {
      color: var(--forest);
      font-weight: 600;
      text-decoration: none;
      margin-left: 4px;
    }

    .subtle a:hover { text-decoration: underline; }

    /* MODAL */
    .modal-success-bg {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.55);
      backdrop-filter: blur(4px);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal-success-bg.active { display: flex; }

    .modal-success {
      background: #fff;
      border-radius: 18px;
      padding: 2.5rem 2rem;
      max-width: 380px;
      width: 90%;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0,0,0,0.2);
      animation: modal-in 0.3s ease;
    }

    @keyframes modal-in {
      from { opacity: 0; transform: scale(0.9) translateY(20px); }
      to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-success .icon {
      width: 64px; height: 64px;
      background: linear-gradient(135deg, var(--forest) 0%, var(--forest-light) 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
    }

    .modal-success .icon svg {
      width: 34px; height: 34px;
      fill: #fff;
    }

    .modal-success .msg {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--text-main);
      margin-bottom: 0.5rem;
    }

    .modal-success .desc {
      font-size: 0.875rem;
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 1rem;
    }

    .redirect-hint {
      font-size: 0.8rem;
      color: var(--forest-light);
      font-weight: 500;
    }

    /* RESPONSIVE */
    @media (max-width: 640px) {
      body { padding: 16px 12px; background-position: 55% center; }
      .card { flex-direction: column; }
      .side.left { width: 100%; min-height: 160px; padding: 1.25rem; gap:8px; }
      .brand-logo { width: 84px; max-height: 84px; }
      .brand-title { font-size: 1rem; }
      .brand-subtitle { font-size:.72rem; }
      .side.right { padding: 2rem 1.5rem; }
    }
  </style>
  <link rel="stylesheet" href="/css/typography.css">
</head>
<body>

  <main class="card" role="main">

    <!-- LEFT -->
    <section class="side left" aria-label="Brand">
      <div class="brand">
        <img
          class="brand-logo"
          src="<?php echo e(asset('images/logo.png')); ?>"
          alt="Company Logo"
          onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        />
        <div style="display:none; width:min(240px, 85%); aspect-ratio:1; border-radius:50%; background:rgba(255,255,255,0.15); align-items:center; justify-content:center;">
          <svg fill="white" viewBox="0 0 24 24" style="width:45%;height:45%;">
            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
          </svg>
        </div>
      </div>
      <h1 class="brand-title" style="margin-top:22px;position:relative;z-index:1;">4ID APAO</h1>
      <p class="brand-subtitle" style="color:#e2eee6;text-align:center;line-height:1.6;margin-top:10px;position:relative;z-index:1;">Army Property Accountability Office<br>Firearm Inspection &amp; Renewal System</p>
    </section>

    <!-- RIGHT -->
    <section class="side right" aria-label="Login form">
      <h2 class="title">Welcome back</h2>
      <p class="subtitle">Sign in to your account to continue.</p>

      <div id="loginError" class="error-message" role="alert" aria-live="polite"></div>

      <form id="loginForm" novalidate>
        <?php echo csrf_field(); ?>

        <div class="field">
          <label class="label" for="email">Email address</label>
          <input class="input" type="email" id="email" name="email"
            placeholder="you@example.com" required autocomplete="email" />
        </div>

       <div class="field">
          <label class="label" for="password">Password</label>
          <div class="password-wrapper">
            <input class="input" type="password" id="password" name="password"
              placeholder="Enter your password" required autocomplete="current-password" />
            <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
              <svg class="icon-eye" viewBox="0 0 24 24" style="display:none;"><path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>
              <svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M2 4.27 3.28 3 21 20.72 19.73 22l-3.08-3.08A11.6 11.6 0 0 1 12 20C5 20 2 13 2 13a17.6 17.6 0 0 1 4.06-5.94L2 4.27zM12 6c7 0 10 7 10 7a17.9 17.9 0 0 1-2.19 3.36l-2.16-2.16A4 4 0 0 0 12 8a3.95 3.95 0 0 0-1.29.22L8.36 5.86A11.6 11.6 0 0 1 12 6zm-3.29 4.29 5 5A4 4 0 0 1 8.71 10.29z"/></svg>
            </button>
          </div>
        </div>

        <div class="row">
          <label class="checkbox" for="remember">
            <input type="checkbox" id="remember" name="remember" />
            <span>Remember me</span>
          </label>
        </div>

        <div class="field">
          <label class="label" for="captcha">Security check</label>
          <div style="display:flex;gap:.65rem;align-items:center;margin-bottom:.55rem;">
            <strong id="captchaQuestion" style="flex:1;padding:.7rem;border-radius:8px;background:#eef2f7;color:#1f2937;text-align:center;letter-spacing:.04em;"><?php echo e($captchaQuestion); ?></strong>
            <button type="button" id="refreshCaptcha" class="toggle-password" style="position:static;width:42px;height:42px;border:1px solid #d3d8e2;border-radius:8px;" aria-label="Get a new security question" title="New question">↻</button>
          </div>
          <input class="input" type="text" id="captcha" name="captcha" inputmode="numeric"
            pattern="[0-9]*" placeholder="Enter the answer" required autocomplete="off" />
        </div>

        <button type="submit" class="btn" id="loginBtn">Login</button>


      </form>
    </section>

  </main>

  <!-- SUCCESS MODAL -->
  <div class="modal-success-bg" id="successModal" role="alertdialog" aria-modal="true">
    <div class="modal-success">
      <div class="icon">
        <svg viewBox="0 0 24 24">
          <path d="M9.707 16.293l-4-4a1 1 0 1 1 1.414-1.414l2.586 2.586 5.586-5.586a1 1 0 1 1 1.414 1.414l-6.293 6.293a1 1 0 0 1-1.414 0z"/>
        </svg>
      </div>
      <div class="msg">Successfully Logged In!</div>
      <div class="desc" id="modalSuccessDesc">
        Welcome back! You have successfully logged in.<br/>You may now access your account.
      </div>
      <p class="redirect-hint" id="redirectHint">Redirecting in 3…</p>
    </div>
  </div>

  <script>
    // A cached login page can retain the success modal when reached through
    // Back/Forward. End any remaining authenticated session and replace that
    // stale history entry with a fresh login page.
    window.addEventListener('pageshow', async function (event) {
      const navigation = window.performance.getEntriesByType('navigation')[0];
      const isHistoryNavigation = event.persisted || navigation?.type === 'back_forward';
      if (!isHistoryNavigation) return;

      document.getElementById('successModal')?.classList.remove('active');

      try {
        await fetch(<?php echo json_encode(route('logout'), 15, 512) ?>, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          cache: 'no-store'
        });
      } finally {
        window.location.replace(<?php echo json_encode(route('login'), 15, 512) ?>);
      }
    });

   const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput     = document.getElementById('password');
    const eyeIcon           = togglePasswordBtn.querySelector('.icon-eye');
    const eyeOffIcon        = togglePasswordBtn.querySelector('.icon-eye-off');

    togglePasswordBtn.addEventListener('click', function () {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      eyeIcon.style.display    = isPassword ? 'block' : 'none';
      eyeOffIcon.style.display = isPassword ? 'none' : 'block';
      togglePasswordBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });

    const captchaEl = document.getElementById('captcha');
    const captchaQuestionEl = document.getElementById('captchaQuestion');

    captchaEl.addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '');
    });

    async function refreshCaptcha() {
      const response = await fetch('<?php echo e(route('login.captcha')); ?>', {
        headers: { 'Accept': 'application/json' },
      });
      const data = await response.json();
      captchaQuestionEl.textContent = data.question;
      captchaEl.value = '';
    }

    document.getElementById('refreshCaptcha').addEventListener('click', function () {
      refreshCaptcha().catch(() => {
        document.getElementById('loginError').textContent = 'Could not refresh the security question.';
        document.getElementById('loginError').classList.add('visible');
      });
    });

    document.getElementById('loginForm').addEventListener('submit', async function (e) {
      e.preventDefault();

      const errorBox = document.getElementById('loginError');
      const modal    = document.getElementById('successModal');
      const hintEl   = document.getElementById('redirectHint');
      const btn      = document.getElementById('loginBtn');
      const emailEl  = document.getElementById('email');
      const passEl   = document.getElementById('password');

      // Reset
      errorBox.textContent = '';
      errorBox.classList.remove('visible');
      emailEl.classList.remove('error');
      passEl.classList.remove('error');
      captchaEl.classList.remove('error');

      // Basic client validation
      if (!emailEl.value.trim()) {
        errorBox.textContent = 'Email is required.';
        errorBox.classList.add('visible');
        emailEl.classList.add('error');
        return;
      }

      if (!passEl.value.trim()) {
        errorBox.textContent = 'Password is required.';
        errorBox.classList.add('visible');
        passEl.classList.add('error');
        return;
      }

      if (!captchaEl.value.trim()) {
        errorBox.textContent = 'Security answer is required.';
        errorBox.classList.add('visible');
        captchaEl.classList.add('error');
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Signing in…';

      try {
        const response = await fetch('/login', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept':       'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            email:    emailEl.value.trim(),
            password: passEl.value,
            remember: document.getElementById('remember').checked,
            captcha:  captchaEl.value,
            _token:   document.querySelector('input[name="_token"]').value,
          }),
        });

        const data = await response.json();

        if (data.success) {
          // Every authenticated session starts in the accessible light theme.
          try { localStorage.setItem('theme', 'light'); } catch (_) {}
          // Show modal
          modal.classList.add('active');

          // Countdown then redirect
          let count = 3;
          hintEl.textContent = `Redirecting in ${count}…`;

          const timer = setInterval(() => {
            count--;
            if (count > 0) {
              hintEl.textContent = `Redirecting in ${count}…`;
            } else {
              clearInterval(timer);
              window.location.replace(data.redirect);
            }
          }, 1000);

        } else {

          errorBox.textContent = data.message || 'Login failed. Please try again.';
          errorBox.classList.add('visible');
          emailEl.classList.add('error');
          passEl.classList.add('error');
          if (data.captcha_question) {
            captchaQuestionEl.textContent = data.captcha_question;
            captchaEl.value = '';
          }
          captchaEl.classList.add('error');
        }

      } catch (err) {
        errorBox.textContent = 'A network error occurred. Please try again.';
        errorBox.classList.add('visible');
        console.error('Login error:', err);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Login';
      }
    });
  </script>

</body>
</html>
