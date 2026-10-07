<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Your Password | APAO</title>
  <link rel="stylesheet" href="/css/typography.css">
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#eef5f0;color:#1d3025;font-family:Inter,system-ui,sans-serif}
    main{width:100%;max-width:460px;padding:32px;background:white;border:1px solid #dce7df;border-radius:20px;box-shadow:0 12px 40px #153c2510}
    h1{font-size:25px;margin:0 0 12px}p{font-size:14px;line-height:1.6;color:#607567}label{display:block;font-size:14px;font-weight:600;margin:18px 0 8px}
    input{width:100%;padding:12px;border:1px solid #cbdacf;border-radius:9px;font:inherit}input:focus{outline:2px solid #197544;outline-offset:2px}
    button{border:0;border-radius:9px;padding:12px;font:inherit;cursor:pointer}button:disabled{opacity:.6;cursor:wait}.submit{width:100%;background:#197544;color:white;margin-top:20px;font-weight:600}.logout{background:#f0f5f2;color:#607567;width:100%;margin-top:12px}
    #strength{font-size:13px;margin-top:8px}.meter{height:5px;background:#e4ece7;border-radius:5px;margin-top:10px;overflow:hidden}#strengthFill{height:100%;width:0;background:#dc5d50;transition:width .2s}#error{color:#b42318;font-size:14px;line-height:1.5}.show{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:400}.show input{width:auto}
  </style>
</head>
<body>
<main>
  <h1>Create your new password</h1>
  <p>Welcome, <?= h($user->name) ?>. Replace the temporary password supplied by your administrator before accessing the dashboard.</p>
  <form id="firstPasswordForm" method="post" action="<?= $user->role === 'staff' ? '/staff/first-password' : '/admin/first-password' ?>">
    <?= csrf_field() ?>
    <label for="newPassword">New password</label>
    <input id="newPassword" name="password" type="password" minlength="8" maxlength="1024" autocomplete="new-password" required>
    <div class="meter" aria-hidden="true"><div id="strengthFill"></div></div>
    <div id="strength" aria-live="polite">Use 8+ characters, uppercase, lowercase, a number and a symbol.</div>
    <label for="confirmPassword">Confirm new password</label>
    <input id="confirmPassword" name="password_confirmation" type="password" minlength="8" maxlength="1024" autocomplete="new-password" required>
    <label class="show"><input id="showPasswords" type="checkbox">Show passwords</label>
    <p id="error" role="alert" hidden></p>
    <button id="savePassword" class="submit" type="submit">Save password and continue</button>
  </form>
  <form method="post" action="/logout"><?= csrf_field() ?><button type="submit" class="logout">Sign out</button></form>
</main>
<script src="/js/action-otp.js"></script>
<script>
  const form = document.getElementById('firstPasswordForm');
  const password = document.getElementById('newPassword');
  const confirm = document.getElementById('confirmPassword');
  password.addEventListener('input', () => {
    const value = password.value;
    const score = [value.length >= 8, /[A-Z]/.test(value), /[a-z]/.test(value), /\d/.test(value), /[^A-Za-z0-9]/.test(value)].filter(Boolean).length;
    document.getElementById('strengthFill').style.width = (score * 20) + '%';
    document.getElementById('strengthFill').style.background = score === 5 ? '#197544' : '#dc5d50';
    document.getElementById('strength').textContent = score === 5 ? 'Strong password' : 'Weak password — use 8+ characters, uppercase, lowercase, a number and a symbol.';
  });
  document.getElementById('showPasswords').addEventListener('change', event => {
    password.type = confirm.type = event.target.checked ? 'text' : 'password';
  });
  form.addEventListener('submit', async event => {
    event.preventDefault();
    const button = document.getElementById('savePassword');
    const error = document.getElementById('error');
    error.hidden = true;
    if (password.value !== confirm.value) { error.textContent = 'Passwords do not match.'; error.hidden = false; return; }
    button.disabled = true;
    button.textContent = 'Saving...';
    try {
      const response = await actionOtpFetch(form.action, {method:'POST', headers:{'Accept':'application/json'}, body:new FormData(form)});
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || 'Unable to save password. Please try again.');
      window.location.replace(data.redirect);
    } catch (failure) { error.textContent = failure.message; error.hidden = false; }
    finally { button.disabled = false; button.textContent = 'Save password and continue'; }
  });
</script>
</body>
</html>
