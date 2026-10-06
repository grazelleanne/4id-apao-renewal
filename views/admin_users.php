<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="/css/mobile-dashboard.css">
  <script defer src="/js/mobile-dashboard.js"></script>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>User Management - Admin Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet" />
  <script src="/tailwind.min.js"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: { sidebarBg: '#181c21', sidebarDark: '#23272f', accent: '#3ec6ff' },
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
    body{background:#1a2025;color:#e5eaf2;margin:0;font-family:Inter,system-ui,sans-serif;}
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

    /* ===== MISC ===== */
    .force-light-text{color:#e5eaf2;}
    .pw-strength-bar-wrap{height:5px;border-radius:4px;background:#2a3040;margin-top:5px;overflow:hidden;}
    .pw-strength-bar{height:100%;border-radius:4px;width:0%;transition:width .3s,background .3s;}
    .pw-strength-text{font-size:11px;margin-top:3px;font-weight:600;min-height:14px;}
    .strength-weak{background:#e53e3e;} .strength-fair{background:#ecc94b;} .strength-good{background:#3ec6ff;} .strength-strong{background:#33b481;}
    .switch-tgl{position:relative;display:inline-block;width:40px;height:20px;vertical-align:middle;}
    .switch-tgl input{opacity:0;width:0;height:0;}
    .switch-tgl .slider-tgl{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#ccc;border-radius:20px;transition:background 0.2s;}
    .switch-tgl .slider-tgl:before{position:absolute;content:"";height:16px;width:16px;left:2px;bottom:2px;background-color:white;border-radius:50%;transition:transform 0.2s;}
    .switch-tgl input:checked + .slider-tgl{background-color:#13d670;}
    .switch-tgl input:checked + .slider-tgl:before{transform:translateX(20px);}
    .status-switch-label{margin-left:10px;min-width:48px;display:inline-block;font-weight:500;}
    .modal-bg{position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(22,24,28,0.78);display:flex;justify-content:center;align-items:center;z-index:50;}
    .modal-content{background:#23272f;color:#e5eaf2;border-radius:6px;min-width:340px;max-width:96vw;box-shadow:2px 4px 26px 0 #0019;position:relative;padding:2.5rem 2.5rem 1.5rem 2.5rem;display:flex;flex-direction:column;gap:4px;font-family:inherit;}
    body.light-mode .modal-content{background:#fff;color:#1f2937;}
    .modal-content input[type="text"],.modal-content select{background:#fff;color:#222;border:1.5px solid #d3d8e2;border-radius:2px;font-family:inherit;font-size:1rem;padding:0.4rem 0.9rem 0.4rem 0.5rem;margin-bottom:0.5rem;width:100%;transition:border 0.14s;}
    .modal-content input[type="text"]:focus,.modal-content select:focus{border-color:#3ec6ff;outline:none;}
    .modal-close{position:absolute;right:16px;top:10px;background:transparent;border:none;color:#acb7e2;font-size:2rem;line-height:1;z-index:5;cursor:pointer;}
    .modal-actions{margin-top:1.5rem;display:flex;gap:0.8rem;flex-wrap:wrap;}
    .save-btn{background:#13d670;color:#111e24;font-weight:bold;padding:0.48rem 1.4rem;border-radius:3px;border:none;font-size:1rem;transition:background 0.2s;cursor:pointer;}
    .save-btn:hover{background:#12b15c;}
    .cancel-btn{background:#313743;color:#e5eaf2;padding:0.48rem 1.2rem;border-radius:3px;border:none;font-size:1rem;font-weight:500;transition:background 0.2s;cursor:pointer;}
    .cancel-btn:hover{background:#404a5e;}


    /* ===== MANAGE USERS SECURITY IMPROVEMENTS ===== */
    .metric-card{border:1px solid #2e3540;}
    .user-row{border-bottom:1px solid #2e333d;transition:background .15s;}
    .user-row:hover{background:rgba(255,255,255,.025);}
    .role-pill,.security-pill,.account-pill{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:4px 9px;font-size:.68rem;font-weight:700;white-space:nowrap;}
    .role-admin{background:#312e81;color:#c7d2fe;}.role-staff{background:#16352a;color:#86efac;}
    .security-current{background:#0c4a6e;color:#bae6fd;}.security-admin{background:#3b2355;color:#e9d5ff;}.security-standard{background:#2b313b;color:#cbd5e1;}
    .last-login-primary{font-weight:600;color:#e5eaf2;}.last-login-secondary{font-size:.66rem;color:#64748b;margin-top:2px;}
    .actions-wrap{position:relative;display:inline-block;}
    .actions-btn{width:30px;height:30px;border-radius:7px;border:1px solid #3a4350;background:#20262e;color:#94a3b8;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;}
    .actions-btn:hover{background:#2b333e;color:#fff;}
    .actions-menu{display:none;position:absolute;right:0;top:34px;min-width:185px;background:#20262e;border:1px solid #39424f;border-radius:9px;box-shadow:0 12px 30px rgba(0,0,0,.35);z-index:80;padding:5px;}
    .actions-menu.open{display:block;}
    .actions-item{width:100%;border:0;background:transparent;color:#d8dee8;text-align:left;padding:9px 10px;border-radius:6px;font-size:.74rem;cursor:pointer;display:flex;align-items:center;gap:8px;}
    .actions-item:hover{background:#2b333e;}.actions-item.danger{color:#fca5a5;}.actions-item:disabled{opacity:.45;cursor:not-allowed;}
    .security-note{font-size:.7rem;line-height:1.45;color:#94a3b8;background:#1c222a;border:1px solid #303846;border-radius:8px;padding:9px 10px;margin-top:8px;}
    .field-help{font-size:.68rem;color:#7c8798;margin-top:-3px;margin-bottom:7px;line-height:1.35;}
    .modal-content input[type="password"],.modal-content input[type="email"]{background:#fff;color:#222;border:1.5px solid #d3d8e2;border-radius:4px;font-family:inherit;font-size:.9rem;padding:.55rem .7rem;margin-bottom:.5rem;width:100%;transition:border .14s;}
    .modal-content input[type="password"]:focus,.modal-content input[type="email"]:focus{border-color:#3ec6ff;outline:none;}
    .modal-content{max-height:90vh;overflow-y:auto;border:1px solid #374151;}
    .danger-btn{background:#dc2626;color:#fff;font-weight:700;padding:.5rem 1.25rem;border-radius:5px;border:none;cursor:pointer;}.danger-btn:hover{background:#b91c1c;}
    .warning-btn{background:#d97706;color:#fff;font-weight:700;padding:.5rem 1.25rem;border-radius:5px;border:none;cursor:pointer;}.warning-btn:hover{background:#b45309;}
    .secondary-btn{background:#334155;color:#e2e8f0;font-weight:700;padding:.5rem 1.1rem;border-radius:5px;border:none;cursor:pointer;}
    .generated-password-box{display:flex;gap:7px;align-items:center;margin-bottom:7px;}
    .generated-password-box input{margin-bottom:0!important;}
    .mini-btn{border:1px solid #475569;background:#27303a;color:#e2e8f0;border-radius:5px;padding:7px 9px;font-size:.7rem;font-weight:700;cursor:pointer;white-space:nowrap;}
    .mini-btn:hover{background:#334155;}
    .self-badge{display:inline-flex;margin-left:6px;padding:2px 6px;border-radius:999px;background:#0c4a6e;color:#bae6fd;font-size:.6rem;font-weight:800;vertical-align:middle;}
    .disabled-switch{opacity:.45;pointer-events:none;}
    .toast-secure{position:fixed;right:24px;bottom:24px;z-index:9999;padding:11px 15px;border-radius:8px;font-size:.78rem;font-weight:700;box-shadow:0 10px 30px rgba(0,0,0,.35);}
    .toast-success{background:#064e3b;color:#d1fae5;border:1px solid #059669;}.toast-error{background:#7f1d1d;color:#fee2e2;border:1px solid #ef4444;}
    body.light-mode .metric-card,body.light-mode .user-row{border-color:#e2e8f0!important;}
    body.light-mode .user-row:hover{background:#f8fafc;}
    body.light-mode .actions-btn{background:#fff;border-color:#cbd5e1;color:#64748b;}
    body.light-mode .actions-menu{background:#fff;border-color:#cbd5e1;box-shadow:0 12px 30px rgba(15,23,42,.16);}
    body.light-mode .actions-item{color:#334155;}body.light-mode .actions-item:hover{background:#f1f5f9;}body.light-mode .actions-item.danger{color:#dc2626;}
    body.light-mode .security-note{background:#f8fafc;border-color:#e2e8f0;color:#64748b;}
    body.light-mode .last-login-primary{color:#1e293b;}
    body.light-mode .security-standard{background:#e2e8f0;color:#475569;}

  </style>
  <link rel="stylesheet" href="/css/typography.css">
  <link rel="stylesheet" href="/css/dashboard-theme.css">
  <script src="/js/dashboard-lists.js" defer></script>
</head>
<body class="app-dashboard light-mode min-h-screen font-inter">
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
      <a href="<?php echo e(route('admin.users')); ?>" class="nav-item active">
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
  <main class="flex-1 bg-[#1a2025] p-7 overflow-y-auto">
    <header class="flex flex-wrap justify-between mb-8 items-center gap-4">
      <div class="relative w-80">
        <input id="searchInput" type="text" placeholder="Search by name..." class="bg-[#23272f] text-white border border-[#363b48] rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-accent pr-10 force-light-text" />
        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M21 21l-4.35-4.35M5 11a6 6 0 1112 0 6 6 0 01-12 0z"/></svg>
      </div>
      <div class="flex flex-row-reverse items-center gap-4">
        <?php echo $__env->make('partials.account_dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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

      </div>
    </header>

    <!-- Metrics -->
    <div class="grid xl:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-5 mb-7">
      <div class="metric-card bg-[#23272f] rounded-lg p-5 shadow shadow-black/10 flex flex-col gap-2">
        <div class="text-xs uppercase font-semibold text-[#cdd6e2] tracking-wide">Total Users</div>
        <p id="totalUsers" class="text-3xl font-extrabold text-blue-400">0</p>
        <p class="text-xs text-gray-400">All administrator and staff accounts.</p>
      </div>
      <div class="metric-card bg-[#23272f] rounded-lg p-5 shadow shadow-black/10 flex flex-col gap-2">
        <div class="text-xs uppercase font-semibold text-green-400 tracking-wide">Active Users</div>
        <p id="totalActive" class="text-3xl font-extrabold text-green-400">0</p>
        <p class="text-xs text-gray-400">Accounts currently allowed to sign in.</p>
      </div>
      <div class="metric-card bg-[#23272f] rounded-lg p-5 shadow shadow-black/10 flex flex-col gap-2">
        <div class="text-xs uppercase font-semibold text-red-400 tracking-wide">Inactive Users</div>
        <p id="totalInactive" class="text-3xl font-extrabold text-red-400">0</p>
        <p class="text-xs text-gray-400">Accounts blocked from signing in.</p>
      </div>
      <div class="metric-card bg-[#23272f] rounded-lg p-5 shadow shadow-black/10 flex flex-col gap-2">
        <div class="text-xs uppercase font-semibold text-purple-400 tracking-wide">Admin Accounts</div>
        <p id="totalAdmins" class="text-3xl font-extrabold text-purple-400">0</p>
        <p class="text-xs text-gray-400">Accounts with administrative privileges.</p>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 mb-10">
      <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
        <div>
          <h2 class="font-semibold text-base force-light-text tracking-tight">User Accounts</h2>
          <p class="text-[11px] text-gray-400 mt-1">Sensitive account changes require administrator password confirmation and are recorded in the Audit Log.</p>
        </div>
        <div class="flex flex-wrap gap-2 items-center ml-auto">
          <label for="sortUsersSelect" class="text-[#b0bac7] text-xs force-light-text">Sort by:</label>
          <select id="sortUsersSelect" class="bg-[#23272f] text-white border border-[#363b48] rounded px-2 py-1.5 text-xs force-light-text">
            <option value="role-asc">Admin / Staff</option>
            <option value="fullName-asc">Full Name (A-Z)</option>
            <option value="fullName-desc">Full Name (Z-A)</option>
            <option value="lastLogin-desc">Last Login (Recent)</option>
          </select>
          <button id="addUserBtn" class="bg-accent text-[#111e24] rounded px-4 py-2 text-xs font-bold shadow hover:bg-[#5dd3ff] transition-colors">+ Add User</button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left min-w-[980px]">
          <thead>
            <tr class="border-b border-[#3a424d] text-[#b0bac7]">
              <th class="py-3 px-2 font-semibold force-light-text">Username / Email</th>
              <th class="py-3 px-2 font-semibold force-light-text">Full Name</th>
              <th class="py-3 px-2 font-semibold force-light-text">Role</th>
              <th class="py-3 px-2 font-semibold force-light-text">Status</th>
              <th class="py-3 px-2 font-semibold force-light-text">Last Login</th>
              <th class="py-3 px-2 font-semibold force-light-text">Security</th>
              <th class="py-3 px-2 font-semibold force-light-text text-center">Actions</th>
            </tr>
          </thead>
          <tbody id="usersTableBody"></tbody>
        </table>
      </div>
    </div>

    <!-- Add User Modal -->
    <div id="userModal" style="display:none;" class="modal-bg">
      <form id="userForm" class="modal-content" style="width:470px;">
        <button type="button" class="modal-close" id="closeModalBtn">&times;</button>
        <h3 class="text-lg font-bold mb-1">Add User</h3>
        <p class="text-xs text-gray-400 mb-4">Create only accounts required for official system access.</p>

        <div class="mb-1">
          <label for="userEmail" class="block mb-1 text-sm font-semibold">Email / Username</label>
          <input id="userEmail" name="email" type="email" required autocomplete="off" placeholder="staff@apao.mil.ph" />
        </div>

        <div class="mb-1">
          <label for="userFullName" class="block mb-1 text-sm font-semibold">Full Name</label>
          <input id="userFullName" name="fullName" type="text" required autocomplete="off" placeholder="Juan Dela Cruz" />
        </div>

        <div class="mb-1">
          <label for="userRole" class="block mb-1 text-sm font-semibold">Role</label>
          <select id="userRole" name="role" required>
            <option value="staff" selected>Staff</option>
            <option value="admin">Admin</option>
          </select>
          <div class="field-help">Admin accounts can manage users, inspections, reports, and audit records. Assign Admin only when necessary.</div>
        </div>

        <div class="mb-1">
          <label for="userPassword" class="block mb-1 text-sm font-semibold">Initial Password</label>
          <div class="generated-password-box">
            <input id="userPassword" name="password" type="password" required autocomplete="new-password" placeholder="Min 8 chars, uppercase, number, symbol" />
            <button id="generatePasswordBtn" type="button" class="mini-btn">Generate</button>
            <button id="showAddPasswordBtn" type="button" class="mini-btn">Show</button>
          </div>
          <div class="pw-strength-bar-wrap"><div class="pw-strength-bar" id="modalStrengthBar"></div></div>
          <div class="pw-strength-text" id="modalStrengthText"></div>
          <div class="field-help">New staff must replace this temporary password on their first login before accessing the dashboard.</div>
        </div>

        <div class="mb-1">
          <label for="createAdminPassword" class="block mb-1 text-sm font-semibold">Your Admin Password</label>
          <input id="createAdminPassword" type="password" required autocomplete="current-password" placeholder="Confirm your administrator password" />
          <div class="field-help">Required to authorize creation of another system account.</div>
        </div>

        <div class="security-note">For security, passwords are never displayed after the account is created. Share the initial password through an approved office channel.</div>

        <div class="modal-actions">
          <button type="submit" class="save-btn">Create User</button>
          <button type="button" class="cancel-btn" id="cancelUserBtn">Cancel</button>
        </div>
      </form>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" style="display:none;" class="modal-bg">
      <form id="editUserForm" class="modal-content" style="width:470px;">
        <button type="button" class="modal-close" id="closeEditModalBtn">&times;</button>
        <h3 class="text-lg font-bold mb-1">Manage Account</h3>
        <p class="text-xs text-gray-400 mb-4">Role and account-status changes are privileged actions.</p>
        <input id="editOriginalEmail" type="hidden" />

        <div class="mb-1">
          <label for="editEmail" class="block mb-1 text-sm font-semibold">Email / Username</label>
          <input id="editEmail" type="email" readonly />
        </div>
        <div class="mb-1">
          <label for="editFullName" class="block mb-1 text-sm font-semibold">Full Name</label>
          <input id="editFullName" type="text" required />
        </div>
        <div class="mb-1">
          <label for="editRole" class="block mb-1 text-sm font-semibold">Role</label>
          <select id="editRole" required>
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="block mb-1 text-sm font-semibold">Account Status</label>
          <label class="switch-tgl">
            <input id="editStatusToggle" type="checkbox" />
            <span class="slider-tgl"></span>
          </label>
          <span id="editStatusLabel" class="status-switch-label force-light-text">Active</span>
        </div>
        <div class="mb-1">
          <label for="editAdminPassword" class="block mb-1 text-sm font-semibold">Your Admin Password</label>
          <input id="editAdminPassword" type="password" autocomplete="current-password" placeholder="Required for role/status changes" />
        </div>
        <div id="editProtectionNote" class="security-note" style="display:none;"></div>
        <div class="modal-actions">
          <button type="submit" class="save-btn">Save Changes</button>
          <button type="button" class="cancel-btn" id="cancelEditUserBtn">Cancel</button>
        </div>
      </form>
    </div>

    <!-- Status Confirmation Modal -->
    <div id="statusConfirmModal" style="display:none;" class="modal-bg">
      <form id="statusConfirmForm" class="modal-content" style="width:440px;">
        <button type="button" class="modal-close" id="closeStatusModalBtn">&times;</button>
        <h3 id="statusConfirmTitle" class="text-lg font-bold mb-2">Confirm Account Status</h3>
        <p id="statusConfirmMessage" class="text-sm text-gray-300 mb-3"></p>
        <div class="mb-1">
          <label for="statusAdminPassword" class="block mb-1 text-sm font-semibold">Your Admin Password</label>
          <input id="statusAdminPassword" type="password" required autocomplete="current-password" placeholder="Enter administrator password" />
        </div>
        <div class="security-note">This action is checked again on the server. You cannot deactivate your own account or remove the last active administrator.</div>
        <div class="modal-actions">
          <button id="statusConfirmBtn" type="submit" class="warning-btn">Confirm</button>
          <button type="button" class="cancel-btn" id="cancelStatusBtn">Cancel</button>
        </div>
      </form>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" style="display:none;" class="modal-bg">
      <form id="resetPasswordForm" class="modal-content" style="width:460px;">
        <button type="button" class="modal-close" id="closeResetModalBtn">&times;</button>
        <h3 class="text-lg font-bold mb-1">Reset User Password</h3>
        <p id="resetPasswordTarget" class="text-xs text-gray-400 mb-4"></p>
        <input id="resetUsername" type="hidden" />

        <label for="resetNewPassword" class="block mb-1 text-sm font-semibold">New Password</label>
        <div class="generated-password-box">
          <input id="resetNewPassword" type="password" required autocomplete="new-password" placeholder="New strong password" />
          <button id="generateResetPasswordBtn" type="button" class="mini-btn">Generate</button>
          <button id="showResetPasswordBtn" type="button" class="mini-btn">Show</button>
        </div>
        <div class="pw-strength-bar-wrap"><div class="pw-strength-bar" id="resetStrengthBar"></div></div>
        <div class="pw-strength-text" id="resetStrengthText"></div>

        <label for="resetConfirmPassword" class="block mb-1 mt-2 text-sm font-semibold">Confirm New Password</label>
        <input id="resetConfirmPassword" type="password" required autocomplete="new-password" />

        <label for="resetAdminPassword" class="block mb-1 mt-2 text-sm font-semibold">Your Admin Password</label>
        <input id="resetAdminPassword" type="password" required autocomplete="current-password" placeholder="Authorize password reset" />

        <div class="security-note">The existing password cannot be viewed. Resetting replaces the stored password hash and creates an Audit Log entry.</div>
        <div class="modal-actions">
          <button type="submit" class="warning-btn">Reset Password</button>
          <button type="button" class="cancel-btn" id="cancelResetBtn">Cancel</button>
        </div>
      </form>
    </div>

  </main>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const ROUTES = {
  usersData:   "<?php echo e(route('admin.users.data')); ?>",
  usersStore:  "<?php echo e(route('admin.users.store')); ?>",
  usersUpdate: "<?php echo e(route('admin.users.update')); ?>",
};

const CURRENT_ADMIN = {
  id: <?php echo json_encode($user->id ?? null, 15, 512) ?>,
  email: <?php echo json_encode($user->email ?? '', 15, 512) ?>,
  role: <?php echo json_encode(strtolower($user->role ?? 'admin'), 15, 512) ?>,
};

let users = [];
let currentSort = 'role-asc';
let pendingStatusUser = null;
let pendingStatusValue = null;
let openActionMenu = null;

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function normalizeRole(role) {
  return String(role || 'staff').toLowerCase();
}

function isStrongPassword(value) {
  return value.length >= 8 && value.length <= 1024 && /[A-Z]/.test(value) && /[a-z]/.test(value) && /[0-9]/.test(value) && /[^A-Za-z0-9]/.test(value);
}

function generateStrongPassword(length = 14) {
  const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
  const lower = 'abcdefghijkmnopqrstuvwxyz';
  const nums  = '23456789';
  const symbols = '!@#$%&*?';
  const all = upper + lower + nums + symbols;
  const rnd = max => crypto.getRandomValues(new Uint32Array(1))[0] % max;
  let chars = [upper[rnd(upper.length)], lower[rnd(lower.length)], nums[rnd(nums.length)], symbols[rnd(symbols.length)]];
  while (chars.length < length) chars.push(all[rnd(all.length)]);
  for (let i = chars.length - 1; i > 0; i--) {
    const j = rnd(i + 1);
    [chars[i], chars[j]] = [chars[j], chars[i]];
  }
  return chars.join('');
}

function showToast(message, type = 'success') {
  const old = document.getElementById('secureToast');
  if (old) old.remove();
  const div = document.createElement('div');
  div.id = 'secureToast';
  div.className = `toast-secure ${type === 'error' ? 'toast-error' : 'toast-success'}`;
  div.textContent = message;
  document.body.appendChild(div);
  setTimeout(() => div.remove(), 3200);
}

function parseJsonSafely(res) {
  return res.json().catch(() => ({ success:false, message:'Unexpected server response.' }));
}

async function secureFetch(url, options = {}) {
  options.headers = {
    'Accept': 'application/json',
    'X-CSRF-TOKEN': CSRF,
    ...(options.headers || {})
  };
  const res = await fetch(url, options);
  const json = await parseJsonSafely(res);
  if (!res.ok || !json.success) {
    throw new Error(json.message || (json.errors ? Object.values(json.errors).flat()[0] : 'Request failed.'));
  }
  return json;
}

function formatLastLogin(value) {
  if (!value) return '<div class="last-login-primary">Never</div><div class="last-login-secondary">No recorded login</div>';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return `<div class="last-login-primary">${escapeHtml(value)}</div>`;
  const date = d.toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
  const time = d.toLocaleTimeString('en-US', {hour:'numeric', minute:'2-digit'});
  return `<div class="last-login-primary">${date}</div><div class="last-login-secondary">${time}</div>`;
}

function updatePasswordStrength(inputId, barId, textId) {
  const val = document.getElementById(inputId).value;
  const bar = document.getElementById(barId);
  const text = document.getElementById(textId);
  let score = 0;
  if (val.length >= 8) score++;
  if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const levels = [
    {pct:'25%', cls:'strength-weak', label:'Weak', color:'#e53e3e'},
    {pct:'50%', cls:'strength-fair', label:'Fair', color:'#ecc94b'},
    {pct:'75%', cls:'strength-good', label:'Good', color:'#3ec6ff'},
    {pct:'100%', cls:'strength-strong', label:'Strong', color:'#33b481'},
  ];
  if (!val) { bar.style.width='0'; text.textContent=''; return; }
  const lvl = levels[Math.max(0, score - 1)];
  bar.style.width = lvl.pct;
  bar.className = 'pw-strength-bar ' + lvl.cls;
  text.textContent = lvl.label + (score === 4 ? ' password' : ' — use 10+ characters with uppercase, lowercase, number and symbol');
  text.style.color = lvl.color;
}

function activeAdminCount() {
  return users.filter(u => normalizeRole(u.role) === 'admin' && u.status === 'Active').length;
}

function isCurrentUser(user) {
  if (CURRENT_ADMIN.id != null && user.id != null) return String(CURRENT_ADMIN.id) === String(user.id);
  return String(user.username || '').toLowerCase() === String(CURRENT_ADMIN.email || '').toLowerCase();
}

function isLastActiveAdmin(user) {
  return normalizeRole(user.role) === 'admin' && user.status === 'Active' && activeAdminCount() <= 1;
}

document.addEventListener('DOMContentLoaded', function () {
  // ===== SIDEBAR =====
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('sidebarToggleBtn');
  const sbIconMenu = document.getElementById('sb-icon-menu');
  const sbIconClose = document.getElementById('sb-icon-close');
  if (localStorage.getItem('sb') === '1') {
    sidebar.classList.add('sidebar-collapsed');
    sbIconMenu.style.display = '';
    sbIconClose.style.display = 'none';
  } else {
    sbIconMenu.style.display = 'none';
    sbIconClose.style.display = '';
  }
  toggleBtn.addEventListener('click', function () {
    const collapsed = sidebar.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sb', collapsed ? '1' : '0');
    sbIconMenu.style.display = collapsed ? '' : 'none';
    sbIconClose.style.display = collapsed ? 'none' : '';
  });

  // ===== THEME =====
  const iconSun = document.getElementById('icon-sun');
  const iconMoon = document.getElementById('icon-moon');
  function applyTheme(t) {
    document.body.classList.toggle('light-mode', t === 'light');
    iconSun.style.display = t === 'light' ? 'none' : '';
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
  const bell = document.getElementById('notificationBell');
  const badge = document.getElementById('notificationBadge');
  const dropdown = document.getElementById('adminNotifDropdown');
  const notifList = document.getElementById('adminNotifList');
  const notifFooter = document.getElementById('adminNotifFooter');
  const markAllRead = document.getElementById('adminMarkAllRead');
  const ADMIN_NOTIF_URL = "<?php echo e(route('admin.notifications')); ?>";
  const ADMIN_NOTIF_READ_URL = "<?php echo e(route('admin.notifications.read')); ?>";

  function timeAgo(dateStr) {
    const diff = Math.floor((new Date() - new Date(dateStr)) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return new Date(dateStr).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
  }

  function getNotifIcon(type) {
    const icons = {
      approval_changed:`<svg class="w-4 h-4" style="color:#3ec6ff" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
      email_sent:`<svg class="w-4 h-4" style="color:#33b481" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>`,
      personnel_added:`<svg class="w-4 h-4" style="color:#33b481" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 4v16m8-8H4"/></svg>`,
      personnel_updated:`<svg class="w-4 h-4" style="color:#ecc94b" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/></svg>`,
      personnel_archived:`<svg class="w-4 h-4" style="color:#fc8181" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M5 8h14M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>`
    };
    return icons[type] || `<svg class="w-4 h-4" style="color:#94a3b8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path d="M12 8v4m0 4h.01" stroke-width="2"/></svg>`;
  }

  async function loadNotifications() {
    try {
      const res = await fetch(ADMIN_NOTIF_URL, {headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}});
      const json = await res.json();
      if (!json.success) return;
      const count = json.unreadCount || 0;
      if (count > 0) {
        bell.classList.add('has-unread'); badge.style.display='flex'; badge.textContent=count>99?'99+':String(count);
      } else {
        bell.classList.remove('has-unread'); badge.style.display='none';
      }
      notifFooter.textContent = count > 0 ? `${count} unread notification${count > 1 ? 's' : ''}` : 'All caught up!';
      if (!json.notifications || !json.notifications.length) {
        notifList.innerHTML = '<div class="notif-empty">No notifications yet.</div>'; return;
      }
      notifList.innerHTML = json.notifications.map(n => `<div class="notif-item ${!n.read?'unread':''}"><div class="notif-icon">${getNotifIcon(n.type)}</div><div class="notif-content"><div class="notif-title">${escapeHtml(n.title)}</div><div class="notif-message">${escapeHtml(n.message)}</div><div class="notif-time">${timeAgo(n.createdAt)}</div></div>${!n.read?'<div class="notif-dot"></div>':''}</div>`).join('');
    } catch (e) {
      notifList.innerHTML = '<div class="notif-empty" style="color:#fc8181;">Failed to load notifications.</div>';
    }
  }

  bell.addEventListener('click', function(e){
    e.stopPropagation(); const isOpen=dropdown.classList.contains('open'); dropdown.classList.toggle('open');
    if(!isOpen){bell.classList.remove('has-unread');badge.style.display='none';badge.textContent='';fetch(ADMIN_NOTIF_READ_URL,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}}).finally(loadNotifications);}
  });
  markAllRead.addEventListener('click', async function(){
    try{await fetch(ADMIN_NOTIF_READ_URL,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}});await loadNotifications();dropdown.classList.remove('open');}catch(e){}
  });
  loadNotifications(); setInterval(loadNotifications,30000);

  document.addEventListener('click', function(e){
    if (!document.getElementById('adminNotifWrapper').contains(e.target)) dropdown.classList.remove('open');
    if (openActionMenu && !e.target.closest('.actions-wrap')) { openActionMenu.classList.remove('open'); openActionMenu=null; }
  });

  // ===== USERS =====
  document.getElementById('searchInput').placeholder = 'Search by name / email / role...';
  document.getElementById('searchInput').addEventListener('input', renderUsersTable);
  document.getElementById('sortUsersSelect').addEventListener('change', e => {currentSort=e.target.value;renderUsersTable();});
  loadUsers();

  // ===== ADD USER =====
  const userModal = document.getElementById('userModal');
  const userForm = document.getElementById('userForm');
  function closeAddModal(){userModal.style.display='none';userForm.reset();document.getElementById('modalStrengthBar').style.width='0';document.getElementById('modalStrengthText').textContent='';}
  document.getElementById('addUserBtn').addEventListener('click',()=>{userForm.reset();userModal.style.display='flex';setTimeout(()=>document.getElementById('userEmail').focus(),100);});
  document.getElementById('closeModalBtn').addEventListener('click',closeAddModal); document.getElementById('cancelUserBtn').addEventListener('click',closeAddModal);
  userModal.addEventListener('mousedown',e=>{if(e.target===userModal)closeAddModal();});
  document.getElementById('generatePasswordBtn').addEventListener('click',()=>{const p=generateStrongPassword();document.getElementById('userPassword').value=p;updatePasswordStrength('userPassword','modalStrengthBar','modalStrengthText');});
  document.getElementById('showAddPasswordBtn').addEventListener('click',function(){const i=document.getElementById('userPassword');i.type=i.type==='password'?'text':'password';this.textContent=i.type==='password'?'Show':'Hide';});
  document.getElementById('userPassword').addEventListener('input',()=>updatePasswordStrength('userPassword','modalStrengthBar','modalStrengthText'));

  userForm.addEventListener('submit', async function(e){
    e.preventDefault();
    const payload={
      username:document.getElementById('userEmail').value.trim(),
      fullName:document.getElementById('userFullName').value.trim(),
      role:document.getElementById('userRole').value,
      password:document.getElementById('userPassword').value,
      status:'Active',
      adminPassword:document.getElementById('createAdminPassword').value
    };
    if(!payload.username||!payload.fullName||!payload.password||!payload.adminPassword){showToast('Complete all required fields.','error');return;}
    if(!isStrongPassword(payload.password)){showToast('Use a password with at least 8 characters, uppercase, lowercase, number, and symbol.','error');return;}
    try{
      await secureFetch(ROUTES.usersStore,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
      closeAddModal(); await loadUsers(); showToast('User account created and recorded in the Audit Log.');
    }catch(err){showToast(err.message,'error');}
  });

  // ===== EDIT USER =====
  const editModal=document.getElementById('editUserModal');
  const editForm=document.getElementById('editUserForm');
  const editStatus=document.getElementById('editStatusToggle');
  editStatus.addEventListener('change',()=>document.getElementById('editStatusLabel').textContent=editStatus.checked?'Active':'Inactive');
  function closeEdit(){editModal.style.display='none';editForm.reset();}
  document.getElementById('closeEditModalBtn').addEventListener('click',closeEdit); document.getElementById('cancelEditUserBtn').addEventListener('click',closeEdit);
  editModal.addEventListener('mousedown',e=>{if(e.target===editModal)closeEdit();});

  window.openEditUser=function(index){
    const u=users[index];if(!u)return;
    document.getElementById('editOriginalEmail').value=u.username;
    document.getElementById('editEmail').value=u.username;
    document.getElementById('editFullName').value=u.fullName||'';
    document.getElementById('editRole').value=normalizeRole(u.role);
    editStatus.checked=u.status==='Active';document.getElementById('editStatusLabel').textContent=editStatus.checked?'Active':'Inactive';
    document.getElementById('editAdminPassword').value='';
    const note=document.getElementById('editProtectionNote');
    if(isCurrentUser(u)){note.style.display='block';note.textContent='Your current administrator account cannot deactivate or demote itself.';}else if(isLastActiveAdmin(u)){note.style.display='block';note.textContent='This is the last active administrator. The server will block deactivation or demotion until another administrator is active.';}else note.style.display='none';
    editModal.style.display='flex'; closeAllActionMenus();
  };

  editForm.addEventListener('submit',async function(e){
    e.preventDefault();
    const username=document.getElementById('editOriginalEmail').value;
    const old=users.find(u=>u.username===username); if(!old)return;
    const payload={username,fullName:document.getElementById('editFullName').value.trim(),role:document.getElementById('editRole').value,status:editStatus.checked?'Active':'Inactive',adminPassword:document.getElementById('editAdminPassword').value};
    const sensitive=normalizeRole(old.role)!==normalizeRole(payload.role)||old.status!==payload.status;
    if(sensitive&&!payload.adminPassword){showToast('Enter your admin password to change role or status.','error');return;}
    try{await secureFetch(ROUTES.usersUpdate,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});closeEdit();await loadUsers();showToast('User account updated securely.');}catch(err){showToast(err.message,'error');}
  });

  // ===== STATUS CONFIRMATION =====
  const statusModal=document.getElementById('statusConfirmModal');
  function closeStatus(){statusModal.style.display='none';document.getElementById('statusConfirmForm').reset();pendingStatusUser=null;pendingStatusValue=null;}
  document.getElementById('closeStatusModalBtn').addEventListener('click',closeStatus); document.getElementById('cancelStatusBtn').addEventListener('click',closeStatus);
  statusModal.addEventListener('mousedown',e=>{if(e.target===statusModal)closeStatus();});
  document.getElementById('statusConfirmForm').addEventListener('submit',async function(e){
    e.preventDefault(); if(!pendingStatusUser)return;
    const adminPassword=document.getElementById('statusAdminPassword').value;if(!adminPassword){showToast('Enter your admin password.','error');return;}
    const u=pendingStatusUser;
    try{await secureFetch(ROUTES.usersUpdate,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:u.username,fullName:u.fullName,role:normalizeRole(u.role),status:pendingStatusValue,adminPassword})});closeStatus();await loadUsers();showToast(`Account ${pendingStatusValue.toLowerCase()} successfully.`);}catch(err){closeStatus();await loadUsers();showToast(err.message,'error');}
  });

  // ===== RESET PASSWORD =====
  const resetModal=document.getElementById('resetPasswordModal');
  function closeReset(){resetModal.style.display='none';document.getElementById('resetPasswordForm').reset();document.getElementById('resetStrengthBar').style.width='0';document.getElementById('resetStrengthText').textContent='';}
  document.getElementById('closeResetModalBtn').addEventListener('click',closeReset); document.getElementById('cancelResetBtn').addEventListener('click',closeReset);
  resetModal.addEventListener('mousedown',e=>{if(e.target===resetModal)closeReset();});
  document.getElementById('generateResetPasswordBtn').addEventListener('click',()=>{const p=generateStrongPassword();document.getElementById('resetNewPassword').value=p;document.getElementById('resetConfirmPassword').value=p;updatePasswordStrength('resetNewPassword','resetStrengthBar','resetStrengthText');});
  document.getElementById('showResetPasswordBtn').addEventListener('click',function(){const a=document.getElementById('resetNewPassword'),b=document.getElementById('resetConfirmPassword');const show=a.type==='password';a.type=show?'text':'password';b.type=show?'text':'password';this.textContent=show?'Hide':'Show';});
  document.getElementById('resetNewPassword').addEventListener('input',()=>updatePasswordStrength('resetNewPassword','resetStrengthBar','resetStrengthText'));

  window.openResetPassword=function(index){
    const u=users[index];if(!u)return;
    document.getElementById('resetUsername').value=u.username;
    document.getElementById('resetPasswordTarget').textContent=`Reset password for ${u.fullName} (${u.username}).`;
    resetModal.style.display='flex';closeAllActionMenus();
  };

  document.getElementById('resetPasswordForm').addEventListener('submit',async function(e){
    e.preventDefault();
    const username=document.getElementById('resetUsername').value; const u=users.find(x=>x.username===username); if(!u)return;
    const newPassword=document.getElementById('resetNewPassword').value; const confirm=document.getElementById('resetConfirmPassword').value; const adminPassword=document.getElementById('resetAdminPassword').value;
    if(newPassword!==confirm){showToast('New passwords do not match.','error');return;}
    if(!isStrongPassword(newPassword)){showToast('New password must be at least 8 characters with uppercase, lowercase, number, and symbol.','error');return;}
    try{await secureFetch(ROUTES.usersUpdate,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:u.username,fullName:u.fullName,role:normalizeRole(u.role),status:u.status,newPassword,adminPassword})});closeReset();showToast('Password reset successfully.');}catch(err){showToast(err.message,'error');}
  });
});

async function loadUsers(){
  try{const json=await secureFetch(ROUTES.usersData);users=Array.isArray(json.data)?json.data:[];}catch(e){users=[];showToast(e.message,'error');}
  renderUsersTable();
}

function updateStats(){
  document.getElementById('totalUsers').textContent=users.length;
  document.getElementById('totalActive').textContent=users.filter(u=>u.status==='Active').length;
  document.getElementById('totalInactive').textContent=users.filter(u=>u.status==='Inactive').length;
  document.getElementById('totalAdmins').textContent=users.filter(u=>normalizeRole(u.role)==='admin').length;
}

function closeAllActionMenus(){document.querySelectorAll('.actions-menu.open').forEach(m=>m.classList.remove('open'));openActionMenu=null;}

window.toggleUserActions=function(event,index){
  event.stopPropagation(); const menu=document.getElementById(`actions-${index}`); if(!menu)return;
  const wasOpen=menu.classList.contains('open');closeAllActionMenus();if(!wasOpen){menu.classList.add('open');openActionMenu=menu;}
};

window.requestStatusChange=function(index,newStatus){
  const u=users[index];if(!u)return;
  if(isCurrentUser(u)){showToast('You cannot change the status of your own administrator account.','error');renderUsersTable();return;}
  if(newStatus==='Inactive'&&isLastActiveAdmin(u)){showToast('You cannot deactivate the last active administrator account.','error');renderUsersTable();return;}
  pendingStatusUser=u;pendingStatusValue=newStatus;
  document.getElementById('statusConfirmTitle').textContent=newStatus==='Active'?'Reactivate User?':'Deactivate User?';
  document.getElementById('statusConfirmMessage').textContent=newStatus==='Active'?`${u.fullName} will be allowed to sign in again.`:`${u.fullName} will be blocked from signing in until reactivated.`;
  document.getElementById('statusConfirmBtn').className=newStatus==='Active'?'save-btn':'danger-btn';
  document.getElementById('statusConfirmBtn').textContent=newStatus==='Active'?'Reactivate':'Deactivate';
  document.getElementById('statusAdminPassword').value=''; document.getElementById('statusConfirmModal').style.display='flex';
};

function renderUsersTable(){
  updateStats();
  const filter=(document.getElementById('searchInput').value||'').toLowerCase();
  let list=users.map((u,index)=>({...u,_index:index})).filter(u=>(u.username||'').toLowerCase().includes(filter)||(u.fullName||'').toLowerCase().includes(filter)||(u.role||'').toLowerCase().includes(filter));
  const [field,dir]=currentSort.split('-');
  list.sort((a,b)=>{
    let av,bv;
    if(field==='lastLogin'){av=a.lastLoginAt?new Date(a.lastLoginAt).getTime():0;bv=b.lastLoginAt?new Date(b.lastLoginAt).getTime():0;}
    else if(field==='role'){av=normalizeRole(a.role);bv=normalizeRole(b.role);}
    else{av=(a.fullName||'').toLowerCase();bv=(b.fullName||'').toLowerCase();}
    return av<bv?(dir==='asc'?-1:1):av>bv?(dir==='asc'?1:-1):0;
  });

  const tbody=document.getElementById('usersTableBody');
  if(!list.length){tbody.innerHTML='<tr><td colspan="7" class="text-center py-8 text-gray-400 force-light-text">No users found.</td></tr>';return;}
  tbody.innerHTML=list.map(u=>{
    const idx=u._index;const active=u.status==='Active';const current=isCurrentUser(u);const lastAdmin=isLastActiveAdmin(u);const role=normalizeRole(u.role);
    const security=current?'<span class="security-pill security-current">Current session</span>':role==='admin'?'<span class="security-pill security-admin">Admin privileged</span>':'<span class="security-pill security-standard">Standard</span>';
    const rolePill=`<span class="role-pill ${role==='admin'?'role-admin':'role-staff'}">${role==='admin'?'Admin':'Staff'}</span>`;
    const disabled=current?'disabled-switch':'';
    return `<tr class="user-row">
      <td class="py-3 px-2 force-light-text font-medium">${escapeHtml(u.username)}${current?'<span class="self-badge">YOU</span>':''}</td>
      <td class="py-3 px-2 force-light-text">${escapeHtml(u.fullName)}</td>
      <td class="py-3 px-2">${rolePill}</td>
      <td class="py-3 px-2"><label class="switch-tgl ${disabled}" title="${current?'You cannot deactivate your own account':'Change account status'}"><input type="checkbox" ${active?'checked':''} ${current?'disabled':''} onchange="requestStatusChange(${idx}, this.checked ? 'Active' : 'Inactive')"><span class="slider-tgl"></span></label><span class="status-switch-label force-light-text">${active?'Active':'Inactive'}</span>${lastAdmin?'<div class="text-[10px] text-amber-400 mt-1">Last active Admin</div>':''}</td>
      <td class="py-3 px-2">${formatLastLogin(u.lastLoginAt)}</td>
      <td class="py-3 px-2">${security}</td>
      <td class="py-3 px-2 text-center"><div class="actions-wrap"><button type="button" class="actions-btn" onclick="toggleUserActions(event,${idx})" aria-label="User actions">⋮</button><div id="actions-${idx}" class="actions-menu"><button type="button" class="actions-item" onclick="openEditUser(${idx})">Manage account</button><button type="button" class="actions-item" onclick="openResetPassword(${idx})">Reset password</button>${current?'<button type="button" class="actions-item danger" disabled>Deactivate current account</button>':''}</div></div></td>
    </tr>`;
  }).join('');
}

</script>
</body>
</html>
