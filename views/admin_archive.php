<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="/css/mobile-dashboard.css">
  <script defer src="/js/mobile-dashboard.js"></script>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Admin - Archive Data</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="/tailwind.min.js"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: { sidebarBg: '#181c21', sidebarDark: '#23272f', accent: '#3ec6ff', mainBg: '#1a2025' },
          fontFamily: { inter: ['Inter', 'system-ui', 'sans-serif'] }
        }
      }
    }
  </script>
  <style>
    /* ===== SIDEBAR ===== */
    #sidebar{width:240px;min-width:64px;background:#181c21;border-right:1px solid #23272f;display:flex;flex-direction:column;padding:16px 12px;transition:width 0.28s cubic-bezier(.4,0,.2,1);overflow:hidden;position:sticky;top:0;height:100vh;}
    #sidebar.sidebar-collapsed{width:64px;}
    .sb-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;min-height:40px;position:relative;}
    #sidebar.sidebar-collapsed .sb-top{justify-content:center;}
    .sb-logo{display:flex;align-items:center;gap:10px;overflow:hidden;flex:1;}
    .sb-logo img{width:38px;height:38px;border-radius:50%;border:2px solid #3ec6ff;object-fit:cover;flex-shrink:0;background:#23272f;}
    .sb-logo-text{color:#e5eaf2;font-weight:700;font-size:1rem;white-space:nowrap;transition:opacity 0.2s,width 0.2s;overflow:hidden;}
    #sidebar.sidebar-collapsed .sb-logo-text{opacity:0;width:0;}
    .sb-toggle{background:transparent;border:none;color:#94a3b8;cursor:pointer;padding:6px;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background 0.15s,color 0.15s;}
    .sb-toggle:hover{background:#23272f;color:#e5eaf2;}
    #sidebar.sidebar-collapsed .sb-toggle{position:absolute;top:0;left:50%;transform:translateX(-50%);}
    #sidebar.sidebar-collapsed .sb-logo{flex:0;}
    nav.sb-nav{flex:1;display:flex;flex-direction:column;gap:2px;}
    .nav-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;color:#94a3b8;text-decoration:none;font-size:0.85rem;font-weight:500;white-space:nowrap;transition:background 0.15s,color 0.15s;overflow:hidden;}
    .nav-item:hover{background:#23272f;color:#e5eaf2;}
    .nav-item.active{background:#23272f;color:#e5eaf2;}
    .nav-item svg{width:20px;height:20px;flex-shrink:0;}
    .nav-label{transition:opacity 0.15s,width 0.15s;overflow:hidden;white-space:nowrap;}
    #sidebar.sidebar-collapsed .nav-label{opacity:0;width:0;}
    #sidebar.sidebar-collapsed .nav-item{justify-content:center;padding:9px 0;gap:0;}
    .sb-bottom{padding-top:12px;border-top:1px solid #23272f;display:flex;justify-content:center;}
    .theme-btn{background:#23272f;border:none;color:#94a3b8;cursor:pointer;padding:8px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:background 0.15s,color 0.15s;}
    .theme-btn:hover{background:#2d3340;color:#e5eaf2;}

    /* ===== LIGHT MODE ===== */
    body.light-mode{background:#f1f5fa;color:#222;}
    body.light-mode .main-bg{background:#f1f5fa !important;}
    body.light-mode .bg-\[\#1a2025\]{background-color:#f1f5fa !important;}
    body.light-mode .bg-\[\#23272f\]{background-color:#e6eaf4 !important;}
    body.light-mode #sidebar{background:#f7fafc;border-color:#e2e8f0;}
    body.light-mode .nav-item{color:#64748b;}
    body.light-mode .nav-item:hover,body.light-mode .nav-item.active{background:#e8edf5;color:#1e293b;}
    body.light-mode .sb-logo-text{color:#1e293b;}
    body.light-mode .sb-bottom{border-color:#e2e8f0;}
    body.light-mode .theme-btn{background:#e8edf5;color:#64748b;}
    body.light-mode .force-light-text{color:#222 !important;}
    body.light-mode input,body.light-mode select{background:#f3f7fa !important;color:#232634 !important;border-color:#cdd5ea !important;}
    body.light-mode .rounded-lg{background:#f8fafc !important;}

    /* ===== NOTIFICATION BELL ===== */
    .notification-bell{position:relative;cursor:pointer;outline:none;transition:color 0.2s ease;}
    .notification-badge{position:absolute;top:-4px;right:-4px;background:#ef4444;color:white;font-weight:bold;border-radius:50%;font-size:0.62rem;min-width:17px;height:17px;display:none;align-items:center;justify-content:center;border:2px solid #1a2025;animation:bell-pop 0.3s ease-out;}
    .notification-bell.has-unread .notification-badge{display:flex;}
    @keyframes bell-pop{0%{transform:scale(0);opacity:0}50%{transform:scale(1.2)}100%{transform:scale(1);opacity:1}}
    #adminNotifDropdown{display:none;position:absolute;top:calc(100% + 10px);right:0;width:320px;background:#23272f;border:1px solid #363b48;border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,0.4);z-index:200;overflow:hidden;}
    #adminNotifDropdown.open{display:block;animation:fadeDown 0.18s ease;}
    @keyframes fadeDown{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
    .notif-header{padding:12px 16px;border-bottom:1px solid #363b48;display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em;}
    .notif-mark-read{font-size:0.72rem;color:#3ec6ff;cursor:pointer;font-weight:600;text-transform:none;letter-spacing:0;}
    .notif-mark-read:hover{text-decoration:underline;}
    .notif-list{max-height:340px;overflow-y:auto;}
    .notif-list::-webkit-scrollbar{width:4px;}
    .notif-list::-webkit-scrollbar-track{background:#1a2025;}
    .notif-list::-webkit-scrollbar-thumb{background:#363b48;border-radius:4px;}
    .notif-item{padding:12px 16px;border-bottom:1px solid #2e333d;display:flex;gap:10px;align-items:flex-start;font-size:0.8rem;color:#cbd5e0;transition:background 0.15s;}
    .notif-item:last-child{border-bottom:none;}
    .notif-item.unread{background:#1c2631;}
    .notif-item:hover{background:#1a2025;}
    .notif-icon{flex-shrink:0;margin-top:2px;}
    .notif-content{flex:1;min-width:0;}
    .notif-title{font-weight:700;font-size:0.78rem;color:#e5eaf2;margin-bottom:2px;}
    .notif-message{color:#94a3b8;line-height:1.4;font-size:0.75rem;}
    .notif-time{color:#4b5563;font-size:0.7rem;margin-top:4px;}
    .notif-dot{width:7px;height:7px;border-radius:50%;background:#3ec6ff;flex-shrink:0;margin-top:5px;}
    .notif-empty{padding:24px 16px;text-align:center;color:#4b5563;font-size:0.8rem;}
    .notif-footer{padding:10px 16px;border-top:1px solid #363b48;text-align:center;font-size:0.72rem;color:#4b5563;}
   body.light-mode .notification-bell{color:#0284c7 !important;}
    body.light-mode #adminNotifDropdown{background:#fff;border-color:#d0d7e4;}
    body.light-mode .notif-title{color:#1e293b!important;}
    body.light-mode .notif-message{color:#475569!important;}
    body.light-mode .notif-time{color:#94a3b8!important;}
    body.light-mode .notif-empty{color:#94a3b8!important;}
    body.light-mode .notif-footer{color:#64748b!important;}
    body.light-mode .notif-header{color:#475569!important;}
   body.light-mode .notif-item{color:#1e293b!important;background:#ffffff!important;}
    body.light-mode .notif-item.unread{background:#eff6ff!important;}
    body.light-mode .notif-item:hover{background:#f8fafc!important;}
    body.light-mode .notif-item.unread:hover{background:#e0f0ff!important;}
    body.light-mode #adminNotifDropdown .notif-list::-webkit-scrollbar-track{background:#f1f5f9!important;}

    /* ===== TABLE ===== */
    .force-light-text{color:#e5eaf2;}
    table.archive-table{width:100%;border-collapse:collapse;margin-top:1rem;}
    table.archive-table th,table.archive-table td{border:1px solid #333745;padding:0.6rem 1rem;text-align:left;font-size:0.82rem;}
    table.archive-table th{background:#1d232d;color:#b0bac7;font-weight:600;}
    table.archive-table td{background:#23272f;color:#e5eaf2;}
    table.archive-table tbody tr:hover td{background:#2a3140;}
    body.light-mode table.archive-table th{background:#f1f5f9 !important;color:#4b5563 !important;border-color:#e5e7eb;}
    body.light-mode table.archive-table td{background:#ffffff !important;color:#1f2937 !important;border-color:#e5e7eb;}
    body.light-mode table.archive-table tbody tr:hover td{background:#eef4fb !important;}
    .action-btn{padding:0.3rem;border-radius:0.4rem;transition:background 0.15s;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;background:transparent;border:none;}
    .action-btn:hover{background:rgba(255,255,255,0.08);}
    body.light-mode .action-btn:hover{background:#e7eef8;}
    .restore-btn{color:#22d3ee;}
    .delete-btn{color:#ef4444;}

    /* ===== MODALS ===== */
    .modal-bg{position:fixed;inset:0;background:rgba(24,28,33,0.72);backdrop-filter:blur(2px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:1rem;}
    .modal-box{background:#202631;color:#e5eaf2;padding:2rem 1.75rem 1.5rem;border-radius:0.95rem;border:1px solid #333b4d;box-shadow:0 20px 42px rgba(0,0,0,0.35);width:min(480px,94vw);position:relative;}
    body.light-mode .modal-bg{background:rgba(15,23,42,0.35);}
    body.light-mode .modal-box{background:#ffffff;color:#1f2937;border-color:#d2dbe9;}
    .modal-close-btn{position:absolute;right:0.75rem;top:0.75rem;border:none;background:none;color:#b0bac7;font-size:1.35rem;cursor:pointer;}
  </style>
  <link rel="stylesheet" href="/css/typography.css">
</head>
<body class="light-mode min-h-screen font-inter main-bg bg-[#1a2025]">
<div class="flex min-h-screen">

  <!-- SIDEBAR -->
  <aside id="sidebar">
    <div class="sb-top">
      <div class="sb-logo">
        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Logo" onerror="this.src=''">
        <span class="sb-logo-text">Admin</span>
      </div>
      <button class="sb-toggle" id="sidebarToggleBtn" title="Toggle sidebar">
        <svg id="sb-icon-menu" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg id="sb-icon-close" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <nav class="sb-nav">
      <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-item">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        <span class="nav-label">Dashboard</span>
      </a>
      <a href="<?php echo e(route('admin.personnel')); ?>" class="nav-item">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        <span class="nav-label">List of Personnel</span>
      </a>
      <a href="<?php echo e(route('admin.inspection')); ?>" class="nav-item">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="nav-label">Inspection/Renewal</span>
      </a>
      <a href="<?php echo e(route('admin.reports')); ?>" class="nav-item">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
        <span class="nav-label">Report</span>
      </a>
      <a href="<?php echo e(route('admin.archive')); ?>" class="nav-item active">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 8v13H3V8"/><path d="M23 3H1v5h22V3z"/><path d="M10 12h4"/></svg>
        <span class="nav-label">Archive Data</span>
      </a>
      <a href="<?php echo e(route('admin.users')); ?>" class="nav-item">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span class="nav-label">Manage Users</span>
      </a>
      <a href="<?php echo e(route('admin.audit')); ?>" class="nav-item">
  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/><circle cx="17" cy="17" r="4"/><path d="M19.5 19.5L21 21"/></svg>
  <span class="nav-label">Audit Log</span>
</a>
    </nav>
    <div class="sb-bottom">
      <button class="theme-btn" id="themeToggle" title="Toggle theme">
        <svg id="icon-sun" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        <svg id="icon-moon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><path d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/></svg>
      </button>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="flex-1 main-bg bg-[#1a2025] p-7 overflow-y-auto">

    <header class="flex flex-wrap justify-between mb-8 items-center gap-4">
      <h1 class="text-2xl font-bold tracking-tight force-light-text">Archive Data</h1>
      <div class="flex items-center gap-4">
        <div class="relative w-72">
          <input id="searchInput" type="text" placeholder="Search archive..." class="bg-[#23272f] text-white border border-[#363b48] rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-accent pr-10 force-light-text" />
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M21 21l-4.35-4.35M5 11a6 6 0 1112 0 6 6 0 01-12 0z"/></svg>
        </div>
        <!-- DYNAMIC NOTIFICATION BELL -->
        <div class="relative" id="adminNotifWrapper">
          <button id="notificationBell" class="notification-bell text-cyan-400 focus:outline-none" aria-label="Notifications" type="button">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11c0-3.074-1.64-5.64-5-5.996V5a2 2 0 10-4 0v.004C6.64 5.36 5 7.926 5 11v3.159c0 .538-.214 1.055-.595 1.436L3 17h5m7 0v1a3 3 0 01-6 0v-1m7 0H8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="notification-badge" id="notificationBadge"></span>
          </button>
          <div id="adminNotifDropdown">
            <div class="notif-header">
              <span>Notifications</span>
              <span class="notif-mark-read" id="adminMarkAllRead">Mark all read</span>
            </div>
            <div class="notif-list" id="adminNotifList"><div class="notif-empty">Loading...</div></div>
            <div class="notif-footer" id="adminNotifFooter">Auto-refreshes every 30 seconds</div>
          </div>
        </div>

        <?php echo $__env->make('partials.account_dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

      </div>
    </header>

    <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 mb-10">
      <h2 class="font-semibold text-base force-light-text tracking-tight mb-4">Archived Personnel</h2>
      <div class="overflow-x-auto rounded-lg">
        <table class="archive-table">
          <thead>
            <tr>
              <th>Item #</th>
              <th>Rank</th>
              <th>Last Name</th>
              <th>First Name</th>
              <th>Unit</th>
              <th>Date Archived</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody id="archiveTableBody"></tbody>
        </table>
        <div id="noArchivedDataMsg" style="display:none;" class="py-8 text-center text-gray-400 text-base">No archived records found.</div>
      </div>
    </div>

  </main>
</div>

<div id="restoreModal" style="display:none;"></div>
<div id="deleteArchiveModal" style="display:none;"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const ROUTES = {
  archiveData:    "<?php echo e(route('admin.archive.data')); ?>",
  archiveRestore: "<?php echo e(route('admin.archive.restore')); ?>",
  archiveDelete:  (id) => `/admin/archive-data/${encodeURIComponent(id)}`,
  login:          "<?php echo e(route('login')); ?>",
};

let archivedPersonnel = [];

document.addEventListener("DOMContentLoaded", function () {

  // ===== SIDEBAR =====
  const sidebar     = document.getElementById('sidebar');
  const toggleBtn   = document.getElementById('sidebarToggleBtn');
  const sbIconMenu  = document.getElementById('sb-icon-menu');
  const sbIconClose = document.getElementById('sb-icon-close');
  if (localStorage.getItem('sb') === '1') {
    sidebar.classList.add('sidebar-collapsed');
    sbIconMenu.style.display  = '';
    sbIconClose.style.display = 'none';
  } else {
    sbIconMenu.style.display  = 'none';
    sbIconClose.style.display = '';
  }
  toggleBtn.addEventListener('click', function () {
    const collapsed = sidebar.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sb', collapsed ? '1' : '0');
    sbIconMenu.style.display  = collapsed ? '' : 'none';
    sbIconClose.style.display = collapsed ? 'none' : '';
  });

  // ===== THEME =====
  const iconSun  = document.getElementById('icon-sun');
  const iconMoon = document.getElementById('icon-moon');
  function applyTheme(t) {
    document.body.classList.toggle('light-mode', t === 'light');
    iconSun.style.display  = t === 'light' ? 'none' : '';
    iconMoon.style.display = t === 'light' ? '' : 'none';
  }
  let currentTheme = localStorage.getItem('theme') || 'light';
  applyTheme(currentTheme);
  document.getElementById('themeToggle').addEventListener('click', function () {
    const next = currentTheme === 'light' ? 'dark' : 'light';
    currentTheme = next;
    localStorage.setItem('theme', next);
    applyTheme(next);
  });

  // ===== DYNAMIC NOTIFICATION BELL =====
  const bell        = document.getElementById('notificationBell');
  const badge       = document.getElementById('notificationBadge');
  const dropdown    = document.getElementById('adminNotifDropdown');
  const notifList   = document.getElementById('adminNotifList');
  const notifFooter = document.getElementById('adminNotifFooter');
  const markAllRead = document.getElementById('adminMarkAllRead');
  const ADMIN_NOTIF_URL      = "<?php echo e(route('admin.notifications')); ?>";
  const ADMIN_NOTIF_READ_URL = "<?php echo e(route('admin.notifications.read')); ?>";

  function timeAgo(dateStr) {
    const diff = Math.floor((new Date() - new Date(dateStr)) / 1000);
    if (diff < 60)    return "Just now";
    if (diff < 3600)  return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return new Date(dateStr).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
  }

  function getNotifIcon(type) {
    const icons = {
      approval_changed:   `<svg class="w-4 h-4" style="color:#3ec6ff" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
      email_sent:         `<svg class="w-4 h-4" style="color:#33b481" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>`,
      personnel_added:    `<svg class="w-4 h-4" style="color:#33b481" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 4v16m8-8H4"/></svg>`,
      personnel_updated:  `<svg class="w-4 h-4" style="color:#ecc94b" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>`,
      personnel_archived: `<svg class="w-4 h-4" style="color:#fc8181" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>`,
    };
    return icons[type] || `<svg class="w-4 h-4" style="color:#94a3b8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>`;
  }

  async function loadNotifications() {
    try {
      const res  = await fetch(ADMIN_NOTIF_URL, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
      const json = await res.json();
      if (!json.success) return;
      const count = json.unreadCount || 0;
      if (count > 0) {
        bell.classList.add('has-unread');
        badge.style.display = 'flex';
        badge.textContent   = count > 99 ? '99+' : String(count);
      } else {
        bell.classList.remove('has-unread');
        badge.style.display = 'none';
      }
      notifFooter.textContent = count > 0 ? `${count} unread notification${count > 1 ? 's' : ''}` : 'All caught up!';
      if (!json.notifications || !json.notifications.length) {
        notifList.innerHTML = `<div class="notif-empty">No notifications yet.</div>`;
        return;
      }
      notifList.innerHTML = json.notifications.map(n => `
        <div class="notif-item ${!n.read ? 'unread' : ''}">
          <div class="notif-icon">${getNotifIcon(n.type)}</div>
          <div class="notif-content">
            <div class="notif-title">${n.title}</div>
            <div class="notif-message">${n.message}</div>
            <div class="notif-time">${timeAgo(n.createdAt)}</div>
          </div>
          ${!n.read ? `<div class="notif-dot"></div>` : ''}
        </div>`).join('');
    } catch (e) {
      notifList.innerHTML = `<div class="notif-empty" style="color:#fc8181;">Failed to load notifications.</div>`;
    }
  }

  bell.addEventListener('click', function (e) {
    e.stopPropagation();
    const isOpen = dropdown.classList.contains('open');
    dropdown.classList.toggle('open');
    if (!isOpen) {
      bell.classList.remove('has-unread');
      badge.style.display = 'none';
      badge.textContent = '';
      fetch(ADMIN_NOTIF_READ_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' } }).finally(loadNotifications);
    }
  });
  document.addEventListener('click', function (e) {
    if (!document.getElementById('adminNotifWrapper').contains(e.target)) {
      dropdown.classList.remove('open');
    }
  });
  markAllRead.addEventListener('click', async function () {
    try {
      await fetch(ADMIN_NOTIF_READ_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
      await loadNotifications();
      dropdown.classList.remove('open');
    } catch (e) {}
  });
  loadNotifications();
  setInterval(loadNotifications, 30000);

  // ===== ARCHIVE DATA =====
  loadArchive();
  document.getElementById('searchInput').addEventListener('input', renderArchiveTable);
  document.addEventListener('keydown', function (evt) {
    if (evt.key === "Escape") {
      document.getElementById('restoreModal').style.display = 'none';
      document.getElementById('deleteArchiveModal').style.display = 'none';
    }
  });
});

async function loadArchive() {
  try {
    const res  = await fetch("<?php echo e(route('admin.archive.data')); ?>");
    const json = await res.json();
    archivedPersonnel = (json.success && json.data) ? json.data : [];
  } catch (e) { archivedPersonnel = []; }
  renderArchiveTable();
}

function renderArchiveTable() {
  const tbody = document.getElementById('archiveTableBody');
  const term  = (document.getElementById('searchInput').value || '').trim().toLowerCase();
  let filtered = archivedPersonnel.filter(row =>
    (row.rank     || '').toLowerCase().includes(term) ||
    (row.lastName || '').toLowerCase().includes(term) ||
    (row.firstName|| '').toLowerCase().includes(term) ||
    (row.unit     || '').toLowerCase().includes(term) ||
    String(row.itemNumber || '').toLowerCase().includes(term)
  );
  tbody.innerHTML = '';
  if (filtered.length === 0) {
    document.getElementById('noArchivedDataMsg').style.display = '';
    return;
  }
  document.getElementById('noArchivedDataMsg').style.display = 'none';
  filtered.forEach(row => {
    const dateStr = row.dateArchived ? new Date(row.dateArchived).toLocaleDateString() : '-';
    tbody.innerHTML += `<tr>
      <td>${row.itemNumber || '-'}</td>
      <td>${row.rank       || '-'}</td>
      <td>${row.lastName   || '-'}</td>
      <td>${row.firstName  || '-'}</td>
      <td>${row.unit       || '-'}</td>
      <td>${dateStr}</td>
      <td class="text-center">
        <button class="action-btn restore-btn" title="Restore" onclick="showRestoreModal('${row.itemNumber}')">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
        <button class="action-btn delete-btn ml-1" title="Permanently Delete" onclick="showDeleteArchiveModal('${row.itemNumber}')">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
      </td>
    </tr>`;
  });
}

function showRestoreModal(itemNumber) {
  const row = archivedPersonnel.find(p => String(p.itemNumber) === String(itemNumber));
  if (!row) return;
  const modal = document.getElementById('restoreModal');
  modal.innerHTML = `<div class="modal-bg"><div class="modal-box"><button class="modal-close-btn" onclick="document.getElementById('restoreModal').style.display='none'">&times;</button><h3 class="font-semibold mb-4 text-lg">Restore Archived Personnel</h3><p class="mb-4">Restore <strong>${row.rank||''} ${row.lastName||''}, ${row.firstName||''}</strong> (Unit: ${row.unit||'-'}) to active records?</p><div class="flex justify-end gap-2 mt-6"><button onclick="document.getElementById('restoreModal').style.display='none'" class="px-3 py-1.5 rounded bg-[#333b4d] text-gray-200 hover:bg-[#404a5e] text-sm">Cancel</button><button onclick="restoreArchived('${itemNumber}')" class="px-4 py-1.5 rounded bg-emerald-600 text-white font-semibold hover:bg-emerald-700 text-sm">Restore</button></div></div></div>`;
  modal.style.display = '';
}

async function restoreArchived(itemNumber) {
  try {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const formData = new FormData();
    formData.append('id', itemNumber);
    formData.append('_token', CSRF);
    const res  = await fetch("<?php echo e(route('admin.archive.restore')); ?>", { method: 'POST', body: formData });
    const json = await res.json();
    if (json.success) { document.getElementById('restoreModal').style.display = 'none'; loadArchive(); alert('Personnel restored successfully!'); }
    else alert(json.error || 'Restore failed.');
  } catch (e) { alert('Something went wrong.'); }
}

function showDeleteArchiveModal(itemNumber) {
  const row = archivedPersonnel.find(p => String(p.itemNumber) === String(itemNumber));
  if (!row) return;
  const modal = document.getElementById('deleteArchiveModal');
  modal.innerHTML = `<div class="modal-bg"><div class="modal-box"><button class="modal-close-btn" onclick="document.getElementById('deleteArchiveModal').style.display='none'">&times;</button><h3 class="font-semibold mb-3 text-lg text-red-400">Permanently Delete</h3><p class="mb-1 font-semibold">${row.rank||''} ${row.lastName||''}, ${row.firstName||''}</p><p class="mb-4 text-sm text-red-400">This action cannot be undone. Are you sure?</p><div class="flex justify-end gap-2 mt-6"><button onclick="document.getElementById('deleteArchiveModal').style.display='none'" class="px-3 py-1.5 rounded bg-[#333b4d] text-gray-200 hover:bg-[#404a5e] text-sm">Cancel</button><button onclick="deleteArchived('${itemNumber}')" class="px-4 py-1.5 rounded bg-red-600 text-white font-semibold hover:bg-red-700 text-sm">Delete</button></div></div></div>`;
  modal.style.display = '';
}

async function deleteArchived(itemNumber) {
  try {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const res  = await fetch(`/admin/archive-data/${encodeURIComponent(itemNumber)}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
    const json = await res.json();
    if (json.success) { document.getElementById('deleteArchiveModal').style.display = 'none'; loadArchive(); alert('Archived record deleted.'); }
    else alert(json.error || 'Delete failed.');
  } catch (e) { alert('Something went wrong.'); }
}

</script>
</body>
</html>

