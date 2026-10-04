(function () {
  'use strict';

  function initMobileDashboard() {
    var sidebar = document.getElementById('sidebar');
    if (!sidebar || document.querySelector('.mobile-menu-button')) return;

    document.body.classList.add('mobile-dashboard');

    var menuButton = document.createElement('button');
    menuButton.type = 'button';
    menuButton.className = 'mobile-menu-button';
    menuButton.setAttribute('aria-label', 'Open navigation menu');
    menuButton.setAttribute('aria-controls', 'sidebar');
    menuButton.setAttribute('aria-expanded', 'false');
    menuButton.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>';

    var overlay = document.createElement('div');
    overlay.className = 'mobile-sidebar-overlay';
    overlay.setAttribute('aria-hidden', 'true');

    document.body.appendChild(menuButton);
    document.body.appendChild(overlay);

    function isMobile() {
      return window.matchMedia('(max-width: 768px)').matches;
    }

    function setOpen(open) {
      if (!isMobile()) open = false;
      sidebar.classList.toggle('mobile-open', open);
      overlay.classList.toggle('open', open);
      document.body.classList.toggle('mobile-nav-open', open);
      menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
      menuButton.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
    }

    menuButton.addEventListener('click', function () {
      setOpen(!sidebar.classList.contains('mobile-open'));
    });
    overlay.addEventListener('click', function () { setOpen(false); });
    sidebar.querySelectorAll('a, .nav-item, .nav-link').forEach(function (item) {
      item.addEventListener('click', function () {
        if (isMobile()) setOpen(false);
      });
    });

    var sidebarToggle = document.getElementById('sidebarToggleBtn');
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', function (event) {
        if (!isMobile()) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        setOpen(false);
      }, true);
    }

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') setOpen(false);
    });
    window.addEventListener('resize', function () {
      if (!isMobile()) setOpen(false);
    });

    document.querySelectorAll('main table').forEach(function (table) {
      if (table.closest('.mobile-table-scroll, .par-preview-scale, .par-receipt, .rpcsp-document')) return;
      var wrapper = document.createElement('div');
      wrapper.className = 'mobile-table-scroll';
      table.parentNode.insertBefore(wrapper, table);
      wrapper.appendChild(table);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileDashboard);
  } else {
    initMobileDashboard();
  }
}());
