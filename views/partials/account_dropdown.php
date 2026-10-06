<?php
  $accountRole = $user->role ?? 'staff';
  $hasAdminProfile = in_array($accountRole, ['super_admin', 'admin'], true);
  $profileUrl = $profileUrl ?? route($hasAdminProfile ? 'admin.profile.update' : 'staff.profile.update');
  $passwordUrl = $passwordUrl ?? route($hasAdminProfile ? 'admin.profile.password' : 'staff.profile.password');
?>
<style>
  .admin-account{position:relative;flex-shrink:0}.admin-account-toggle{display:flex;align-items:center;gap:6px;background:transparent;border:0;color:#e5eaf2;font:inherit;font-size:.875rem;cursor:pointer;padding:7px 9px;border-radius:7px}.admin-account-toggle:hover,.admin-account-toggle:focus-visible{background:#2b313b;outline:2px solid transparent}.admin-account-chevron{transition:transform .18s}.admin-account-toggle[aria-expanded="true"] .admin-account-chevron{transform:rotate(180deg)}
  .admin-account-menu{display:none;position:absolute;right:0;top:calc(100% + 8px);width:245px;background:#23272f;border:1px solid #363b48;border-radius:10px;box-shadow:0 14px 34px rgba(0,0,0,.4);z-index:600;overflow:hidden;color:#e5eaf2}.admin-account-menu.open{display:block}.admin-account-summary{padding:13px 15px;border-bottom:1px solid #363b48}.admin-account-name{font-size:.82rem;font-weight:700;overflow-wrap:anywhere}.admin-account-email{font-size:.7rem;color:#94a3b8;margin-top:3px;overflow-wrap:anywhere}.admin-account-item{display:flex;width:100%;gap:9px;align-items:center;padding:10px 14px;border:0;background:transparent;color:#dbe3ee;font-size:.78rem;text-align:left;cursor:pointer}.admin-account-item:hover,.admin-account-item:focus-visible{background:#2b313b;outline:none}.admin-account-logout{color:#fca5a5}
  .admin-profile-overlay{display:none;position:fixed;inset:0;background:rgba(8,12,18,.76);z-index:1000;align-items:center;justify-content:center;padding:18px}.admin-profile-overlay.open{display:flex}.admin-profile-dialog{width:min(660px,100%);max-height:92vh;overflow-y:auto;background:#23272f;color:#e5eaf2;border:1px solid #3a4350;border-radius:12px;box-shadow:0 20px 55px rgba(0,0,0,.5);padding:22px;position:relative}.admin-profile-title{font-size:1.1rem;font-weight:800;margin:0 35px 18px 0}.admin-profile-close{position:absolute;right:14px;top:10px;border:0;background:transparent;color:#94a3b8;font-size:1.8rem;cursor:pointer}.admin-profile-section+ .admin-profile-section{border-top:1px solid #363b48;margin-top:22px;padding-top:20px}.admin-profile-section h3{font-size:.84rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800;margin:0 0 13px}.admin-profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px}.admin-profile-field{display:flex;flex-direction:column;gap:5px}.admin-profile-field.full{grid-column:1/-1}.admin-profile-field label{font-size:.73rem;font-weight:700;color:#cbd5e1}.admin-profile-field input{width:100%;background:#1a2025!important;color:#e5eaf2!important;border:1px solid #3b4553!important;border-radius:6px!important;padding:9px 10px!important;font-size:.82rem!important;margin:0!important}.admin-profile-field input:focus{outline:none;border-color:#3ec6ff!important;box-shadow:0 0 0 2px rgba(62,198,255,.15)}.admin-profile-help{font-size:.68rem;color:#94a3b8;line-height:1.45}.admin-profile-errors{min-height:18px;font-size:.7rem;color:#fca5a5;margin-top:8px}.admin-profile-status{display:none;padding:9px 11px;border-radius:6px;font-size:.74rem;margin-bottom:14px}.admin-profile-status.success{display:block;background:#064e3b;color:#d1fae5;border:1px solid #059669}.admin-profile-status.error{display:block;background:#7f1d1d;color:#fee2e2;border:1px solid #ef4444}.admin-profile-actions{display:flex;gap:9px;margin-top:14px}.admin-profile-save,.admin-profile-cancel{border:0;border-radius:6px;padding:9px 13px;font-size:.76rem;font-weight:800;cursor:pointer}.admin-profile-save{background:#13d670;color:#10221a}.admin-profile-save:hover{background:#12b15c}.admin-profile-save:disabled{opacity:.55;cursor:wait}.admin-profile-cancel{background:#343c48;color:#e5eaf2}
  body.light-mode .admin-account-toggle{color:#1e293b}body.light-mode .admin-account-toggle:hover,body.light-mode .admin-account-toggle:focus-visible{background:#e8edf5}body.light-mode .admin-account-menu,body.light-mode .admin-profile-dialog{background:#fff;color:#1e293b;border-color:#d6deea;box-shadow:0 14px 34px rgba(15,23,42,.18)}body.light-mode .admin-account-summary,body.light-mode .admin-profile-section+ .admin-profile-section{border-color:#e2e8f0}body.light-mode .admin-account-email,body.light-mode .admin-profile-help{color:#64748b}body.light-mode .admin-account-item{color:#334155}body.light-mode .admin-account-item:hover,body.light-mode .admin-account-item:focus-visible{background:#f1f5f9}body.light-mode .admin-account-logout{color:#dc2626}body.light-mode .admin-profile-field label{color:#475569}body.light-mode .admin-profile-field input{background:#f8fafc!important;color:#1e293b!important;border-color:#cbd5e1!important}body.light-mode .admin-profile-cancel{background:#e2e8f0;color:#334155}
  @media(max-width:640px){.admin-account-menu{right:-8px;width:min(245px,calc(100vw - 28px))}.admin-profile-grid{grid-template-columns:1fr}.admin-profile-field.full{grid-column:auto}.admin-profile-dialog{padding:18px}.admin-profile-actions{flex-wrap:wrap}}
  @media(max-width:360px){.admin-account-toggle .welcome-prefix{display:none}}
</style>

<div class="admin-account" id="adminAccount">
  <button type="button" class="admin-account-toggle" id="adminAccountToggle" aria-haspopup="menu" aria-expanded="false">
    <span><span class="welcome-prefix">Welcome, </span><strong id="adminHeaderName"><?php echo e($user->name ?? 'Admin'); ?></strong></span>
    <svg class="admin-account-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
  </button>
  <div class="admin-account-menu" id="adminAccountMenu" role="menu">
    <div class="admin-account-summary">
      <div class="admin-account-name" id="adminMenuName"><?php echo e($user->name ?? 'Admin'); ?></div>
      <div class="admin-account-email" id="adminMenuEmail"><?php echo e($user->email ?? ''); ?></div>
    </div>
    <button type="button" class="admin-account-item" id="openAdminProfile" role="menuitem">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
      Profile Settings
    </button>
    <form method="POST" action="<?php echo e(route('logout')); ?>" id="adminAccountLogoutForm">
      <?php echo csrf_field(); ?>
      <button type="submit" class="admin-account-item admin-account-logout" role="menuitem">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/></svg>
        Logout
      </button>
    </form>
  </div>
</div>

<div class="admin-profile-overlay" id="adminProfileOverlay" aria-hidden="true">
  <div class="admin-profile-dialog" role="dialog" aria-modal="true" aria-labelledby="adminProfileTitle">
    <button type="button" class="admin-profile-close" id="closeAdminProfile" aria-label="Close profile settings">&times;</button>
    <h2 class="admin-profile-title" id="adminProfileTitle">Profile Settings</h2>
    <div class="admin-profile-status" id="adminProfileStatus" role="status" aria-live="polite"></div>

    <section class="admin-profile-section">
      <h3>Account Information</h3>
      <form id="adminProfileForm" novalidate>
        <div class="admin-profile-grid">
          <div class="admin-profile-field">
            <label for="adminProfileName">Full Name</label>
            <input id="adminProfileName" name="name" type="text" maxlength="255" value="<?php echo e($user->name ?? ''); ?>" autocomplete="name" required>
          </div>
          <div class="admin-profile-field">
            <label for="adminProfileEmail">Email</label>
            <input id="adminProfileEmail" name="email" type="email" maxlength="255" value="<?php echo e($user->email ?? ''); ?>" autocomplete="email" required>
          </div>
          <div class="admin-profile-field full">
            <label for="adminProfileCurrentPassword">Current Password <span class="admin-profile-help">(required only when changing email)</span></label>
            <input id="adminProfileCurrentPassword" name="current_password" type="password" autocomplete="current-password">
          </div>
        </div>
        <div class="admin-profile-errors" id="adminProfileErrors" role="alert"></div>
        <div class="admin-profile-actions">
          <button type="submit" class="admin-profile-save">Save Profile Changes</button>
        </div>
      </form>
    </section>

    <section class="admin-profile-section">
      <h3>Change Password</h3>
      <form id="adminPasswordForm" novalidate>
        <div class="admin-profile-grid">
          <div class="admin-profile-field full"><label for="adminPasswordCurrent">Current Password</label><input id="adminPasswordCurrent" name="current_password" type="password" autocomplete="current-password" required></div>
          <div class="admin-profile-field">
            <label for="adminPasswordNew">New Password</label>
            <input id="adminPasswordNew" name="new_password" type="password" autocomplete="new-password" minlength="8" maxlength="1024" aria-describedby="adminPasswordStrength adminPasswordRules" required>
            <div style="height:5px;background:#94a3b840;border-radius:4px;overflow:hidden;" aria-hidden="true"><div id="adminPasswordStrengthBar" style="height:100%;width:0;transition:width .15s;"></div></div>
            <span id="adminPasswordStrength" class="admin-profile-help" role="status" aria-live="polite">Enter a new password.</span>
          </div>
          <div class="admin-profile-field"><label for="adminPasswordConfirm">Confirm New Password</label><input id="adminPasswordConfirm" name="new_password_confirmation" type="password" autocomplete="new-password" required></div>
        </div>
        <div id="adminPasswordRules" class="admin-profile-help" style="margin-top:9px">Minimum 8 characters, with uppercase, lowercase, a number, and a special character.</div>
        <div class="admin-profile-errors" id="adminPasswordErrors" role="alert"></div>
        <div class="admin-profile-actions">
          <button type="submit" class="admin-profile-save">Change Password</button>
          <button type="button" class="admin-profile-cancel" id="cancelAdminProfile">Close</button>
        </div>
      </form>
    </section>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const account = document.getElementById('adminAccount');
  const toggle = document.getElementById('adminAccountToggle');
  const menu = document.getElementById('adminAccountMenu');
  const overlay = document.getElementById('adminProfileOverlay');
  const profileForm = document.getElementById('adminProfileForm');
  const passwordForm = document.getElementById('adminPasswordForm');
  const status = document.getElementById('adminProfileStatus');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const newPassword = document.getElementById('adminPasswordNew');
  function updatePasswordStrength() {
    const value = newPassword.value;
    const rules = [value.length >= 8 && value.length <= 1024, /[A-Z]/.test(value), /[a-z]/.test(value), /[0-9]/.test(value), /[^A-Za-z0-9]/.test(value)];
    const score = rules.filter(Boolean).length;
    const strong = rules.every(Boolean);
    const label = document.getElementById('adminPasswordStrength');
    const bar = document.getElementById('adminPasswordStrengthBar');
    label.textContent = !value ? 'Enter a new password.' : strong ? 'Strong — meets all password requirements.' : 'Weak — ' + score + ' of 5 requirements met.';
    bar.style.width = value ? (score / 5 * 100) + '%' : '0%';
    bar.style.background = strong ? '#22c55e' : '#ef4444';
    return strong;
  }
  newPassword.addEventListener('input', updatePasswordStrength);
  passwordForm.addEventListener('reset', () => setTimeout(updatePasswordStrength, 0));
  updatePasswordStrength();

  function setMenu(open) { menu.classList.toggle('open', open); toggle.setAttribute('aria-expanded', String(open)); }
  function setModal(open) {
    overlay.classList.toggle('open', open); overlay.setAttribute('aria-hidden', String(!open));
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) { status.className = 'admin-profile-status'; status.textContent = ''; document.getElementById('adminProfileName').focus(); }
    else toggle.focus();
  }
  function messages(data) {
    if (data && data.errors) return Object.values(data.errors).flat().join(' ');
    return data && data.message ? data.message : 'Unable to save changes. Please try again.';
  }
  function showStatus(message, type) { status.textContent = message; status.className = 'admin-profile-status ' + type; }
  async function submitForm(form, url, errorBox) {
    const button = form.querySelector('button[type="submit"]');
    const original = button.textContent;
    button.disabled = true; button.textContent = 'Saving...'; errorBox.textContent = '';
    try {
      const response = await fetch(url, { method: 'PUT', headers: {'Accept':'application/json','X-CSRF-TOKEN':csrf}, body: new FormData(form) });
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data.success) throw data;
      showStatus(data.message, 'success');
      return data;
    } catch (error) {
      const message = messages(error); errorBox.textContent = message; showStatus(message, 'error'); return null;
    } finally { button.disabled = false; button.textContent = original; }
  }

  toggle.addEventListener('click', () => setMenu(!menu.classList.contains('open')));
  document.getElementById('openAdminProfile').addEventListener('click', () => { setMenu(false); setModal(true); });
  document.getElementById('closeAdminProfile').addEventListener('click', () => setModal(false));
  document.getElementById('cancelAdminProfile').addEventListener('click', () => setModal(false));
  overlay.addEventListener('click', event => { if (event.target === overlay) setModal(false); });
  document.addEventListener('click', event => { if (!account.contains(event.target)) setMenu(false); });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') { if (overlay.classList.contains('open')) setModal(false); else setMenu(false); }
    if (event.key === 'ArrowDown' && document.activeElement === toggle) { event.preventDefault(); setMenu(true); menu.querySelector('[role="menuitem"]').focus(); }
  });

  profileForm.addEventListener('submit', async event => {
    event.preventDefault();
    const data = await submitForm(profileForm, <?php echo json_encode($profileUrl, 15, 512) ?>, document.getElementById('adminProfileErrors'));
    if (!data) return;
    document.getElementById('adminHeaderName').textContent = data.user.name;
    document.getElementById('adminMenuName').textContent = data.user.name;
    document.getElementById('adminMenuEmail').textContent = data.user.email;
    document.getElementById('adminProfileName').value = data.user.name;
    document.getElementById('adminProfileEmail').value = data.user.email;
    document.getElementById('adminProfileCurrentPassword').value = '';
  });
  passwordForm.addEventListener('submit', async event => {
    event.preventDefault();
    if (!updatePasswordStrength()) {
      document.getElementById('adminPasswordErrors').textContent = 'Use at least 8 characters with uppercase, lowercase, a number, and a special character.';
      newPassword.focus();
      return;
    }
    if (newPassword.value !== document.getElementById('adminPasswordConfirm').value) {
      document.getElementById('adminPasswordErrors').textContent = 'New passwords do not match.';
      return;
    }
    const data = await submitForm(passwordForm, <?php echo json_encode($passwordUrl, 15, 512) ?>, document.getElementById('adminPasswordErrors'));
    if (data) passwordForm.reset();
  });
});
</script>

<?php echo $__env->make('partials.session_timeout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
