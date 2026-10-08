<!DOCTYPE html>
<html lang="en">
<head>
  <script src="/js/dashboard-state.js?v=<?php echo substr(hash_file('sha256', public_path('js/dashboard-state.js')), 0, 12); ?>"></script>
  <style>.dashboard-restoring #sidebar,.dashboard-restoring #sidebar *{transition:none!important}.dashboard-restoring #sidebar.sidebar-collapsed #sb-icon-menu{display:block!important}.dashboard-restoring #sidebar.sidebar-collapsed #sb-icon-close{display:none!important}</style>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="/css/mobile-dashboard.css">
  <script defer src="/js/mobile-dashboard.js"></script>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>Admin - List of Personnel</title>
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
    #sidebar { width:240px; min-width:64px; background:#181c21; border-right:1px solid #23272f; display:flex; flex-direction:column; padding:16px 12px; transition:width 0.28s cubic-bezier(.4,0,.2,1); overflow:hidden; position:sticky; top:0; height:100vh; }
    #sidebar.sidebar-collapsed { width:64px; }
    .sb-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; min-height:40px; position:relative; }
    #sidebar.sidebar-collapsed .sb-top { justify-content:center; }
    .sb-logo { display:flex; align-items:center; gap:10px; overflow:hidden; flex:1; }
    .sb-logo img { width:38px; height:38px; border-radius:50%; border:2px solid #3ec6ff; object-fit:cover; flex-shrink:0; background:#23272f; }
    .sb-logo-text { color:#e5eaf2; font-weight:700; font-size:1rem; white-space:nowrap; transition:opacity 0.2s,width 0.2s; overflow:hidden; }
    #sidebar.sidebar-collapsed .sb-logo-text { opacity:0; width:0; }
    .sb-toggle { background:transparent; border:none; color:#94a3b8; cursor:pointer; padding:6px; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:background 0.15s,color 0.15s; }
    .sb-toggle:hover { background:#23272f; color:#e5eaf2; }
    #sidebar.sidebar-collapsed .sb-toggle { position:absolute; top:0; left:50%; transform:translateX(-50%); }
    #sidebar.sidebar-collapsed .sb-logo { flex:0; }
    nav.sb-nav { flex:1; display:flex; flex-direction:column; gap:2px; }
    .nav-item { display:flex; align-items:center; gap:10px; padding:9px 10px; border-radius:8px; color:#94a3b8; text-decoration:none; font-size:0.85rem; font-weight:500; white-space:nowrap; transition:background 0.15s,color 0.15s; overflow:hidden; }
    .nav-item:hover { background:#23272f; color:#e5eaf2; }
    .nav-item.active { background:#23272f; color:#e5eaf2; }
    .nav-item svg { width:20px; height:20px; flex-shrink:0; }
    .nav-label { transition:opacity 0.15s,width 0.15s; overflow:hidden; white-space:nowrap; }
    #sidebar.sidebar-collapsed .nav-label { opacity:0; width:0; }
    #sidebar.sidebar-collapsed .nav-item { justify-content:center; padding:9px 0; gap:0; }
    .sb-bottom { padding-top:12px; border-top:1px solid #23272f; display:flex; justify-content:center; }
    .theme-btn { background:#23272f; border:none; color:#94a3b8; cursor:pointer; padding:8px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:background 0.15s,color 0.15s; }
    .theme-btn:hover { background:#2d3340; color:#e5eaf2; }
    body, .main-bg { transition:background 0.2s,color 0.2s; }
    body.light-mode { background:#f1f5fa; color:#222; }
    body.light-mode .main-bg { background:#f1f5fa !important; }
    body.light-mode .bg-\[\#1a2025\] { background-color:#f1f5fa !important; }
    body.light-mode #sidebar { background:#f7fafc; border-color:#e2e8f0; }
    body.light-mode .nav-item { color:#64748b; }
    body.light-mode .nav-item:hover, body.light-mode .nav-item.active { background:#e8edf5; color:#1e293b; }
    body.light-mode .sb-logo-text { color:#1e293b; }
    body.light-mode .sb-bottom { border-color:#e2e8f0; }
    body.light-mode .theme-btn { background:#e8edf5; color:#64748b; }
    body.light-mode .force-light-text { color:#222 !important; }
    body.light-mode .notification-bell { color:#0284c7 !important; }
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
    .force-light-text { color:#e5eaf2; }
    .notification-bell { position:relative; cursor:pointer; outline:none; transition:color 0.2s ease; }
    .notification-badge { position:absolute; top:-4px; right:-4px; background:#ef4444; color:white; font-weight:bold; border-radius:50%; font-size:0.62rem; min-width:17px; height:17px; display:none; align-items:center; justify-content:center; border:2px solid #1a2025; animation:bell-pop 0.3s ease-out; }
    .notification-bell.has-unread .notification-badge { display:flex; }
    @keyframes bell-pop { 0%{transform:scale(0);opacity:0} 50%{transform:scale(1.2)} 100%{transform:scale(1);opacity:1} }
    #adminNotifDropdown { display:none; position:absolute; top:calc(100% + 10px); right:0; width:320px; background:#23272f; border:1px solid #363b48; border-radius:12px; box-shadow:0 12px 32px rgba(0,0,0,0.4); z-index:200; overflow:hidden; }
    #adminNotifDropdown.open { display:block; animation:fadeDown 0.18s ease; }
    @keyframes fadeDown { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
    .notif-header { padding:12px 16px; border-bottom:1px solid #363b48; display:flex; justify-content:space-between; align-items:center; font-size:0.8rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.06em; }
    .notif-mark-read { font-size:0.72rem; color:#3ec6ff; cursor:pointer; font-weight:600; text-transform:none; letter-spacing:0; }
    .notif-mark-read:hover { text-decoration:underline; }
    .notif-list { max-height:340px; overflow-y:auto; }
    .notif-list::-webkit-scrollbar { width:4px; }
    .notif-list::-webkit-scrollbar-track { background:#1a2025; }
    .notif-list::-webkit-scrollbar-thumb { background:#363b48; border-radius:4px; }
    .notif-item { padding:12px 16px; border-bottom:1px solid #2e333d; display:flex; gap:10px; align-items:flex-start; font-size:0.8rem; color:#cbd5e0; transition:background 0.15s; }
    .notif-item:last-child { border-bottom:none; }
    .notif-item.unread { background:#1c2631; }
    .notif-item:hover { background:#1a2025; }
    .notif-icon { flex-shrink:0; margin-top:2px; }
    .notif-content { flex:1; min-width:0; }
    .notif-title { font-weight:700; font-size:0.78rem; color:#e5eaf2; margin-bottom:2px; }
    .notif-message { color:#94a3b8; line-height:1.4; font-size:0.75rem; }
    .notif-time { color:#4b5563; font-size:0.7rem; margin-top:4px; }
    .notif-dot { width:7px; height:7px; border-radius:50%; background:#3ec6ff; flex-shrink:0; margin-top:5px; }
    .notif-empty { padding:24px 16px; text-align:center; color:#4b5563; font-size:0.8rem; }
    .notif-footer { padding:10px 16px; border-top:1px solid #363b48; text-align:center; font-size:0.72rem; color:#4b5563; }
    body.light-mode #adminNotifDropdown { background:#fff; border-color:#d0d7e4; }
    body.light-mode .notif-header { border-color:#e5e7eb; color:#6b7280; }
    body.light-mode .notif-item { color:#374151; border-color:#e5e7eb; }
    body.light-mode .notif-item.unread { background:#eff6ff; }
    body.light-mode .notif-item:hover { background:#f8fafc; }
    body.light-mode .notif-message { color:#6b7280; }
    body.light-mode .notif-time { color:#9ca3af; }
    body.light-mode .notif-footer { border-color:#e5e7eb; }
    body.light-mode .notification-badge { border-color:#f1f5fa; }
    .personnel-panel { border:1px solid #313848; border-radius:0.9rem; background:#222831; box-shadow:0 10px 28px rgba(0,0,0,0.16); }
    .control-input, .control-select { background:#1f2530 !important; border:1px solid #3b4456 !important; color:#e5eaf2 !important; border-radius:0.625rem; }
    .control-input::placeholder { color:#8d99ab; }
    .control-input:focus, .control-select:focus { border-color:#3ec6ff !important; box-shadow:0 0 0 2px rgba(62,198,255,0.22); }
    .primary-btn { background:#35b4df !important; color:#10212e !important; border:1px solid transparent; border-radius:0.625rem; font-weight:600; padding:0.47rem 0.9rem; transition:all 0.18s ease; }
    .primary-btn:hover { background:#249bc2 !important; color:#f8fcff !important; }
    body.light-mode .personnel-panel { background:#ffffff !important; border-color:#d5dce8 !important; }
    body.light-mode .control-input, body.light-mode .control-select { background:#f8fafd !important; color:#1f2937 !important; border:1px solid #ced8e6 !important; }
    table.personnel-table th, table.personnel-table td { white-space:nowrap; }
    table.personnel-table th { background:#1d232d; color:#b7c2d0; font-weight:600; font-size:0.74rem; letter-spacing:0.03em; }
    table.personnel-table td { background:#222831; color:#e5eaf2; border-bottom:1px solid #343b4b; }
    table.personnel-table tbody tr:hover td { background:#2a3140; }
    body.light-mode table.personnel-table th { background:#f1f5f9 !important; color:#4b5563 !important; }
    body.light-mode table.personnel-table td { background:#ffffff !important; color:#1f2937 !important; border-bottom:1px solid #e5eaf2 !important; }
    body.light-mode table.personnel-table tbody tr:hover td { background:#eef4fb !important; }
    .action-btn { width:1.95rem; height:1.95rem; padding:0; border-radius:0.5rem; transition:background 0.15s ease,transform 0.15s ease; display:inline-flex; align-items:center; justify-content:center; }
    .action-btn:hover { background:rgba(255,255,255,0.08); transform:translateY(-1px); }
    body.light-mode .action-btn:hover { background:#e7eef8; }
    .abadge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:999px; font-size:0.68rem; font-weight:700; white-space:nowrap; }
    .abadge-renewed { background:#0d3325; color:#33b481; }
    .abadge-within  { background:#2d2a0a; color:#ecc94b; }
    .abadge-expired { background:#2d0a0a; color:#fc8181; }
    .abadge-pending { background:#1e2430; color:#64748b; }
    .abadge-new     { background:#0a1f3a; color:#3ec6ff; }
    body.light-mode .abadge-renewed { background:#d1fae5; color:#065f46; }
    body.light-mode .abadge-within  { background:#fef9c3; color:#92400e; }
    body.light-mode .abadge-expired { background:#fee2e2; color:#991b1b; }
    body.light-mode .abadge-pending { background:#e2e8f0; color:#475569; }
    body.light-mode .abadge-new     { background:#dbeafe; color:#1e40af; }
    .modal-bg { position:fixed; inset:0; background:rgba(24,28,33,0.72); backdrop-filter:blur(2px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:1rem; }
    .modal-box { background:#202631; color:#e5eaf2; padding:1.25rem; border-radius:0.95rem; border:1px solid #333b4d; box-shadow:0 20px 42px rgba(0,0,0,0.35); width:min(980px,94vw); max-height:90vh; overflow-y:auto; position:relative; }
    #personnelModal .modal-box { width:min(680px,92vw); }
    body.light-mode .modal-bg { background:rgba(15,23,42,0.35); }
    body.light-mode .modal-box { background:#ffffff; color:#1f2937; border-color:#d2dbe9; }
    body.light-mode #personnelModal [style*="background:#1e2530"],
    body.light-mode #personnelModal [style*="background:#1a2025"] { background:#ffffff !important; }
    body.light-mode #personnelModal [style*="background:#181c24"] { background:#f1f5f9 !important; }
    body.light-mode #personnelModal [style*="background:#252f3e"] { background:#f8fafc !important; }
    body.light-mode #personnelModal [style*="color:#e5eaf2"] { color:#1e293b !important; }
    body.light-mode #personnelModal [style*="color:#94a3b8"] { color:#475569 !important; }
    body.light-mode #personnelModal [style*="color:#4b5563"] { color:#64748b !important; }
    body.light-mode #personnelModal [style*="border-color:#2a3140"],
    body.light-mode #personnelModal [style*="border:#2e3749"] { border-color:#dbe3ee !important; }
    .modal-close-btn { position:absolute; right:0.75rem; top:0.75rem; border:none; background:none; color:#b0bac7; font-size:1.25rem; cursor:pointer; line-height:1; }
    .modal-details-table { width:100%; border-collapse:collapse; }
    .modal-details-table th, .modal-details-table td { padding:0.42rem 0.5rem; text-align:left; font-size:0.9rem; vertical-align:top; border-bottom:1px solid #333b4d; }
    .modal-details-table th { background:none; color:#9ec2e5; font-weight:600; width:42%; }
    .modal-details-table td { background:none; color:#e5eaf2; }
    body.light-mode .modal-details-table th { color:#1d4f88; border-bottom-color:#e2e8f0; }
    body.light-mode .modal-details-table td { color:#1f2937; border-bottom-color:#e2e8f0; }
    .modal-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0.9rem; }
    .form-section { border:1px solid #384155; border-radius:0.75rem; background:#1a202a; padding:0.85rem; }
    .form-section-title { font-size:0.72rem; letter-spacing:0.08em; text-transform:uppercase; color:#9ec2e5; margin-bottom:0.7rem; font-weight:700; }
    .field-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0.65rem 0.75rem; }
    #addPersonnelModal .form-row, #editPersonnelModal .form-row { margin-bottom:0; min-width:0; }
    #addPersonnelModal .form-row.span-2, #editPersonnelModal .form-row.span-2 { grid-column:1 / -1; }
    #addPersonnelModal .modal-box input, #addPersonnelModal .modal-box select,
    #editPersonnelModal .modal-box input, #editPersonnelModal .modal-box select { width:100%; background:#262e3a; color:#e5eaf2; border:1px solid #40495c; border-radius:0.5rem; padding:0.45rem 0.6rem; margin-bottom:0; font-size:0.88rem; }
    #addPersonnelModal label, #editPersonnelModal label { font-size:0.76rem; font-weight:600; color:#a8bdd6; margin-bottom:0.25rem; display:block; letter-spacing:0.02em; }
    #addPersonnelModal .actions-row, #editPersonnelModal .actions-row { display:flex; justify-content:flex-end; gap:0.55rem; margin-top:1rem; padding-top:0.85rem; border-top:1px solid #364052; }
    .secondary-btn { background:transparent; color:#c2cede; border:1px solid #52607a; border-radius:0.5rem; padding:0.45rem 0.9rem; font-weight:600; cursor:pointer; transition:all 0.15s ease; }
    .secondary-btn:hover { background:#2a3343; color:#ffffff; }
    #addPersonnelModal .add-btn, #editPersonnelModal .edit-btn { background:#35b4df; color:#10212e; font-weight:700; border-radius:0.5rem; padding:0.45rem 1.1rem; border:none; cursor:pointer; transition:all 0.18s ease; }
    #addPersonnelModal .add-btn:hover, #editPersonnelModal .edit-btn:hover { background:#249bc2; color:#ffffff; }
    body.light-mode .form-section { background:#f8fafc; border-color:#dbe4f0; }
    body.light-mode .form-section-title { color:#486a93; }
    body.light-mode #addPersonnelModal .modal-box input, body.light-mode #addPersonnelModal .modal-box select,
    body.light-mode #editPersonnelModal .modal-box input, body.light-mode #editPersonnelModal .modal-box select { background:#ffffff; color:#1f2937; border-color:#ccd7e7; }
    body.light-mode #addPersonnelModal label, body.light-mode #editPersonnelModal label { color:#4b6079; }
    body.light-mode #addPersonnelModal .actions-row, body.light-mode #editPersonnelModal .actions-row { border-top-color:#d9e1ec; }
    body.light-mode .secondary-btn { color:#334155; border-color:#c3cfe0; background:#ffffff; }
    body.light-mode .secondary-btn:hover { background:#edf2f9; color:#0f172a; }
    @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
    @media (max-width:1024px) { .modal-form-grid { grid-template-columns:1fr; } }
    @media (max-width:640px) { .field-grid { grid-template-columns:1fr; } .modal-box { width:95vw; padding:1rem; } #addPersonnelModal .actions-row, #editPersonnelModal .actions-row { flex-direction:column-reverse; } .secondary-btn, #addPersonnelModal .add-btn, #editPersonnelModal .edit-btn { width:100%; } }
    .pd-row { display:flex; align-items:flex-start; gap:6px; font-size:0.76rem; line-height:1.5; }
    .personnel-profile-frame { width:min(1000px,100%); max-height:calc(100dvh - 2rem); display:flex; flex-direction:column; }
    .personnel-profile-body { display:flex; min-height:0; overflow-y:auto; }
    .personnel-profile-sidebar { width:300px; flex-shrink:0; }
    .personnel-profile-history { min-width:0; }
    .personnel-profile-frame .pd-val { overflow-wrap:anywhere; min-width:0; }
    @media (max-width:640px) {
      .personnel-profile-body { flex-direction:column; }
      .personnel-profile-sidebar { width:100%; border-right:0 !important; border-bottom:1px solid #2a3140; }
      .personnel-profile-history { min-height:260px; flex-shrink:0; }
    }
    .pd-lbl { color:#4b5563; font-weight:700; font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap; min-width:80px; padding-top:1px; }
    .pd-val { color:#cbd5e0; word-break:break-word; }
    body.light-mode .pd-lbl { color:#6b7280; }
    body.light-mode .pd-val { color:#1f2937; }
  </style>
  <link rel="stylesheet" href="/css/typography.css">
  <link rel="stylesheet" href="/css/dashboard-theme.css?v=<?php echo substr(hash_file('sha256', public_path('css/dashboard-theme.css')), 0, 12); ?>">
  <script src="/js/dashboard-lists.js?v=<?php echo substr(hash_file('sha256', public_path('js/dashboard-lists.js')), 0, 12); ?>" defer></script>
<script src="/js/ui-dialog.js" defer></script>
</head>
<body class="app-dashboard light-mode min-h-screen font-inter main-bg bg-[#1a2025]">
<div class="flex min-h-screen">

  <aside id="sidebar">
    <script>restoreDashboardState();</script>
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
      <a href="<?php echo e(route('admin.personnel')); ?>" class="nav-item active">
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

  <main class="flex-1 main-bg bg-[#1a2025] p-7 overflow-y-auto">
    <header class="flex flex-wrap justify-between mb-8 items-center gap-4">
      <div class="relative w-80">
        <input id="searchInput" type="text" placeholder="Search personnel..."
          class="control-input bg-[#23272f] text-white border border-[#363b48] rounded px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-accent pr-10 transition-all force-light-text" />
        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M21 21l-4.35-4.35M5 11a6 6 0 1112 0 6 6 0 01-12 0z"/></svg>
      </div>
      <div class="flex flex-row-reverse items-center gap-4">
        <?php echo $__env->make('partials.account_dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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

    <div class="flex flex-wrap gap-2 items-center mb-4">
      <input id="rankFilter" type="text" placeholder="Rank" class="control-input bg-[#23272f] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text" />
      <input id="unitFilter" type="text" placeholder="Unit / Office / Department" class="control-input bg-[#23272f] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text" />
      <label class="text-xs text-[#94a3b8] force-light-text">Filter by Approval:</label>
      <select id="approvalFilter" class="control-select bg-[#23272f] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text">
        <option value="">All</option>
        <option value="new">New</option>
        <option value="pending">Pending</option>
        <option value="renewed">Renewed</option>
        <option value="within">Within Renewal</option>
        <option value="expired">Expired</option>
      </select>
      <button id="clearFilters" type="button" class="control-select bg-[#23272f] text-[#94a3b8] border border-[#363b48] rounded px-3 py-1 text-xs">Clear Filters</button>
    </div>

    <section class="personnel-panel bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 mb-10 mt-2">
      <div class="flex flex-wrap items-center justify-between mb-4 gap-2">
        <h2 class="font-semibold text-base text-[#e5eaf2] tracking-tight force-light-text">List of Personnel</h2>
        <div class="flex flex-wrap gap-2 items-center ml-auto">
          <label for="sortSelect" class="text-[#b0bac7] text-xs force-light-text mr-1">Sort by:</label>
          <select id="sortSelect" class="control-select bg-[#23272f] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text">
            <option value="approvedStatus-asc" selected>Approval Status</option>
            <option value="itemNumber-asc">Item # (Asc)</option>
            <option value="itemNumber-desc">Item # (Desc)</option>
            <option value="lastName-asc">Last Name (A-Z)</option>
            <option value="lastName-desc">Last Name (Z-A)</option>
            <option value="dateOfValidity-asc">Date of Validity (Earliest)</option>
            <option value="dateOfValidity-desc">Date of Validity (Latest)</option>
          </select>

        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left personnel-table aligned-list-table">
          <thead>
            <tr>
              <th class="py-2 px-2">Item #</th>
              <th class="py-2 px-2">Last Name | Rank</th>
              <th class="py-2 px-2">First Name</th>
              <th class="py-2 px-2">Date of Validity</th>
              <th class="py-2 px-2">Approved Status</th>
              <th class="py-2 px-2">Actions</th>
            </tr>
          </thead>
          <tbody id="personnelTableBody"></tbody>
        </table>
      </div>
      <div id="personnelCount" class="mt-4 text-xs text-[#b0bac7] force-light-text"></div>
      <div id="personnelPagination" class="mt-3 flex flex-wrap gap-1"></div>
    </section>

    <div id="personnelModal"     style="display:none;"></div>
    <div id="editPersonnelModal" style="display:none;"></div>
  </main>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

const ROUTES = {
  personnelData:   "<?php echo e(route('admin.personnel.data')); ?>",
  personnelStore:  "<?php echo e(route('admin.personnel.store')); ?>",
  personnelUpdate: (id) => `/admin/personnel-data/${id}`,
  personnelDelete: (id) => `/admin/personnel-data/${id}`,
  renewalHistory:  (id) => `/admin/personnel/${id}/renewal-history`,
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
  let currentTheme = localStorage.getItem('theme') || 'light';
  applyTheme(currentTheme);
  document.getElementById('themeToggle').addEventListener('click', function () {
    const next = currentTheme === 'light' ? 'dark' : 'light';
    currentTheme = next;
    localStorage.setItem('theme', next);
    applyTheme(next);
  });

  // ===== NOTIFICATION BELL =====
  (function initNotificationBell() {
    const ADMIN_NOTIF_URL      = "<?php echo e(route('admin.notifications')); ?>";
    const ADMIN_NOTIF_READ_URL = "<?php echo e(route('admin.notifications.read')); ?>";
    const bell        = document.getElementById('notificationBell');
    const badge       = document.getElementById('notificationBadge');
    const dropdown    = document.getElementById('adminNotifDropdown');
    const notifList   = document.getElementById('adminNotifList');
    const notifFooter = document.getElementById('adminNotifFooter');
    const markAllRead = document.getElementById('adminMarkAllRead');

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
        const res  = await fetch(ADMIN_NOTIF_URL, { headers: { 'Accept':'application/json', 'X-CSRF-TOKEN':CSRF } });
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
              <div class="notif-title">${String(n.title ?? "").replace(/[&<>"']/g, c => "&#" + c.charCodeAt(0) + ";")}</div>
              <div class="notif-message">${String(n.message ?? "").replace(/[&<>"']/g, c => "&#" + c.charCodeAt(0) + ";")}</div>
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
      if (!document.getElementById('adminNotifWrapper').contains(e.target)) dropdown.classList.remove('open');
    });
    markAllRead.addEventListener('click', async function () {
      try {
        await fetch(ADMIN_NOTIF_READ_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' } });
        await loadNotifications();
        dropdown.classList.remove('open');
      } catch (e) {}
    });
    loadNotifications();
    setInterval(loadNotifications, 30000);
  })();

  // ===== PERSONNEL DATA =====
  let personnel = [];

  function asText(v) { return v == null ? "" : String(v).trim(); }

  // normalizeRow — sanitize null/undefined strings from API
  function normalizeRow(row, index) {
    const s = row || {};
    return {
      itemNumber:         Number(s.itemNumber) || index + 1,
dateOfValidity: (s.dateOfValidity && s.dateOfValidity !== 'null' && s.dateOfValidity !== 'undefined')
                  ? asText(s.dateOfValidity)
                  : (s.validity && s.validity !== 'null' && s.validity !== 'undefined')
                    ? asText(s.validity)
                    : (s.dateIssued && s.dateIssued !== 'null' && s.dateIssued !== 'undefined')
                      ? asText(s.dateIssued)
                      : (s.date_issued && s.date_issued !== 'null' && s.date_issued !== 'undefined')
                        ? asText(s.date_issued)
                        : '',
      rank:               asText(s.rank).toUpperCase(),
      lastName:           asText(s.lastName),
      firstName:          asText(s.firstName),
      middleName:         asText(s.middleName),
      afpSerialNumber:    asText(s.afpSerialNumber || s.afpSerial),
      afosMos:            asText(s.afosMos),
      branch:             asText(s.branch),
      dateOfBirth:        asText(s.dateOfBirth || s.dob),
      pistolNomenclature: asText(s.pistolNomenclature || s.pistol),
      pistolSerialNumber: asText(s.pistolSerialNumber || s.serial),
      qtyAmmo:            Number(s.qtyAmmo) || 0,
      unit:               asText(s.unit),
      approvedStatus:     asText(s.approvedStatus) || 'pending',
      photo:              s.photo || null,
      email:              (s.email && s.email !== 'undefined' && s.email !== 'null') ? asText(s.email) : '',
    };
  }

  async function loadPersonnelData() {
    try {
      const res  = await fetch(ROUTES.personnelData, {headers:{'Accept':'application/json'}, signal:AbortSignal.timeout(20000)});
      const json = await res.json();
      if (json.success && Array.isArray(json.data)) {
        personnel = json.data.map((row, i) => normalizeRow(row, i));
        personnel.sort((a, b) => b.itemNumber - a.itemNumber);
      }
    } catch (e) { personnel = []; }
    renderPersonnelTable();
  }

  // ===== STATUS =====
  function getValidityStatus(dateOfValidity) {
    if (!dateOfValidity) return 'pending';
    const currDate     = new Date();
    const validityDate = new Date(dateOfValidity);
    if (isNaN(validityDate.getTime())) return 'pending';
    const diffDays = Math.ceil((validityDate - currDate) / (1000 * 60 * 60 * 24));
    if (diffDays > 90) return 'renewed';
    if (diffDays >= 0) return 'within';
    return 'expired';
  }

  // resolveStatus — don't let default "pending" block date-based status
  function resolveStatus(row) {
    const manual = (row.approvedStatus || '').trim().toLowerCase();
    // If explicitly set to something other than "pending", honour it
    if (manual && manual !== 'pending') return manual;
    // If there's a validity date, let the date decide
    if (row.dateOfValidity) return getValidityStatus(row.dateOfValidity);
    return manual || 'pending';
  }

  function approvedStatusBadge(row) {
    const status = resolveStatus(row);
    const map = {
      new:     `<span class="abadge abadge-new">New</span>`,
      renewed: `<span class="abadge abadge-renewed">✓ Renewed</span>`,
      within:  `<span class="abadge abadge-within">⏱ Within Renewal</span>`,
      expired: `<span class="abadge abadge-expired">✕ Expired</span>`,
      pending: `<span class="abadge abadge-pending">— Pending —</span>`,
    };
    return map[status] || map['pending'];
  }

  // ===== FORM CONFIG =====
  const FORM_FIELDS = [
    ["rank",               "Rank",                   "text"],
    ["lastName",           "Last Name",              "text"],
    ["firstName",          "First Name",             "text"],
    ["middleName",         "Middle Name",            "text"],
    ["afpSerialNumber",    "AFP Serial #",           "text"],
    ["afosMos",            "AFOS/MOS",               "text"],
    ["branch",             "Branch",                 "text"],
    ["unit",               "Unit",                   "text"],
    ["dateOfValidity",     "Date of Validity",       "date"],
    ["dateOfBirth",        "Date of Birth",          "date"],
    ["pistolNomenclature", "Nomenclature of Pistol", "text"],
    ["pistolSerialNumber", "Pistol Serial #",        "text"],
    ["qtyAmmo",            "Qty Ammo",               "number"],
    ["approvedStatus",     "Approved Status",        "select"],
  ];

  const FORM_SECTIONS = [
    { title: "Identity",        keys: ["rank","lastName","firstName","middleName","dateOfBirth"] },
    { title: "Service Details", keys: ["afpSerialNumber","afosMos","branch","unit","dateOfValidity"] },
    { title: "Firearm Details", keys: ["pistolNomenclature","pistolSerialNumber","qtyAmmo"] },
    { title: "Approval",        keys: ["approvedStatus"] },
  ];

  const WIDE_FIELDS  = new Set(["pistolNomenclature","approvedStatus"]);
  const RANK_OPTIONS = ["LTC","MAJ","CPT","1LT","2LT","MSG","TSG","SSG","SGT","CPL","PFC","PVT"];

  function escapeHtml(v) {
    return String(v).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;");
  }

  function buildPersonnelFormRows(prefix, row = {}) {
    const meta = Object.fromEntries(FORM_FIELDS.map(([key, label, type]) => [key, { label, type }]));
    return `<div class="modal-form-grid">
      ${FORM_SECTIONS.map(section => `
        <section class="form-section">
          <h4 class="form-section-title">${section.title}</h4>
          <div class="field-grid">
            ${section.keys.map(key => {
              const field = meta[key]; if (!field) return "";
              let val = row[key] !== undefined ? row[key] : (key === "approvedStatus" ? "pending" : "");
              if (field.type === "date" && val) val = String(val).slice(0, 10);
              const value      = String(val ?? "").trim();
              const rowClass   = WIDE_FIELDS.has(key) ? "form-row span-2" : "form-row";
              const isRank     = key === "rank";
              const isApproval = key === "approvedStatus";
              const STATUS_OPTIONS = ['new','pending','renewed','within','expired'];
              const STATUS_LABELS  = { new:'New', pending:'— Pending —', renewed:'✓ Renewed', within:'⏱ Within Renewal', expired:'✕ Expired' };
              const normRank = value.toUpperCase();
              const hasKnown = RANK_OPTIONS.includes(normRank);
              return `<div class="${rowClass}">
                <label for="${prefix}_${key}">${field.label}</label>
                ${isRank
                  ? `<select id="${prefix}_${key}" name="${key}" required>
                      <option value="" disabled ${normRank ? "" : "selected"}>Select rank</option>
                      ${!hasKnown && normRank ? `<option value="${escapeHtml(value)}" selected>${escapeHtml(value)}</option>` : ""}
                      ${RANK_OPTIONS.map(r => `<option value="${r}" ${normRank === r ? "selected" : ""}>${r}</option>`).join("")}
                    </select>`
                  : isApproval
                    ? `<select id="${prefix}_${key}" name="${key}">
                        ${STATUS_OPTIONS.map(s => `<option value="${s}" ${value === s ? "selected" : ""}>${STATUS_LABELS[s]}</option>`).join("")}
                      </select>`
                    : `<input id="${prefix}_${key}" name="${key}" type="${field.type}" value="${escapeHtml(val)}" autocomplete="new-password" ${field.type !== "number" ? 'required' : ''} />`
                }
              </div>`;
            }).join("")}
          </div>
        </section>`).join("")}
    </div>`;
  }

  // ===== TABLE =====
  let currentSort = "approvedStatus-asc";
  let personnelPage = 1;
  const PERSONNEL_PER_PAGE = 15;
  const APPROVAL_STATUS_ORDER = { new: 0, pending: 1, renewed: 2, within: 3, expired: 4 };

  function sortPersonnel(list, sortBy) {
    const [key, dir] = sortBy.split("-");
    return list.slice().sort((a, b) => {
      if (key === "approvedStatus") {
        const aOrder = APPROVAL_STATUS_ORDER[resolveStatus(a)] ?? Number.MAX_SAFE_INTEGER;
        const bOrder = APPROVAL_STATUS_ORDER[resolveStatus(b)] ?? Number.MAX_SAFE_INTEGER;
        if (aOrder !== bOrder) return dir === "asc" ? aOrder - bOrder : bOrder - aOrder;
        return Number(b.itemNumber) - Number(a.itemNumber);
      }
      let aVal = a[key], bVal = b[key];
      if (key === "itemNumber" || key === "qtyAmmo") { aVal = Number(aVal); bVal = Number(bVal); }
      else if (key !== "dateOfValidity" && key !== "dateOfBirth") { aVal = (aVal || "").toString().toLowerCase(); bVal = (bVal || "").toString().toLowerCase(); }
      if (aVal < bVal) return dir === "asc" ? -1 : 1;
      if (aVal > bVal) return dir === "asc" ? 1 : -1;
      return 0;
    });
  }

  function renderPersonnelTable() {
    const nameFilter     = (document.getElementById("searchInput")?.value || "").trim().toLowerCase();
    const approvalFilter = (document.getElementById("approvalFilter")?.value || "");
    const rankFilter     = (document.getElementById("rankFilter")?.value || "").trim().toLowerCase();
    const unitFilter     = (document.getElementById("unitFilter")?.value || "").trim().toLowerCase();

    let filtered = [...new Map(personnel.map(p => [p.itemNumber, p])).values()].filter(p => {
      const nameMatch     = p.firstName.toLowerCase().includes(nameFilter) ||
                            p.middleName.toLowerCase().includes(nameFilter) ||
                            p.lastName.toLowerCase().includes(nameFilter);
      const approvalMatch = approvalFilter ? resolveStatus(p) === approvalFilter : true;
      const rankMatch = !rankFilter || p.rank.toLowerCase().includes(rankFilter);
      const unitMatch = !unitFilter || p.unit.toLowerCase().includes(unitFilter);
      return nameMatch && approvalMatch && rankMatch && unitMatch;
    });
    filtered = sortPersonnel(filtered, currentSort);

    const tbody = document.getElementById("personnelTableBody");
    tbody.innerHTML = "";

    if (filtered.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-400 force-light-text">No personnel found.</td></tr>`;
      document.getElementById("personnelCount").innerText = "No personnel found.";
      document.getElementById('personnelPagination').innerHTML = '';
      return;
    }

    // Date of Validity column — show "Pending Inspection" in amber when empty
    const totalPages = Math.max(1, Math.ceil(filtered.length / PERSONNEL_PER_PAGE));
    if (personnelPage > totalPages) personnelPage = 1;
    const pageStart = (personnelPage - 1) * PERSONNEL_PER_PAGE;
    const pageRows = filtered.slice(pageStart, pageStart + PERSONNEL_PER_PAGE);
    pageRows.forEach((row, i) => {
     const resolvedSt = resolveStatus(row);
const validityDisplay = row.dateOfValidity
  ? `<span>${row.dateOfValidity}</span>`
  : (resolvedSt === 'new' || resolvedSt === 'pending')
    ? `<span style="color:#f59e0b;font-style:italic;">Pending Inspection</span>`
    : `<span style="color:#4b5563;font-style:italic;">—</span>`;

      tbody.innerHTML += `<tr>
        <td class="py-2 px-2 force-light-text">${row.itemNumber}</td>
        <td class="py-2 px-2 force-light-text">${escapeHtml(row.lastName.toUpperCase())} <span class="text-[#64748b]">|</span> ${escapeHtml(row.rank)}</td>
        <td class="py-2 px-2 force-light-text">${row.firstName}</td>
        <td class="py-2 px-2 force-light-text">${validityDisplay}</td>
        <td class="py-2 px-2">${approvedStatusBadge(row)}</td>
        <td class="py-2 px-2 text-center">
          <button class="action-btn view-btn" data-idx="${i}" title="View Details">
            <svg fill="none" stroke="currentColor" class="w-4 h-4 text-accent" viewBox="0 0 24 24"><path stroke-width="2" d="M1.777 12C3.397 7.943 7.386 5 12 5s8.603 2.943 10.223 7c-1.62 4.057-5.609 7-10.223 7s-8.603-2.943-10.223-7zm10.223 4a4 4 0 100-8 4 4 0 000 8z"/><circle cx="12" cy="12" r="2" stroke-width="2"/></svg>
          </button>
          <button type="button" class="action-btn personnel-edit-btn" data-idx="${i}" title="Edit Personnel" aria-label="Edit personnel">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M16 4l4 4M4 20l4-1 12-12a2.8 2.8 0 0 0-4-4L4 15l-1 6 6-1M13 21h8"/></svg>
          </button>
          <button type="button" class="action-btn remove-btn" data-idx="${i}" title="Archive Personnel" aria-label="Archive personnel">
            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-1"/><path stroke-width="2" d="M9 10v6m6-6v6M10 4h4a1 1 0 011 1v2H9V5a1 1 0 011-1z"/></svg>
          </button>
        </td>
      </tr>`;
    });

    document.getElementById("personnelCount").innerText = `Showing ${filtered.length ? pageStart + 1 : 0}–${Math.min(pageStart + PERSONNEL_PER_PAGE, filtered.length)} of ${filtered.length} personnel.`;
    document.getElementById('personnelPagination').innerHTML = totalPages > 1
      ? Array.from({length: totalPages}, (_, index) => `<button type="button" data-page="${index + 1}" class="px-2 py-1 rounded text-xs ${index + 1 === personnelPage ? 'bg-[#33b481] text-white' : 'bg-[#1a2025] text-[#94a3b8]'}">${index + 1}</button>`).join('')
      : '';
    document.querySelectorAll('#personnelPagination button').forEach(button => button.addEventListener('click', () => { personnelPage = Number(button.dataset.page); renderPersonnelTable(); }));

    document.querySelectorAll('.view-btn').forEach(btn =>
      btn.addEventListener('click', function () { showPersonnelModal(pageRows[parseInt(this.dataset.idx)]); })
    );
    document.querySelectorAll('.personnel-edit-btn').forEach(btn =>
      btn.addEventListener('click', function () { showEditPersonnelModal(pageRows[parseInt(this.dataset.idx)]); })
    );
    document.querySelectorAll('.remove-btn').forEach(btn =>
      btn.addEventListener('click', async function () {
        const idx      = parseInt(this.dataset.idx);
        const selected = pageRows[idx];
        if (!selected) return;
        const displayName = `${selected.firstName || ""} ${selected.lastName || ""}`.trim() || `Item #${selected.itemNumber}`;
        if (!await showUiDialog({title:'Archive Personnel?',message:`Archive ${displayName}? They will be moved to Archive Data.`,confirmText:'Archive',cancelText:'Cancel',danger:true})) return;
        const actualIndex = personnel.findIndex(p => p.itemNumber === selected.itemNumber);
        if (actualIndex === -1) return;
        try {
          this.disabled = true;
          const res  = await fetch(ROUTES.personnelDelete(selected.itemNumber), { method:'DELETE', headers:{ 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' }, signal:AbortSignal.timeout(20000) });
          const json = await res.json();
          if (json.success) { personnel.splice(actualIndex, 1); renderPersonnelTable(); await showUiDialog({title:'Personnel Archived',message:`${displayName} has been moved to Archive Data.`}); }
          else await showUiDialog({title:'Archive Failed',message:json.message || json.error || 'Archive failed.'});
        } catch (e) { await showUiDialog({title:'Archive Failed',message:'Archive failed. Please try again.'}); }
        finally { this.disabled = false; }
      })
    );
  }

  // ===== VIEW MODAL =====
  function showPersonnelModal(row) {
    const modal    = document.getElementById("personnelModal");
    const fullName = [row.firstName, row.middleName ? row.middleName.charAt(0) + '.' : '', row.lastName].filter(Boolean).join(' ');
    const status   = resolveStatus(row);
    const statusColors = {
      renewed: { bg:'#0d3325', color:'#33b481', label:'Renewed' },
      within:  { bg:'#2d2a0a', color:'#ecc94b', label:'Within Renewal' },
      expired: { bg:'#2d0a0a', color:'#fc8181', label:'Expired' },
      pending: { bg:'#1e2430', color:'#64748b', label:'Pending' },
      new:     { bg:'#0a1f3a', color:'#3ec6ff', label:'New' },
    };
    const sc = statusColors[status] || statusColors['pending'];

    const cleanEmail = (row.email && row.email !== 'undefined' && row.email !== 'null' && row.email !== '')
      ? escapeHtml(row.email) : '—';

    // VALIDITY in modal — show "Pending Inspection" in amber when empty
    const validityHtml = row.dateOfValidity
      ? `<span class="pd-val">${escapeHtml(row.dateOfValidity)}</span>`
      : `<span class="pd-val" style="color:#f59e0b;font-style:italic;font-size:0.7rem;">Pending Inspection</span>`;

    modal.innerHTML = `
      <div class="modal-bg" id="personnelModalBg">
        <div class="personnel-profile-frame" style="background:#1e2530;border-radius:1rem;overflow:hidden;position:relative;box-shadow:0 24px 60px rgba(0,0,0,0.5);border:1px solid #2e3749;">

          <!-- TOP BAR -->
          <div style="display:flex;flex-shrink:0;align-items:center;gap:12px;padding:12px 20px;background:#181c24;border-bottom:1px solid #2a3140;">
            <button id="personnelModalBack"
              style="display:flex;align-items:center;gap:6px;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:0.8rem;font-weight:800;letter-spacing:0.06em;padding:4px 10px;border-radius:6px;transition:all 0.15s;"
              onmouseover="this.style.background='#2a3140';this.style.color='#e5eaf2'"
              onmouseout="this.style.background='none';this.style.color='#94a3b8'">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
              BACK
            </button>
            <span style="flex:1;font-size:0.75rem;color:#4b5563;">Personnel Profile</span>
            <button id="personnelModalClose" style="background:none;border:none;color:#64748b;font-size:1.4rem;cursor:pointer;line-height:1;padding:0 4px;">&times;</button>
          </div>

          <!-- BODY -->
          <div class="personnel-profile-body">

            <!-- LEFT PANEL -->
            <div class="personnel-profile-sidebar" style="padding:24px 22px;display:flex;flex-direction:column;gap:14px;background:#1e2530;border-right:1px solid #2a3140;">

              <!-- Photo -->
              <div class="personnel-profile-photo">
                ${row.photo
                  ? `<img src="${escapeHtml(row.photo)}" alt="Personnel photo" width="192" height="192">`
                  : `<svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="1">
                       <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                       <circle cx="12" cy="7" r="4"/>
                     </svg>`
                }
              </div>

              <!-- Name -->
              <div style="padding-bottom:10px;border-bottom:1px solid #2a3140;">
                <div style="font-size:0.88rem;font-weight:800;color:#e5eaf2;line-height:1.3;text-transform:uppercase;">${escapeHtml(fullName)}</div>
                <div style="font-size:0.75rem;color:#64748b;margin-top:3px;">${escapeHtml(row.rank)} &bull; Item #${row.itemNumber}</div>
              </div>

              <!-- Personal info -->
              <div style="display:flex;flex-direction:column;gap:5px;">
                <div style="font-size:0.65rem;color:#3ec6ff;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:2px;">Personal Info</div>
                <div class="pd-row"><span class="pd-lbl">UNIT</span><span class="pd-val">${escapeHtml(row.unit)||'—'}</span></div>
                <div class="pd-row"><span class="pd-lbl">BRANCH</span><span class="pd-val">${escapeHtml(row.branch)||'—'}</span></div>
                <div class="pd-row"><span class="pd-lbl">DATE OF BIRTH</span><span class="pd-val">${escapeHtml(row.dateOfBirth)||'—'}</span></div>
                <div class="pd-row"><span class="pd-lbl">AFP SERIAL</span><span class="pd-val">${escapeHtml(row.afpSerialNumber)||'—'}</span></div>
                <div class="pd-row"><span class="pd-lbl">AFOS/MOS</span><span class="pd-val">${escapeHtml(row.afosMos)||'—'}</span></div>
                <div class="pd-row"><span class="pd-lbl">EMAIL</span><span class="pd-val" style="word-break:break-all;">${cleanEmail}</span></div>
              </div>

              <!-- Firearm info -->
              <div style="display:flex;flex-direction:column;gap:5px;padding-top:10px;border-top:1px solid #2a3140;">
                <div style="font-size:0.65rem;color:#3ec6ff;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:2px;">Assigned Firearm</div>
                <div class="pd-row"><span class="pd-lbl">FIREARM</span><span class="pd-val">${escapeHtml(row.pistolNomenclature)||'—'}</span></div>
                <div class="pd-row"><span class="pd-lbl">SERIAL NO</span><span class="pd-val">${escapeHtml(row.pistolSerialNumber)||'—'}</span></div>
                <div class="pd-row"><span class="pd-lbl">QTY AMMO</span><span class="pd-val">${row.qtyAmmo != null ? row.qtyAmmo : '—'}</span></div>
                <div class="pd-row"><span class="pd-lbl">VALIDITY</span>${validityHtml}</div>
              </div>

              <!-- Status -->
              <div style="margin-top:auto;padding-top:10px;border-top:1px solid #2a3140;display:flex;align-items:center;gap:8px;">
                <span style="font-size:0.65rem;color:#4b5563;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;">Status</span>
                <span style="background:${sc.bg};color:${sc.color};font-size:0.7rem;font-weight:700;padding:2px 12px;border-radius:999px;">${sc.label}</span>
              </div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="personnel-profile-history" style="flex:1;display:flex;flex-direction:column;position:relative;overflow:hidden;background:#1a2025;">

              <!-- Watermark -->
              <img src="/images/logo2.png" alt=""
                style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:90%;height:90%;object-fit:contain;opacity:0.12;pointer-events:none;z-index:0;"
                onerror="this.style.display='none'">

              <div style="position:relative;z-index:2;padding:16px 18px;border-bottom:1px solid #2a3140;background:#1e2530;">
                <div style="font-size:0.82rem;font-weight:700;color:#e5eaf2;letter-spacing:0.02em;">Renewal History</div>
              </div>

              <div id="pd-tab-renewal"
                   style="display:flex;position:relative;z-index:1;flex:1;flex-direction:column;overflow-y:auto;padding:16px;">
                <div id="pd-renewal-loading"
                     style="text-align:center;color:#4b5563;padding:40px 0;display:none;">
                  <svg width="24" height="24" fill="none" stroke="#3ec6ff" stroke-width="2" viewBox="0 0 24 24"
                       style="margin:0 auto 8px;display:block;animation:spin 1s linear infinite;">
                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                  Loading renewal history...
                </div>
                <div id="pd-renewal-content"></div>
              </div>

            </div>
          </div>
        </div>
      </div>`;

    modal.style.display = '';
    loadRenewalHistory(row.itemNumber);

    const close = () => { modal.style.display = 'none'; };
    document.getElementById('personnelModalClose').onclick = close;
    document.getElementById('personnelModalBack').onclick  = close;
    document.getElementById('personnelModalBg').onclick    = (e) => {
      if (e.target.id === 'personnelModalBg') close();
    };
  }

  // ===== RENEWAL HISTORY =====
  async function loadRenewalHistory(itemNumber) {
    const loading = document.getElementById('pd-renewal-loading');
    const content = document.getElementById('pd-renewal-content');
    if (loading) { loading.style.display = 'block'; }
    if (content) { content.innerHTML = ''; }

    try {
      const res  = await fetch(ROUTES.renewalHistory(itemNumber), {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, signal:AbortSignal.timeout(20000)
      });
      const json = await res.json();
      if (!res.ok || !json.success) throw new Error(json.message || 'Unable to load renewal history.');
      if (loading) loading.style.display = 'none';

      if (!json.success || !json.history || !json.history.length) {
        content.innerHTML = `
          <div style="text-align:center;color:#4b5563;padding:40px 0;">
            <svg width="36" height="36" fill="none" stroke="#374151" stroke-width="1.5"
                 viewBox="0 0 24 24" style="margin:0 auto 8px;display:block;">
              <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0
                       0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span style="font-size:0.75rem;">No renewal history available.</span>
          </div>`;
        return;
      }

      const statusColor = {
        renewed:    { bg:'#0d3325', color:'#33b481', label:'Renewed' },
        within:     { bg:'#2d2a0a', color:'#ecc94b', label:'Within Renewal' },
        expired:    { bg:'#2d0a0a', color:'#fc8181', label:'Expired' },
        pending:    { bg:'#1e2430', color:'#64748b', label:'Pending' },
        new:        { bg:'#0a1f3a', color:'#3ec6ff', label:'New' },
        for_repair: { bg:'#1a1a2e', color:'#a78bfa', label:'For Repair' },
      };

      content.innerHTML = json.history.map(h => {
        const sc = statusColor[h.action] || statusColor['pending'];
        const dateFormatted = h.date
          ? new Date(h.date).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' })
          : '—';
        return `
          <div style="background:#1e2530;border:1px solid #2a3140;border-radius:8px;padding:12px 14px;margin-bottom:8px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <span style="background:${sc.bg};color:${sc.color};font-size:0.7rem;font-weight:700;padding:2px 10px;border-radius:999px;">
                ${sc.label}
              </span>
              <span style="font-size:0.7rem;color:#4b5563;">${dateFormatted}</span>
            </div>
            <div style="font-size:0.75rem;color:#94a3b8;display:flex;flex-direction:column;gap:3px;">
              ${h.dateOfValidity    ? `<span>New Validity: <strong style="color:#e5eaf2;">${escapeHtml(h.dateOfValidity)}</strong></span>` : ''}
              ${h.previousValidity  ? `<span>Previous Validity: <strong style="color:#e5eaf2;">${escapeHtml(h.previousValidity)}</strong></span>` : ''}
              ${h.inspectedBy       ? `<span>Inspected by: <strong style="color:#e5eaf2;">${escapeHtml(h.inspectedBy)}</strong></span>` : ''}
              ${h.remarks           ? `<span>Remarks: <strong style="color:#e5eaf2;">${escapeHtml(h.remarks)}</strong></span>` : ''}
            </div>
          </div>`;
      }).join('');

    } catch (e) {
      if (loading) loading.style.display = 'none';
      if (content) content.innerHTML = `
        <div style="text-align:center;color:#fc8181;padding:20px;">
          Failed to load renewal history. Please try again.
        </div>`;
    }
  }


  //  EDIT MODAL //
  function showEditPersonnelModal(row) {
    const modal = document.getElementById("editPersonnelModal");
    modal.innerHTML = `
      <div class="modal-bg">
        <form class="modal-box personnel-form" id="editPersonnelForm" autocomplete="off" onsubmit="return false;">
          <button class="modal-close-btn" title="Close" type="button">&times;</button>
          <h3 class="font-semibold mb-4 text-lg">Edit Personnel &mdash; Item #${row.itemNumber}</h3>
          ${buildPersonnelFormRows("edit", row)}
          <div class="actions-row">
            <button type="button" class="secondary-btn modal-cancel-btn">Cancel</button>
            <button type="submit" class="edit-btn">Save Changes</button>
          </div>
        </form>
      </div>`;
    modal.style.display = '';

    const close = () => { modal.style.display = 'none'; };
    modal.querySelector('.modal-close-btn').onclick  = close;
    modal.querySelector('.modal-cancel-btn').onclick = close;
    modal.querySelector('.modal-bg').onclick = (e) => {
      if (e.target === modal.querySelector('.modal-bg')) close();
    };

    modal.querySelector("#editPersonnelForm").onsubmit = async function (e) {
      e.preventDefault();
      const payload = {};
      FORM_FIELDS.forEach(([key,, type]) => {
        const input = modal.querySelector(`[name='${key}']`);
        payload[key] = type === "number" ? Number(input?.value) : (input?.value ?? "");
      });
      try {
        const res  = await fetch(ROUTES.personnelUpdate(row.itemNumber), {
          method:  'PUT',
          headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
          body:    JSON.stringify(payload),
        });
        const json = await res.json();
        if (json.success) {
          const idx = personnel.findIndex(p => p.itemNumber === row.itemNumber);
          if (idx !== -1) personnel[idx] = normalizeRow({ ...row, ...payload }, idx);
          close();
          renderPersonnelTable();
        } else {
          alert(json.message || json.error || 'Update failed.');
        }
      } catch (x) {
        alert('Update failed. Please try again.');
      }
    };
  }

  // ===== INIT =====
  loadPersonnelData();
  ['searchInput','rankFilter','unitFilter'].forEach(id => document.getElementById(id)?.addEventListener('input', () => { personnelPage = 1; renderPersonnelTable(); }));
  document.getElementById("sortSelect").addEventListener("change", function (e) { currentSort = e.target.value; renderPersonnelTable(); });
  document.getElementById("approvalFilter").addEventListener("change", () => { personnelPage = 1; renderPersonnelTable(); });
  document.getElementById('clearFilters').addEventListener('click', () => {
    ['searchInput','rankFilter','unitFilter','approvalFilter'].forEach(id => { document.getElementById(id).value = ''; });
    personnelPage = 1;
    renderPersonnelTable();
  });

});
</script>
</body>
</html>
