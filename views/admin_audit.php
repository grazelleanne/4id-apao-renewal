<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="/css/mobile-dashboard.css">
  <script defer src="/js/mobile-dashboard.js"></script>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Admin - Audit Log</title>
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
          fontFamily: { inter: ['Inter', 'system-ui', 'sans-serif'] },
        },
      },
    };
  </script>
  <style>
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

    body,. main-bg{transition:background 0.2s,color 0.2s;}
    body.light-mode{background:#f1f5fa;color:#222;}
    body.light-mode .main-bg{background:#f1f5fa !important;}
    body.light-mode .bg-\[\#1a2025\]{background-color:#f1f5fa !important;}
    body.light-mode #sidebar{background:#f7fafc;border-color:#e2e8f0;}
    body.light-mode .nav-item{color:#64748b;}
    body.light-mode .nav-item:hover,body.light-mode .nav-item.active{background:#e8edf5;color:#1e293b;}
    body.light-mode .sb-logo-text{color:#1e293b;}
    body.light-mode .sb-bottom{border-color:#e2e8f0;}
    body.light-mode .theme-btn{background:#e8edf5;color:#64748b;}
    body.light-mode .force-light-text{color:#222 !important;}
    body.light-mode .notification-bell{color:#0284c7 !important;}

    .force-light-text{color:#e5eaf2;}

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
    body.light-mode #adminNotifDropdown{background:#fff;border-color:#d0d7e4;}
    body.light-mode .notif-header{border-color:#e5e7eb;color:#6b7280;}
    body.light-mode .notif-item{color:#374151;border-color:#e5e7eb;}
    body.light-mode .notif-item.unread{background:#eff6ff;}
    body.light-mode .notif-item:hover{background:#f8fafc;}
    body.light-mode .notif-message{color:#6b7280;}
    body.light-mode .notif-time{color:#9ca3af;}
    body.light-mode .notif-footer{border-color:#e5e7eb;}
    body.light-mode .notification-badge{border-color:#f1f5fa;}

    /* AUDIT TABLE */
    .audit-panel{border:1px solid #313848;border-radius:0.9rem;background:#222831;box-shadow:0 10px 28px rgba(0,0,0,0.16);}
    table.audit-table th{background:#1d232d;color:#b7c2d0;font-weight:600;font-size:0.74rem;letter-spacing:0.03em;white-space:nowrap;}
    table.audit-table td{background:#222831;color:#e5eaf2;border-bottom:1px solid #343b4b;white-space:nowrap;}
    table.audit-table tbody tr:hover td{background:#2a3140;}
    body.light-mode .audit-panel{background:#ffffff !important;border-color:#d5dce8 !important;}
    body.light-mode table.audit-table th{background:#f1f5f9 !important;color:#4b5563 !important;}
    body.light-mode table.audit-table td{background:#ffffff !important;color:#1f2937 !important;border-bottom:1px solid #e5eaf2 !important;}
    body.light-mode table.audit-table tbody tr:hover td{background:#eef4fb !important;}

    /* ACTION BADGES */
    .action-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:999px;font-size:0.68rem;font-weight:700;white-space:nowrap;}
    .ab-login{background:#0d2535;color:#3ec6ff;}
    .ab-logout{background:#1e2430;color:#64748b;}
    .ab-personnel_added{background:#0d3325;color:#33b481;}
    .ab-personnel_updated{background:#2d2a0a;color:#ecc94b;}
    .ab-personnel_archived{background:#2d1a0a;color:#f97316;}
    .ab-personnel_restored{background:#0d2535;color:#818cf8;}
    .ab-personnel_deleted{background:#2d0a0a;color:#fc8181;}
    .ab-approval_changed{background:#1a1f35;color:#818cf8;}
    .ab-email_sent{background:#0d3325;color:#33b481;}
    .ab-user_created{background:#0d3325;color:#33b481;}
    .ab-user_updated{background:#2d2a0a;color:#ecc94b;}
    .ab-default{background:#1e2430;color:#94a3b8;}
    body.light-mode .ab-login{background:#e0f2fe;color:#0369a1;}
    body.light-mode .ab-logout{background:#f1f5f9;color:#475569;}
    body.light-mode .ab-personnel_added{background:#d1fae5;color:#065f46;}
    body.light-mode .ab-personnel_updated{background:#fef9c3;color:#92400e;}
    body.light-mode .ab-personnel_archived{background:#ffedd5;color:#c2410c;}
    body.light-mode .ab-personnel_restored{background:#ede9fe;color:#5b21b6;}
    body.light-mode .ab-personnel_deleted{background:#fee2e2;color:#991b1b;}
    body.light-mode .ab-approval_changed{background:#ede9fe;color:#5b21b6;}
    body.light-mode .ab-email_sent{background:#d1fae5;color:#065f46;}
    body.light-mode .ab-user_created{background:#d1fae5;color:#065f46;}
    body.light-mode .ab-user_updated{background:#fef9c3;color:#92400e;}
    body.light-mode .ab-default{background:#f1f5f9;color:#475569;}

    /* FILTER INPUTS */
    .filter-input,.filter-select{background:#1f2530 !important;border:1px solid #3b4456 !important;color:#e5eaf2 !important;border-radius:0.5rem;font-size:0.8rem;}
    .filter-input::placeholder{color:#8d99ab;}
    .filter-input:focus,.filter-select:focus{border-color:#3ec6ff !important;outline:none;box-shadow:0 0 0 2px rgba(62,198,255,0.18);}
    body.light-mode .filter-input,body.light-mode .filter-select{background:#f8fafd !important;color:#1f2937 !important;border:1px solid #ced8e6 !important;}

    /* DETAILS MODAL */
    .modal-bg{position:fixed;inset:0;background:rgba(24,28,33,0.72);backdrop-filter:blur(2px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:1rem;}
    .modal-box{background:#202631;color:#e5eaf2;padding:1.5rem;border-radius:0.95rem;border:1px solid #333b4d;box-shadow:0 20px 42px rgba(0,0,0,0.35);width:min(540px,94vw);max-height:85vh;overflow-y:auto;position:relative;}
    body.light-mode .modal-box{background:#ffffff;color:#1f2937;border-color:#d2dbe9;}
    .modal-close-btn{position:absolute;right:0.75rem;top:0.75rem;border:none;background:none;color:#b0bac7;font-size:1.25rem;cursor:pointer;}
    .detail-row{display:flex;gap:0.5rem;padding:0.45rem 0;border-bottom:1px solid #2e3748;font-size:0.83rem;}
    .detail-row:last-child{border-bottom:none;}
    .detail-label{color:#94a3b8;font-weight:600;min-width:110px;flex-shrink:0;}
    .detail-value{color:#e5eaf2;word-break:break-all;}
    body.light-mode .detail-row{border-color:#e5e7eb;}
    body.light-mode .detail-label{color:#64748b;}
    body.light-mode .detail-value{color:#1f2937;}

    /* EXPORT BTN */
    .export-btn{background:#0d3325;color:#33b481;border:1px solid #1a5c3a;border-radius:0.5rem;font-weight:600;font-size:0.78rem;padding:0.42rem 0.9rem;cursor:pointer;transition:background 0.15s;}
    .export-btn:hover{background:#154d32;}
  </style>
  <link rel="stylesheet" href="/css/typography.css">
  <link rel="stylesheet" href="/css/dashboard-theme.css">
</head>
<body class="app-dashboard light-mode min-h-screen font-inter main-bg bg-[#1a2025]">
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
      <a href="<?php echo e(route('admin.archive')); ?>" class="nav-item">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 8v13H3V8"/><path d="M23 3H1v5h22V3z"/><path d="M10 12h4"/></svg>
        <span class="nav-label">Archive Data</span>
      </a>
      <a href="<?php echo e(route('admin.users')); ?>" class="nav-item">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span class="nav-label">Manage Users</span>
      </a>
      <a href="<?php echo e(route('admin.audit')); ?>" class="nav-item active">
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

    <!-- HEADER -->
    <header class="flex flex-wrap justify-between mb-8 items-center gap-4">
      <div>
        <h1 class="text-xl font-bold force-light-text tracking-tight">Audit Log</h1>
        <p class="text-xs text-[#64748b] mt-0.5">Complete record of all system actions and user activity.</p>
      </div>
      <div class="flex flex-row-reverse items-center gap-4">
        <?php echo $__env->make('partials.account_dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <!-- NOTIFICATION BELL -->
        <div class="relative" id="adminNotifWrapper">
          <button id="notificationBell" class="notification-bell text-cyan-400 focus:outline-none" aria-label="Notifications" type="button">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11c0-3.074-1.64-5.64-5-5.996V5a2 2 0 10-4 0v.004C6.64 5.36 5 7.926 5 11v3.159c0 .538-.214 1.055-.595 1.436L3 17h5m7 0v1a3 3 0 01-6 0v-1m7 0H8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
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
      </div>
    </header>

    <!-- FILTERS -->
    <div class="audit-panel p-5 mb-6">
      <div class="flex flex-wrap gap-3 items-end mb-4">
        <div class="flex flex-col gap-1">
          <label class="text-xs text-[#94a3b8] font-semibold">Date From</label>
          <input id="filterDateFrom" type="date" class="filter-input px-3 py-2" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-xs text-[#94a3b8] font-semibold">Date To</label>
          <input id="filterDateTo" type="date" class="filter-input px-3 py-2" />
        </div>
        <button id="applyFiltersBtn" class="bg-[#35b4df] text-[#10212e] font-700 rounded-lg px-4 py-2 text-xs font-bold hover:bg-[#249bc2] transition-colors self-end">Apply Filters</button>
        <button id="clearFiltersBtn" class="bg-transparent text-[#94a3b8] border border-[#3b4456] rounded-lg px-4 py-2 text-xs font-semibold hover:bg-[#23272f] transition-colors self-end">Clear</button>
        <button id="exportCsvBtn" class="export-btn self-end ml-auto">⬇ Export Excel</button>
      </div>

      <div class="flex flex-wrap gap-3 mb-4 items-end">
        <div class="flex flex-col gap-1">
          <label for="filterAction" class="text-xs font-semibold">Action</label>
          <select id="filterAction" class="filter-input px-3 py-2"><option value="">All Actions</option></select>
        </div>
        <div class="flex flex-col gap-1">
          <label for="filterRole" class="text-xs font-semibold">Role</label>
          <select id="filterRole" class="filter-input px-3 py-2">
            <option value="">All Roles</option><option value="super_admin">System Administrator</option>
            <option value="admin">Admin</option><option value="staff">Staff</option><option value="system">System</option>
          </select>
        </div>
        <div class="flex flex-col gap-1 flex-1">
          <label for="filterSearch" class="text-xs font-semibold">Search user, target or IP address</label>
          <input id="filterSearch" type="search" maxlength="255" placeholder="Enter name, target or IP..." class="filter-input px-3 py-2">
        </div>
      </div>
      <!-- SUMMARY BADGES -->
      <div class="flex flex-wrap gap-2 mb-1" id="summaryBadges"></div>
    </div>

    <!-- TABLE -->
    <div class="audit-panel overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left audit-table">
          <thead>
            <tr>
              <th class="py-2 px-3">#</th>
              <th class="py-2 px-3">Date & Time</th>
              <th class="py-2 px-3">User</th>
              <th class="py-2 px-3">Role</th>
              <th class="py-2 px-3">Action</th>
              <th class="py-2 px-3">Target</th>
              <th class="py-2 px-3">IP Address</th>
              <th class="py-2 px-3">Details</th>
            </tr>
          </thead>
          <tbody id="auditTableBody">
            <tr><td colspan="8" class="text-center py-10 text-[#64748b]">Loading audit logs...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-between px-4 py-3 border-t border-[#313848] flex-wrap gap-2">
        <span id="auditCount" class="text-xs text-[#64748b]"></span>
        <div class="flex gap-1" id="auditPagination"></div>
      </div>
    </div>

  </main>
</div>

<!-- DETAILS MODAL -->
<div id="detailsModal" style="display:none;"></div>

<script src="<?php echo e(asset('js/rpcsp_excel.js')); ?>"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const AUDIT_URL      = "<?php echo e(route('admin.audit.data')); ?>";
const NOTIF_URL      = "<?php echo e(route('admin.notifications')); ?>";
const NOTIF_READ_URL = "<?php echo e(route('admin.notifications.read')); ?>";

document.addEventListener("DOMContentLoaded", function () {

  // SIDEBAR
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

  // THEME
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

  // NOTIFICATIONS
  (function initNotif() {
    const bell        = document.getElementById('notificationBell');
    const badge       = document.getElementById('notificationBadge');
    const dropdown    = document.getElementById('adminNotifDropdown');
    const notifList   = document.getElementById('adminNotifList');
    const notifFooter = document.getElementById('adminNotifFooter');
    const markAllRead = document.getElementById('adminMarkAllRead');

    function timeAgo(dateStr) {
      const diff = Math.floor((new Date() - new Date(dateStr)) / 1000);
      if (diff < 60)    return "Just now";
      if (diff < 3600)  return `${Math.floor(diff/60)}m ago`;
      if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
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
        const res  = await fetch(NOTIF_URL, { headers: { 'Accept':'application/json','X-CSRF-TOKEN':CSRF } });
        const json = await res.json();
        if (!json.success) return;
        const count = json.unreadCount || 0;
        if (count > 0) { bell.classList.add('has-unread'); badge.style.display='flex'; badge.textContent=count>99?'99+':String(count); }
        else { bell.classList.remove('has-unread'); badge.style.display='none'; }
        notifFooter.textContent = count > 0 ? `${count} unread notification${count>1?'s':''}` : 'All caught up!';
        if (!json.notifications?.length) { notifList.innerHTML=`<div class="notif-empty">No notifications yet.</div>`; return; }
        notifList.innerHTML = json.notifications.map(n => `
          <div class="notif-item ${!n.read?'unread':''}">
            <div class="notif-icon">${getNotifIcon(n.type)}</div>
            <div class="notif-content">
              <div class="notif-title">${String(n.title ?? "").replace(/[&<>"']/g, c => "&#" + c.charCodeAt(0) + ";")}</div>
              <div class="notif-message">${String(n.message ?? "").replace(/[&<>"']/g, c => "&#" + c.charCodeAt(0) + ";")}</div>
              <div class="notif-time">${timeAgo(n.createdAt)}</div>
            </div>
            ${!n.read?`<div class="notif-dot"></div>`:''}
          </div>`).join('');
      } catch(e) { notifList.innerHTML=`<div class="notif-empty" style="color:#fc8181;">Failed to load.</div>`; }
    }

    bell.addEventListener('click', function(e) {
      e.stopPropagation();
      const isOpen = dropdown.classList.contains('open');
      dropdown.classList.toggle('open');
      if (!isOpen) {
      bell.classList.remove('has-unread');
      badge.style.display = 'none';
      badge.textContent = '';
      fetch(NOTIF_READ_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' } }).finally(loadNotifications);
    }
    });
    document.addEventListener('click', function(e) {
      if (!document.getElementById('adminNotifWrapper').contains(e.target)) dropdown.classList.remove('open');
    });
    markAllRead.addEventListener('click', async function() {
      try { await fetch(NOTIF_READ_URL, { method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'} }); await loadNotifications(); dropdown.classList.remove('open'); } catch(e) {}
    });
    loadNotifications();
    setInterval(loadNotifications, 30000);
  })();

  // AUDIT LOG
  let allLogs = [];
  let currentPage = 1;
  const ROWS_PER_PAGE = 20;

  const ACTION_LABELS = {
    first_login_password_changed: 'First Login Password Changed',
    login:               'Login',
    logout:              'Logout',
    personnel_added:     'Personnel Added',
    personnel_updated:   'Personnel Updated',
    personnel_archived:  'Personnel Archived',
    personnel_restored:  'Personnel Restored',
    personnel_deleted:   'Personnel Deleted',
    approval_changed:    'Approval Changed',
    email_sent:          'Email Sent',
    user_created:        'User Created',
    user_updated:        'User Updated',
  };

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    })[character]);
  }

  function safeActionClass(action) {
    return String(action || 'unknown').replace(/[^a-z0-9_-]/gi, '-');
  }

  function actionBadge(action) {
    const cls   = `ab-${safeActionClass(action)}`;
    const label = ACTION_LABELS[action] || action;
    return `<span class="action-badge ${cls}">${escapeHtml(label || 'Unknown')}</span>`;
  }

  function formatDate(str) {
    if (!str) return '—';
    const d = new Date(str);
    if (isNaN(d)) return escapeHtml(str);
    return d.toLocaleString('en-PH', { year:'numeric', month:'short', day:'2-digit', hour:'2-digit', minute:'2-digit', second:'2-digit' });
  }

  function roleBadge(role) {
    role = escapeHtml(role || '');
    const map = { admin:'<span style="color:#3ec6ff;font-weight:700;">Admin</span>', staff:'<span style="color:#33b481;font-weight:700;">Staff</span>' };
    return map[(role||'').toLowerCase()] || `<span style="color:#94a3b8;">${role||'—'}</span>`;
  }

  let auditRequestVersion = 0;
  let appliedFilters = {};
  async function loadAuditLogs() {
    const requestVersion = ++auditRequestVersion;
    allLogs = [];
    document.getElementById('summaryBadges').innerHTML = '';
    document.getElementById('auditCount').textContent = 'Loading filtered records...';
    document.getElementById('exportCsvBtn').disabled = true;
    const tbody = document.getElementById('auditTableBody');
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-[#64748b]">Loading...</td></tr>`;

    const params = new URLSearchParams();
    const from = document.getElementById('filterDateFrom').value;
    const to   = document.getElementById('filterDateTo').value;
    if (from) params.set('date_from', from);
    if (to)   params.set('date_to', to);
    ['Action','Role','Search'].forEach(name => {
      const value = document.getElementById('filter' + name).value.trim();
      if (value) params.set(name.toLowerCase(), value);
    });

    try {
      const res  = await fetch(`${AUDIT_URL}?${params.toString()}`, { headers: { 'Accept':'application/json','X-CSRF-TOKEN':CSRF } });
      const rawText = await res.text();
      if (requestVersion !== auditRequestVersion) return;
      let json;
      try {
        json = JSON.parse(rawText);
      } catch (parseErr) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-red-400">
          Server returned non-JSON (status ${res.status}).<br>
          <span style="font-size:0.68rem;opacity:0.7;">${rawText.slice(0,200).replace(/</g,'&lt;')}</span>
        </td></tr>`;
        return;
      }

      if (!res.ok || json.success === false) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-red-400">
          Request failed (status ${res.status}): ${escapeHtml(json.error || json.message || 'Unknown error')}
        </td></tr>`;
        return;
      }

      // Accept whichever key the backend actually used
      allLogs = json.data || json.logs || json.audit_logs || json.records || [];

      if (!Array.isArray(allLogs)) allLogs = [];
      appliedFilters = Object.fromEntries(params);
      const actionSelect = document.getElementById('filterAction');
      const selectedAction = actionSelect.value;
      allLogs.forEach(log => { if (log.action) knownActions.add(log.action); });
      actionSelect.innerHTML = '<option value="">All Actions</option>' + Array.from(knownActions).sort().map(action => `<option value="${escapeHtml(action)}">${escapeHtml(ACTION_LABELS[action] || action.replace(/_/g, ' '))}</option>`).join('');
      actionSelect.value = selectedAction;

      currentPage = 1;
      renderSummary();
      renderTable();

    } catch(e) {
      if (requestVersion !== auditRequestVersion) return;
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-red-400">
        Unable to load audit logs: ${escapeHtml(e.message)}
      </td></tr>`;
    } finally {
      if (requestVersion === auditRequestVersion) document.getElementById('exportCsvBtn').disabled = !allLogs.length;
    }
  }

  function renderSummary() {
    const counts = {};
    allLogs.forEach(l => { counts[l.action] = (counts[l.action]||0) + 1; });
    const container = document.getElementById('summaryBadges');
    if (!Object.keys(counts).length) { container.innerHTML = ''; return; }
    container.innerHTML = Object.entries(counts)
      .sort((a,b) => b[1]-a[1])
      .map(([action, count]) => `<span class="action-badge ab-${safeActionClass(action)}" style="font-size:0.7rem;">${escapeHtml(ACTION_LABELS[action] || action)}: ${count}</span>`)
      .join('');
  }

  function renderTable() {
    const tbody      = document.getElementById('auditTableBody');
    const totalPages = Math.max(1, Math.ceil(allLogs.length / ROWS_PER_PAGE));
    if (currentPage > totalPages) currentPage = 1;
    const start    = (currentPage - 1) * ROWS_PER_PAGE;
    const pageData = allLogs.slice(start, start + ROWS_PER_PAGE);

    document.getElementById('auditCount').textContent =
      allLogs.length ? `Showing ${start+1}–${Math.min(start+ROWS_PER_PAGE, allLogs.length)} of ${allLogs.length} records` : 'No records found.';

    if (!pageData.length) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-10 text-[#64748b]">No audit logs found.</td></tr>`;
      renderPagination(0); return;
    }

    tbody.innerHTML = pageData.map((log, i) => {
      const details = log.details ? JSON.stringify(log.details, null, 2) : null;
      const hasDetails = details && details !== '{}' && details !== 'null';
      return `<tr>
        <td class="py-2 px-3 force-light-text opacity-60">${start + i + 1}</td>
        <td class="py-2 px-3 force-light-text" style="min-width:160px;">${formatDate(log.createdAt)}</td>
        <td class="py-2 px-3 force-light-text font-semibold">${escapeHtml(log.userName || '—')}</td>
        <td class="py-2 px-3">${roleBadge(log.userRole)}</td>
        <td class="py-2 px-3">${actionBadge(log.action)}</td>
        <td class="py-2 px-3 force-light-text" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;" title="${escapeHtml(log.target || '')}">${escapeHtml(log.target || '—')}</td>
        <td class="py-2 px-3 force-light-text opacity-60" style="font-family:monospace;">${escapeHtml(log.ipAddress || '—')}</td>
        <td class="py-2 px-3">
          ${hasDetails
            ? `<button class="view-details-btn text-[#3ec6ff] text-xs font-semibold hover:underline" data-idx="${start+i}">View</button>`
            : `<span class="text-[#3b4456] text-xs">—</span>`}
        </td>
      </tr>`;
    }).join('');

    renderPagination(totalPages);

    document.querySelectorAll('.view-details-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        showDetailsModal(allLogs[parseInt(this.dataset.idx)]);
      });
    });
  }

  function renderPagination(totalPages) {
    const container = document.getElementById('auditPagination');
    if (totalPages <= 1) { container.innerHTML = ''; return; }
    let html = '';
    for (let i = 1; i <= totalPages; i++) {
      const active = i === currentPage
        ? 'background:#35b4df;color:#10212e;'
        : 'background:#1a2025;color:#94a3b8;';
      html += `<button data-p="${i}" style="${active}padding:4px 10px;border-radius:5px;font-size:0.72rem;font-weight:700;cursor:pointer;border:none;transition:opacity 0.15s;">${i}</button>`;
    }
    container.innerHTML = html;
    container.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', function() { currentPage = parseInt(this.dataset.p); renderTable(); });
    });
  }

  function showDetailsModal(log) {
    log = {
      ...log,
      userName: escapeHtml(log.userName || ''),
      userRole: escapeHtml(log.userRole || ''),
      action: escapeHtml(log.action || ''),
      target: escapeHtml(log.target || ''),
      ipAddress: escapeHtml(log.ipAddress || ''),
    };
    const modal   = document.getElementById('detailsModal');
    const details = log.details || {};
    const rows    = Object.entries(details).map(([k,v]) =>
      `<div class="detail-row"><span class="detail-label">${escapeHtml(k)}</span><span class="detail-value">${escapeHtml(typeof v === 'object' ? JSON.stringify(v) : v)}</span></div>`
    ).join('');

    modal.innerHTML = `
      <div class="modal-bg">
        <div class="modal-box">
          <button class="modal-close-btn">&times;</button>
          <h3 class="font-bold text-base mb-1 force-light-text">Action Details</h3>
          <p class="text-xs text-[#64748b] mb-4">${ACTION_LABELS[log.action]||log.action} — ${formatDate(log.createdAt)}</p>
          <div class="detail-row"><span class="detail-label">User</span><span class="detail-value">${log.userName||'—'}</span></div>
          <div class="detail-row"><span class="detail-label">Role</span><span class="detail-value">${log.userRole||'—'}</span></div>
          <div class="detail-row"><span class="detail-label">Action</span><span class="detail-value">${log.action||'—'}</span></div>
          <div class="detail-row"><span class="detail-label">Target</span><span class="detail-value">${log.target||'—'}</span></div>
          <div class="detail-row"><span class="detail-label">IP Address</span><span class="detail-value" style="font-family:monospace;">${log.ipAddress||'—'}</span></div>
          ${rows ? `<div class="mt-3 mb-1 text-xs text-[#64748b] font-semibold uppercase tracking-wider">Extra Details</div>${rows}` : ''}
        </div>
      </div>`;
    modal.style.display = '';
    modal.querySelector('.modal-close-btn').onclick = () => { modal.style.display='none'; };
    modal.querySelector('.modal-bg').onclick = (e) => { if(e.target===modal.querySelector('.modal-bg')) modal.style.display='none'; };
  }

  // EXPORT EXCEL
  document.getElementById('exportCsvBtn').addEventListener('click', function() {
    if (!allLogs.length) return;
    const from = appliedFilters.date_from || '';
    const to = appliedFilters.date_to || '';
    const today = new Date();
    const filenameDate = [
      today.getFullYear(),
      String(today.getMonth() + 1).padStart(2, '0'),
      String(today.getDate()).padStart(2, '0'),
    ].join('-');
    let period = 'All dates';
    if (from && to) period = `${from} to ${to}`;
    else if (from) period = `From ${from}`;
    else if (to) period = `Up to ${to}`;
    if (appliedFilters.action) period += ` | Action: ${ACTION_LABELS[appliedFilters.action] || appliedFilters.action}`;
    if (appliedFilters.role) period += ` | Role: ${appliedFilters.role}`;
    if (appliedFilters.search) period += ` | Search: ${appliedFilters.search}`;

    window.exportAuditLogExcel({
      filename: `APAO_Audit_Log_${filenameDate}.xls`,
      generatedDate: today.toLocaleString('en-PH', {
        year: 'numeric', month: 'long', day: '2-digit',
        hour: '2-digit', minute: '2-digit',
      }),
      period,
      rows: allLogs.map((log, index) => ({
        number: index + 1,
        createdAt: formatDate(log.createdAt).replace(/&mdash;/g, '—'),
        userName: log.userName || '',
        userRole: log.userRole || '',
        action: log.action || '',
        actionLabel: ACTION_LABELS[log.action] || '',
        target: log.target || '',
        ipAddress: log.ipAddress || '',
        details: log.details || '',
      })),
    });
  });

  // FILTER EVENTS
  const knownActions = new Set(Object.keys(ACTION_LABELS));
  ['filterAction','filterRole'].forEach(id => document.getElementById(id).addEventListener('change', loadAuditLogs));
  document.getElementById('filterSearch').addEventListener('keydown', event => {
    if (event.key === 'Enter') { event.preventDefault(); loadAuditLogs(); }
  });
  document.getElementById('applyFiltersBtn').addEventListener('click', loadAuditLogs);
  document.getElementById('clearFiltersBtn').addEventListener('click', function() {
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value   = '';
    ['filterAction','filterRole','filterSearch'].forEach(id => document.getElementById(id).value = '');
    loadAuditLogs();
  });

  // INIT
  loadAuditLogs();
});
</script>
</body>
</html>
