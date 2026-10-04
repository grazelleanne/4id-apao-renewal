<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Admin | Reports</title>
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
    body.light-mode .card-main-text{color:#176146 !important;}
    body.light-mode .card-label{color:#36a380 !important;}
    body.light-mode .card-desc{color:#25775c !important;}
    body.light-mode .force-light-text{color:#222 !important;}
    body.light-mode input,body.light-mode select{background:#f3f7fa !important;color:#232634 !important;border-color:#cdd5ea !important;}
    body.light-mode .rounded-lg{background:#f8fafc !important;}
   body.light-mode .badge-renewed{color:#176146!important;}
    body.light-mode .badge-expired{color:#c53030!important;}
    body.light-mode .badge-within{color:#92400e!important;}

    /* ===== CARDS ===== */
    .card-main-text{color:#33b481 !important;font-weight:bold;}
    .card-label{color:#228b68 !important;font-weight:600;}
    .card-desc{color:#89e2bc !important;}
    .force-light-text{color:#e5eaf2;}

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

    /* ===== STATUS BADGES ===== */
  .badge-renewed{color:#33b481;font-weight:700;}
    .badge-expired{color:#e53e3e;font-weight:700;}
    .badge-within{color:#ecc94b;font-weight:700;}
   .report-summary-row td{border-bottom:1px solid #2a2f3a;}
    body.light-mode .report-summary-row td{border-bottom:1px solid #d1d9e6;}
    body.light-mode .border-\[\#2a2f3a\]{border-color:#e2e8f0!important;}
    body.light-mode .hover\:bg-\[\#1e2329\]:hover{background:#f8fafc!important;}
  
    /* ===== REPORT CENTER / RPCSP ===== */
    .report-center-tabs{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:22px;}
    .report-type-btn{display:flex;align-items:center;gap:9px;border:1px solid #363b48;background:#23272f;color:#94a3b8;border-radius:9px;padding:11px 16px;font-size:.82rem;font-weight:700;cursor:pointer;transition:.15s;}
    .report-type-btn:hover{border-color:#3ec6ff;color:#e5eaf2;}
    .report-type-btn.active{background:#123044;border-color:#3ec6ff;color:#55d4ff;box-shadow:0 0 0 1px rgba(62,198,255,.08) inset;}
    body.light-mode .report-type-btn{background:#fff;border-color:#d5ddea;color:#64748b;}
    body.light-mode .report-type-btn:hover{color:#1e293b;border-color:#38bdf8;}
    body.light-mode .report-type-btn.active{background:#eff8ff;color:#0369a1;border-color:#38bdf8;}

    .rpcsp-config-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;}
    .rpcsp-field label{display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#94a3b8;margin-bottom:5px;}
    .rpcsp-field input,.rpcsp-field select{width:100%;background:#1a2025;border:1px solid #363b48;border-radius:7px;color:#e5eaf2;padding:8px 10px;font-size:.77rem;outline:none;}
    .rpcsp-field input:focus,.rpcsp-field select:focus{border-color:#3ec6ff;box-shadow:0 0 0 2px rgba(62,198,255,.08);}
    body.light-mode .rpcsp-field label{color:#64748b;}
    body.light-mode .rpcsp-field input,body.light-mode .rpcsp-field select{background:#fff!important;color:#1e293b!important;border-color:#cbd5e1!important;}

    .rpcsp-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;}
    .rpcsp-btn{border:0;border-radius:7px;padding:9px 14px;font-size:.76rem;font-weight:800;cursor:pointer;}
    .rpcsp-btn-primary{background:#3ec6ff;color:#0f172a;}
    .rpcsp-btn-green{background:#22c55e;color:#fff;}
    .rpcsp-btn-amber{background:#f59e0b;color:#fff;}

    .rpcsp-preview-shell{overflow:auto;background:#11161d;border:1px solid #303947;border-radius:10px;padding:18px;}
    body.light-mode .rpcsp-preview-shell{background:#e9eef5;border-color:#d8e0eb;}
    .rpcsp-document{width:1120px;min-height:720px;margin:0 auto;background:#fff;color:#111;padding:18px 20px 22px;font-family:Arial,Helvetica,sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.24);}
    .rpcsp-title{text-align:center;font-weight:700;font-size:15px;line-height:1.15;margin:0;}
    .rpcsp-subtitle{text-align:center;font-size:11px;line-height:1.25;margin:1px 0;}
    .rpcsp-asof{text-align:center;font-size:11px;margin:2px 0 12px;}
    .rpcsp-fund{font-size:11px;text-decoration:underline;margin:0 0 12px;}
    .rpcsp-forwhich{font-size:11px;line-height:1.35;margin:0 0 9px;}
    .rpcsp-forwhich .accountable-name{font-weight:700;text-transform:uppercase;}
    .rpcsp-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:9.5px;}
    .rpcsp-table th,.rpcsp-table td{border:1px solid #333;padding:4px 5px;vertical-align:middle;}
    .rpcsp-table th{text-align:center;font-weight:700;background:#fff;}
    .rpcsp-table td{text-align:center;}
    .rpcsp-table td:nth-child(1),.rpcsp-table td:nth-child(2),.rpcsp-table td:nth-child(10){text-align:left;}
    .rpcsp-summary{display:flex;justify-content:flex-end;gap:28px;margin-top:9px;font-size:10px;}
    .rpcsp-summary strong{font-size:11px;}
    .rpcsp-note{font-size:9px;color:#555;margin-top:8px;}
    .rpcsp-certification{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:52px;margin-top:42px;font-size:11px;line-height:1.35;color:#111;}
    .rpcsp-certification-title{font-weight:700;margin:0 0 42px;}
    .rpcsp-certification-column,.rpcsp-signatory{break-inside:avoid;page-break-inside:avoid;}
    .rpcsp-signatory + .rpcsp-signatory{margin-top:40px;}
    .rpcsp-signatory p{margin:0;}
    .rpcsp-signatory-name{font-weight:700;}
    .rpcsp-empty{text-align:center!important;padding:14px!important;color:#666;}
    .rpcsp-preview-section[hidden]{display:none!important;}
    .rpcsp-preview-status{margin-top:10px;min-height:18px;font-size:.75rem;color:#94a3b8;}
    .rpcsp-preview-status.success{color:#22c55e;}
    .rpcsp-preview-status.error{color:#f87171;}
    @media(max-width:1100px){.rpcsp-config-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
    @media(max-width:640px){.rpcsp-config-grid{grid-template-columns:1fr;}}

  </style>
</head>
<body class="min-h-screen font-inter main-bg bg-[#1a2025]">
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
      <a href="<?php echo e(route('admin.reports')); ?>" class="nav-item active">
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

    <header class="flex flex-wrap justify-between mb-8 items-center gap-4">
      <h1 class="text-2xl font-bold tracking-tight force-light-text">Reports</h1>
      <div class="flex flex-row-reverse items-center gap-4">
        <?php echo $__env->make('partials.account_dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- NOTIFICATION BELL -->
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

    <!-- REPORT CENTER -->
    <div class="report-center-tabs">
      <button type="button" id="showRenewalReportBtn" class="report-type-btn active">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
        Personnel Renewal Report
      </button>
      <button type="button" id="showRpcspBtn" class="report-type-btn">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M4 9h16M9 4v16"/></svg>
        RPCSP
      </button>
    </div>

    <div id="renewalReportPanel">

    <!-- Report Filter -->
    <section class="mb-6">
      <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-3">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <p class="text-lg font-semibold force-light-text mb-2">Generate &amp; Export Personnel Renewal Reports</p>
          <form class="flex flex-wrap gap-2 items-center" id="reportFilterForm" onsubmit="return false;">
            <label for="reportPeriod" class="text-xs force-light-text mr-2">Period:</label>
            <select id="reportPeriod" class="bg-[#23272f] text-white border border-[#363b48] rounded px-3 py-1 text-xs force-light-text">
              <option value="this-year">This Year</option>
              <option value="last-year">Last Year</option>
              <option value="custom">Custom</option>
            </select>
            <input type="date" id="customStart" class="hidden bg-[#23272f] text-white border border-[#363b48] rounded px-3 py-1 text-xs" />
            <span id="customDash" class="hidden text-gray-400">–</span>
            <input type="date" id="customEnd" class="hidden bg-[#23272f] text-white border border-[#363b48] rounded px-3 py-1 text-xs" />
            <button type="submit" class="bg-accent hover:bg-cyan-500 text-white rounded px-4 py-1 ml-2 text-sm font-semibold">Preview</button>
            <button type="button" id="downloadReportBtn" class="bg-green-500 hover:bg-green-600 text-white rounded px-4 py-1 ml-2 text-sm font-semibold">Download PDF</button>
            <button type="button" id="exportExcelBtn" class="bg-amber-500 hover:bg-amber-600 text-white rounded px-4 py-1 ml-2 text-sm font-semibold">Export Excel</button>
          </form>
        </div>
        <div class="text-xs text-[#b0bac7] force-light-text">Choose the period and click "Preview" to filter the table, or download/export for sharing.</div>
      </div>
    </section>

    <!-- Metrics -->
    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-6 mb-7">
      <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
        <div class="text-xs uppercase font-semibold card-label tracking-wide mb-1">Total Personnel</div>
        <p id="totalUsers" class="text-3xl font-extrabold card-main-text">0</p>
        <p class="text-xs card-desc">Personnel managed by the platform.</p>
      </div>
      <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
        <div class="text-xs uppercase font-semibold card-label tracking-wide mb-1">Renewed</div>
        <p id="totalRenewed" class="text-3xl font-extrabold card-main-text">--</p>
        <p class="text-xs card-desc">Personnel with up-to-date renewal.</p>
      </div>
      <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
        <div class="text-xs uppercase font-semibold card-label tracking-wide mb-1">Within Renewal</div>
        <p id="withinRenewal" class="text-3xl font-extrabold card-main-text">--</p>
        <p class="text-xs card-desc">Personnel within current renewal window.</p>
      </div>
      <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
        <div class="text-xs uppercase font-semibold card-label tracking-wide mb-1">Expired</div>
        <p id="expired" class="text-3xl font-extrabold card-main-text">--</p>
        <p class="text-xs card-desc">Personnel with expired renewal.</p>
      </div>
    </div>

    <!-- Report Summary -->
    <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 mb-7">
      <h3 class="font-semibold text-base force-light-text tracking-tight mb-4">Report Summary</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead>
            <tr class="border-b border-[#363b48]">
              <th class="py-2 px-4 font-semibold force-light-text">Status</th>
              <th class="py-2 px-4 font-semibold force-light-text">Count</th>
              <th class="py-2 px-4 font-semibold force-light-text">Percentage</th>
              <th class="py-2 px-4 font-semibold force-light-text">Description</th>
            </tr>
          </thead>
          <tbody>
            <tr class="report-summary-row">
              <td class="py-3 px-4"><span class="badge-renewed text-xs font-semibold">● Renewed</span></td>
              <td class="py-3 px-4 font-bold card-main-text" id="summaryRenewed">--</td>
              <td class="py-3 px-4 force-light-text" id="summaryRenewedPct">--%</td>
              <td class="py-3 px-4 text-xs card-desc">Validity date is more than 90 days away.</td>
            </tr>
            <tr class="report-summary-row">
              <td class="py-3 px-4"><span class="badge-within text-xs font-semibold">⏱ Within Renewal</span></td>
              <td class="py-3 px-4 font-bold" style="color:#ecc94b;" id="summaryWithin">--</td>
              <td class="py-3 px-4 force-light-text" id="summaryWithinPct">--%</td>
              <td class="py-3 px-4 text-xs card-desc">Validity date is within 0–90 days.</td>
            </tr>
            <tr>
             <td class="py-3 px-4"><span class="badge-expired text-xs font-semibold">✕ Expired</span></td>
              <td class="py-3 px-4 font-bold" style="color:#e53e3e;" id="summaryExpired">--</td>
              <td class="py-3 px-4 force-light-text" id="summaryExpiredPct">--%</td>
              <td class="py-3 px-4 text-xs card-desc">Validity date has already passed.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Personnel Table -->
    <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 mb-10">
      <div class="flex flex-wrap items-center justify-between mb-4 gap-2">
        <h2 class="font-semibold text-base force-light-text tracking-tight">Personnel Renewal List</h2>
        <div class="flex flex-wrap gap-2 items-center ml-auto">
          <label for="sortSelect" class="text-[#b0bac7] text-xs force-light-text">Sort by:</label>
          <select id="sortSelect" class="bg-[#23272f] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text">
            <option value="itemNumber-asc">Item # (Asc)</option>
            <option value="itemNumber-desc">Item # (Desc)</option>
            <option value="lastName-asc">Last Name (A-Z)</option>
            <option value="lastName-desc">Last Name (Z-A)</option>
            <option value="dateOfValidity-asc">Date of Validity (Earliest)</option>
            <option value="dateOfValidity-desc">Date of Validity (Latest)</option>
          </select>
          <label for="searchInput" class="text-[#b0bac7] text-xs force-light-text">Search:</label>
          <input id="searchInput" type="text" placeholder="Enter name..." class="bg-[#23272f] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text" />
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left force-light-text">
          <thead>
            <tr class="border-b border-[#252b32] text-[#b0bac7]">
              <th class="py-2 px-2 font-semibold force-light-text">Item #</th>
              <th class="py-2 px-2 font-semibold force-light-text">Date of Validity</th>
              <th class="py-2 px-2 font-semibold force-light-text">Status</th>
              <th class="py-2 px-2 font-semibold force-light-text">Last Name</th>
              <th class="py-2 px-2 font-semibold force-light-text">First Name</th>
              <th class="py-2 px-2 font-semibold force-light-text">Middle Name</th>
              <th class="py-2 px-2 font-semibold force-light-text">AFP Serial #</th>
              <th class="py-2 px-2 font-semibold force-light-text">Date of Birth</th>
              <th class="py-2 px-2 font-semibold force-light-text">Nomenclature of Pistol</th>
              <th class="py-2 px-2 font-semibold force-light-text">Pistol Serial #</th>
              <th class="py-2 px-2 font-semibold force-light-text">Qty Ammo</th>
            </tr>
          </thead>
          <tbody id="personnelTableBody"></tbody>
        </table>
      </div>
    </div>

    </div><!-- /renewalReportPanel -->

    <!-- =========================================================
         RPCSP REPORT
         ========================================================= -->
    <div id="rpcspPanel" style="display:none;">

      <!-- RPCSP Configuration -->
      <section class="mb-6">
        <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10">
          <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
              <h2 class="text-lg font-semibold force-light-text">Report on the Physical Count of Semi-Expendable Property (RPCSP)</h2>
              <p class="text-xs text-[#94a3b8] mt-1">Prepare the consolidated physical-count report using the existing personnel and firearm records.</p>
            </div>
            <div class="text-xs text-[#94a3b8]">Admin Report Center</div>
          </div>

          <div class="rpcsp-config-grid">
            <div class="rpcsp-field">
              <label for="rpcspAsOfDate">As of Date</label>
              <input type="date" id="rpcspAsOfDate">
            </div>

            <div class="rpcsp-field">
              <label for="rpcspFundCluster">Fund Cluster</label>
              <input type="text" id="rpcspFundCluster" value="General Fund">
            </div>

            <div class="rpcsp-field">
              <label for="rpcspOfficer">Accountable Officer</label>
              <input type="text" id="rpcspOfficer" value="MS ROSEMARIE O VILBAR">
            </div>

            <div class="rpcsp-field">
              <label for="rpcspDesignation">Designation / Office</label>
              <input type="text" id="rpcspDesignation" value="Chief, PAOGS, APAO, PA">
            </div>

            <div class="rpcsp-field">
              <label for="rpcspAssumptionDate">Date of Assumption</label>
              <input type="date" id="rpcspAssumptionDate" value="2022-03-24">
            </div>

            <div class="rpcsp-field">
              <label for="rpcspUnitValue">Default Unit Value (₱)</label>
              <input type="number" id="rpcspUnitValue" min="0" step="0.01" value="16450.00">
            </div>

            <div class="rpcsp-field">
              <label for="rpcspUnitFilter">Unit / Organization</label>
              <select id="rpcspUnitFilter">
                <option value="">All Units</option>
              </select>
            </div>

            <div class="rpcsp-field">
              <label for="rpcspRemarks">Default Remarks</label>
              <select id="rpcspRemarks">
                <option value="Serviceable">Serviceable</option>
                <option value="Unserviceable">Unserviceable</option>
                <option value="For Repair">For Repair</option>
              </select>
            </div>
          </div>

          <div class="rpcsp-actions">
            <button type="button" id="previewRpcspBtn" class="rpcsp-btn rpcsp-btn-primary" aria-controls="rpcspPreviewSection" aria-expanded="false">Preview RPCSP</button>
            <button type="button" id="printRpcspBtn" class="rpcsp-btn rpcsp-btn-green">Print / Save PDF</button>
            <button type="button" id="exportRpcspBtn" class="rpcsp-btn rpcsp-btn-amber">Export Excel</button>
          </div>
          <div id="rpcspPreviewStatus" class="rpcsp-preview-status" role="status" aria-live="polite"></div>
        </div>
      </section>

      <!-- RPCSP Preview -->
      <section class="mb-10 rpcsp-preview-section" id="rpcspPreviewSection" hidden>
        <div class="rpcsp-preview-shell">
          <div class="rpcsp-document" id="rpcspDocument">
            <h1 class="rpcsp-title">REPORT ON THE PHYSICAL COUNT OF SEMI-EXPENDABLE PROPERTY</h1>
            <p class="rpcsp-subtitle">MILITARY, POLICE &amp; SECURITY EQUIPMENT (ACCOUNT CODE: 1-04-05-090-00)</p>
            <p class="rpcsp-subtitle">(Type of Property, Plant and Equipment)</p>
            <p class="rpcsp-asof">As of <span id="rpcspPreviewAsOf">—</span></p>

            <p class="rpcsp-fund">Fund Cluster: <span id="rpcspPreviewFund">General Fund</span></p>

            <p class="rpcsp-forwhich">
              For which:
              <span class="accountable-name" id="rpcspPreviewOfficer">MS ROSEMARIE O VILBAR</span>,
              <span id="rpcspPreviewDesignation">Chief, PAOGS, APAO, PA</span>
              is accountable, having assumed such accountability on
              <span id="rpcspPreviewAssumption">24 Mar 2022</span>.
            </p>

            <table class="rpcsp-table">
              <colgroup>
                <col style="width:7%">
                <col style="width:13%">
                <col style="width:14%">
                <col style="width:8%">
                <col style="width:9%">
                <col style="width:8%">
                <col style="width:7%">
                <col style="width:6%">
                <col style="width:7%">
                <col style="width:11%">
                <col style="width:10%">
              </colgroup>
              <thead>
                <tr>
                  <th rowspan="2">ARTICLE</th>
                  <th rowspan="2">DESCRIPTION</th>
                  <th rowspan="2">SEMI-EXPENDABLE<br>PROPERTY NUMBER</th>
                  <th rowspan="2">UNIT OF<br>MEASURE</th>
                  <th rowspan="2">UNIT VALUE</th>
                  <th rowspan="2">BALANCE PER<br>PROPERTY CARD</th>
                  <th rowspan="2">ON HAND<br>PER COUNT</th>
                  <th colspan="2">SHORTAGE / OVERAGE</th>
                  <th rowspan="2">REMARKS</th>
                  <th rowspan="2">DATE</th>
                </tr>
                <tr>
                  <th>QUANTITY</th>
                  <th>VALUE</th>
                </tr>
              </thead>
              <tbody id="rpcspTableBody">
                <tr><td colspan="11" class="rpcsp-empty">Click “Preview RPCSP” to generate the report.</td></tr>
              </tbody>
            </table>

            <div class="rpcsp-summary">
              <span>Total Property Count: <strong id="rpcspTotalCount">0</strong></span>
              <span>Total Value: <strong id="rpcspTotalValue">₱0.00</strong></span>
            </div>

            <p class="rpcsp-note">
              Generated from the active personnel/firearm records currently stored in the APAO system.
            </p>

            <section class="rpcsp-certification" aria-label="Report signatories">
              <div class="rpcsp-certification-column">
                <p class="rpcsp-certification-title">Certified Correct by:</p>
                <div class="rpcsp-signatory">
                  <p class="rpcsp-signatory-name">MS ROSEMARIE O VILBAR, MPA</p>
                  <p>CHIEF, PAOGS, APAO, PA</p>
                  <p>TEAM LEADER</p>
                </div>
                <div class="rpcsp-signatory">
                  <p class="rpcsp-signatory-name">Mr Jan Harold C Novo CE</p>
                  <p>UPO, 4ID, PA</p>
                  <p>Member</p>
                </div>
              </div>
              <div class="rpcsp-certification-column">
                <p class="rpcsp-certification-title">Approved by:</p>
                <div class="rpcsp-signatory">
                  <p class="rpcsp-signatory-name">ANTHONY A BACUS</p>
                  <p>LTC INF (GSC) PA</p>
                  <p>Commanding Officer</p>
                </div>
              </div>
              <div class="rpcsp-certification-column">
                <p class="rpcsp-certification-title">Witnessed by:</p>
                <div class="rpcsp-signatory">
                  <p class="rpcsp-signatory-name">Mr Darrell Antoni J Wong</p>
                  <p>Rep, COA, HPA</p>
                  <p>Member</p>
                </div>
              </div>
            </section>
          </div>
        </div>
      </section>

    </div><!-- /rpcspPanel -->

  </main>
</div>

<script src="<?php echo e(asset('js/rpcsp_excel.js')); ?>"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const ROUTES = {
  personnelData: "<?php echo e(route('admin.personnel.data')); ?>",
  login:         "<?php echo e(route('login')); ?>",
};

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
  applyTheme(localStorage.getItem('theme') || 'dark');
  document.getElementById('themeToggle').addEventListener('click', function () {
    const next = localStorage.getItem('theme') === 'light' ? 'dark' : 'light';
    localStorage.setItem('theme', next);
    applyTheme(next);
  });

  // ===== NOTIFICATION BELL =====
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

  // ===== REPORT PERIOD CONTROLS =====
  const periodSelect = document.getElementById("reportPeriod");
  const customStart  = document.getElementById("customStart");
  const customEnd    = document.getElementById("customEnd");
  const customDash   = document.getElementById("customDash");
  periodSelect.addEventListener("change", function () {
    if (this.value === "custom") {
      customStart.classList.remove("hidden"); customEnd.classList.remove("hidden"); customDash.classList.remove("hidden");
    } else {
      customStart.classList.add("hidden"); customEnd.classList.add("hidden"); customDash.classList.add("hidden");
      customStart.value = ""; customEnd.value = "";
    }
  });

  // ===== STATUS HELPERS (matches admin personnel page logic) =====

  function getValidityStatus(dateOfValidity) {
    if (!dateOfValidity) return 'pending';
    const currDate     = new Date();
    const validityDate = new Date(dateOfValidity);
    const diffDays     = Math.ceil((validityDate - currDate) / (1000 * 60 * 60 * 24));
    if (diffDays > 90) return 'renewed';
    if (diffDays >= 0) return 'within';
    return 'expired';
  }

  // Manual approvedStatus overrides date-based status (unless it's 'pending' or empty)
  function resolveStatus(p) {
    const manual = (p.approvedStatus || '').trim().toLowerCase();
    if (manual && manual !== 'pending') return manual;
    return getValidityStatus(p.dateOfValidity);
  }

 function statusBadgeHtml(p) {
    const s = resolveStatus(p);
    if (s === 'renewed') return `<span class="badge-renewed text-xs font-semibold">● Renewed</span>`;
    if (s === 'within')  return `<span class="badge-within text-xs font-semibold">⏱ Within Renewal</span>`;
    if (s === 'expired') return `<span class="badge-expired text-xs font-semibold">✕ Expired</span>`;
    return `<span class="text-xs font-semibold" style="color:#64748b;">— Pending —</span>`;
  }

  // ===== PERSONNEL DATA =====
  let allPersonnel = [];
  let currentSort  = "itemNumber-asc";

  async function loadPersonnel() {
    try {
      const res  = await fetch(ROUTES.personnelData);
      const json = await res.json();
      allPersonnel = (json.success && Array.isArray(json.data)) ? json.data : [];
    } catch (e) { allPersonnel = []; }
    renderPersonnelTable(allPersonnel);
  }

  function updateMetrics(filtered) {
    let renewed = 0, withinRenewal = 0, expired = 0;
    filtered.forEach(p => {
      const s = resolveStatus(p);
      if (s === 'renewed') renewed++;
      else if (s === 'within') withinRenewal++;
      else if (s === 'expired') expired++;
    });
    const total = filtered.length;
    document.getElementById("totalUsers").innerText    = total;
    document.getElementById("totalRenewed").innerText  = renewed;
    document.getElementById("withinRenewal").innerText = withinRenewal;
    document.getElementById("expired").innerText       = expired;
    const pct = (n) => total ? Math.round((n / total) * 100) + '%' : '--';
    document.getElementById("summaryRenewed").innerText    = renewed;
    document.getElementById("summaryWithin").innerText     = withinRenewal;
    document.getElementById("summaryExpired").innerText    = expired;
    document.getElementById("summaryRenewedPct").innerText = pct(renewed);
    document.getElementById("summaryWithinPct").innerText  = pct(withinRenewal);
    document.getElementById("summaryExpiredPct").innerText = pct(expired);
  }

  function sortPersonnel(list, sortBy) {
    const [key, dir] = sortBy.split("-");
    return list.slice().sort((a, b) => {
      let aVal = a[key], bVal = b[key];
      if (key === "itemNumber" || key === "qtyAmmo") { aVal = Number(aVal); bVal = Number(bVal); }
      else if (key !== "dateOfValidity" && key !== "dateOfBirth") { aVal = (aVal||"").toString().toLowerCase(); bVal = (bVal||"").toString().toLowerCase(); }
      if (aVal < bVal) return dir === "asc" ? -1 : 1;
      if (aVal > bVal) return dir === "asc" ? 1 : -1;
      return 0;
    });
  }

  function renderPersonnelTable(data) {
    let displayed = (data || allPersonnel).slice();
    const val = (document.getElementById("searchInput")?.value?.trim().toLowerCase()) || "";
    if (val) displayed = displayed.filter(p =>
      p.firstName?.toLowerCase().includes(val) ||
      p.middleName?.toLowerCase().includes(val) ||
      p.lastName?.toLowerCase().includes(val)
    );
    displayed = sortPersonnel(displayed, currentSort);
    const tbody = document.getElementById("personnelTableBody");
    tbody.innerHTML = "";
    if (!displayed.length) {
      tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-gray-400 force-light-text">No data found.</td></tr>`;
      updateMetrics([]);
      return;
    }
    displayed.forEach(row => {
      tbody.innerHTML += `<tr class="border-b border-[#2a2f3a] hover:bg-[#1e2329] transition-colors">
        <td class="py-2 px-2 force-light-text">${row.itemNumber||''}</td>
        <td class="py-2 px-2 force-light-text">${row.dateOfValidity||''}</td>
        <td class="py-2 px-2">${statusBadgeHtml(row)}</td>
        <td class="py-2 px-2 force-light-text">${row.lastName||''}</td>
        <td class="py-2 px-2 force-light-text">${row.firstName||''}</td>
        <td class="py-2 px-2 force-light-text">${row.middleName||''}</td>
        <td class="py-2 px-2 force-light-text">${row.afpSerialNumber||''}</td>
        <td class="py-2 px-2 force-light-text">${row.dateOfBirth||''}</td>
        <td class="py-2 px-2 force-light-text">${row.pistolNomenclature||''}</td>
        <td class="py-2 px-2 force-light-text">${row.pistolSerialNumber||''}</td>
        <td class="py-2 px-2 force-light-text">${row.qtyAmmo||0}</td>
      </tr>`;
    });
    updateMetrics(displayed);
  }

  document.getElementById("searchInput").addEventListener("input", () => renderPersonnelTable());
  document.getElementById("sortSelect").addEventListener("change", function (e) { currentSort = e.target.value; renderPersonnelTable(); });

  // ===== PERIOD FILTER =====
  document.getElementById("reportFilterForm").addEventListener("submit", function (e) {
    e.preventDefault();
    let filtered = allPersonnel.slice();
    const period = periodSelect.value;
    const today  = new Date();
    if (period === "this-year") {
      filtered = filtered.filter(p => (new Date(p.dateOfValidity)).getFullYear() === today.getFullYear());
    } else if (period === "last-year") {
      filtered = filtered.filter(p => (new Date(p.dateOfValidity)).getFullYear() === today.getFullYear() - 1);
    } else if (period === "custom") {
      const from = customStart.value ? new Date(customStart.value) : null;
      const to   = customEnd.value   ? new Date(customEnd.value)   : null;
      filtered = filtered.filter(p => {
        const d = new Date(p.dateOfValidity);
        let ok = true;
        if (from) ok = ok && (d >= from);
        if (to)   ok = ok && (d <= to);
        return ok;
      });
    }
    renderPersonnelTable(filtered);
  });

  document.getElementById("downloadReportBtn").onclick = function () { const rows = document.querySelectorAll("#personnelTableBody tr"); if (!rows.length || (rows.length === 1 && rows[0].querySelector("td[colspan]"))) { alert("No data. Click Preview first."); return; } const headers = ["Item #","Date of Validity","Status","Last Name","First Name","Middle Name","AFP Serial #","Date of Birth","Nomenclature of Pistol","Pistol Serial #","Qty Ammo"]; let tableRows = ""; rows.forEach(row => { const cells = row.querySelectorAll("td"); if (!cells.length) return; let r = ""; cells.forEach(cell => { r += `<td style="border:1px solid #ccc;padding:5px 8px;font-size:11px;">${cell.innerText.trim()}</td>`; }); tableRows += `<tr>${r}</tr>`; }); const today = new Date().toLocaleDateString(); const win = window.open("","_blank"); win.document.write(`<!DOCTYPE html><html><head><meta charset="UTF-8"><title>APAO Report</title><style>body{font-family:Arial;margin:30px;}h2{text-align:center;}table{width:100%;border-collapse:collapse;}th{background:#1a3a2a;color:#fff;padding:6px;font-size:11px;border:1px solid #ccc;text-align:left;}</style></head><body><h2>ARMY PROPERTY ACCOUNTABILITY OFFICE</h2><h2>Personnel Renewal Report</h2><p style="text-align:center;font-size:12px;color:#555;">Generated: ${today}</p><table><thead><tr>${headers.map(h=>"<th>"+h+"</th>").join("")}</tr></thead><tbody>${tableRows}</tbody></table></body></html>`); win.document.close(); setTimeout(()=>{win.focus();win.print();},400); };
  document.getElementById("exportExcelBtn").onclick = function () {
    const tableRows = document.querySelectorAll("#personnelTableBody tr");
    if (!tableRows.length || (tableRows.length === 1 && tableRows[0].querySelector("td[colspan]"))) {
      alert("No data. Click Preview first.");
      return;
    }

    const periodLabels = {
      "this-year": "This Year",
      "last-year": "Last Year",
      "custom": [customStart.value, customEnd.value].filter(Boolean).join(" to ") || "Custom Period"
    };

    const rows = Array.from(tableRows).map(row => {
      const cells = Array.from(row.querySelectorAll("td"));
      return {
        itemNumber: Number(cells[0]?.innerText.trim() || 0),
        dateOfValidity: cells[1]?.innerText.trim() || "",
        status: cells[2]?.innerText.trim() || "",
        lastName: cells[3]?.innerText.trim() || "",
        firstName: cells[4]?.innerText.trim() || "",
        middleName: cells[5]?.innerText.trim() || "",
        afpSerialNumber: cells[6]?.innerText.trim() || "",
        dateOfBirth: cells[7]?.innerText.trim() || "",
        pistolNomenclature: cells[8]?.innerText.trim() || "",
        pistolSerialNumber: cells[9]?.innerText.trim() || "",
        qtyAmmo: Number(cells[10]?.innerText.trim() || 0)
      };
    });

    exportPersonnelRenewalExcel({
      filename: "APAO_Personnel_Renewal_Report_" + new Date().toISOString().slice(0, 10) + ".xls",
      generatedDate: new Date().toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" }),
      period: periodLabels[periodSelect.value] || "All Records",
      rows
    });
  };


  // ================================================================
  // REPORT CENTER SWITCHING
  // ================================================================
  const renewalPanel = document.getElementById('renewalReportPanel');
  const rpcspPanel = document.getElementById('rpcspPanel');
  const renewalBtn = document.getElementById('showRenewalReportBtn');
  const rpcspBtn = document.getElementById('showRpcspBtn');

  function switchReport(type) {
    const isRpcsp = type === 'rpcsp';
    renewalPanel.style.display = isRpcsp ? 'none' : 'block';
    rpcspPanel.style.display = isRpcsp ? 'block' : 'none';
    renewalBtn.classList.toggle('active', !isRpcsp);
    rpcspBtn.classList.toggle('active', isRpcsp);

    if (isRpcsp) {
      populateRpcspUnitFilter();
    }
  }

  renewalBtn.addEventListener('click', () => switchReport('renewal'));
  rpcspBtn.addEventListener('click', () => switchReport('rpcsp'));

  // ================================================================
  // RPCSP
  // ================================================================
  const rpcspAsOfDate = document.getElementById('rpcspAsOfDate');
  const rpcspFundCluster = document.getElementById('rpcspFundCluster');
  const rpcspOfficer = document.getElementById('rpcspOfficer');
  const rpcspDesignation = document.getElementById('rpcspDesignation');
  const rpcspAssumptionDate = document.getElementById('rpcspAssumptionDate');
  const rpcspUnitValue = document.getElementById('rpcspUnitValue');
  const rpcspUnitFilter = document.getElementById('rpcspUnitFilter');
  const rpcspRemarks = document.getElementById('rpcspRemarks');
  const rpcspPreviewSection = document.getElementById('rpcspPreviewSection');
  const rpcspPreviewStatus = document.getElementById('rpcspPreviewStatus');

  rpcspAsOfDate.value = new Date().toISOString().slice(0, 10);

  function rpcspEscape(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function rpcspMoney(value) {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(Number(value || 0)).replace('PHP', '₱');
  }

  function rpcspLongDate(value) {
    if (!value) return '—';
    const d = new Date(value + (String(value).length === 10 ? 'T00:00:00' : ''));
    if (isNaN(d)) return value;
    return d.toLocaleDateString('en-US', {
      day: '2-digit',
      month: 'short',
      year: 'numeric'
    });
  }

  function populateRpcspUnitFilter() {
    const current = rpcspUnitFilter.value;
    const units = [...new Set(
      allPersonnel.map(p => (p.unit || '').trim()).filter(Boolean)
    )].sort((a, b) => a.localeCompare(b));

    rpcspUnitFilter.innerHTML =
      '<option value="">All Units</option>' +
      units.map(unit => `<option value="${rpcspEscape(unit)}">${rpcspEscape(unit)}</option>`).join('');

    if (units.includes(current)) rpcspUnitFilter.value = current;
  }

  function getRpcspRows() {
    const unit = (rpcspUnitFilter.value || '').trim().toLowerCase();

    return allPersonnel.filter(p => {
      if (!unit) return true;
      return (p.unit || '').trim().toLowerCase() === unit;
    });
  }

  function renderRpcsp() {
    const rows = getRpcspRows();
    const body = document.getElementById('rpcspTableBody');
    const unitValue = Number(rpcspUnitValue.value || 0);
    const defaultRemarks = rpcspRemarks.value || 'Serviceable';

    document.getElementById('rpcspPreviewAsOf').textContent =
      rpcspLongDate(rpcspAsOfDate.value);

    document.getElementById('rpcspPreviewFund').textContent =
      rpcspFundCluster.value.trim() || 'General Fund';

    document.getElementById('rpcspPreviewOfficer').textContent =
      rpcspOfficer.value.trim() || '—';

    document.getElementById('rpcspPreviewDesignation').textContent =
      rpcspDesignation.value.trim() || '—';

    document.getElementById('rpcspPreviewAssumption').textContent =
      rpcspLongDate(rpcspAssumptionDate.value);

    if (!rows.length) {
      body.innerHTML =
        '<tr><td colspan="11" class="rpcsp-empty">No personnel/firearm records match the selected filter.</td></tr>';
      document.getElementById('rpcspTotalCount').textContent = '0';
      document.getElementById('rpcspTotalValue').textContent = rpcspMoney(0);
      return;
    }

    body.innerHTML = rows.map(p => {
      const propertyNumber =
        (p.pistolSerialNumber || '').trim() ||
        (p.afpSerialNumber || '').trim() ||
        '—';

      const description =
        (p.pistolNomenclature || '').trim() || 'Pistol';

      const balance = 1;
      const onHand = 1;
      const shortageQty = Math.max(0, balance - onHand);
      const shortageValue = shortageQty * unitValue;

      return `
        <tr>
          <td>Pistol</td>
          <td>${rpcspEscape(description)}</td>
          <td>${rpcspEscape(propertyNumber)}</td>
          <td>ea</td>
          <td>${rpcspMoney(unitValue)}</td>
          <td>${balance}</td>
          <td>${onHand}</td>
          <td>${shortageQty === 0 ? '' : shortageQty}</td>
          <td>${shortageValue === 0 ? '' : rpcspMoney(shortageValue)}</td>
          <td>${rpcspEscape(defaultRemarks)}</td>
          <td>${rpcspEscape(rpcspLongDate(p.dateOfValidity))}</td>
        </tr>
      `;
    }).join('');

    document.getElementById('rpcspTotalCount').textContent = String(rows.length);
    document.getElementById('rpcspTotalValue').textContent =
      rpcspMoney(rows.length * unitValue);
  }

  function refreshVisibleRpcspPreview() {
    if (!rpcspPreviewSection.hidden) renderRpcsp();
  }

  document.getElementById('previewRpcspBtn').addEventListener('click', function () {
    const previewButton = this;

    if (!rpcspPreviewSection.hidden) {
      rpcspPreviewSection.hidden = true;
      previewButton.textContent = 'Preview RPCSP';
      previewButton.setAttribute('aria-expanded', 'false');
      rpcspPreviewStatus.className = 'rpcsp-preview-status';
      rpcspPreviewStatus.textContent = '';
      return;
    }

    previewButton.disabled = true;
    previewButton.textContent = 'Generating...';
    rpcspPreviewStatus.className = 'rpcsp-preview-status';
    rpcspPreviewStatus.textContent = 'Preparing the RPCSP document...';

    try {
      renderRpcsp();
      rpcspPreviewSection.hidden = false;
      previewButton.setAttribute('aria-expanded', 'true');
      const rowCount = getRpcspRows().length;
      rpcspPreviewStatus.className = 'rpcsp-preview-status success';
      rpcspPreviewStatus.textContent = rowCount
        ? `RPCSP preview generated with ${rowCount} record${rowCount === 1 ? '' : 's'}.`
        : 'RPCSP preview generated. No records match the selected unit.';

      requestAnimationFrame(() => {
        rpcspPreviewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    } catch (error) {
      console.error('Unable to generate RPCSP preview:', error);
      rpcspPreviewStatus.className = 'rpcsp-preview-status error';
      rpcspPreviewStatus.textContent = 'Unable to generate the preview. Please reload the page and try again.';
    } finally {
      previewButton.disabled = false;
      previewButton.textContent = rpcspPreviewSection.hidden
        ? 'Preview RPCSP'
        : 'Close RPCSP Preview';
    }
  });

  rpcspUnitFilter.addEventListener('change', refreshVisibleRpcspPreview);
  rpcspRemarks.addEventListener('change', refreshVisibleRpcspPreview);
  [rpcspAsOfDate, rpcspFundCluster, rpcspOfficer, rpcspDesignation, rpcspAssumptionDate, rpcspUnitValue]
    .forEach(field => field.addEventListener('change', refreshVisibleRpcspPreview));

  document.getElementById('printRpcspBtn').addEventListener('click', function () {
    renderRpcsp();

    const report = document.getElementById('rpcspDocument').innerHTML;
    const win = window.open('', '_blank');

    if (!win) {
      alert('Please allow pop-ups to print the RPCSP.');
      return;
    }

    win.document.write(`<!doctype html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>RPCSP - ${rpcspEscape(rpcspAsOfDate.value)}</title>
        <style>
          @page { size: A4 landscape; margin: 8mm; }
          * { box-sizing: border-box; }
          body { margin:0; font-family:Arial,Helvetica,sans-serif; color:#111; background:#fff; }
          .rpcsp-document { width:100%; background:#fff; color:#111; padding:0; box-shadow:none; }
          .rpcsp-title { text-align:center; font-weight:700; font-size:14px; line-height:1.15; margin:0; }
          .rpcsp-subtitle { text-align:center; font-size:10px; line-height:1.2; margin:1px 0; }
          .rpcsp-asof { text-align:center; font-size:10px; margin:2px 0 10px; }
          .rpcsp-fund { font-size:10px; text-decoration:underline; margin:0 0 9px; }
          .rpcsp-forwhich { font-size:10px; line-height:1.3; margin:0 0 8px; }
          .accountable-name { font-weight:700; text-transform:uppercase; }
          .rpcsp-table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:8px; }
          .rpcsp-table th,.rpcsp-table td { border:1px solid #222; padding:3px 4px; vertical-align:middle; }
          .rpcsp-table th { text-align:center; font-weight:700; background:#fff; }
          .rpcsp-table td { text-align:center; }
          .rpcsp-table td:nth-child(1),.rpcsp-table td:nth-child(2),.rpcsp-table td:nth-child(10) { text-align:left; }
          .rpcsp-summary { display:flex; justify-content:flex-end; gap:25px; margin-top:8px; font-size:9px; }
          .rpcsp-note { font-size:8px; color:#555; margin-top:7px; }
          .rpcsp-certification { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:36px; margin-top:30px; font-size:10px; line-height:1.3; }
          .rpcsp-certification-title { font-weight:700; margin:0 0 34px; }
          .rpcsp-certification-column,.rpcsp-signatory { break-inside:avoid; page-break-inside:avoid; }
          .rpcsp-signatory + .rpcsp-signatory { margin-top:28px; }
          .rpcsp-signatory p { margin:0; }
          .rpcsp-signatory-name { font-weight:700; }
        </style>
      </head>
      <body>
        <div class="rpcsp-document">${report}</div>
      </body>
      </html>`);

    win.document.close();
    setTimeout(() => {
      win.focus();
      win.print();
    }, 350);
  });

  document.getElementById('exportRpcspBtn').addEventListener('click', function () {
    const rows = getRpcspRows();
    if (!rows.length) {
      alert('No RPCSP data to export.');
      return;
    }
    const unitValue = Number(rpcspUnitValue.value || 0);
    const defaultRemarks = rpcspRemarks.value || 'Serviceable';

    exportRpcspExcel({
      filename: 'RPCSP_' + (rpcspAsOfDate.value || new Date().toISOString().slice(0,10)) + '.xls',
      asOf: rpcspLongDate(rpcspAsOfDate.value),
      fund: rpcspFundCluster.value.trim() || 'General Fund',
      officer: rpcspOfficer.value.trim() || '—',
      designation: rpcspDesignation.value.trim() || '—',
      assumption: rpcspLongDate(rpcspAssumptionDate.value),
      totalValue: rows.length * unitValue,
      rows: rows.map(p => ({
        article: 'Pistol',
        description: p.pistolNomenclature || 'Pistol',
        propertyNumber: (p.pistolSerialNumber || '').trim() || (p.afpSerialNumber || '').trim() || '—',
        unit: 'ea', unitValue: unitValue, balance: 1, onHand: 1,
        shortageQuantity: '', shortageValue: '', remarks: defaultRemarks,
        date: rpcspLongDate(p.dateOfValidity)
      }))
    });
  });

  // Keep RPCSP filters synchronized after personnel data loads.
  const originalLoadPersonnel = loadPersonnel;
  loadPersonnel = async function() {
    await originalLoadPersonnel();
    populateRpcspUnitFilter();
  };

  loadPersonnel();
});
</script>
</body>
</html>
