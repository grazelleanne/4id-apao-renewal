<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Admin Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="/tailwind.min.js"></script>
  <script src="/chart.min.js"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: { sidebarBg: '#181c21', sidebarDark: '#23272f', mainBg: '#1a2025', accent: '#3ec6ff' },
          fontFamily: { inter: ['Inter', 'system-ui', 'sans-serif'] }
        }
      }
    }
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
    .card-main-text{color:#33b481 !important;font-weight:bold;}
    .card-label{color:#228b68 !important;font-weight:600;}
    .card-desc{color:#89e2bc !important;}
    .status-card-renewed .card-label,.status-card-renewed .status-card-value{color:#33b481!important;}
    .status-card-renewed .card-desc{color:#89e2bc!important;}
    .status-card-within .card-label,.status-card-within .status-card-value{color:#ecc94b!important;}
    .status-card-within .card-desc{color:#f6e58d!important;}
    .status-card-expired .card-label,.status-card-expired .status-card-value{color:#fc8181!important;}
    .status-card-expired .card-desc{color:#f7a8a8!important;}
    .force-light-text{color:#e5eaf2;}
    .abadge{display:inline-flex;align-items:center;gap:3px;padding:2px 9px;border-radius:999px;font-size:0.68rem;font-weight:700;white-space:nowrap;}
    .abadge-new{background:#0a1f3a;color:#3ec6ff;}
    .abadge-renewed{background:#0d3325;color:#33b481;}
    .abadge-within{background:#2d2a0a;color:#ecc94b;}
    .abadge-expired{background:#2d0a0a;color:#fc8181;}
    .abadge-pending{background:#1e2430;color:#64748b;}
    .chart-legend{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:12px;}
    .chart-legend-item{display:flex;align-items:center;gap:6px;font-size:0.72rem;color:#94a3b8;}
    .chart-legend-dot{width:10px;height:10px;border-radius:2px;flex-shrink:0;}
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
    body.light-mode{background:#f1f5fa;color:#222;}
    body.light-mode .main-bg{background:#f1f5fa !important;}
    body.light-mode .bg-\[\#1a2025\]{background-color:#f1f5fa !important;}
    body.light-mode .bg-\[\#23272f\]{background-color:#ffffff !important;border:1px solid #e2e8f0 !important;box-shadow:0 1px 4px rgba(0,0,0,0.06) !important;}
    body.light-mode #sidebar{background:#f7fafc;border-color:#e2e8f0;}
    body.light-mode .nav-item{color:#64748b;}
    body.light-mode .nav-item:hover,body.light-mode .nav-item.active{background:#e8edf5;color:#1e293b;}
    body.light-mode .sb-logo-text{color:#1e293b;}
    body.light-mode .sb-bottom{border-color:#e2e8f0;}
    body.light-mode .theme-btn{background:#e8edf5;color:#64748b;}
    body.light-mode .card-main-text{color:#0d6641 !important;}
    body.light-mode .card-label{color:#1a7a55 !important;}
    body.light-mode .card-desc{color:#2d8a65 !important;}
    body.light-mode .status-card-renewed .card-label,body.light-mode .status-card-renewed .status-card-value{color:#067647!important;}
    body.light-mode .status-card-renewed .card-desc{color:#287a5b!important;}
    body.light-mode .status-card-within .card-label,body.light-mode .status-card-within .status-card-value{color:#9a6700!important;}
    body.light-mode .status-card-within .card-desc{color:#8a6512!important;}
    body.light-mode .status-card-expired .card-label,body.light-mode .status-card-expired .status-card-value{color:#b42318!important;}
    body.light-mode .status-card-expired .card-desc{color:#a33a32!important;}
    body.light-mode .force-light-text{color:#1e293b !important;}
    body.light-mode input,body.light-mode select{background:#f8fafc !important;color:#1e293b !important;border-color:#cbd5e1 !important;}
    body.light-mode .abadge-new{background:#dbeafe;color:#1e40af;}
    body.light-mode .abadge-renewed{background:#d1fae5;color:#065f46;}
    body.light-mode .abadge-within{background:#fef9c3;color:#92400e;}
    body.light-mode .abadge-expired{background:#fee2e2;color:#991b1b;}
    body.light-mode .abadge-pending{background:#e2e8f0;color:#475569;}
    body.light-mode table td{background:#ffffff !important;color:#1e293b !important;border-color:#e5e7eb !important;}
    body.light-mode table th{background:#f1f5f9 !important;color:#475569 !important;border-color:#e5e7eb !important;}
    body.light-mode table tbody tr:hover td{background:#f8fafc !important;}
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
    body.light-mode .admin-panel{background:#ffffff!important;border-color:#e2e8f0!important;}
    body.light-mode .admin-panel-title{color:#1e293b!important;}
    body.light-mode .admin-action-row{background:#f8fafc!important;border-color:#e2e8f0!important;}
    body.light-mode .admin-action-label{color:#334155!important;}
    body.light-mode .admin-action-desc{color:#64748b!important;}
    body.light-mode .activity-row{border-color:#e2e8f0!important;}
    body.light-mode .activity-title{color:#334155!important;}
    body.light-mode .activity-meta{color:#64748b!important;}

  </style>
    
  </style>
</head>
<body class="min-h-screen font-inter main-bg bg-[#1a2025]">
<div class="flex min-h-screen">

  <!-- SIDEBAR -->
  <aside id="sidebar">
    <div class="sb-top">
      <div class="sb-logo">
        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Logo" onerror="this.src=''">
        <span class="sb-logo-text">4ID APAO Firearms</span>
      </div>
      <button class="sb-toggle" id="sidebarToggleBtn" title="Toggle sidebar">
        <svg id="sb-icon-menu" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg id="sb-icon-close" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <nav class="sb-nav">
      <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-item active">
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

    <header class="flex flex-wrap justify-end mb-8 items-center gap-4">
      <div class="flex flex-row-reverse items-center gap-4">
        <?php echo $__env->make('partials.account_dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
      </div>
    </header>

    <!-- METRICS CARDS -->
    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-6 mb-7">
      <a href="<?php echo e(route('admin.users')); ?>" class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2 transition hover:shadow-lg hover:-translate-y-0.5" style="text-decoration:none;">
        <div class="text-xs uppercase font-semibold card-label tracking-wide mb-1">Total Users</div>
        <p id="totalUsers" class="text-3xl font-extrabold card-main-text">--</p>
        <p class="text-xs card-desc">Total users managed on the platform. <span class="underline text-accent font-medium">View all users</span></p>
      </a>
      <div class="status-card-renewed bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2" style="border-left:4px solid #33b481;">
        <div class="text-xs uppercase font-semibold card-label tracking-wide mb-1">Total Renewed</div>
        <p id="totalRenewed" class="status-card-value text-3xl font-extrabold">--</p>
        <p class="text-xs card-desc">Personnel with up-to-date renewal.</p>
      </div>
      <div class="status-card-within bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2" style="border-left:4px solid #ecc94b;">
        <div class="text-xs uppercase font-semibold card-label tracking-wide mb-1">Within Renewal Period</div>
        <p id="withinRenewal" class="status-card-value text-3xl font-extrabold">--</p>
        <p class="text-xs card-desc">Personnel within the current renewal window.</p>
      </div>
      <div class="status-card-expired bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2" style="border-left:4px solid #e53e3e;">
        <div class="text-xs uppercase font-semibold card-label tracking-wide mb-1">Expired</div>
        <p id="expired" class="status-card-value text-3xl font-extrabold">--</p>
        <p class="text-xs card-desc">Personnel with expired renewal.</p>
      </div>
    </div>

    <!-- CHARTS -->
    <div class="grid lg:grid-cols-2 gap-6 mb-7">
      <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10">
        <h3 class="font-semibold text-base force-light-text tracking-tight mb-3">Approval Status Breakdown</h3>
        <div class="chart-legend">
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#3ec6ff;"></span>New</span>
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#33b481;"></span>Renewed</span>
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#ecc94b;"></span>Within Renewal</span>
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#e53e3e;"></span>Expired</span>
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#64748b;"></span>Pending</span>
        </div>
        <div class="relative" style="height:220px;"><canvas id="lineChart"></canvas></div>
      </div>
      <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10">
        <h3 class="font-semibold text-base force-light-text tracking-tight mb-3">Personnel Status Distribution</h3>
        <div class="chart-legend">
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#3ec6ff;"></span>New</span>
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#33b481;"></span>Renewed</span>
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#ecc94b;"></span>Within Renewal</span>
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#e53e3e;"></span>Expired</span>
          <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#64748b;"></span>Pending</span>
        </div>
        <div class="relative" style="height:220px;"><canvas id="pieChart"></canvas></div>
      </div>
    </div>

    <!-- ADMIN OVERSIGHT -->
    <div class="grid lg:grid-cols-2 gap-6 mb-7">

      <!-- PENDING ADMINISTRATIVE ACTIONS -->
      <section class="admin-panel bg-[#23272f] border border-[#363b48] rounded-lg p-6 shadow shadow-black/10">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h3 class="admin-panel-title font-semibold text-base force-light-text tracking-tight">Pending Administrative Actions</h3>
            <p class="text-xs text-[#94a3b8] mt-1">Items that currently require Admin attention.</p>
          </div>
          <a href="<?php echo e(route('admin.inspection')); ?>" class="text-xs text-cyan-400 hover:underline font-semibold">View Inspection →</a>
        </div>

        <div class="space-y-3">
          <a href="<?php echo e(route('admin.inspection')); ?>" class="admin-action-row flex items-center justify-between gap-4 bg-[#1a2025] border border-[#303640] rounded-lg p-4 hover:border-cyan-500/50 transition" style="text-decoration:none;">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/><path d="M11 8v4l2 2"/></svg>
              </div>
              <div>
                <div class="admin-action-label text-sm font-semibold text-[#e5eaf2]">Awaiting Inspection</div>
                <div class="admin-action-desc text-xs text-[#94a3b8]">Personnel sent by Staff and waiting for Admin review.</div>
              </div>
            </div>
            <span id="pendingInspectionCount" class="text-2xl font-extrabold text-amber-400">0</span>
          </a>

          <a href="<?php echo e(route('admin.inspection')); ?>" class="admin-action-row flex items-center justify-between gap-4 bg-[#1a2025] border border-[#303640] rounded-lg p-4 hover:border-yellow-500/50 transition" style="text-decoration:none;">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-10 rounded-lg bg-yellow-500/10 text-yellow-400 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01"/><path d="M10.3 3.7 2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 3.7a2 2 0 00-3.4 0z"/></svg>
              </div>
              <div>
                <div class="admin-action-label text-sm font-semibold text-[#e5eaf2]">Within Renewal Period</div>
                <div class="admin-action-desc text-xs text-[#94a3b8]">Personnel approaching the end of firearm validity.</div>
              </div>
            </div>
            <span id="actionWithinRenewal" class="text-2xl font-extrabold text-yellow-400">0</span>
          </a>

          <a href="<?php echo e(route('admin.inspection')); ?>" class="admin-action-row flex items-center justify-between gap-4 bg-[#1a2025] border border-[#303640] rounded-lg p-4 hover:border-red-500/50 transition" style="text-decoration:none;">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg>
              </div>
              <div>
                <div class="admin-action-label text-sm font-semibold text-[#e5eaf2]">Expired</div>
                <div class="admin-action-desc text-xs text-[#94a3b8]">Personnel with expired firearm validity requiring attention.</div>
              </div>
            </div>
            <span id="actionExpired" class="text-2xl font-extrabold text-red-400">0</span>
          </a>
        </div>
      </section>

      <!-- RECENT SYSTEM ACTIVITY -->
      <section class="admin-panel bg-[#23272f] border border-[#363b48] rounded-lg p-6 shadow shadow-black/10">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h3 class="admin-panel-title font-semibold text-base force-light-text tracking-tight">Recent System Activity</h3>
            <p class="text-xs text-[#94a3b8] mt-1">Latest important activity across the system.</p>
          </div>
          <a href="<?php echo e(route('admin.audit')); ?>" class="text-xs text-cyan-400 hover:underline font-semibold">View Audit Log →</a>
        </div>

        <div id="recentActivityList">
          <div class="text-center py-10 text-sm text-[#64748b]">Loading recent activity...</div>
        </div>
      </section>
    </div>

  </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

  const CSRF = document.querySelector('meta[name="csrf-token"]').content;

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
  applyTheme(localStorage.getItem('theme') || 'dark');
  document.getElementById('themeToggle').addEventListener('click', function () {
    const next = localStorage.getItem('theme') === 'light' ? 'dark' : 'light';
    localStorage.setItem('theme', next);
    applyTheme(next);
  });

  // ===== NOTIFICATIONS =====
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
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
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
      if (count > 0) { bell.classList.add('has-unread'); badge.style.display = 'flex'; badge.textContent = count > 99 ? '99+' : String(count); }
      else { bell.classList.remove('has-unread'); badge.style.display = 'none'; }
      notifFooter.textContent = count > 0 ? `${count} unread notification${count > 1 ? 's' : ''}` : 'All caught up!';
      if (!json.notifications || !json.notifications.length) { notifList.innerHTML = `<div class="notif-empty">No notifications yet.</div>`; return; }
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
    } catch (e) { notifList.innerHTML = `<div class="notif-empty" style="color:#fc8181;">Failed to load notifications.</div>`; }
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
    if (!document.getElementById('adminNotifWrapper').contains(e.target)) dropdown.classList.remove('open');
  });
  markAllRead.addEventListener('click', async function () {
    try { await fetch(ADMIN_NOTIF_READ_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }); await loadNotifications(); dropdown.classList.remove('open'); } catch (e) {}
  });
  loadNotifications();
  setInterval(loadNotifications, 30000);
  // ===== DATA =====
  const CHART_COLORS = ['#3ec6ff','#33b481','#ecc94b','#e53e3e','#64748b'];
  const CHART_LABELS = ['New','Renewed','Within Renewal','Expired','Pending'];
  const gridColor    = 'rgba(255,255,255,0.07)';
  const tickColor    = '#94a3b8';
  const tooltipOpts  = {
    backgroundColor: '#181c21',
    borderColor: '#363b48',
    borderWidth: 1,
    titleColor: '#e5eaf2',
    bodyColor: '#94a3b8',
    padding: 10
  };

  // ===== CHARTS =====
  function initCharts(totalNew, renewed, within, expired, pending) {
    const data = [totalNew, renewed, within, expired, pending ?? 0];

    new Chart(document.getElementById("lineChart").getContext("2d"), {
      type: "bar",
      data: {
        labels: CHART_LABELS,
        datasets: [{ label: "Personnel Count", data, backgroundColor: CHART_COLORS, borderRadius: 6, borderSkipped: false, barPercentage: 0.55 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: tooltipOpts },
        scales: {
          x: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 } }, border: { color: 'transparent' } },
          y: { grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1 }, beginAtZero: true, border: { color: 'transparent' } }
        }
      }
    });

    new Chart(document.getElementById("pieChart").getContext("2d"), {
      type: "bar",
      data: {
        labels: CHART_LABELS,
        datasets: [{ label: "Count", data, backgroundColor: CHART_COLORS, borderRadius: 6, borderSkipped: false, barPercentage: 0.6 }]
      },
      options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: tooltipOpts },
        scales: {
          x: { grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1 }, beginAtZero: true, border: { color: 'transparent' } },
          y: { grid: { color: 'transparent' }, ticks: { color: tickColor, font: { size: 11 } }, border: { color: 'transparent' } }
        }
      }
    });
  }

  // ===== RECENT SYSTEM ACTIVITY =====
  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[char]);
  }

  function renderRecentActivities(items) {
    const list = document.getElementById('recentActivityList');

    if (!Array.isArray(items) || !items.length) {
      list.innerHTML = `<div class="text-center py-10 text-sm text-[#64748b]">No recent activity available.</div>`;
      return;
    }

    list.innerHTML = items.slice(0, 6).map(item => {
      const title = item.title || item.action || item.description || item.message || 'System activity';
      const actor = item.userName || item.user || item.actor || item.performedBy || 'System';
      const date  = item.createdAt || item.created_at || item.date || item.timestamp || '';

      return `
        <div class="activity-row flex items-start gap-3 py-3 border-b border-[#303640] last:border-b-0">
          <div class="w-9 h-9 rounded-full bg-cyan-500/10 text-cyan-400 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/>
            </svg>
          </div>
          <div class="min-w-0 flex-1">
            <div class="activity-title text-sm font-semibold text-[#e5eaf2]">${escapeHtml(title)}</div>
            <div class="activity-meta text-xs text-[#64748b] mt-1">
              ${escapeHtml(actor)}${date ? ` • ${escapeHtml(timeAgo(date))}` : ''}
            </div>
          </div>
        </div>`;
    }).join('');
  }

  async function loadRecentActivity() {
    const list = document.getElementById('recentActivityList');

    try {
      // Reuse the notification feed as a safe recent-activity source.
      // If your dashboard API later returns recentActivity, that data is preferred below.
      const res = await fetch(ADMIN_NOTIF_URL, {
        headers: { 'Accept':'application/json', 'X-CSRF-TOKEN':CSRF }
      });
      const json = await res.json();
      renderRecentActivities(json.notifications || []);
    } catch (e) {
      list.innerHTML = `<div class="text-center py-10 text-sm text-red-400">Failed to load recent activity.</div>`;
    }
  }

  // ===== LOAD DASHBOARD =====
  async function loadDashboard() {
    try {
      const res = await fetch("<?php echo e(route('admin.dashboard.data')); ?>", {
        headers: { "Accept": "application/json", "X-CSRF-TOKEN": CSRF }
      });
      if (res.status === 401 || res.status === 403) { window.location.href = "<?php echo e(route('login')); ?>"; return; }
      const json = await res.json();
      if (!json.success) throw new Error("Failed");
      const m = json.metrics || {};
      document.getElementById("totalUsers").innerText    = json.totalUsers  ?? '--';
      document.getElementById("totalRenewed").innerText  = m.totalRenewed   ?? '0';
      document.getElementById("withinRenewal").innerText = m.withinRenewal  ?? '0';
      document.getElementById("expired").innerText       = m.expired        ?? '0';

      // Admin oversight counters.
      document.getElementById("actionWithinRenewal").innerText = m.withinRenewal ?? '0';
      document.getElementById("actionExpired").innerText       = m.expired ?? '0';

      // Uses an inspection-specific count if your dashboard endpoint provides one.
      // Falls back to the existing pending metric so the dashboard remains compatible.
      document.getElementById("pendingInspectionCount").innerText =
        m.pendingInspection ?? m.forInspection ?? m.pending ?? '0';

      if (Array.isArray(json.recentActivity) && json.recentActivity.length) {
        renderRecentActivities(json.recentActivity);
      }

      initCharts(m.totalNew ?? 0, m.totalRenewed ?? 0, m.withinRenewal ?? 0, m.expired ?? 0, m.pending ?? 0);
    } catch (e) {
      console.error('Dashboard load error:', e);
      ["totalUsers","totalRenewed","withinRenewal","expired"].forEach(id => {
        document.getElementById(id).innerText = '--';
      });
    }
  }
loadDashboard();
loadRecentActivity();

});
</script>
</body>
</html>
