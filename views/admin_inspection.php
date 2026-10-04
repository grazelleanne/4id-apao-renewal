<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Admin | Inspection / Renewal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">  <script src="/tailwind.min.js"></script>
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
  .notification-bell{position:relative;cursor:pointer;outline:none;transition:color .2s ease;}
  .notification-badge{position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;font-weight:700;border-radius:999px;font-size:.62rem;min-width:17px;height:17px;display:none;align-items:center;justify-content:center;border:2px solid #1a2025;}
  .notification-bell.has-unread .notification-badge{display:flex;}
  #adminNotifDropdown{display:none;position:absolute;top:calc(100% + 10px);right:0;width:320px;background:#23272f;border:1px solid #363b48;border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.4);z-index:200;overflow:hidden;}
  #adminNotifDropdown.open{display:block;}
  .notif-header{padding:12px 16px;border-bottom:1px solid #363b48;display:flex;justify-content:space-between;align-items:center;font-size:.8rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;}
  .notif-mark-read{font-size:.72rem;color:#3ec6ff;cursor:pointer;text-transform:none;letter-spacing:0;}
  .notif-list{max-height:340px;overflow-y:auto;}
  .notif-item{padding:12px 16px;border-bottom:1px solid #2e333d;display:flex;gap:10px;align-items:flex-start;font-size:.8rem;color:#cbd5e0;}
  .notif-item.unread{background:#1c2631;}
  .notif-content{flex:1;min-width:0;}.notif-title{font-weight:700;color:#e5eaf2;}.notif-message{color:#94a3b8;font-size:.75rem;line-height:1.4;}.notif-time{color:#64748b;font-size:.7rem;margin-top:4px;}
  .notif-dot{width:7px;height:7px;border-radius:50%;background:#3ec6ff;flex-shrink:0;margin-top:5px;}
  .notif-empty,.notif-footer{padding:10px 16px;text-align:center;color:#64748b;font-size:.72rem;}.notif-empty{padding:24px 16px;}.notif-footer{border-top:1px solid #363b48;}
  /* ── SIDEBAR ── */
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
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;color:#e5eaf2 !important;text-decoration:none;font-size:0.85rem;font-weight:500;white-space:nowrap;transition:background 0.15s,color 0.15s;overflow:hidden;}
.nav-item:hover{background:#23272f;color:#e5eaf2;}
.nav-item.active{background:#23272f;color:#e5eaf2;}
.nav-item svg{width:20px;height:20px;flex-shrink:0;}
.nav-label{transition:opacity 0.15s,width 0.15s;overflow:hidden;white-space:nowrap;}
#sidebar.sidebar-collapsed .nav-label{opacity:0;width:0;}
#sidebar.sidebar-collapsed .nav-item{justify-content:center;padding:9px 0;gap:0;}
.sb-bottom{padding-top:12px;border-top:1px solid #23272f;display:flex;justify-content:center;}
.theme-btn{background:#23272f;border:none;color:#94a3b8;cursor:pointer;padding:8px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:background 0.15s,color 0.15s;}
.theme-btn:hover{background:#2d3340;color:#e5eaf2;}

/* ── STAT CARDS ── */
.stat-card{background:#23272f;border:2px solid #2a2f3a;border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;cursor:pointer;transition:border-color .2s,box-shadow .2s,background .2s;user-select:none;position:relative;}
.stat-card .text-2xl{color:#e5eaf2;}
body.light-mode .stat-card .text-2xl{color:#1e293b;}
.stat-card:hover{border-color:#363b48;background:#2a2f3a;}
.stat-card.active-pending{border-color:#94a3b8;box-shadow:0 0 0 3px rgba(148,163,184,.13);}
.stat-card.active-renewal{border-color:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.12);}
.stat-card.active-under{border-color:#eab308;box-shadow:0 0 0 3px rgba(234,179,8,.12);}
/* Inspection workflow order: Pending -> Under Inspection -> Ready for Renewal */
#card-pending{order:1;}
#card-under{order:2;}
#card-renewal{order:3;}
.stat-card .card-count{position:absolute;top:14px;right:16px;font-size:.78rem;font-weight:800;padding:2px 9px;border-radius:999px;}
#statPending{color:#94a3b8;}
#statApproved{color:#22c55e;}
#statUnder{color:#eab308;}
.stat-card.active-pending .card-count{background:#27303b;color:#cbd5e1;}
.stat-card.active-renewal .card-count{background:#0d3325;color:#4ade80;}
.stat-card.active-under .card-count{background:#352b08;color:#facc15;}
.stat-card:not(.active-pending):not(.active-renewal):not(.active-under) .card-count{background:#1a2025;color:#94a3b8;}

/* ── BADGES ── */
.badge{display:inline-flex;align-items:center;gap:8px;padding:0;border:0;border-radius:0;background:transparent;font-size:.75rem;font-weight:700;}
.badge::before{content:'';width:7px;height:7px;border-radius:50%;flex-shrink:0;}
.badge-pending{color:#94a3b8;}
.badge-pending::before{background:#94a3b8;}
.badge-under{color:#eab308;}
.badge-under::before{background:#eab308;}
.badge-approved{color:#22c55e;}
.badge-approved::before{background:#22c55e;}
.badge-needs_repair{background:#2d0a0a;color:#fc8181;border:1px solid #991b1b;}

/* ── TABLE ── */
.tbl th{padding:9px 12px;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:1px solid #23272f;text-align:left;white-space:nowrap;}
.tbl td{padding:10px 12px;font-size:.82rem;border-bottom:1px solid #1a2025;color:#cbd5e1;}
.tbl tr:hover td{background:#1a2025;}

/* ── ACTION BUTTONS ── */
.btn-inspect,.btn-notify,.btn-continue{display:inline-flex;align-items:center;justify-content:center;gap:7px;background:#16a34a;color:#fff;border:1px solid #16a34a;border-radius:8px;padding:6px 12px;font-size:.75rem;font-weight:700;cursor:pointer;white-space:nowrap;box-shadow:0 1px 2px rgba(15,23,42,.18);transition:background .15s,border-color .15s,transform .15s,box-shadow .15s;}
.btn-notify svg{width:14px;height:14px;stroke-width:2;}
.btn-inspect:hover,.btn-notify:hover,.btn-continue:hover{background:#15803d;border-color:#15803d;box-shadow:0 3px 8px rgba(22,163,74,.24);transform:translateY(-1px);}
.btn-inspect:focus-visible,.btn-notify:focus-visible,.btn-continue:focus-visible{outline:3px solid rgba(34,197,94,.3);outline-offset:2px;}
body.light-mode .btn-inspect,body.light-mode .btn-notify,body.light-mode .btn-continue{background:#16a34a;color:#fff;border-color:#16a34a;}
body.light-mode .btn-inspect:hover,body.light-mode .btn-notify:hover,body.light-mode .btn-continue:hover{background:#15803d;border-color:#15803d;}

/* ── TAB LABELS ── */
.tab-label{font-size:.72rem;padding:2px 9px;border-radius:999px;font-weight:700;margin-left:8px;vertical-align:middle;}
.tab-label-pending{background:#27303b;color:#cbd5e1;}
.tab-label-renewal{background:#0d3325;color:#4ade80;}
.tab-label-under{background:#352b08;color:#facc15;}

/* ── CHECKLIST ── */
.cl-table{width:100%;border-collapse:collapse;font-size:.73rem;}
.cl-table th{background:#181c21;color:#64748b;padding:5px 6px;border:1px solid #2a2f3a;text-align:center;font-weight:600;font-size:.68rem;}
.cl-table td{padding:4px 6px;border:1px solid #2a2f3a;color:#cbd5e1;vertical-align:middle;}
.cl-table tr:nth-child(even) td{background:#1a2025;}
.check-radio{width:13px;height:13px;accent-color:#3ec6ff;cursor:pointer;}

/* ── SIG BLOCK ── */
.sig-block{border:1px solid #2a2f3a;border-radius:8px;padding:12px 10px;text-align:center;background:#181c21;min-height:140px;display:flex;flex-direction:column;align-items:center;}
.sig-img{max-height:48px;max-width:100%;object-fit:contain;margin:6px auto;}
.sig-name{font-weight:700;font-size:.75rem;color:#e5eaf2;margin-top:6px;text-transform:uppercase;}
.sig-pos{font-size:.68rem;color:#94a3b8;line-height:1.4;}

/* ── CHECKLIST ACTION BUTTONS ── */
.btn-renew{background:#0d3325;color:#33b481;border:1px solid #166534;border-radius:7px;padding:9px 20px;font-size:.82rem;font-weight:700;cursor:pointer;}
.btn-renew:hover{background:#14532d;}
.btn-print{background:#23272f;color:#94a3b8;border:1px solid #363b48;border-radius:7px;padding:9px 20px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;}
.btn-print:hover{border-color:#94a3b8;color:#e5eaf2;}
.btn-edit{font-size:.72rem;color:#3ec6ff;border:1px solid #3ec6ff;border-radius:5px;padding:3px 10px;background:transparent;cursor:pointer;margin-top:8px;}
.btn-edit:hover{background:#0a1f3a;}
.btn-save{background:#3ec6ff;color:#0a1f3a;border:none;border-radius:7px;padding:9px 20px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;}
.btn-save:hover{background:#6dd4ff;}

/* ── PAGINATION ── */
.pg-btn{background:#23272f;border:1px solid #363b48;color:#94a3b8;border-radius:6px;padding:5px 11px;font-size:.78rem;cursor:pointer;}
.pg-btn:hover,.pg-btn.active{background:#3ec6ff;color:#0a1f3a;border-color:#3ec6ff;}
/* ── ICON CIRCLES ── */
.icon-circle-pending{background:#27303b;}
.icon-circle-renewal{background:#0d3325;}
.icon-circle-under{background:#352b08;}

body.light-mode .icon-circle-pending{background:#e2e8f0;}
body.light-mode .icon-circle-renewal{background:#dcfce7;}
body.light-mode .icon-circle-under{background:#fef9c3;}

/* ── LIGHT MODE BADGES ── */
body.light-mode .badge-pending{background:transparent;color:#64748b;border:0;}
body.light-mode .badge-under{background:transparent;color:#a16207;border:0;}
body.light-mode .badge-approved{background:transparent;color:#15803d;border:0;}
body.light-mode .badge-pending::before{background:#64748b;}
body.light-mode .badge-under::before{background:#ca8a04;}
body.light-mode .badge-approved::before{background:#16a34a;box-shadow:none;}
body.light-mode .badge-needs_repair{background:#fee2e2;color:#b91c1c;border-color:#fecaca;}
body.light-mode .tab-label-pending{background:#e2e8f0;color:#475569;}
body.light-mode .tab-label-renewal{background:#dcfce7;color:#15803d;}
body.light-mode .tab-label-under{background:#fef9c3;color:#a16207;}
/* ── LIGHT MODE CHECKLIST BOX ── */
body.light-mode .bg-\[\#1e2430\]{background:#fff !important;border:1px solid #e2e8f0 !important;}

/* ── LIGHT MODE NOTIFY MODAL ── */
body.light-mode .bg-\[\#0d2e1a\]{background:#d1fae5 !important;}
body.light-mode .bg-\[\#13151a\]{background:#f1f5f9 !important;border-color:#e2e8f0 !important;}

/* ── MODALS ── */
.modal-overlay{display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.75);align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal-box{background:#23272f;border:1px solid #363b48;border-radius:12px;padding:24px;width:100%;max-width:380px;}
.modal-box-lg{background:#23272f;border:1px solid #363b48;border-radius:12px;padding:28px;width:100%;max-width:480px;}
.fi{background:#181c21;border:1px solid #363b48;border-radius:6px;padding:7px 10px;font-size:.82rem;color:#e5eaf2;width:100%;outline:none;margin-top:4px;box-sizing:border-box;}
.fi:focus{border-color:#3ec6ff;}
.force-light-text{color:#e5eaf2;}
/* ── APP BACKGROUND ── */
.app-body,.app-main{background:#1a2025;}
body.light-mode.app-body,
body.light-mode .app-main{background:#f1f5fa;}

/* ── LIGHT MODE ── */
body.light-mode{background:#f1f5fa;color:#222;}
body.light-mode #sidebar{background:#f7fafc;border-color:#e2e8f0;}
body.light-mode .nav-item{color:#64748b !important;}
body.light-mode .nav-item:hover,body.light-mode .nav-item.active{background:#e8edf5;color:#1e293b;}
body.light-mode .sb-logo-text{color:#1e293b;}
body.light-mode .sb-bottom{border-color:#e2e8f0;}
body.light-mode .theme-btn{background:#e8edf5;color:#64748b;}
body.light-mode .force-light-text{color:#1e293b !important;}
body.light-mode .stat-card{background:#fff;border-color:#e2e8f0;}
body.light-mode .stat-card:hover{background:#f8fafc;}
body.light-mode .tbl td{color:#374151;border-color:#e5e7eb;}
body.light-mode .tbl th{color:#64748b;border-color:#e5e7eb;}
body.light-mode .tbl tr:hover td{background:#f8fafc;}
body.light-mode .cl-table th{background:#f1f5f9;color:#475569;border-color:#e2e8f0;}
body.light-mode .cl-table td{border-color:#e2e8f0;color:#374151;}
body.light-mode .cl-table tr:nth-child(even) td{background:#f8fafc;}
body.light-mode .sig-block{background:#f8fafc;border-color:#e2e8f0;}
body.light-mode .sig-name{color:#1e293b;}
body.light-mode .modal-box,.modal-box-lg{background:#fff;border-color:#e2e8f0;}
body.light-mode .fi{background:#f8fafc;border-color:#cbd5e0;color:#1e293b;}
body.light-mode input,body.light-mode select,body.light-mode textarea{background:#f8fafc !important;color:#1e293b !important;border-color:#cbd5e1 !important;}
body.light-mode .btn-print{background:#e8edf5;color:#475569;border-color:#cbd5e1;}
body.light-mode .bg-\[\#23272f\]{background:#fff !important;border:1px solid #e2e8f0 !important;}

/* ── INSPECTION CONDITION SUMMARY ── */
.condition-summary{
  margin-top:12px;
  border:1px solid #363b48;
  border-radius:8px;
  padding:11px 12px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  background:#181c21;
}
.condition-summary-left{display:flex;align-items:center;gap:10px;}
.condition-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;background:#94a3b8;}
.condition-label{font-size:.68rem;color:#64748b;text-transform:uppercase;letter-spacing:.05em;font-weight:700;}
.condition-value{font-size:.82rem;font-weight:800;color:#e5eaf2;margin-top:1px;}
.condition-help{font-size:.69rem;color:#64748b;text-align:right;max-width:320px;line-height:1.35;}
.condition-serviceable{border-color:#166534;background:#0d3325;}
.condition-serviceable .condition-dot{background:#33b481;}
.condition-serviceable .condition-value{color:#33b481;}
.condition-repair{border-color:#92400e;background:#2a1f00;}
.condition-repair .condition-dot{background:#f59e0b;}
.condition-repair .condition-value{color:#f59e0b;}
.condition-replacement{border-color:#9a3412;background:#2b1608;}
.condition-replacement .condition-dot{background:#fb923c;}
.condition-replacement .condition-value{color:#fb923c;}
.condition-unserviceable{border-color:#991b1b;background:#2d0a0a;}
.condition-unserviceable .condition-dot{background:#fc8181;}
.condition-unserviceable .condition-value{color:#fc8181;}

.btn-save-under{
  background:#1e1040;
  color:#c4b5fd;
  border:1px solid #6d28d9;
  border-radius:7px;
  padding:9px 20px;
  font-size:.82rem;
  font-weight:700;
  cursor:pointer;
  display:flex;
  align-items:center;
  gap:6px;
}
.btn-save-under:hover{background:#2d1a5a;}
.btn-renew:disabled{opacity:.45;cursor:not-allowed;background:#1f2937;color:#94a3b8;border-color:#374151;}
body.light-mode .condition-summary{background:#f8fafc;border-color:#cbd5e1;}
body.light-mode .condition-serviceable{background:#ecfdf5;border-color:#a7f3d0;}
body.light-mode .condition-repair{background:#fffbeb;border-color:#fde68a;}
body.light-mode .condition-replacement{background:#fff7ed;border-color:#fdba74;}
body.light-mode .condition-unserviceable{background:#fef2f2;border-color:#fecaca;}
body.light-mode .condition-value{color:#1e293b;}
body.light-mode .condition-serviceable .condition-value{color:#047857;}
body.light-mode .condition-repair .condition-value{color:#b45309;}
body.light-mode .condition-replacement .condition-value{color:#c2410c;}
body.light-mode .condition-unserviceable .condition-value{color:#b91c1c;}
body.light-mode .btn-save-under{background:#ede9fe;color:#6d28d9;border-color:#c4b5fd;}

</style>
</head>
<body class="min-h-screen font-inter app-body">
<div class="flex min-h-screen">
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
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      <span class="nav-label">Dashboard</span>
    </a>
    <a href="<?php echo e(route('admin.personnel')); ?>" class="nav-item">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
      <span class="nav-label">List of Personnel</span>
    </a>
    <a href="<?php echo e(route('admin.inspection')); ?>" class="nav-item active">
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
  <main class="flex-1 p-7 overflow-y-auto app-main">

    <!-- Header -->
   <header class="flex flex-wrap justify-between mb-6 items-start gap-4">
     <div>
       <h1 class="text-2xl font-bold mb-1 force-light-text">Inspection / Renewal</h1>
       <p class="text-sm text-[#64748b] force-light-text">Review and inspect firearms and documents of personnel. After inspection, update the status accordingly.</p>
     </div>
     <div class="flex items-center gap-4 pt-1">
       <div class="relative" id="adminNotifWrapper">
         <button id="notificationBell" class="notification-bell text-cyan-400 focus:outline-none" aria-label="Notifications" type="button">
           <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11c0-3.074-1.64-5.64-5-5.996V5a2 2 0 10-4 0v.004C6.64 5.36 5 7.926 5 11v3.159c0 .538-.214 1.055-.595 1.436L3 17h5m7 0v1a3 3 0 01-6 0v-1m7 0H8" stroke-linecap="round" stroke-linejoin="round"/></svg>
           <span class="notification-badge" id="notificationBadge"></span>
         </button>
         <div id="adminNotifDropdown">
           <div class="notif-header"><span>Notifications</span><span class="notif-mark-read" id="adminMarkAllRead">Mark all read</span></div>
           <div class="notif-list" id="adminNotifList"><div class="notif-empty">Loading...</div></div>
           <div class="notif-footer" id="adminNotifFooter">Auto-refreshes every 30 seconds</div>
         </div>
       </div>
       <?php echo $__env->make('partials.account_dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
     </div>
   </header>


      <!-- ===== LIST VIEW ===== -->
      <div id="viewList">
        <!-- ── Stat Cards (filter tabs) ── -->
        <div class="grid grid-cols-3 gap-4 mb-6">

          <!-- Pending Inspection -->
<div class="stat-card" id="card-pending" onclick="setActiveTab('pending')">
  <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 icon-circle-pending">
    <svg fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
  </div>
  <div>
    <div class="text-xs font-bold uppercase tracking-wide" style="color:#94a3b8;">Pending Inspection</div>
    <div class="text-2xl font-black" id="statPending">—</div>
    <div class="text-xs text-[#64748b]">Awaiting inspection and review</div>
  </div>
  <span class="card-count" id="countPending">—</span>
</div>

          <!-- Ready for Renewal -->
          <div class="stat-card" id="card-renewal" onclick="setActiveTab('renewal')">
         <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 icon-circle-renewal">
               <svg fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </div>
            <div>
            <div class="text-xs font-bold uppercase tracking-wide" style="color:#22c55e;">Ready for Renewal</div>
              <div class="text-2xl font-black" id="statApproved">—</div>
              <div class="text-xs text-[#64748b]">For approval and renewal</div>
            </div>
            <span class="card-count" id="countRenewal">—</span>
          </div>

          <!-- Under Inspection -->
          <div class="stat-card" id="card-under" onclick="setActiveTab('under')">
         <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 icon-circle-under">
          <svg fill="none" stroke="#eab308" stroke-width="2" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
              <div class="text-xs font-bold uppercase tracking-wide" style="color:#eab308;">Under Inspection</div>
              <div class="text-2xl font-black" id="statUnder">—</div>
              <div class="text-xs text-[#64748b]">Awaiting inspection results</div>
            </div>
            <span class="card-count" id="countUnder">—</span>
          </div>

        </div>

        <!-- Search (applies to the active inspection tab) -->
        <div class="flex justify-end mb-4">
          <div class="relative w-full" style="max-width:280px;">
            <input id="searchInput" type="text" placeholder="Search by name / AFP serial..." oninput="filterTable()" class="bg-[#23272f] text-white border border-[#363b48] rounded px-3 py-1.5 text-sm w-full focus:outline-none focus:ring-2 focus:ring-[#3ec6ff] pr-9 force-light-text" />
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M21 21l-4.35-4.35M5 11a6 6 0 1112 0 6 6 0 01-12 0z"/></svg>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10">
          <div class="flex items-center justify-between px-5 py-3 border-b border-[#2a2f3a]">
            <span class="font-bold text-sm" id="tableTitle">
              Pending Inspection
              <span class="tab-label tab-label-pending" id="tableTitleBadge">Pending</span>
            </span>
            <div class="flex flex-wrap items-center justify-end gap-2 text-sm text-[#64748b]">
              <span>Filter:</span>
              <select id="rankFilter" class="bg-[#13151a] border border-[#2a2f3a] rounded px-2 py-1 text-[#e5eaf2] text-xs outline-none" onchange="filterTable()">
                <option value="">All Ranks</option>
              </select>
              <select id="pistolFilter" class="bg-[#13151a] border border-[#2a2f3a] rounded px-2 py-1 text-[#e5eaf2] text-xs outline-none" onchange="filterTable()">
                <option value="">All Pistol Types</option>
              </select>

              <span class="ml-1">Sort by:</span>
              <select id="sortSelect" class="bg-[#13151a] border border-[#2a2f3a] rounded px-2 py-1 text-[#e5eaf2] text-xs outline-none" onchange="filterTable()">
                <option value="newest">Date Registered (Newest)</option>
                <option value="oldest">Date Registered (Oldest)</option>
                <option value="name">Last Name A-Z</option>
                <option value="rank">Rank A-Z</option>
              </select>
            </div>
          </div>
          <table class="tbl w-full">
            <thead>
              <tr><th>Name</th><th>AFP Serial #</th><th>Pistol</th><th>Date Registered</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody id="inspTable">
              <tr><td colspan="6" class="text-center py-10 text-[#64748b]">Loading...</td></tr>
            </tbody>
          </table>
          <div class="flex items-center justify-between px-5 py-3 border-t border-[#2a2f3a]">
            <span class="text-xs text-[#64748b]" id="pagInfo">Showing 0 entries</span>
            <div class="flex gap-1" id="pagBtns"></div>
          </div>
        </div>
      </div>

      <!-- ===== CHECKLIST VIEW ===== -->
      <div id="viewChecklist" style="display:none;">

        <button onclick="showList()" class="flex items-center gap-2 text-[#d4a017] text-sm font-semibold mb-5 hover:underline">
          <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          Back to List
        </button>

        <!-- Personnel Info -->
        <div class="bg-[#23272f] rounded-lg p-5 mb-4 shadow shadow-black/10">
          <div class="flex items-center gap-2 mb-3">
            <svg fill="none" stroke="#d4a017" stroke-width="2" viewBox="0 0 24 24" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span class="font-bold text-sm force-light-text">Inspection Checklist</span>
          </div>
          <div class="grid grid-cols-3 gap-x-8 gap-y-2 text-sm">
            <div><span class="text-[#64748b] text-xs block">Personnel Name:</span><span class="font-semibold" id="cl_name">—</span></div>
            <div><span class="text-[#64748b] text-xs block">Nomenclature:</span><span class="font-semibold" id="cl_nomen">—</span></div>
            <div><span class="text-[#64748b] text-xs block">Unit:</span><span class="font-semibold" id="cl_unit">—</span></div>
            <div><span class="text-[#64748b] text-xs block">Made:</span><span class="font-semibold" id="cl_made">—</span></div>
            <div><span class="text-[#64748b] text-xs block">SN:</span><span class="font-semibold font-mono" id="cl_sn">—</span></div>
            <div><span class="text-[#64748b] text-xs block">Date:</span><span class="font-semibold" id="cl_date">—</span></div>
          </div>
        </div>

        <!-- Full Checklist -->
        <div class="bg-[#1e2430] border border-[#2a2f3a] rounded-xl p-5 mb-4">
          <div class="flex items-center gap-2 mb-2">
            <svg fill="none" stroke="#d4a017" stroke-width="2" viewBox="0 0 24 24" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-bold text-sm">Inspection Checklist</span>
          </div>
          <div class="text-xs text-[#64748b] mb-3 pb-2 border-b border-[#2a2f3a] flex flex-wrap gap-3">
            <span>Legend:</span>
            <span><span class="text-[#34d399] font-bold">(✓)</span> Serviceable</span>
            <span>(XX) Repair</span><span>(XXX) Replace</span><span>(U) Unserviceable</span>
            <span>(N/A) Not Applicable</span><span>(O) Missing</span><span>(D) Damaged</span>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <table class="cl-table">
              <thead><tr><th style="width:24px;">#</th><th style="text-align:left;padding-left:6px;">Items to Inspect</th><th>✓</th><th>XX</th><th>XXX</th><th>U</th><th>N/A</th><th>O</th><th>D</th></tr></thead>
              <tbody id="checklistLeft"></tbody>
            </table>
            <table class="cl-table">
              <thead><tr><th style="width:24px;">#</th><th style="text-align:left;padding-left:6px;">Items to Inspect</th><th>✓</th><th>XX</th><th>XXX</th><th>U</th><th>N/A</th><th>O</th><th>D</th></tr></thead>
              <tbody id="checklistRight"></tbody>
            </table>
          </div>
          <div class="mt-4">
            <label class="text-xs text-[#64748b] font-semibold block mb-1">Remarks</label>
            <textarea id="cl_remarks" rows="2" class="fi" style="resize:vertical;" placeholder="e.g. Firearm is Serviceable."></textarea>
          </div>

          <div id="inspectionConditionBox" class="condition-summary condition-serviceable">
            <div class="condition-summary-left">
              <span class="condition-dot"></span>
              <div>
                <div class="condition-label">Current Firearm Condition</div>
                <div class="condition-value" id="inspectionConditionValue">Serviceable</div>
              </div>
            </div>
            <div class="condition-help" id="inspectionConditionHelp">
              All inspected parts are serviceable or not applicable. The firearm may be marked for renewal.
            </div>
          </div>
        </div>

        <!-- Signatories -->
        <div class="bg-[#1e2430] border border-[#2a2f3a] rounded-xl p-5 mb-4">
          <div class="grid grid-cols-4 gap-3">
            <div class="sig-block">
              <div class="text-xs text-[#64748b] font-semibold uppercase">Inspected By:</div>
              <img id="sig_insp_img" src="" class="sig-img hidden" alt="">
              <div class="sig-name text-xs mt-2" id="sig_insp_name">—</div>
              <div class="sig-pos" id="sig_insp_rank"></div>
              <div class="sig-pos" id="sig_insp_pos"></div>
              <button onclick="openEditSig('insp')" class="btn-edit">Edit info</button>
            </div>
            <div class="sig-block">
              <div class="text-xs text-[#64748b] font-semibold uppercase">Witnessed By:</div>
              <img id="sig_wit_img" src="" class="sig-img hidden" alt="">
              <div class="sig-name text-xs mt-2" id="sig_wit_name">—</div>
              <div class="sig-pos" id="sig_wit_rank"></div>
              <div class="sig-pos" id="sig_wit_pos"></div>
              <button onclick="openEditSig('wit')" class="btn-edit">Edit info</button>
            </div>
            <div class="sig-block">
              <div class="text-xs text-[#64748b] font-semibold uppercase">Approved By:</div>
              <img id="sig_app_img" src="" class="sig-img hidden" alt="">
              <div class="sig-name text-xs mt-2" id="sig_app_name">—</div>
              <div class="sig-pos" id="sig_app_rank"></div>
              <div class="sig-pos" id="sig_app_pos"></div>
              <button onclick="openEditSig('app')" class="btn-edit">Edit info</button>
            </div>
            <div class="sig-block">
              <div class="text-xs text-[#64748b] font-semibold uppercase">Noted By:</div>
              <img id="sig_not_img" src="" class="sig-img hidden" alt="">
              <div class="sig-name text-xs mt-2" id="sig_not_name">—</div>
              <div class="sig-pos" id="sig_not_rank"></div>
              <div class="sig-pos" id="sig_not_pos"></div>
              <button onclick="openEditSig('not')" class="btn-edit">Edit info</button>
            </div>
          </div>
        </div>

        <!-- Error / Success -->
        <div id="cl_error" class="hidden text-red-400 text-sm mb-3 p-3 rounded-lg bg-red-400/10 border border-red-400/30"></div>
        <div id="cl_success" class="hidden text-green-400 text-sm mb-3 p-3 rounded-lg bg-green-400/10 border border-green-400/30"></div>

      <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3">
          <button onclick="saveInspection('under')" class="btn-save-under">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="14" height="14"><path d="M5 13l4 4L19 7"/></svg>
            Save Inspection
          </button>

          <button id="markRenewalBtn" onclick="saveInspection('approved')" class="btn-renew">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="14" height="14" style="display:inline;vertical-align:middle;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Mark For Renewal
          </button>
          <button onclick="printReport()" class="btn-print">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Report
          </button>
        </div>

      </div><!-- end checklist view -->
    </main>
  </div>

  <!-- Edit Signatory Modal -->
  <div id="editSigModal" class="modal-overlay">
    <div class="modal-box">
      <h3 class="font-bold text-sm text-[#e5eaf2] mb-4" id="editSigTitle">Edit Signatory</h3>
      <input type="hidden" id="editSigKey">
      <div class="mb-3"><label class="text-xs text-[#64748b] font-semibold">Full Name</label><input type="text" id="editSigName" class="fi" placeholder="Full name"></div>
      <div class="mb-3"><label class="text-xs text-[#64748b] font-semibold">Rank</label><input type="text" id="editSigRank" class="fi" placeholder="e.g. Cpl (OS) PA"></div>
      <div class="mb-3"><label class="text-xs text-[#64748b] font-semibold">Position</label><input type="text" id="editSigPos" class="fi" placeholder="e.g. Armaments NCO"></div>
      <div class="mb-4"><label class="text-xs text-[#64748b] font-semibold">Signature Image (optional)</label><input type="file" id="editSigFile" accept="image/*" class="fi" style="padding:4px;"></div>
      <div class="flex gap-3">
        <button onclick="saveEditSig()" class="btn-save flex-1 justify-center">Save</button>
        <button onclick="closeEditSig()" class="btn-print flex-1 justify-center">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Notify Staff Modal -->
  <div id="notifyModal" class="modal-overlay">
    <div class="modal-box-lg">
      <div class="flex items-center gap-3 mb-5">
        <div class="w-9 h-9 rounded-full bg-[#0d2e1a] flex items-center justify-center flex-shrink-0">
          <svg fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </div>
        <div>
         <h3 class="font-bold text-[#e5eaf2] force-light-text">Notify Staff for ICS Renewal</h3>
          <p class="text-xs text-[#64748b]" id="notifyPersonnelName">—</p>
        </div>
      </div>

      <div class="bg-[#13151a] rounded-lg border border-[#2a2f3a] p-4 mb-4">
        <div class="text-xs font-semibold text-[#64748b] uppercase mb-2 tracking-wide force-light-text">ICS Details</div>
        <div class="grid grid-cols-2 gap-3 text-sm">
         <div><span class="text-[#64748b] text-xs block">AFP Serial #</span><span class="font-mono font-semibold force-light-text" id="notifySerial">—</span></div>
        <div><span class="text-[#64748b] text-xs block">Pistol Type</span><span class="font-semibold force-light-text" id="notifyPistol">—</span></div>
        </div>
      </div>

      <div class="mb-4">
        <label class="text-xs text-[#64748b] font-semibold block mb-1 force-light-text">Notification Message</label>
        <textarea id="notifyMessage" rows="4" class="fi" style="resize:vertical;" placeholder="Type your message to the staff..."></textarea>
      </div>

      <div id="notifyError" class="hidden text-red-400 text-xs mb-3 p-2 rounded bg-red-400/10 border border-red-400/20"></div>
      <div id="notifySuccess" class="hidden text-green-400 text-xs mb-3 p-2 rounded bg-green-400/10 border border-green-400/20"></div>

      <div class="flex gap-3">
        <button onclick="sendNotify()" class="btn-renew flex-1 text-center">
          Send for Renewal
        </button>
        <button onclick="closeNotifyModal()" class="btn-print flex-1 justify-center">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // Admin notifications
    const notifBell = document.getElementById('notificationBell');
    const notifBadge = document.getElementById('notificationBadge');
    const notifDropdown = document.getElementById('adminNotifDropdown');
    const adminNotifList = document.getElementById('adminNotifList');
    const adminNotifFooter = document.getElementById('adminNotifFooter');
    const adminMarkAllRead = document.getElementById('adminMarkAllRead');
    const ADMIN_NOTIF_URL = "<?php echo e(route('admin.notifications')); ?>";
    const ADMIN_NOTIF_READ_URL = "<?php echo e(route('admin.notifications.read')); ?>";

    function notifTimeAgo(dateStr) {
      const diff = Math.floor((new Date() - new Date(dateStr)) / 1000);
      if (diff < 60) return 'Just now';
      if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
      if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
      return new Date(dateStr).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
    }

    async function loadAdminNotifications() {
      try {
        const response = await fetch(ADMIN_NOTIF_URL, {headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}});
        const json = await response.json();
        if (!json.success) return;
        const count = json.unreadCount || 0;
        notifBell.classList.toggle('has-unread', count > 0);
        notifBadge.style.display = count > 0 ? 'flex' : 'none';
        notifBadge.textContent = count > 99 ? '99+' : String(count || '');
        adminNotifFooter.textContent = count ? `${count} unread notification${count > 1 ? 's' : ''}` : 'All caught up!';
        adminNotifList.innerHTML = json.notifications?.length
          ? json.notifications.map(n => `<div class="notif-item ${!n.read?'unread':''}"><div class="notif-content"><div class="notif-title">${escapeHtml(n.title)}</div><div class="notif-message">${escapeHtml(n.message)}</div><div class="notif-time">${notifTimeAgo(n.createdAt)}</div></div>${!n.read?'<div class="notif-dot"></div>':''}</div>`).join('')
          : '<div class="notif-empty">No notifications yet.</div>';
      } catch (error) {
        adminNotifList.innerHTML = '<div class="notif-empty">Failed to load notifications.</div>';
      }
    }

    notifBell.addEventListener('click', event => {
      event.stopPropagation();
      notifDropdown.classList.toggle('open');
    });
    document.addEventListener('click', event => {
      if (!document.getElementById('adminNotifWrapper').contains(event.target)) notifDropdown.classList.remove('open');
    });
    adminMarkAllRead.addEventListener('click', async event => {
      event.stopPropagation();
      await fetch(ADMIN_NOTIF_READ_URL, {method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}});
      await loadAdminNotifications();
    });
    loadAdminNotifications();
    setInterval(loadAdminNotifications, 30000);

    const PARTS = [
      'barrel','slide','recoil_spring_assembly','firing_pin','firing_pin_safety',
      'extractor','rear_sight','front_sight','frame','magazine','magazine_catch',
      'magazine_catch_spring','trigger','trigger_spring','trigger_bar','slide_stop_lever',
      'trigger_pin','trigger_mechanism_housing','trigger_housing_pin','locking_block',
      'locking_block_pin','slide_lock','slide_lock_spring','connector','guide_rod'
    ];
    const PART_LABELS = [
      'Barrel','Slide','Recoil Spring Assembly','Firing Pin / Striker','Firing Pin Safety',
      'Extractor','Rear Sight','Front Sight','Frame','Magazine','Magazine Catch',
      'Magazine Catch Spring','Trigger','Trigger Spring','Trigger Bar','Slide Stop Lever',
      'Trigger Pin','Trigger Housing / Mechanism Housing','Trigger Housing Pin','Locking Block',
      'Locking Block Pin','Slide Lock','Slide Lock Spring','Connector','Guide Rod'
    ];

    let allPersonnel = [], currentPage = 1, currentItemNumber = null, activeTab = 'pending';
    let currentInspectionStatus = 'under';
    let notifyItem = null;
    const PER_PAGE = 10;
    let sigData = {
      insp:{name:'',rank:'',pos:'',img:''},
      wit:{name:'',rank:'',pos:'',img:''},
      app:{name:'',rank:'',pos:'',img:''},
      not:{name:'',rank:'',pos:'',img:''}
    };

    // ── Tab switcher ──
    function setActiveTab(tab) {
      activeTab = tab;
      currentPage = 1;

      ['pending','renewal','under'].forEach(t => {
        const c = document.getElementById('card-'+t);
        c.classList.remove('active-pending','active-renewal','active-under');
      });
      document.getElementById('card-'+tab).classList.add('active-'+tab);

      const titleMap = {
        pending: { text:'Pending Inspection', badge:'Pending', cls:'tab-label-pending' },
        renewal: { text:'Ready for Renewal',  badge:'For Renewal', cls:'tab-label-renewal' },
        under:   { text:'Under Inspection',   badge:'Under Inspection', cls:'tab-label-under' },
      };
      const t = titleMap[tab];
      document.getElementById('tableTitle').innerHTML =
        `${t.text} <span class="tab-label ${t.cls}">${t.badge}</span>`;

      renderTable();
    }

    function tabStatuses() {
      if (activeTab === 'pending') return ['pending', null, undefined, ''];
      if (activeTab === 'renewal') return ['approved'];
      if (activeTab === 'under')   return ['under'];
      return [];
    }

    function actionBtn(p) {
      if (activeTab === 'pending') {
        return `<button onclick="openInspect(${p.itemNumber})" class="btn-inspect">Inspect</button>`;
      }
      if (activeTab === 'renewal') {
        return `<button type="button" onclick="openNotifyModal(${JSON.stringify(p).replace(/"/g,'&quot;')})" class="btn-notify" aria-label="Notify staff about renewal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0h6Z"/></svg>
          <span>Notify Staff</span>
        </button>`;
      }
      if (activeTab === 'under') {
        return `<button onclick="openInspect(${p.itemNumber})" class="btn-continue">Continue Inspection</button>`;
      }
      return '';
    }

    async function loadData() {
      const res  = await fetch('/admin/inspection-data');
      const data = await res.json();
      if (!data.success) return;
      allPersonnel = data.data;
      populateFilterOptions();
      document.getElementById('statPending').textContent  = data.pending;
      document.getElementById('statUnder').textContent    = data.under;
      document.getElementById('statApproved').textContent = data.approved;
      document.getElementById('countPending').textContent  = data.pending;
      document.getElementById('countUnder').textContent    = data.under;
      document.getElementById('countRenewal').textContent  = data.approved;
      renderTable();
    }

    function populateFilterOptions() {
      const rankSelect = document.getElementById('rankFilter');
      const pistolSelect = document.getElementById('pistolFilter');

      if (!rankSelect || !pistolSelect) return;

      const selectedRank = rankSelect.value;
      const selectedPistol = pistolSelect.value;

      const ranks = [...new Set(
        allPersonnel
          .map(p => (p.rank || '').trim())
          .filter(Boolean)
      )].sort((a, b) => a.localeCompare(b));

      const pistols = [...new Set(
        allPersonnel
          .map(p => (p.pistolType || '').trim())
          .filter(Boolean)
      )].sort((a, b) => a.localeCompare(b));

      rankSelect.innerHTML =
        '<option value="">All Ranks</option>' +
        ranks.map(rank => `<option value="${escapeHtml(rank)}">${escapeHtml(rank)}</option>`).join('');

      pistolSelect.innerHTML =
        '<option value="">All Pistol Types</option>' +
        pistols.map(type => `<option value="${escapeHtml(type)}">${escapeHtml(type)}</option>`).join('');

      if (ranks.includes(selectedRank)) rankSelect.value = selectedRank;
      if (pistols.includes(selectedPistol)) pistolSelect.value = selectedPistol;
    }

    function escapeHtml(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function filterTable() { currentPage = 1; renderTable(); }

    function renderTable() {
      const sort   = document.getElementById('sortSelect').value;
      const search = (document.getElementById('searchInput').value || '').trim().toLowerCase();
      const rankFilter = document.getElementById('rankFilter')?.value || '';
      const pistolFilter = document.getElementById('pistolFilter')?.value || '';

      const validStatuses = tabStatuses();
      let rows = allPersonnel.filter(p => {
        const s = p.inspectionStatus || '';
        if (!validStatuses.includes(s)) return false;
        if (s === '' || s === 'pending' || s == null) {
          return p.icsStatus === 'under';
        }
        return true;
      });

      if (rankFilter) {
        rows = rows.filter(p => (p.rank || '').trim() === rankFilter);
      }

      if (pistolFilter) {
        rows = rows.filter(p => (p.pistolType || '').trim() === pistolFilter);
      }

      if (search) {
        rows = rows.filter(p => {
          const searchable = [
            p.lastName,
            p.rank,
            p.firstName,
            p.middleName,
            p.afpSerialNumber,
            p.pistolType,
            p.unit,
            p.dateRegistered,
            p.inspectionStatus
          ]
            .filter(v => v !== null && v !== undefined)
            .join(' ')
            .toLowerCase();

          return searchable.includes(search);
        });
      }

      const safeDate = value => {
        const time = Date.parse(value || '');
        return Number.isNaN(time) ? 0 : time;
      };

      if (sort === 'oldest') rows.sort((a,b) => safeDate(a.dateRegistered) - safeDate(b.dateRegistered));
      if (sort === 'newest') rows.sort((a,b) => safeDate(b.dateRegistered) - safeDate(a.dateRegistered));
      if (sort === 'name')   rows.sort((a,b) => (a.lastName || '').localeCompare(b.lastName || ''));
      if (sort === 'rank')   rows.sort((a,b) => (a.rank || '').localeCompare(b.rank || ''));

      const total = rows.length;
      const pages = Math.max(1, Math.ceil(total/PER_PAGE));
      const start = (currentPage-1)*PER_PAGE;
      const slice = rows.slice(start, start+PER_PAGE);

      const bc = {
        pending:'badge-pending',
        under:'badge-under',
        approved:'badge-approved',
        needs_repair:'badge-needs_repair'
      };
      const bl = {
        pending:'Pending',
        under:'Under Inspection',
        approved:'For Renewal',
        needs_repair:'Needs Repair'
      };

      document.getElementById('inspTable').innerHTML = slice.length ? slice.map(p=>`
        <tr>
          <td class="font-semibold text-[#e5eaf2]">
            ${escapeHtml(p.lastName || '')}
            <span class="ml-1 text-xs font-bold text-[#94a3b8]">${escapeHtml(p.rank || '')}</span>,
            ${escapeHtml(p.firstName || '')}${p.middleName ? ' ' + escapeHtml(p.middleName.charAt(0)) + '.' : ''}
          </td>
          <td class="font-mono text-xs">${p.afpSerialNumber||'—'}</td>
          <td>${p.pistolType||'—'}</td>
          <td class="text-xs text-[#94a3b8]">${p.dateRegistered||'—'}</td>
          <td><span class="badge ${bc[p.inspectionStatus]||'badge-pending'}">${bl[p.inspectionStatus]||'Pending'}</span></td>
          <td>${actionBtn(p)}</td>
        </tr>`).join('') :
        `<tr><td colspan="6" class="text-center py-10 text-[#64748b]">No personnel found for this tab.</td></tr>`;

      document.getElementById('pagInfo').textContent =
        `Showing ${total ? start+1 : 0} to ${Math.min(start+PER_PAGE,total)} of ${total} entries`;

      const pd = document.getElementById('pagBtns');
      pd.innerHTML = '';
      if (pages > 1) {
        [['‹',()=>{if(currentPage>1){currentPage--;renderTable();}}],
         ...Array.from({length:pages},(_,i)=>[i+1,()=>{currentPage=i+1;renderTable();}]),
         ['›',()=>{if(currentPage<pages){currentPage++;renderTable();}}]
        ].forEach(([lbl,fn])=>{
          const b=document.createElement('button');
          b.className='pg-btn'+(lbl===currentPage?' active':'');
          b.textContent=lbl; b.onclick=fn;
          pd.appendChild(b);
        });
      }
    }


    // ── Derive firearm condition from the current checklist ──
    // Priority:
    // Unserviceable > For Replacement > For Repair > Serviceable
    function getInspectionCondition() {
      let hasRepair = false;
      let hasReplacement = false;
      let hasUnserviceable = false;

      PARTS.forEach(key => {
        const checked = document.querySelector(`input[name="${key}"]:checked`);
        const value = checked ? checked.value : 'serviceable';

        if (value === 'unserviceable') {
          hasUnserviceable = true;
        } else if (value === 'replace') {
          hasReplacement = true;
        } else if (['repair', 'damaged', 'missing'].includes(value)) {
          hasRepair = true;
        }
      });

      if (hasUnserviceable) return 'Unserviceable';
      if (hasReplacement) return 'For Replacement';
      if (hasRepair) return 'For Repair';
      return 'Serviceable';
    }

    function refreshInspectionConditionUI() {
      const box = document.getElementById('inspectionConditionBox');
      const valueEl = document.getElementById('inspectionConditionValue');
      const helpEl = document.getElementById('inspectionConditionHelp');
      const renewalBtn = document.getElementById('markRenewalBtn');

      if (!box || !valueEl || !helpEl) return;

      const condition = getInspectionCondition();

      box.classList.remove(
        'condition-serviceable',
        'condition-repair',
        'condition-replacement',
        'condition-unserviceable'
      );

      let cls = 'condition-serviceable';
      let help = 'All inspected parts are serviceable or not applicable. The firearm may be marked for renewal.';

      if (condition === 'For Repair') {
        cls = 'condition-repair';
        help = 'The firearm remains Under Inspection. Repair/correct the finding, then inspect the firearm again.';
      } else if (condition === 'For Replacement') {
        cls = 'condition-replacement';
        help = 'A component is marked for replacement. Keep the firearm Under Inspection until corrected and re-inspected.';
      } else if (condition === 'Unserviceable') {
        cls = 'condition-unserviceable';
        help = 'The firearm cannot be marked for renewal while an item is unserviceable. Correct the condition and re-inspect.';
      }

      box.classList.add(cls);
      valueEl.textContent = condition;
      helpEl.textContent = help;

      if (renewalBtn) {
        renewalBtn.disabled = condition !== 'Serviceable';
        renewalBtn.title = condition === 'Serviceable'
          ? 'Mark this firearm for renewal'
          : `Cannot mark for renewal while condition is ${condition}.`;
      }

      return condition;
    }

    // Checklist rows are generated dynamically, so listen at document level.
    document.addEventListener('change', function (event) {
      if (event.target && event.target.classList.contains('check-radio')) {
        refreshInspectionConditionUI();
      }
    });

    async function openInspect(itemNumber) {
      currentItemNumber = itemNumber;

      const res  = await fetch(`/admin/inspection/${itemNumber}/detail`);
      const data = await res.json();
      if (!data.success) return;
      const p=data.personnel, ins=data.inspection, ics=data.ics||{};
      const activeParts = Array.isArray(data.checklistParts) ? data.checklistParts : PARTS;

      document.getElementById('cl_name').textContent  = `${p.lastName} ${p.rank}, ${p.firstName}`;
      document.getElementById('cl_unit').textContent  = p.unit||'—';
      document.getElementById('cl_nomen').textContent = `Pistol 9mm, ${p.pistolType||'Glock 17'}`;
      document.getElementById('cl_made').textContent  = p.pistolType||'Glock 17';
      document.getElementById('cl_sn').textContent    = p.afpSerialNumber||'—';
      document.getElementById('cl_date').textContent  = new Date().toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});

      const opts = ['serviceable','repair','replace','unserviceable','na','missing','damaged'];
      function buildRows(parts, startIdx) {
        return parts.map((key,i)=>{
          const val = ins?(ins[key]||'serviceable'):'serviceable';
          const labelIndex = PARTS.indexOf(key);
          return `<tr>
            <td class="text-center text-[#64748b] text-xs">${startIdx+i+1}.</td>
            <td style="padding-left:6px;">${PART_LABELS[labelIndex]}</td>
            ${opts.map(o=>`<td class="text-center"><input type="radio" name="${key}" value="${o}" class="check-radio" ${val===o?'checked':''}></td>`).join('')}
          </tr>`;
        }).join('');
      }
      const splitAt = Math.ceil(activeParts.length / 2);
      document.getElementById('checklistLeft').innerHTML  = buildRows(activeParts.slice(0,splitAt),0);
      document.getElementById('checklistRight').innerHTML = buildRows(activeParts.slice(splitAt),splitAt);
      document.getElementById('cl_remarks').value = ins?.remarks||'';
      currentInspectionStatus = ins?.status || 'under';

      // Immediately show the derived physical condition and disable
      // "Mark For Renewal" whenever the checklist contains a defect.
      refreshInspectionConditionUI();

      const defaultSignatories = {
        insp: { name:'Rennan F. Maglasang Jr', rank:'Cpl (OS) PA',  pos:'Armaments NCO', img:<?php echo json_encode(asset('images/maglasang.png'), 15, 512) ?> },
        wit:  { name:'Marcelito H. Anino',     rank:'MAJ (QMS) PA', pos:'901BDE, 9ID, PA', img:<?php echo json_encode(asset('images/anino.png'), 15, 512) ?> },
        app:  { name:'Wenlie B. Enriola',      rank:'CPT (OS) PA',  pos:'CO, Maintenance Coy', img:<?php echo json_encode(asset('images/enriola.png'), 15, 512) ?> },
        not:  { name:'Darrell P. Mariano',     rank:'LTC OS (GSC) PA', pos:'CO, 10FSSU, SPTCOM, PA', img:<?php echo json_encode(asset('images/mariano.png'), 15, 512) ?> },
      };

      sigData = {
        insp:{name:ins?.inspectedByName||defaultSignatories.insp.name,rank:ins?.inspectedByRank||defaultSignatories.insp.rank,pos:ins?.inspectedByPosition||defaultSignatories.insp.pos,img:ins?.inspectedBySig||defaultSignatories.insp.img},
        wit: {name:ins?.witnessedByName||defaultSignatories.wit.name,rank:ins?.witnessedByRank||defaultSignatories.wit.rank,pos:ins?.witnessedByPosition||defaultSignatories.wit.pos,img:ins?.witnessedBySig||defaultSignatories.wit.img},
        app: {name:ins?.approvedByName||defaultSignatories.app.name,rank:ins?.approvedByRank||defaultSignatories.app.rank,pos:ins?.approvedByPosition||defaultSignatories.app.pos,img:ins?.approvedBySig||defaultSignatories.app.img},
        not: {name:ins?.notedByName||defaultSignatories.not.name,rank:ins?.notedByRank||defaultSignatories.not.rank,pos:ins?.notedByPosition||defaultSignatories.not.pos,img:ins?.notedBySig||defaultSignatories.not.img},
      };
      renderSigs();

      document.getElementById('viewList').style.display      = 'none';
      document.getElementById('viewChecklist').style.display = 'block';
      window.scrollTo(0,0);
    }

    function renderSigs() {
      ['insp','wit','app','not'].forEach(k=>{
        document.getElementById('sig_'+k+'_name').textContent = sigData[k].name||'—';
        document.getElementById('sig_'+k+'_rank').textContent = sigData[k].rank||'';
        document.getElementById('sig_'+k+'_pos').textContent  = sigData[k].pos||'';
        const img=document.getElementById('sig_'+k+'_img');
        if(sigData[k].img){img.src=sigData[k].img;img.classList.remove('hidden');}
        else img.classList.add('hidden');
      });
    }

    function showList() {
      document.getElementById('viewChecklist').style.display='none';
      document.getElementById('viewList').style.display='block';
      window.scrollTo(0,0);
    }

    async function saveInspection(status) {
      const errEl=document.getElementById('cl_error'), sucEl=document.getElementById('cl_success');
      errEl.classList.add('hidden'); sucEl.classList.add('hidden');

      const condition = refreshInspectionConditionUI();

      // UI guard. The backend also enforces this rule.
      if (status === 'approved' && condition !== 'Serviceable') {
        errEl.textContent = `Cannot mark for renewal. Current firearm condition: ${condition}. Save the inspection and perform re-inspection after the firearm is corrected.`;
        errEl.classList.remove('hidden');
        return;
      }

      const body={
        itemNumber:currentItemNumber, status,
        remarks:document.getElementById('cl_remarks').value,
        inspectedByName:sigData.insp.name, inspectedByRank:sigData.insp.rank, inspectedByPosition:sigData.insp.pos,
        inspectedBySig:sigData.insp.img,
        witnessedByName:sigData.wit.name,  witnessedByRank:sigData.wit.rank,  witnessedByPosition:sigData.wit.pos,
        witnessedBySig:sigData.wit.img,
        approvedByName:sigData.app.name,   approvedByRank:sigData.app.rank,   approvedByPosition:sigData.app.pos,
        approvedBySig:sigData.app.img,
        notedByName:sigData.not.name,      notedByRank:sigData.not.rank,      notedByPosition:sigData.not.pos,
        notedBySig:sigData.not.img,
      };
      document.querySelectorAll('#checklistLeft input:checked, #checklistRight input:checked').forEach(c => { body[c.name] = c.value; });
      const res  = await fetch('/admin/inspection/save',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},body:JSON.stringify(body)});
      const data = await res.json();
      if (data.success) {
        const savedCondition = data.rpcspRemark || condition || 'Serviceable';

        let message = 'Inspection saved ✓';
        if (status === 'approved') {
          message = 'Inspection passed and marked for renewal ✓';
        } else if (savedCondition === 'For Repair') {
          message = 'Saved under inspection — firearm is For Repair ✓';
        } else if (savedCondition === 'For Replacement') {
          message = 'Saved under inspection — component replacement required ✓';
        } else if (savedCondition === 'Unserviceable') {
          message = 'Saved under inspection — firearm is Unserviceable ✓';
        } else {
          message = 'Inspection saved under inspection ✓';
        }

        sucEl.textContent = message;
        sucEl.classList.remove('hidden');

        // Backend keeps repair/replacement/unserviceable cases in "under".
        currentInspectionStatus = data.status || status;

        setTimeout(()=>{sucEl.classList.add('hidden'); loadData();},2000);
      } else {
        errEl.textContent=data.error||'Failed.'; errEl.classList.remove('hidden');
      }
    }

    // ── FIXED: opens the print tab synchronously (before the save request) so the
    //           browser's popup blocker doesn't kill it, then navigates that tab to
    //           the print URL (with a cache-busting param) once the save completes.
    //           Shows the REAL server error instead of a generic message. ──
    async function printReport() {
      if (!currentItemNumber) return;
      const errEl = document.getElementById('cl_error');
      errEl.classList.add('hidden');

      // Open the tab RIGHT AWAY, synchronously, so the popup blocker allows it.
      // We'll navigate it to the real print URL once saving finishes.
      const printWindow = window.open('', '_blank');
      if (printWindow) {
        printWindow.document.write(
          '<p style="font-family:sans-serif;padding:20px;">Saving inspection data, please wait…</p>'
        );
      }

      const printCondition = refreshInspectionConditionUI();
      const safePrintStatus =
        (currentInspectionStatus === 'approved' && printCondition !== 'Serviceable')
          ? 'under'
          : (currentInspectionStatus || 'under');

      const body = {
        itemNumber: currentItemNumber,
        status: safePrintStatus,
        remarks: document.getElementById('cl_remarks').value,
        inspectedByName: sigData.insp.name, inspectedByRank: sigData.insp.rank, inspectedByPosition: sigData.insp.pos,
        inspectedBySig: sigData.insp.img,
        witnessedByName: sigData.wit.name,  witnessedByRank: sigData.wit.rank,  witnessedByPosition: sigData.wit.pos,
        witnessedBySig: sigData.wit.img,
        approvedByName: sigData.app.name,   approvedByRank: sigData.app.rank,   approvedByPosition: sigData.app.pos,
        approvedBySig: sigData.app.img,
        notedByName: sigData.not.name,      notedByRank: sigData.not.rank,      notedByPosition: sigData.not.pos,
        notedBySig: sigData.not.img,
      };
      document.querySelectorAll('#checklistLeft input:checked, #checklistRight input:checked').forEach(c => { body[c.name] = c.value; });

      try {
        const res = await fetch('/admin/inspection/save', {
          method: 'POST',
          headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
          body: JSON.stringify(body)
        });

        const rawText = await res.text();
        let data;
        try {
          data = JSON.parse(rawText);
        } catch (parseErr) {
          console.error('Non-JSON response from /admin/inspection/save:', res.status, rawText);
          errEl.textContent = `Server error (status ${res.status}). Open the browser console for details.`;
          errEl.classList.remove('hidden');
          if (printWindow) printWindow.close();
          return;
        }

        if (!data.success) {
          console.error('Save failed:', data);
          errEl.textContent = data.error || 'Could not save before printing.';
          errEl.classList.remove('hidden');
          if (printWindow) printWindow.close();
          return;
        }

        // Navigate the tab we already opened, with a cache-buster so it can't show a stale copy.
        const printUrl = `/admin/inspection/${currentItemNumber}/print?_=${Date.now()}`;
        if (printWindow) {
          printWindow.location.href = printUrl;
        } else {
          // Fallback in case the browser blocked even the synchronous open
          window.open(printUrl, '_blank');
        }

      } catch (e) {
        console.error('Fetch failed on printReport:', e);
        errEl.textContent = 'Network error: ' + e.message;
        errEl.classList.remove('hidden');
        if (printWindow) printWindow.close();
      }
    }

    // ── Notify Staff Modal ──
    function openNotifyModal(p) {
      notifyItem = p;
      document.getElementById('notifyPersonnelName').textContent =
        `${p.lastName} ${p.rank}, ${p.firstName}${p.middleName?' '+p.middleName.charAt(0)+'.':''}`;
      document.getElementById('notifySerial').textContent = p.afpSerialNumber || '—';
      document.getElementById('notifyPistol').textContent = p.pistolType || '—';
      document.getElementById('notifyMessage').value =
        `Please process the ICS renewal for ${p.lastName} ${p.rank}, ${p.firstName} (AFP Serial: ${p.afpSerialNumber||'N/A'}). Firearm has been cleared for renewal.`;
      document.getElementById('notifyError').classList.add('hidden');
      document.getElementById('notifySuccess').classList.add('hidden');
      document.getElementById('notifyModal').classList.add('open');
    }
    function closeNotifyModal() {
      document.getElementById('notifyModal').classList.remove('open');
      notifyItem = null;
    }
    async function sendNotify() {
      const errEl = document.getElementById('notifyError');
      const sucEl = document.getElementById('notifySuccess');
      errEl.classList.add('hidden'); sucEl.classList.add('hidden');
      const msg = document.getElementById('notifyMessage').value.trim();
      if (!msg) { errEl.textContent='Please enter a message.'; errEl.classList.remove('hidden'); return; }
      try {
        const res  = await fetch('/admin/inspection/notify-staff', {
          method:'POST',
          headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
          body:JSON.stringify({ itemNumber: notifyItem?.itemNumber, message: msg })
        });
        const data = await res.json();
        if (data.success) {
          sucEl.textContent='Staff notified successfully ✓'; sucEl.classList.remove('hidden');
          setTimeout(()=>{ closeNotifyModal(); loadData(); }, 1800);
        } else {
          errEl.textContent = data.error || 'Failed to send renewal.'; errEl.classList.remove('hidden');
        }
      } catch(e) {
        errEl.textContent='Network error. Please try again.'; errEl.classList.remove('hidden');
      }
    }
    document.getElementById('notifyModal').addEventListener('click',function(e){if(e.target===this)closeNotifyModal();});

    // ── Edit signatory ──
    function openEditSig(key) {
      document.getElementById('editSigKey').value=key;
      document.getElementById('editSigTitle').textContent={insp:'Inspected By',wit:'Witnessed By',app:'Approved By',not:'Noted By'}[key];
      document.getElementById('editSigName').value=sigData[key].name;
      document.getElementById('editSigRank').value=sigData[key].rank;
      document.getElementById('editSigPos').value=sigData[key].pos;
      document.getElementById('editSigFile').value='';
      document.getElementById('editSigModal').classList.add('open');
    }
    function closeEditSig() { document.getElementById('editSigModal').classList.remove('open'); }
    function saveEditSig() {
      const key=document.getElementById('editSigKey').value;
      sigData[key].name=document.getElementById('editSigName').value;
      sigData[key].rank=document.getElementById('editSigRank').value;
      sigData[key].pos=document.getElementById('editSigPos').value;
      const file=document.getElementById('editSigFile').files[0];
      if(file){const r=new FileReader();r.onload=e=>{sigData[key].img=e.target.result;renderSigs();};r.readAsDataURL(file);}
      renderSigs(); closeEditSig();
    }
    document.getElementById('editSigModal').addEventListener('click',function(e){if(e.target===this)closeEditSig();});

    // Sidebar toggle
    const sidebar    = document.getElementById('sidebar');
    const toggleBtn  = document.getElementById('sidebarToggleBtn');
    const sbIconMenu = document.getElementById('sb-icon-menu');
    const sbIconClose= document.getElementById('sb-icon-close');
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

    // Theme toggle
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

    loadData();
  </script>
</body>
</html>
