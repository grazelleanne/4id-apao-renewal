<?php
  $sessionTimeoutMinutes = in_array($accountRole ?? 'staff', ['super_admin', 'admin'], true) ? 15 : 30;
  $sessionAccountKey = hash('sha256', (string) ($user->id ?? $user->email ?? $accountRole ?? 'staff'));
?>

<script src="<?php echo e(asset('js/single_tab_guard.js')); ?>"></script>
<script>
// Back/Forward can restore a page without requesting it from Laravel. Always
// verify the server-side session before allowing a restored dashboard to stay.
window.addEventListener('pageshow', async function () {
  try {
    const response = await fetch(<?php echo json_encode(route('session.status'), 15, 512) ?>, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin',
      cache: 'no-store'
    });

    if (!response.ok) window.location.replace(<?php echo json_encode(route('login'), 15, 512) ?>);
  } catch (_) {
    // Fail closed when the session cannot be verified.
    window.location.replace(<?php echo json_encode(route('login'), 15, 512) ?>);
  }
});

document.addEventListener('DOMContentLoaded', function () {
  const timeoutMs = <?php echo json_encode($sessionTimeoutMinutes * 60 * 1000, 15, 512) ?>;
  const warningMs = 60 * 1000;
  const activityKey = 'apao:session:last-activity';
  const logoutLockKey = 'apao:session:logout-lock';
  const loggedOutKey = 'apao:session:logged-out';
  const activeTabKey = 'apao:session:active-tab:' + <?php echo json_encode($sessionAccountKey, 15, 512) ?>;
  const activeTabTtlMs = 8000;
  const tabId = window.ApaoSingleTabGuard.getTabId(
    window.sessionStorage,
    window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID.bind(window.crypto) : null
  );

  let warningTimer;
  let logoutTimer;
  let countdownTimer;
  let activeTabTimer;
  let lastPublishedAt = 0;
  let loggingOut = false;
  let ownsActiveTab = false;

  async function forceLogoutAndRedirect() {
    if (loggingOut) return;
    loggingOut = true;
    clearInterval(activeTabTimer);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
      await fetch(<?php echo json_encode(route('logout'), 15, 512) ?>, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        credentials: 'same-origin'
      });
    } finally {
      localStorage.setItem(loggedOutKey, String(Date.now()));
      window.location.replace(<?php echo json_encode(route('login'), 15, 512) ?>);
    }
  }

  function claimActiveTab() {
    const now = Date.now();
    const currentOwner = localStorage.getItem(activeTabKey);
    if (window.ApaoSingleTabGuard.hasActiveConflict(currentOwner, tabId, now)) {
      forceLogoutAndRedirect();
      return false;
    }

    localStorage.setItem(activeTabKey, JSON.stringify({ tabId: tabId, expiresAt: now + activeTabTtlMs }));
    ownsActiveTab = true;
    return true;
  }

  if (!claimActiveTab()) return;
  activeTabTimer = setInterval(claimActiveTab, activeTabTtlMs / 2);

  // Keep one same-page history entry so the browser Back button can securely
  // end the authenticated session instead of returning to a cached login or
  // dashboard entry with the session still active.
  window.history.pushState({ apaoSessionGuard: true }, '', window.location.href);
  window.addEventListener('popstate', function () {
    forceLogoutAndRedirect();
  }, { once: true });

  const banner = document.createElement('div');
  banner.id = 'session-timeout-banner';
  banner.setAttribute('role', 'alert');
  banner.style.cssText = 'display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;background:#92400e;color:#fef3c7;padding:12px 24px;border-radius:10px;font-size:.85rem;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.4);text-align:center;min-width:min(320px,calc(100vw - 32px));';
  banner.innerHTML = 'Your session will expire in <span data-session-countdown>60</span> seconds due to inactivity. <button type="button" data-session-stay-logged-in style="margin-left:12px;background:#fde68a;color:#92400e;border:none;border-radius:5px;padding:4px 12px;font-weight:700;cursor:pointer;">Stay Logged In</button>';
  document.body.appendChild(banner);

  function readLastActivity() {
    const value = Number(localStorage.getItem(activityKey));
    return Number.isFinite(value) && value > 0 ? value : Date.now();
  }

  function hideWarning() {
    banner.style.display = 'none';
    clearInterval(countdownTimer);
  }

  function updateCountdown() {
    const seconds = Math.max(0, Math.ceil((readLastActivity() + timeoutMs - Date.now()) / 1000));
    const countdown = banner.querySelector('[data-session-countdown]');
    if (countdown) countdown.textContent = seconds;
  }

  function showWarning() {
    if (loggingOut) return;
    banner.style.display = 'block';
    updateCountdown();
    clearInterval(countdownTimer);
    countdownTimer = setInterval(updateCountdown, 1000);
  }

  function scheduleFromSharedActivity() {
    clearTimeout(warningTimer);
    clearTimeout(logoutTimer);
    hideWarning();

    const remaining = readLastActivity() + timeoutMs - Date.now();
    if (remaining <= 0) {
      attemptLogout();
      return;
    }

    if (remaining <= warningMs) showWarning();
    else warningTimer = setTimeout(showWarning, remaining - warningMs);

    logoutTimer = setTimeout(attemptLogout, remaining);
  }

  function publishActivity(force) {
    if (loggingOut) return;
    const now = Date.now();
    if (!force && now - lastPublishedAt < 1000) return;
    lastPublishedAt = now;
    localStorage.setItem(activityKey, String(now));
    localStorage.removeItem(logoutLockKey);
    scheduleFromSharedActivity();
  }

  async function attemptLogout() {
    if (loggingOut) return;

    // A delayed background-tab timer must respect newer activity from any tab.
    if (Date.now() < readLastActivity() + timeoutMs) {
      scheduleFromSharedActivity();
      return;
    }

    const now = Date.now();
    let lock;
    try { lock = JSON.parse(localStorage.getItem(logoutLockKey) || 'null'); } catch (_) { lock = null; }
    if (lock && lock.expiresAt > now && lock.tabId !== tabId) {
      logoutTimer = setTimeout(attemptLogout, lock.expiresAt - now + 100);
      return;
    }

    localStorage.setItem(logoutLockKey, JSON.stringify({ tabId: tabId, expiresAt: now + 30000 }));
    try { lock = JSON.parse(localStorage.getItem(logoutLockKey) || 'null'); } catch (_) { lock = null; }
    if (!lock || lock.tabId !== tabId) {
      logoutTimer = setTimeout(attemptLogout, 1000);
      return;
    }

    loggingOut = true;
    hideWarning();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
      await fetch(<?php echo json_encode(route('logout'), 15, 512) ?>, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        credentials: 'same-origin'
      });
    } finally {
      localStorage.setItem(loggedOutKey, String(Date.now()));
      window.location.replace(<?php echo json_encode(route('login'), 15, 512) ?>);
    }
  }

  window.addEventListener('storage', function (event) {
    if (event.key === activityKey && event.newValue) scheduleFromSharedActivity();
    if (event.key === loggedOutKey && event.newValue) window.location.replace(<?php echo json_encode(route('login'), 15, 512) ?>);
    if (event.key === activeTabKey && event.newValue &&
        window.ApaoSingleTabGuard.hasActiveConflict(event.newValue, tabId, Date.now())) {
      forceLogoutAndRedirect();
    }
  });

  window.addEventListener('pagehide', function () {
    clearInterval(activeTabTimer);
    if (!ownsActiveTab) return;
    const owner = window.ApaoSingleTabGuard.parseOwner(localStorage.getItem(activeTabKey));
    if (owner && owner.tabId === tabId) localStorage.removeItem(activeTabKey);
  });

  ['pointerdown', 'pointermove', 'keydown', 'scroll', 'touchstart'].forEach(function (eventName) {
    document.addEventListener(eventName, function () { publishActivity(false); }, { passive: true });
  });
  window.addEventListener('focus', function () { publishActivity(true); });
  banner.querySelector('[data-session-stay-logged-in]').addEventListener('click', function () { publishActivity(true); });

  // Loading an authenticated page is activity and gives every open tab one shared deadline.
  publishActivity(true);
});
</script>
