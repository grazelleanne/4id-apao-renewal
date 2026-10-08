// Called as soon as the sidebar markup starts, before dashboard content paints.
document.addEventListener('DOMContentLoaded', () => {
  const bell = document.getElementById('notificationBell');
  const dropdown = document.getElementById('adminNotifDropdown') || document.getElementById('notifDropdown');
  const badge = document.getElementById('notificationBadge');
  if (!bell || !dropdown || !badge) return;
  // Keep the badge hidden while the user is viewing notifications.
  const hideViewedBadge = () => {
    if (dropdown.classList.contains('open') && badge.style.display !== 'none') badge.style.display = 'none';
  };
  new MutationObserver(hideViewedBadge).observe(badge, {attributes:true, childList:true});
  bell.addEventListener('click', () => queueMicrotask(async () => {
    if (!dropdown.classList.contains('open')) return;
    hideViewedBadge();
    try {
      const prefix = dropdown.id === 'adminNotifDropdown' ? '/admin' : '/staff';
      const response = await fetch(prefix + '/notifications/read', {
        method:'POST', credentials:'same-origin',
        headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content || ''}
      });
      if (!response.ok) return;
      const result = await response.json();
      if (!result.success) return;
      bell.classList.remove('has-unread');
      badge.textContent = '';
      badge.style.display = 'none';
      dropdown.querySelectorAll('.notif-item.unread').forEach(item => item.classList.remove('unread'));
      dropdown.querySelectorAll('.notif-dot').forEach(dot => dot.remove());
      const footer = dropdown.querySelector('.notif-footer');
      if (footer) footer.textContent = 'All caught up!';
    } catch (_) { /* Keep the existing notification refresh available for retry. */ }
  }));
});
document.addEventListener('DOMContentLoaded', () => {
  const main = document.querySelector('main');
  const header = main?.querySelector(':scope > header');
  if (!header) return;
  const update = () => {
    main.style.setProperty('--dashboard-gutter', getComputedStyle(main).paddingLeft);
    main.style.setProperty('--dashboard-header-height', `${Math.ceil(header.getBoundingClientRect().height)}px`);
  };
  requestAnimationFrame(update);
  new ResizeObserver(update).observe(header);
  window.addEventListener('resize', update);
});
window.restoreDashboardState = function () {
  try {
    const sidebar = document.getElementById('sidebar');
    const collapsed = localStorage.getItem('sb') === '1';
    sidebar.classList.toggle('sidebar-collapsed', collapsed);
    document.body.classList.toggle('light-mode', (localStorage.getItem('theme') || 'light') === 'light');
    // Keep the initial restore still; normal toggle transitions resume after loading.
    document.documentElement.classList.add('dashboard-restoring');
    document.addEventListener('DOMContentLoaded', () => {
      requestAnimationFrame(() => requestAnimationFrame(() => {
        document.documentElement.classList.remove('dashboard-restoring');
      }));
    }, {once:true});
  } catch (_) {
    // Storage may be disabled. Keep the default expanded, light layout.
  }
};
