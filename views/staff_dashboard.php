  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/css/mobile-dashboard.css">
    <script defer src="/js/mobile-dashboard.js"></script>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Staff Dashboard</title>
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
      .nav-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;color:#94a3b8;text-decoration:none;font-size:0.85rem;font-weight:500;white-space:nowrap;transition:background 0.15s,color 0.15s;overflow:hidden;cursor:pointer;border:none;background:transparent;width:100%;text-align:left;}
      .nav-item:hover{background:#23272f;color:#e5eaf2;}
      .nav-item.active{background:#23272f;color:#e5eaf2;}
      .nav-item svg{width:20px;height:20px;flex-shrink:0;}
      .nav-label{transition:opacity 0.15s,width 0.15s;overflow:hidden;white-space:nowrap;}
      #sidebar.sidebar-collapsed .nav-label{opacity:0;width:0;}
      #sidebar.sidebar-collapsed .nav-item{justify-content:center;padding:9px 0;gap:0;}
      .sb-bottom{padding-top:12px;border-top:1px solid #23272f;display:flex;justify-content:center;}
      .theme-btn{background:#23272f;border:none;color:#94a3b8;cursor:pointer;padding:8px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:background 0.15s,color 0.15s;}
      .theme-btn:hover{background:#2d3340;color:#e5eaf2;}
      .notification-bell{position:relative;cursor:pointer;outline:none;transition:color 0.2s ease;}
      .staff-topbar{position:sticky;top:0;z-index:400;min-height:54px;background:#1a2025;padding:4px 0 10px;border-bottom:1px solid #252b32;isolation:isolate;box-sizing:border-box;}
      .staff-topbar-inner{display:flex;align-items:center;gap:1rem;margin-left:auto;}
      body.light-mode .staff-topbar{background:#f4f7fb!important;border-bottom-color:#dbe3ed!important;}
      .notification-badge{position:absolute;top:-4px;right:-4px;background:#ef4444;color:white;font-weight:bold;border-radius:50%;font-size:0.62rem;min-width:17px;height:17px;display:none;align-items:center;justify-content:center;border:2px solid #1a2025;animation:bell-pop 0.3s ease-out;}
      .notification-bell.has-unread .notification-badge{display:flex;}
      @keyframes bell-pop{0%{transform:scale(0);opacity:0}50%{transform:scale(1.2)}100%{transform:scale(1);opacity:1}}
      #notifDropdown{display:none;position:absolute;top:calc(100% + 10px);right:0;width:320px;background:#23272f;border:1px solid #363b48;border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,0.4);z-index:200;overflow:hidden;}
      #notifDropdown.open{display:block;animation:fadeDown 0.18s ease;}
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
      .notif-title{font-weight:700;font-size:0.78rem;margin-bottom:2px;}
      .notif-title.expired{color:#fc8181;}
      .notif-title.within_renewal{color:#f6e05e;}
      .notif-title.renewed{color:#68d391;}
      .notif-message{color:#94a3b8;line-height:1.4;font-size:0.75rem;}
      .notif-time{color:#4b5563;font-size:0.7rem;margin-top:4px;}
      .notif-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-top:5px;}
      .notif-dot.expired{background:#fc8181;}
      .notif-dot.within_renewal{background:#f6e05e;}
      .notif-dot.renewed{background:#68d391;}
      .notif-dot.read{background:#4b5563;}
      .notif-empty{padding:24px 16px;text-align:center;color:#4b5563;font-size:0.8rem;}
      .notif-footer{padding:10px 16px;border-top:1px solid #363b48;text-align:center;font-size:0.72rem;color:#4b5563;}
      .page-section{display:none;}
      .page-section.active{display:block;padding-top:0.5rem;}
      .badge{display:inline-block;padding:0;border-radius:0;font-size:0.7rem;font-weight:600;background:transparent!important;}
      .badge-text{background:transparent!important;padding:0!important;border-radius:0!important;}
      .badge-renewed{color:#33b481;}
      .badge-within{color:#b7791f;}
      .badge-expired{color:#ef4444;}
      .badge-pending{color:#64748b;}
      .badge-new{color:#2563eb;}
      .card-main-text{color:#33b481!important;font-weight:bold;}
      .card-label{color:#228b68!important;font-weight:600;}
      .card-desc{color:#89e2bc!important;}
      .force-light-text{color:#e5eaf2;}
      @keyframes spin{to{transform:rotate(360deg);}}
      .saving-spinner{display:inline-block;width:10px;height:10px;border:2px solid #3ec6ff;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;vertical-align:middle;margin-left:4px;}
      .notify-modal-overlay{position:fixed;inset:0;background:rgba(10,14,20,0.80);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem;animation:fadeIn 0.15s ease;}
      @keyframes fadeIn{from{opacity:0}to{opacity:1}}
      .notify-modal-box{background:#1a2330;border:1px solid #2e3d52;border-radius:14px;padding:1.6rem;width:min(500px,94vw);box-shadow:0 24px 48px rgba(0,0,0,0.5);position:relative;animation:slideUp 0.18s ease;}
      @keyframes slideUp{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
      .notify-modal-close{position:absolute;right:1rem;top:1rem;background:none;border:none;color:#64748b;font-size:1.3rem;cursor:pointer;line-height:1;transition:color 0.15s;}
      .notify-modal-close:hover{color:#e5eaf2;}
      .notify-input{width:100%;background:#111827;color:#e5eaf2;border:1px solid #2e3748;border-radius:7px;padding:0.55rem 0.75rem;font-size:0.85rem;outline:none;box-sizing:border-box;transition:border-color 0.18s;font-family:inherit;}
      .notify-input:focus{border-color:#3ec6ff;}
      .notify-input[readonly]{opacity:.75;cursor:not-allowed;background:#0d1420;}
      .notify-label{display:block;font-size:0.72rem;font-weight:700;color:#64748b;margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:0.06em;}
      .notify-send-btn{background:#e53e3e;color:#fff;border:none;border-radius:7px;padding:0.5rem 1.2rem;font-size:0.85rem;font-weight:700;cursor:pointer;transition:background 0.18s;}
      .notify-send-btn:hover{background:#c53030;}
      .notify-send-btn:disabled{opacity:0.6;cursor:not-allowed;}
      .notify-cancel-btn{background:transparent;color:#94a3b8;border:1px solid #2e3748;border-radius:7px;padding:0.5rem 1rem;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.15s;}
      .notify-cancel-btn:hover{background:#1e2a3a;color:#e5eaf2;}
      .notify-within-btn{background:#92400e;color:#fef3c7;border:none;border-radius:7px;padding:0.5rem 1.2rem;font-size:0.85rem;font-weight:700;cursor:pointer;transition:background 0.18s;}
      .notify-within-btn:hover{background:#b45309;}
      .system-modal-overlay{position:fixed;inset:0;background:rgba(10,14,20,0.80);z-index:1100;display:none;align-items:center;justify-content:center;padding:1rem;animation:fadeIn 0.15s ease;}
      .system-modal-box{background:#1a2330;border:1px solid #2e3d52;border-radius:14px;padding:1.5rem;width:min(460px,94vw);box-shadow:0 24px 48px rgba(0,0,0,0.5);position:relative;animation:slideUp 0.18s ease;}
      .system-modal-title{color:#e5eaf2;font-size:1rem;font-weight:700;margin:0 0 0.65rem;}
      .system-modal-message{color:#cbd5e0;font-size:0.85rem;line-height:1.55;margin:0;white-space:pre-line;}
      .system-modal-actions{display:flex;justify-content:flex-end;gap:0.6rem;margin-top:1.25rem;}
      .system-modal-confirm{background:#3ec6ff;color:#0a1520;border:none;border-radius:7px;padding:0.5rem 1.2rem;font-size:0.85rem;font-weight:700;cursor:pointer;}
      .system-modal-confirm:hover{background:#71dbff;}
      body.light-mode .system-modal-box{background:#ffffff!important;border-color:#d1d9e6!important;}
      body.light-mode .system-modal-title{color:#1e293b!important;}
      body.light-mode .system-modal-message{color:#475569!important;}
      .register-modal-overlay{position:fixed;inset:0;background:rgba(10,14,20,0.85);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem;}
      .register-modal-box{background:#1a2330;border:1px solid #2e3d52;border-radius:14px;padding:1.6rem;width:min(760px,96vw);max-height:90vh;overflow-y:auto;box-shadow:0 24px 48px rgba(0,0,0,0.5);position:relative;animation:slideUp 0.18s ease;}
      .register-modal-close{position:absolute;right:1rem;top:1rem;background:none;border:none;color:#64748b;font-size:1.3rem;cursor:pointer;line-height:1;transition:color 0.15s;}
      .register-modal-close:hover{color:#e5eaf2;}
      .reg-input{width:100%;background:#111827;color:#e5eaf2;border:1px solid #2e3748;border-radius:7px;padding:0.5rem 0.7rem;font-size:0.85rem;outline:none;box-sizing:border-box;transition:border-color 0.18s;font-family:inherit;}
      .reg-input:focus{border-color:#3ec6ff;}
      .reg-label{display:block;font-size:0.72rem;font-weight:700;color:#64748b;margin-bottom:0.28rem;text-transform:uppercase;letter-spacing:0.05em;}
      .reg-section{background:#0d1420;border:1px solid #1e2d42;border-radius:9px;padding:1rem;margin-bottom:1rem;}
      .reg-section-title{font-size:0.7rem;font-weight:700;color:#3ec6ff;text-transform:uppercase;letter-spacing:0.09em;margin-bottom:0.7rem;}
      .reg-field-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:0.65rem 0.85rem;}
      .reg-field-grid .span-2{grid-column:1/-1;}
      .reg-submit-btn{background:#3ec6ff;color:#0a1520;border:none;border-radius:8px;padding:0.55rem 1.4rem;font-size:0.87rem;font-weight:800;cursor:pointer;transition:background 0.18s;}
      .reg-submit-btn:hover{background:#71dbff;}
      .reg-submit-btn:disabled{opacity:0.6;cursor:not-allowed;}
      .reg-cancel-btn{background:transparent;color:#94a3b8;border:1px solid #2e3748;border-radius:8px;padding:0.55rem 1.1rem;font-size:0.87rem;font-weight:600;cursor:pointer;transition:all 0.15s;}
      .reg-cancel-btn:hover{background:#1e2a3a;color:#e5eaf2;}
      .email-section{background:#0a1628;border:1px solid #1a3a5c;border-radius:9px;padding:1rem;margin-bottom:1rem;}
      .email-section-title{font-size:0.7rem;font-weight:700;color:#3ec6ff;text-transform:uppercase;letter-spacing:0.09em;margin-bottom:0.55rem;display:flex;align-items:center;gap:6px;}
      .email-manual-row{margin-bottom:0.7rem;}
      .email-divider{display:flex;align-items:center;gap:8px;margin:0.65rem 0;color:#374151;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;}
      .email-divider::before,.email-divider::after{content:'';flex:1;height:1px;background:#1e2d42;}
      .email-gen-controls{display:flex;flex-wrap:wrap;align-items:center;gap:0.4rem;margin-bottom:0.5rem;}
      .email-fmt-btn{background:#0d2240;color:#7dd3fc;border:1px solid #1e4a7a;border-radius:5px;padding:3px 9px;font-size:0.71rem;font-weight:700;cursor:pointer;transition:all 0.15s;line-height:1.5;}
      .email-fmt-btn.active{background:#1a4a8a;color:#3ec6ff;border-color:#3ec6ff;}
      .email-fmt-btn:hover{background:#153366;}
      .email-domain-row{display:flex;align-items:center;gap:0.5rem;margin-bottom:0.55rem;flex-wrap:wrap;}
      .email-domain-label{font-size:0.72rem;color:#64748b;font-weight:600;white-space:nowrap;}
      .email-domain-input{background:#111827;color:#e5eaf2;border:1px solid #2e3748;border-radius:5px;padding:0.3rem 0.5rem;font-size:0.78rem;outline:none;font-family:monospace;width:170px;transition:border-color 0.18s;}
      .email-domain-input:focus{border-color:#3ec6ff;}
      .email-gen-btn{background:#1a4a8a;color:#7dd3fc;border:1px solid #3ec6ff;border-radius:5px;padding:4px 13px;font-size:0.75rem;font-weight:700;cursor:pointer;transition:all 0.15s;white-space:nowrap;}
      .email-gen-btn:hover{background:#1f5eae;}
      .email-preview-wrap{display:flex;align-items:center;gap:0.5rem;}
      .email-preview{flex:1;background:#060e18;border:1px solid #1a3a5c;border-radius:6px;padding:0.45rem 0.7rem;font-size:0.8rem;font-family:monospace;word-break:break-all;min-height:2rem;line-height:1.6;color:#7dd3fc;transition:background 0.18s;}
      .email-preview.empty{color:#374151;font-style:italic;font-family:inherit;font-size:0.75rem;}
      .email-use-btn{background:#0d3325;color:#33b481;border:1px solid #1a5c3a;border-radius:5px;padding:4px 10px;font-size:0.72rem;font-weight:700;cursor:pointer;transition:all 0.15s;white-space:nowrap;display:none;}
      .email-use-btn:hover{background:#154d32;}
      .chart-legend{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:12px;}
      .chart-legend-item{display:flex;align-items:center;gap:6px;font-size:0.72rem;color:#94a3b8;}
      .chart-legend-dot{width:10px;height:10px;border-radius:2px;flex-shrink:0;}

      /* ===== DASHBOARD STATUS CARD COLOR CODING ===== */
      .dashboard-status-card{
        position:relative;
        overflow:hidden;
        border:1px solid #2f3540;
      }
      .dashboard-status-card::before{
        content:'';
        position:absolute;
        top:0;
        left:0;
        right:0;
        height:4px;
        border-radius:8px 8px 0 0;
      }

      /* New = Blue */
      .dashboard-status-new::before{background:#3ec6ff;}
      .dashboard-status-new .dashboard-status-label{color:#3ec6ff!important;}
      .dashboard-status-new .dashboard-status-count{color:#3ec6ff!important;}
      .dashboard-status-new .dashboard-status-desc{color:#7dd3fc!important;}

      /* Renewed = Green */
      .dashboard-status-renewed::before{background:#33b481;}
      .dashboard-status-renewed .dashboard-status-label{color:#33b481!important;}
      .dashboard-status-renewed .dashboard-status-count{color:#33b481!important;}
      .dashboard-status-renewed .dashboard-status-desc{color:#68d391!important;}

      /* Within Renewal = Yellow */
      .dashboard-status-within::before{background:#ecc94b;}
      .dashboard-status-within .dashboard-status-label{color:#ecc94b!important;}
      .dashboard-status-within .dashboard-status-count{color:#ecc94b!important;}
      .dashboard-status-within .dashboard-status-desc{color:#f6e05e!important;}

      /* Expired = Red */
      .dashboard-status-expired::before{background:#e53e3e;}
      .dashboard-status-expired .dashboard-status-label{color:#e53e3e!important;}
      .dashboard-status-expired .dashboard-status-count{color:#e53e3e!important;}
      .dashboard-status-expired .dashboard-status-desc{color:#fc8181!important;}

      /* Pending = Gray */
      .dashboard-status-pending::before{background:#64748b;}
      .dashboard-status-pending .dashboard-status-label{color:#94a3b8!important;}
      .dashboard-status-pending .dashboard-status-count{color:#94a3b8!important;}
      .dashboard-status-pending .dashboard-status-desc{color:#64748b!important;}

      body.light-mode .dashboard-status-card{
        background:#ffffff!important;
        border-color:#e2e8f0!important;
      }
      body.light-mode .dashboard-status-new .dashboard-status-label,
      body.light-mode .dashboard-status-new .dashboard-status-count{color:#0284c7!important;}
      body.light-mode .dashboard-status-new .dashboard-status-desc{color:#0369a1!important;}

      body.light-mode .dashboard-status-renewed .dashboard-status-label,
      body.light-mode .dashboard-status-renewed .dashboard-status-count{color:#047857!important;}
      body.light-mode .dashboard-status-renewed .dashboard-status-desc{color:#15803d!important;}

      body.light-mode .dashboard-status-within .dashboard-status-label,
      body.light-mode .dashboard-status-within .dashboard-status-count{color:#a16207!important;}
      body.light-mode .dashboard-status-within .dashboard-status-desc{color:#b7791f!important;}

      body.light-mode .dashboard-status-expired .dashboard-status-label,
      body.light-mode .dashboard-status-expired .dashboard-status-count{color:#dc2626!important;}
      body.light-mode .dashboard-status-expired .dashboard-status-desc{color:#b91c1c!important;}

      body.light-mode .dashboard-status-pending .dashboard-status-label,
      body.light-mode .dashboard-status-pending .dashboard-status-count{color:#475569!important;}
      body.light-mode .dashboard-status-pending .dashboard-status-desc{color:#64748b!important;}

      body.light-mode{background:#f1f5fa!important;color:#1e293b!important;}
      body.light-mode .main-bg,body.light-mode main{background:#f1f5fa!important;}
      body.light-mode .bg-\[\#23272f\],body.light-mode .bg-\[\#1d232d\]{background-color:#ffffff!important;border:1px solid #e2e8f0!important;box-shadow:0 1px 4px rgba(0,0,0,0.06)!important;}
      body.light-mode table td{background:#ffffff!important;color:#1e293b!important;border-color:#e5e7eb!important;}
      body.light-mode table th{background:#f1f5f9!important;color:#475569!important;border-color:#e5e7eb!important;}
      body.light-mode table tbody tr:hover td{background:#f8fafc!important;}
      body.light-mode #sidebar{background:#f7fafc!important;border-color:#e2e8f0!important;}
      body.light-mode .nav-item{color:#64748b!important;}
      body.light-mode .nav-item:hover,body.light-mode .nav-item.active{background:#e8edf5!important;color:#1e293b!important;}
      body.light-mode .sb-logo-text{color:#1e293b!important;}
      body.light-mode .sb-bottom{border-color:#e2e8f0!important;}
      body.light-mode .theme-btn{background:#e8edf5!important;color:#64748b!important;}
      body.light-mode .theme-btn:hover,body.light-mode .sb-toggle:hover{background:#dbe4ef!important;color:#1e293b!important;}
      body.light-mode .sb-toggle{color:#64748b!important;}
      body.light-mode .card-main-text{color:#0d6641!important;}
      body.light-mode .card-label{color:#1a7a55!important;}
      body.light-mode .card-desc{color:#2d8a65!important;}
      body.light-mode .force-light-text{color:#1e293b!important;}
      body.light-mode input,body.light-mode select,body.light-mode textarea{background:#f8fafc!important;color:#1e293b!important;border-color:#cbd5e1!important;}
      body.light-mode #personnelClearFilters{background:#ffffff!important;color:#475569!important;border-color:#cbd5e1!important;}
      body.light-mode #personnelClearFilters:hover{background:#f8fafc!important;color:#1e293b!important;}
      body.light-mode #searchInput{background:#f0f4f9!important;color:#1e293b!important;border-color:#cbd5e1!important;}
      body.light-mode .badge-new{background:transparent!important;color:#1e40af!important;}
      body.light-mode .badge-renewed{background:transparent!important;color:#047857!important;}
      body.light-mode .badge-within{background:transparent!important;color:#a16207!important;}
      body.light-mode .badge-expired{background:transparent!important;color:#b91c1c!important;}
      body.light-mode .badge-pending{background:transparent!important;color:#475569!important;}
      body.light-mode .report-status-badge{background:transparent!important;border:0!important;border-radius:0!important;padding:0!important;}
      body.light-mode .reg-step-circle{background:#ffffff!important;border-color:#cbd5e1!important;color:#64748b!important;}
      body.light-mode .reg-step-circle.is-active{background:#fff7ed!important;border-color:#d4a017!important;color:#b7791f!important;}
      body.light-mode .reg-step-circle.is-complete{background:#d4a017!important;border-color:#d4a017!important;color:#ffffff!important;}
      body.light-mode .reg-step-line{background:#cbd5e1!important;}
      body.light-mode .reg-step-line.is-complete{background:#d4a017!important;}
      body.light-mode .notification-bell{color:#0284c7!important;}
      body.light-mode #notifDropdown{background:#ffffff!important;border-color:#d0d7e4!important;}
      body.light-mode .notif-item.unread{background:#eff6ff!important;}
      body.light-mode .reg-input{background:#f8fafc!important;color:#1e293b!important;border-color:#cbd5e1!important;}
      body.light-mode .reg-section{background:#f1f5f9!important;border-color:#e2e8f0!important;}
      body.light-mode .reg-label{color:#475569!important;}
  body.light-mode .notify-modal-box,.light-mode .register-modal-box{background:#ffffff!important;border-color:#d1d9e6!important;color:#1e293b!important;}
  body.light-mode .notify-input{background:#f8fafc!important;color:#1f2937!important;border-color:#cbd5e1!important;}
  body.light-mode .notify-label{color:#475569!important;}
  body.light-mode #personnelDetailsOverlay .notify-modal-box h3,
  body.light-mode #personnelDetailsOverlay .notify-modal-box p[style*="e5eaf2"]{color:#1e293b!important;}
  body.light-mode #personnelDetailsOverlay .notify-modal-box .notify-label{color:#475569!important;}
  body.light-mode #personnelDetailsOverlay #pd_name,
  body.light-mode #personnelDetailsOverlay #pd_rank,
  body.light-mode #personnelDetailsOverlay #pd_serial,
  body.light-mode #personnelDetailsOverlay #pd_unit{color:#1e293b!important;}
  body.light-mode #personnelDetailsOverlay #pd_renewalInfo,
  body.light-mode #personnelDetailsOverlay #pd_icsInfo,
  body.light-mode #personnelDetailsOverlay #pd_notes{color:#64748b!important;}
  body.light-mode #personnelDetailsOverlay #pd_renewalInfo span[style*="e5eaf2"],
  body.light-mode #personnelDetailsOverlay #pd_icsInfo span[style*="e5eaf2"]{color:#1e293b!important;}
  body.light-mode #personnelDetailsOverlay .badge-renewed{background:transparent!important;color:#047857!important;}
  body.light-mode #personnelDetailsOverlay [style*="2e3748"]{border-color:#cbd5e1!important;}
  body.light-mode .email-section{background:#eff6ff!important;border-color:#bfdbfe!important;}
      body.light-mode .email-preview{background:#dbeafe!important;color:#1e40af!important;border-color:#93c5fd!important;}
      body.light-mode .email-fmt-btn{background:#dbeafe!important;color:#1e40af!important;border-color:#93c5fd!important;}
      body.light-mode .email-fmt-btn.active{background:#bfdbfe!important;border-color:#3b82f6!important;}
      body.light-mode .email-domain-input{background:#f8fafc!important;color:#1e293b!important;border-color:#93c5fd!important;}
      body.light-mode .quick-nav{background:#ffffff!important;border:1px solid #e2e8f0!important;}
      body.light-mode .quick-nav:hover{background:#f0f4f9!important;}
      body.light-mode .notif-title.expired{color:#c53030!important;}
  body.light-mode .notif-title.within_renewal{color:#b7791f!important;}
  body.light-mode .notif-title.renewed{color:#2f855a!important;}
  body.light-mode .notif-message{color:#475569!important;}
  body.light-mode .notif-time{color:#94a3b8!important;}
  body.light-mode .notif-empty{color:#94a3b8!important;}
  body.light-mode .notif-footer{color:#64748b!important;}
  body.light-mode .notif-header{color:#475569!important;}
  body.light-mode .notif-item{color:#1e293b!important;}
  body.light-mode .notif-item:hover{background:#f8fafc!important;}
      /* ===== ICS PAGE — LIGHT MODE ===== */
  body.light-mode .ics-side-box{background:#f8fafc!important;border-color:#e2e8f0!important;}
  body.light-mode .ics-side-title{color:#475569!important;}
  body.light-mode .ics-mini-input{background:#ffffff!important;color:#1e293b!important;border-color:#cbd5e1!important;}
  body.light-mode .ics-label{color:#475569!important;}
  body.light-mode .ics-btn-secondary{background:#eef2f7!important;color:#475569!important;border-color:#cbd5e1!important;}
  body.light-mode .ics-sig-box{background:#ffffff!important;border-color:#cbd5e1!important;color:#94a3b8!important;}
  body.light-mode .ics-photo-preview{background:#ffffff!important;border-color:#cbd5e1!important;}
  body.light-mode #ics-tbody .ics-ready-pill{background:transparent!important;color:#166534!important;}
  body.light-mode #ics-tbody .ics-ready-pill > span{background:#16a34a!important;}
  body.light-mode #ics-tbody .ics-ready-note{color:#15803d!important;}
  body.light-mode #ics-tbody .ics-inspection-pill{background:transparent!important;color:#a16207!important;}
  body.light-mode #ics-tbody .ics-inspection-pill > span{background:#d97706!important;}
  body.light-mode #ics-tbody .ics-under-pill{background:transparent!important;color:#0369a1!important;}
  body.light-mode #ics-tbody .ics-under-pill > span{background:#0ea5e9!important;}
  body.light-mode #ics-tbody .ics-process-btn{background:#166534!important;color:#ffffff!important;border-color:#166534!important;}
  body.light-mode #ics-tbody .ics-process-btn:hover{background:#15803d!important;}
  body.light-mode #ics-tbody .ics-action-send{background:#fffbeb!important;color:#b77913!important;border-color:#f2cc72!important;}
  body.light-mode #ics-tbody .ics-action-send:hover{background:#fef3c7!important;color:#92400e!important;border-color:#eab308!important;}

  /* ICS summary cards + tab bar (inline-styled, no class) */
  body.light-mode [style*="background:#1c2c18"]{background:#fef9e7!important;border-color:#f0d998!important;}
  body.light-mode [style*="background:#0f1e2e"]{background:#eaf6ff!important;border-color:#bde3ff!important;}
  body.light-mode [style*="background:#0c2418"]{background:#eafaf0!important;border-color:#b8e6c8!important;}

  /* ICS table header bar (inline-styled) */
  body.light-mode [style*="background:#13181e"]{background:#f1f5f9!important;border-color:#e2e8f0!important;}

  /* Renewal page: balanced light-mode status colors */
  body.light-mode #page-renewal > .grid > button:nth-child(1){background:#ecfdf5!important;border-color:#a7f3d0!important;}
  body.light-mode #page-renewal > .grid > button:nth-child(1) > div > div{background:#d1fae5!important;}
  body.light-mode #page-renewal > .grid > button:nth-child(1) span{color:#047857!important;}
  body.light-mode #page-renewal > .grid > button:nth-child(1) p{color:#065f46!important;}

  body.light-mode #page-renewal > .grid > button:nth-child(2){background:#fffbeb!important;border-color:#fcd34d!important;}
  body.light-mode #page-renewal > .grid > button:nth-child(2) > div > div{background:#fef3c7!important;}
  body.light-mode #page-renewal > .grid > button:nth-child(2) span{color:#a16207!important;}
  body.light-mode #page-renewal > .grid > button:nth-child(2) p{color:#a16207!important;}

  body.light-mode #page-renewal > .grid > button:nth-child(3){background:#fef2f2!important;border-color:#fca5a5!important;}
  body.light-mode #page-renewal > .grid > button:nth-child(3) > div > div{background:#fee2e2!important;}
  body.light-mode #page-renewal > .grid > button:nth-child(3) span{color:#dc2626!important;}
  body.light-mode #page-renewal > .grid > button:nth-child(3) p{color:#b91c1c!important;}

  body.light-mode #page-renewal > .grid > button svg{stroke:currentColor!important;}
  body.light-mode #page-renewal #renewaltab-renewed{background:#ecfdf5!important;border-color:#86efac!important;color:#047857!important;}
  body.light-mode #page-renewal #renewaltab-within{background:#fffbeb!important;border-color:#fcd34d!important;color:#a16207!important;}
  body.light-mode #page-renewal #renewaltab-expired{background:#fef2f2!important;border-color:#fca5a5!important;color:#dc2626!important;}
  body.light-mode #page-renewal #renewalNotifyAllBtn{background:#fffbeb!important;color:#a16207!important;border-color:#fcd34d!important;}
  body.light-mode #page-renewal #renewalFootnote span:first-of-type{color:#dc2626!important;}
  body.light-mode #page-renewal #renewalFootnote span:last-of-type{color:#a16207!important;}
  body.light-mode #page-renewal .badge-renewed{background:transparent!important;color:#047857!important;}
  body.light-mode #page-renewal .badge-within{background:transparent!important;color:#a16207!important;}
  body.light-mode #page-renewal .badge-expired{background:transparent!important;color:#dc2626!important;}
  body.light-mode #page-renewal #renewalTbody tr:hover{background:#f8fafc!important;}
  body.light-mode #page-renewal #renewalTbody .renewal-action-renewed{background:#ffffff!important;color:#047857!important;border-color:#34d399!important;}
  body.light-mode #page-renewal #renewalTbody .renewal-action-within{background:#fffbeb!important;color:#a16207!important;border-color:#f59e0b!important;}
  body.light-mode #page-renewal #renewalTbody .renewal-action-expired{background:#fef2f2!important;color:#dc2626!important;border-color:#f87171!important;}
  body.light-mode #page-renewal #renewalTbody tr:has(.badge-within) button{background:#fffbeb!important;color:#a16207!important;border-color:#f59e0b!important;}
  body.light-mode #page-renewal #renewalTbody tr:has(.badge-expired) button{background:#fef2f2!important;color:#dc2626!important;border-color:#f87171!important;}
  body.light-mode #page-renewal #renewalTblPages button{background:#ffffff!important;color:#64748b!important;border-color:#cbd5e1!important;}
  body.light-mode #page-renewal #renewalTblPages button[style*="background:#33b481"]{background:#10b981!important;color:#ffffff!important;border-color:#10b981!important;}
  /* Personnel list: keep the banner, controls, and all status badges balanced */
  body.light-mode #page-personnel .new-status-banner{background:#eaf6ff!important;border-color:#93c5fd!important;color:#0369a1!important;}
  body.light-mode #page-personnel #registerPersonnelBtn{background:#eaf6ff!important;color:#0284c7!important;border-color:#7dd3fc!important;}
  body.light-mode #page-personnel #exportBtn{background:#ecfdf5!important;color:#047857!important;border-color:#86efac!important;}
  body.light-mode #page-personnel .badge-new{background:transparent!important;color:#1d4ed8!important;}
  body.light-mode #page-personnel .badge-renewed{background:transparent!important;color:#047857!important;}
  body.light-mode #page-personnel .badge-within{background:transparent!important;color:#a16207!important;}
  body.light-mode #page-personnel .badge-expired{background:transparent!important;color:#dc2626!important;}
  body.light-mode #page-personnel .badge-pending{background:transparent!important;color:#475569!important;}
  body.light-mode #page-personnel tbody tr:hover{background:#f8fafc!important;}
  body.light-mode #page-personnel #pagination button{background:#ffffff!important;color:#64748b!important;}
  body.light-mode #page-personnel #pagination button.bg-\[\#33b481\]{background:#10b981!important;color:#ffffff!important;}

  /* ICS table rows — text was inline #e5eaf2 / #94a3b8, invisible on white */
  body.light-mode #ics-tbody td[style*="e5eaf2"]{color:#1e293b!important;}
  body.light-mode #ics-tbody td[style*="94a3b8"]{color:#475569!important;}

  /* ===== REGISTRATION WIZARD — LIGHT MODE ===== */
  body.light-mode .border-\[\#2a2d35\]{border-color:#e2e8f0!important;}
  body.light-mode #rp_reviewPersonal span[style*="e5eaf2"],
  body.light-mode #rp_reviewFirearm span[style*="e5eaf2"],
  body.light-mode #rp_reviewRemarks span[style*="e5eaf2"]{color:#1e293b!important;}
  body.light-mode #rp_reviewPersonal div[style*="2a2d35"],
  body.light-mode #rp_reviewFirearm div[style*="2a2d35"],
  body.light-mode #rp_reviewRemarks div[style*="2a2d35"]{border-color:#e2e8f0!important;}
      .new-status-banner{background:linear-gradient(90deg,#0a1f3a 0%,#0d2d4a 100%);border:1px solid #1a4a7a;border-radius:8px;padding:10px 14px;margin-bottom:10px;display:flex;align-items:center;gap:8px;font-size:0.78rem;color:#7dd3fc;}
      .ics-paper{width:min(860px,100%);min-height:860px;background:#fff;border-radius:10px;border:1px solid #d6dde8;box-shadow:0 12px 36px rgba(0,0,0,0.25);overflow:hidden;}
      .ics-paper-grid{min-height:860px;padding:18px 16px 14px;background-color:#fff;background-image:linear-gradient(to right,rgba(21,33,52,0.07) 1px,transparent 1px),linear-gradient(to bottom,rgba(21,33,52,0.07) 1px,transparent 1px);background-size:48px 24px;}
      .ics-header-row{display:flex;align-items:flex-start;gap:6px;}
      .ics-doc-header{flex:1;text-align:center;color:#141b2c;}
      .ics-doc-header .small{font-size:0.88rem;line-height:1.25;font-weight:700;}
      .ics-doc-header .tiny{font-size:0.8rem;line-height:1.25;}
      .ics-doc-header .main-title{font-size:1.85rem;line-height:1.12;margin-top:0.4rem;font-weight:800;letter-spacing:0.02em;}
      .ics-logo{width:70px;height:70px;object-fit:cover;border-radius:9999px;border:2px solid #1e8b46;background:#f8fafc;margin:0 auto 6px;display:block;}
      .ics-photo-2x2{width:152px!important;min-width:152px!important;max-width:152px!important;height:152px!important;min-height:152px!important;max-height:152px!important;border:2px solid #202a39;background:#f0f4f8;border-radius:4px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;align-self:flex-start;box-sizing:border-box;}
      .ics-photo-2x2 img{display:block;width:100%!important;height:100%!important;max-width:152px!important;max-height:152px!important;object-fit:cover!important;object-position:center top;}
      .ics-meta{width:100%;border-collapse:collapse;margin-top:0.7rem;margin-bottom:0.65rem;}
      .ics-meta td{border:1px solid #1f2938;font-size:0.79rem;padding:0.3rem 0.4rem;color:#111827;}
      .ics-meta .label{font-weight:700;text-align:right;width:20%;background:#f6f9fc;}
      .ics-meta .value{font-weight:700;width:30%;}
      .ics-main-table{width:100%;border-collapse:collapse;table-layout:fixed;margin-top:0.35rem;}
      .ics-main-table th,.ics-main-table td{border:1px solid #1f2938;font-size:0.76rem;color:#101826;padding:0.2rem 0.28rem;vertical-align:top;}
      .ics-main-table th{background:#f1f6fb;font-weight:800;text-align:center;vertical-align:middle;}
      .ics-signatures{width:100%;margin-top:0.6rem;border-collapse:collapse;table-layout:fixed;}
      .ics-signatures td{border:1px solid #1f2938;min-height:128px;height:128px;vertical-align:top;padding:0.24rem 0.32rem;color:#101826;font-size:0.74rem;}
      .ics-sign-wrap{height:100%;display:flex;flex-direction:column;justify-content:space-between;}
      .ics-sign-name{text-align:center;font-weight:800;font-size:0.82rem;margin-top:0.8rem;text-transform:uppercase;}
      .ics-sign-sub{text-align:center;font-size:0.72rem;color:#1f2937;font-weight:600;}
      .ics-sign-date{text-align:center;font-size:0.75rem;font-weight:700;margin-bottom:0.2rem;}
      .ics-side-box{background:#1e2430;border:1px solid #2e3748;border-radius:10px;padding:1.2rem 1rem;margin-bottom:1rem;}
      .ics-side-title{color:#94a3b8;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.85rem;}
      .ics-mini-input{width:100%;background:#141820;color:#e5eaf2;border:1px solid #2e3748;border-radius:5px;padding:0.38rem 0.55rem;font-size:0.82rem;outline:none;margin-top:0.22rem;transition:border 0.18s;}
      .ics-mini-input:focus{border-color:#3ec6ff;}
      .ics-label{display:block;font-size:0.75rem;color:#64748b;font-weight:600;margin-top:0.55rem;}
      .ics-btn-primary{background:#3ec6ff;color:#0a0e14;border-radius:6px;font-weight:700;font-size:0.82rem;padding:0.42rem 1rem;border:none;cursor:pointer;width:100%;transition:background 0.18s;}
      .ics-btn-primary:hover{background:#71dbff;}
      .ics-btn-secondary{background:#1e2430;color:#94a3b8;border-radius:6px;font-weight:600;font-size:0.82rem;padding:0.42rem 1rem;border:1px solid #2e3748;cursor:pointer;width:100%;margin-top:0.5rem;transition:background 0.18s;}
      .ics-btn-secondary:hover{background:#252d3d;}
      .ics-sig-box{border:1.5px dashed #2e3748;border-radius:6px;background:#141820;min-height:40px;margin-top:0.5rem;margin-bottom:0.3rem;color:#4b5a72;display:flex;align-items:center;justify-content:center;font-size:0.8rem;}
      .ics-photo-preview{border-radius:8px;border:2px dashed #2e3748;background:#141820;aspect-ratio:1/1;width:120px;margin:0 auto 0.75rem auto;display:flex;align-items:center;justify-content:center;overflow:hidden;}
      .ics-photo-preview img{width:100%;height:100%;object-fit:cover;}
      .ics-status-card{background:#101923;border:1px solid #223043;border-radius:10px;padding:1rem;cursor:pointer;transition:border-color .18s,transform .18s,background .18s;}
      .ics-status-card:hover{transform:translateY(-1px);border-color:#3a4b65;background:#122031;}
      .ics-status-card.active{border-color:#d4a017;box-shadow:0 0 0 1px rgba(212,160,23,.28);}
      .ics-pill{display:inline-flex;align-items:center;border-radius:999px;padding:2px 9px;font-size:.68rem;font-weight:800;line-height:1.35;}
      .ics-pill-for{background:#2c2208;color:#f4b63f;}
      .ics-pill-under{background:#102844;color:#75b7ff;}
      .ics-pill-ready{background:#12321f;color:#64d485;}
      .ics-ready-pill{background:transparent!important;padding:0!important;border-radius:0!important;}
      .ics-inspection-pill{background:transparent!important;padding:0!important;border-radius:0!important;}
      .ics-under-pill{background:transparent!important;padding:0!important;border-radius:0!important;}
      .ics-action-btn{border-radius:6px;padding:.38rem .7rem;font-size:.72rem;font-weight:800;cursor:pointer;transition:opacity .15s,background .15s;}
      .ics-action-send{background:#241b07;color:#f4b63f;border:1px solid #b77913;}
      .ics-action-send:hover{background:#33270a;}
      .ics-action-process{background:#062414;color:#3dff7f;border:1px solid #00d94b;}
      .ics-action-btn:disabled{opacity:.6;cursor:not-allowed;}
      .ics-doc-toolbar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;position:sticky;top:54px;z-index:350;background:#1a2025;padding:0.55rem 0;border-bottom:1px solid #252b32;}
      .ics-doc-toolbar-actions{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:0.5rem;margin-left:auto;min-width:0;}
      #icsPersonnelSearch{width:17rem;max-width:100%;min-width:0;box-sizing:border-box;}
      .ics-print-btn{flex:0 0 auto;white-space:nowrap;}
      body.light-mode .ics-doc-toolbar{background:#f4f7fb!important;border-bottom-color:#dbe3ed!important;}
      @media(max-width:1050px){#ics-layout{flex-direction:column!important;}#ics-panel{width:100%!important;min-width:unset!important;}}
      @media(max-width:760px){.ics-doc-toolbar{align-items:stretch}.ics-doc-toolbar>div:first-child{width:100%}.ics-doc-toolbar-actions{width:100%;margin-left:0;justify-content:flex-start}.ics-doc-toolbar-actions #icsPersonnelSearch{flex:1 1 210px;width:auto}.ics-print-btn{flex:0 0 auto;}}
      @media(max-width:480px){.ics-doc-toolbar-actions{display:grid;grid-template-columns:1fr}.ics-doc-toolbar-actions #icsPersonnelSearch,.ics-print-btn{width:100%;justify-content:center;}}
      @media print{
        @page{size:A4 portrait;margin:0;}
        html,body{margin:0!important;padding:0!important;background:#fff!important;}
        body > div{display:block!important;width:210mm!important;max-width:210mm!important;min-width:210mm!important;margin:0!important;padding:0!important;}
        body > div > aside,body > div > main > *{display:none!important;}
        body > div > main{display:block!important;width:210mm!important;max-width:210mm!important;min-width:210mm!important;margin:0!important;padding:0!important;overflow:visible!important;}
        body > div > main > #page-ics{display:block!important;width:210mm!important;max-width:210mm!important;min-width:210mm!important;margin:0!important;padding:0!important;}
        #page-ics > #ics-doc-view{display:block!important;}
        #page-ics > #ics-list-view{display:none!important;}
        #page-ics,#page-ics *{visibility:visible!important;}
        #ics-doc-view{display:block!important;position:static!important;margin:0!important;padding:0!important;}
        #ics-doc-view .no-print,#ics-doc-view #ics-panel{display:none!important;}
        #ics-layout{display:block!important;margin:0!important;padding:0!important;}
        #ics-layout > div:first-child{display:block!important;width:210mm!important;max-width:210mm!important;margin:0!important;}
        #ics-doc-view .ics-paper{display:block!important;width:210mm!important;max-width:210mm!important;min-height:0!important;height:auto!important;margin:0!important;border:0!important;border-radius:0!important;box-shadow:none!important;overflow:visible!important;}
        #ics-doc-view .ics-paper-grid{width:210mm!important;min-height:0!important;padding:10mm!important;background-image:none!important;box-sizing:border-box!important;}
      }
      [contenteditable="true"]:focus {
    outline: 1px solid #3ec6ff;
    background: rgba(62,198,255,0.06);
  }
  [contenteditable="true"]:hover {
    background: rgba(62,198,255,0.03);
    cursor: text;
  }
    

      /* ===== COMPLETE LIGHT-MODE CONTRAST FIX ===== */
      body.light-mode main .page-section:not(#page-ics) [style*="color:#e5eaf2"],
      body.light-mode main .page-section:not(#page-ics) [style*="color: #e5eaf2"],
      body.light-mode main .page-section:not(#page-ics) [style*="color:#eef2f7"],
      body.light-mode main .page-section:not(#page-ics) [style*="color:#edf2f7"],
      body.light-mode main .page-section:not(#page-ics) [style*="color:#d6dde7"],
      body.light-mode main .page-section:not(#page-ics) .text-white {
        color:#1e293b!important;
      }
      body.light-mode main .page-section [style*="color:#94a3b8"],
      body.light-mode main .page-section [style*="color: #94a3b8"],
      body.light-mode main .page-section .text-\[\#94a3b8\],
      body.light-mode main .page-section .text-\[\#b0bac7\] {
        color:#475569!important;
      }
      body.light-mode main .page-section [style*="color:#64748b"],
      body.light-mode main .page-section [style*="color: #64748b"],
      body.light-mode main .page-section .text-\[\#64748b\] {
        color:#64748b!important;
      }
      body.light-mode main .page-section [style*="color:#4b5563"],
      body.light-mode main .page-section [style*="color: #4b5563"] {
        color:#64748b!important;
      }

      /* Dark inline panels used by registration, upload areas, and status cards */
      body.light-mode main .page-section [style*="background:#13151a"],
      body.light-mode main .page-section [style*="background: #13151a"],
      body.light-mode main .page-section [style*="background:#1e2128"],
      body.light-mode main .page-section [style*="background:#1b1f26"],
      body.light-mode main .page-section [style*="background:#111827"],
      body.light-mode main .page-section [style*="background:#0d1117"] {
        background:#f8fafc!important;
        border-color:#cbd5e1!important;
      }
      body.light-mode main .page-section [style*="border:1px solid #2a2d35"],
      body.light-mode main .page-section [style*="border:2px dashed #2a2d35"],
      body.light-mode main .page-section [style*="border:1px solid #2e3748"] {
        border-color:#cbd5e1!important;
      }

      /* Registration wizard headings, side card, review page and signature modal */
      body.light-mode #page-registration .bg-\[\#23272f\] {background:#ffffff!important;border-color:#e2e8f0!important;}
      body.light-mode #page-registration div[style*="font-weight:700"][style*="color:#e5eaf2"],
      body.light-mode #page-registration span[style*="font-weight:700"][style*="color:#e5eaf2"] {color:#1e293b!important;}
      body.light-mode #page-registration #regLabel1,
      body.light-mode #page-registration #regLabel2,
      body.light-mode #page-registration #regLabel3,
      body.light-mode #page-registration #regLabel5 {color:#475569!important;}
      body.light-mode #page-registration #regStep1 .is-active + div,
      body.light-mode #page-registration [id^="regStep"] .is-active + div {color:#a16207!important;}
      body.light-mode #rp_sigModal > div {background:#ffffff!important;border-color:#cbd5e1!important;}
      body.light-mode #rp_sigModal h3 {color:#1e293b!important;}
      body.light-mode #rp_sigCanvas {background:#f8fafc!important;border-color:#cbd5e1!important;}
      body.light-mode #rp_cameraModal > div {background:#ffffff!important;border-color:#cbd5e1!important;}
      body.light-mode #rp_cameraModal h3 {color:#1e293b!important;}
      body.light-mode #rpAttachPhotoBtn {background:#e2e8f0!important;border-color:#94a3b8!important;color:#1e293b!important;}
      #rpAttachPhotoBtn:hover {background:#475569!important;border-color:#94a3b8!important;}
      body.light-mode #rpAttachPhotoBtn:hover {background:#cbd5e1!important;border-color:#64748b!important;}

      /* Modals: headings, helper boxes, buttons and read-only content */
      body.light-mode .register-modal-box h3,
      body.light-mode .notify-modal-box h3,
      body.light-mode .register-modal-box [style*="color:#e5eaf2"],
      body.light-mode .notify-modal-box [style*="color:#e5eaf2"] {color:#1e293b!important;}
      body.light-mode .register-modal-box [style*="color:#64748b"],
      body.light-mode .notify-modal-box [style*="color:#64748b"],
      body.light-mode .register-modal-box [style*="color:#94a3b8"],
      body.light-mode .notify-modal-box [style*="color:#94a3b8"] {color:#475569!important;}
      body.light-mode #notifyModalOverlay [style*="background:#111827"] {background:#f8fafc!important;}
      body.light-mode .notify-cancel-btn,
      body.light-mode .reg-cancel-btn {background:#ffffff!important;color:#475569!important;border-color:#cbd5e1!important;}
      body.light-mode .notify-cancel-btn:hover,
      body.light-mode .reg-cancel-btn:hover {background:#f1f5f9!important;color:#1e293b!important;}

      /* ICS application UI only; do not alter the white printable ICS paper */
      body.light-mode #page-ics #ics-list-view [style*="color:#e5eaf2"],
      body.light-mode #page-ics #ics-doc-view .no-print [style*="color:#e5eaf2"] {color:#1e293b!important;}
      body.light-mode #page-ics #ics-list-view [style*="color:#64748b"],
      body.light-mode #page-ics #ics-doc-view .no-print [style*="color:#64748b"] {color:#475569!important;}
      body.light-mode #page-ics #ics-panel [style*="background:#0d1117"] {background:#ffffff!important;}
      body.light-mode #page-ics #ics-panel [style*="color:#4b5a72"] {color:#64748b!important;}
      body.light-mode #ics-doc-view .no-print button[style*="background:#1a2025"] {background:#ffffff!important;color:#475569!important;border-color:#cbd5e1!important;}

      /* Profile and generic card headings */
      body.light-mode .chart-legend-item {color:#475569!important;}
      body.light-mode .text-gray-400 {color:#64748b!important;}

      /* ===== STAFF REPORT CENTER / RPCSP ===== */
      .staff-report-tabs{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;}
      .staff-report-tab{display:inline-flex;align-items:center;gap:8px;border:1px solid #363b48;background:#23272f;color:#94a3b8;border-radius:8px;padding:10px 15px;font-size:.78rem;font-weight:700;cursor:pointer;transition:.15s;}
      .staff-report-tab:hover{border-color:#3ec6ff;color:#e5eaf2;}
      .staff-report-tab.active{background:#0d2d3a;border-color:#3ec6ff;color:#3ec6ff;}
      .staff-report-panel{display:none;}
      .staff-report-panel.active{display:block;}

      .rpcsp-config-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;}
      .rpcsp-field label{display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:5px;}
      .rpcsp-field input,.rpcsp-field select{width:100%;background:#1a2025;color:#e5eaf2;border:1px solid #363b48;border-radius:7px;padding:8px 10px;font-size:.78rem;outline:none;}
      .rpcsp-field input:focus,.rpcsp-field select:focus{border-color:#3ec6ff;box-shadow:0 0 0 2px rgba(62,198,255,.08);}
      .rpcsp-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px;}
      .rpcsp-btn{border-radius:7px;padding:9px 14px;font-size:.76rem;font-weight:800;cursor:pointer;border:1px solid transparent;transition:.15s;}
      .rpcsp-btn-preview{background:#0a1f2d;color:#3ec6ff;border-color:#1a3a4f;}
      .rpcsp-btn-preview:hover{background:#112840;}
      .rpcsp-btn-print{background:#0d3325;color:#33b481;border-color:#1a5c3a;}
      .rpcsp-btn-print:hover{background:#154d32;}
      .rpcsp-btn-export{background:#2d2208;color:#ecc94b;border-color:#6b5515;}
      .rpcsp-btn-export:hover{background:#3b2d0a;}

      .rpcsp-preview-shell{overflow:auto;background:#11161d;border:1px solid #303947;border-radius:10px;padding:18px;}
      .rpcsp-document{width:1120px;min-height:710px;margin:0 auto;background:#fff;color:#111;padding:18px 20px 22px;font-family:Arial,Helvetica,sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.24);}
      .rpcsp-title{text-align:center;font-weight:700;font-size:15px;line-height:1.15;margin:0;}
      .rpcsp-subtitle{text-align:center;font-size:11px;line-height:1.25;margin:1px 0;}
      .rpcsp-asof{text-align:center;font-size:11px;margin:2px 0 12px;}
      .rpcsp-fund{font-size:11px;text-decoration:underline;margin:0 0 12px;}
      .rpcsp-forwhich{font-size:11px;line-height:1.35;margin:0 0 9px;}
      .rpcsp-forwhich .accountable-name{font-weight:700;text-transform:uppercase;}
      .rpcsp-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:9.5px;}
      .rpcsp-table th,.rpcsp-table td{border:1px solid #333;padding:4px 5px;vertical-align:middle;}
      .rpcsp-table th{text-align:center;font-weight:700;background:#fff;color:#111;}
      .rpcsp-table td{text-align:center;background:#fff;color:#111;}
      .rpcsp-table td:nth-child(1),.rpcsp-table td:nth-child(2),.rpcsp-table td:nth-child(10){text-align:left;}
      .rpcsp-empty{text-align:center!important;padding:14px!important;color:#666!important;}
      .rpcsp-summary{display:flex;justify-content:flex-end;gap:28px;margin-top:9px;font-size:10px;color:#111;}
      .rpcsp-summary strong{font-size:11px;}
      .rpcsp-note{font-size:9px;color:#555;margin-top:8px;}
      .rpcsp-certification{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:52px;margin-top:42px;font-size:11px;line-height:1.35;color:#111;}
      .rpcsp-certification-title{font-weight:700;margin:0 0 42px;}
      .rpcsp-certification-column{break-inside:avoid;page-break-inside:avoid;}
      .rpcsp-signatory{break-inside:avoid;page-break-inside:avoid;}
      .rpcsp-signatory + .rpcsp-signatory{margin-top:40px;}
      .rpcsp-signatory p{margin:0;}
      .rpcsp-signatory-name{font-weight:700;}

      body.light-mode .staff-report-tab{background:#fff!important;border-color:#cbd5e1!important;color:#64748b!important;}
      body.light-mode .staff-report-tab.active{background:#eaf6ff!important;border-color:#38bdf8!important;color:#0369a1!important;}
      body.light-mode .rpcsp-field label{color:#475569!important;}
      body.light-mode .rpcsp-field input,body.light-mode .rpcsp-field select{background:#f8fafc!important;color:#1e293b!important;border-color:#cbd5e1!important;}
      body.light-mode .rpcsp-preview-shell{background:#e9eef5!important;border-color:#d8e0eb!important;}
      body.light-mode .rpcsp-document,body.light-mode .rpcsp-document *{color:#111!important;}
      body.light-mode .rpcsp-document,.rpcsp-document{background:#fff!important;}

      @media(max-width:1100px){.rpcsp-config-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
      @media(max-width:640px){.rpcsp-config-grid{grid-template-columns:1fr;}}

</style>
    <link rel="stylesheet" href="/css/typography.css">
  <link rel="stylesheet" href="/css/dashboard-theme.css?v=<?php echo filemtime(dirname(__DIR__) . '/public/css/dashboard-theme.css'); ?>">
  <script src="/js/dashboard-lists.js" defer></script>
</head>
  <body class="app-dashboard light-mode min-h-screen font-inter main-bg bg-[#1a2025]">
  <?php
    $staffInitialPersonnel = collect($initialDashboardData['personnel'] ?? []);
    $staffIcsCounts = $staffInitialPersonnel->countBy(fn ($person) => $person['icsStatus'] ?? 'inspection');
    $staffRenewalCounts = $staffInitialPersonnel->countBy(fn ($person) => $person['approvedStatus'] ?? 'pending');
  ?>
  <div class="flex min-h-screen">

    
    <aside id="sidebar">
      <div class="sb-top">
        <div class="sb-logo">
          <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Logo" onerror="this.src=''">
          <span class="sb-logo-text">Staff</span>
        </div>
        <button class="sb-toggle" id="sidebarToggleBtn" title="Toggle sidebar">
          <svg id="sb-icon-menu" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg id="sb-icon-close" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <nav class="sb-nav" id="mainNav">
        <button data-page="dashboard" class="nav-link nav-item active">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          <span class="nav-label">Dashboard</span>
        </button>
        <button data-page="registration" class="nav-link nav-item">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          <span class="nav-label">New Registration</span>
        </button>
        <button data-page="personnel" class="nav-link nav-item">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          <span class="nav-label">List of Personnel</span>
        </button>
        <button data-page="ics" class="nav-link nav-item">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
          <span class="nav-label">ICS</span>
        </button>
        <button data-page="par" class="nav-link nav-item">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h8M9 17h6"/></svg>
          <span class="nav-label">PAR</span>
        </button>
        <button data-page="renewal" class="nav-link nav-item">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.13-3.36L23 10M1 14l5.36 4.36A9 9 0 0020.49 15"/></svg>
    <span class="nav-label">Renewal</span>
  </button>
        <button data-page="report" class="nav-link nav-item">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
          <span class="nav-label">Report</span>
        </button>
        <button data-page="archive" class="nav-link nav-item">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 8v13H3V8"/><path d="M23 3H1v5h22V3z"/><path d="M10 12h4"/></svg>
          <span class="nav-label">Archive Data</span>
        </button>
      </nav>
      <div class="sb-bottom">
        <button class="theme-btn" id="themeToggle" title="Toggle theme">
          <svg id="icon-sun" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
          <svg id="icon-moon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none"><path d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/></svg>
        </button>
      </div>
    </aside>

    <main class="flex-1 main-bg bg-[#1a2025] p-4 overflow-y-auto">

      
      <header class="staff-topbar flex flex-wrap justify-between mb-2 items-center gap-4 min-h-10">
        <div class="staff-topbar-inner">
  <div class="relative" id="notifWrapper">
    <button id="notificationBell" class="notification-bell text-cyan-400 focus:outline-none" aria-label="Notifications" type="button">
      <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11c0-3.074-1.64-5.64-5-5.996V5a2 2 0 10-4 0v.004C6.64 5.36 5 7.926 5 11v3.159c0 .538-.214 1.055-.595 1.436L3 17h5m7 0v1a3 3 0 01-6 0v-1m7 0H8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <span class="notification-badge" id="notificationBadge">0</span>
    </button>
    <div id="notifDropdown">
      <div class="notif-header"><span>Notifications</span><span class="notif-mark-read" id="markAllRead">Mark all read</span></div>
      <div class="notif-list" id="notifList"><div class="notif-empty">Loading notifications...</div></div>
      <div class="notif-footer" id="notifFooter">Auto-refreshes every 15 seconds</div>
    </div>
  </div>

  <?php echo $__env->make('partials.account_dropdown', [
    'profileUrl' => route('staff.profile.update'),
    'passwordUrl' => route('staff.profile.password'),
  ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
      </header>

      
      <div id="page-dashboard" class="page-section active">
        <div class="grid lg:grid-cols-6 md:grid-cols-3 grid-cols-2 gap-6 mb-7">
          <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
            <div class="text-xs uppercase font-semibold card-label tracking-wide mb-1">Total Personnel</div>
            <p id="totalPersonnel" class="text-3xl font-extrabold card-main-text">--</p>
            <p class="text-xs card-desc">All personnel records.</p>
          </div>
          <div class="dashboard-status-card dashboard-status-new bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
            <div class="dashboard-status-label text-xs uppercase font-semibold tracking-wide mb-1">New</div>
            <p id="totalNew" class="dashboard-status-count text-3xl font-extrabold">--</p>
            <p class="dashboard-status-desc text-xs">Awaiting admin inspection.</p>
          </div>
          <div class="dashboard-status-card dashboard-status-renewed bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
            <div class="dashboard-status-label text-xs uppercase font-semibold tracking-wide mb-1">Renewed</div>
            <p id="totalRenewed" class="dashboard-status-count text-3xl font-extrabold">--</p>
            <p class="dashboard-status-desc text-xs">Approved as renewed.</p>
          </div>
          <div class="dashboard-status-card dashboard-status-within bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
            <div class="dashboard-status-label text-xs uppercase font-semibold tracking-wide mb-1">Within Renewal</div>
            <p id="withinRenewal" class="dashboard-status-count text-3xl font-extrabold">--</p>
            <p class="dashboard-status-desc text-xs">Within renewal period.</p>
          </div>
          <div class="dashboard-status-card dashboard-status-expired bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
            <div class="dashboard-status-label text-xs uppercase font-semibold tracking-wide mb-1">Expired</div>
            <p id="expired" class="dashboard-status-count text-3xl font-extrabold">--</p>
            <p class="dashboard-status-desc text-xs">Approved as expired.</p>
          </div>
          <div class="dashboard-status-card dashboard-status-pending bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 flex flex-col gap-2">
            <div class="dashboard-status-label text-xs uppercase font-semibold tracking-wide mb-1">Pending</div>
            <p id="pending" class="dashboard-status-count text-3xl font-extrabold">--</p>
            <p class="dashboard-status-desc text-xs">Awaiting admin approval.</p>
          </div>
        </div>

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
            <h3 class="font-semibold text-base force-light-text tracking-tight mb-3">Personnel Distribution</h3>
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-7">
          <button data-page="personnel" class="quick-nav bg-[#23272f] rounded-lg p-5 text-left hover:bg-[#2a3040] transition-colors shadow shadow-black/10">
            <div class="flex items-center gap-3 mb-2"><div class="w-9 h-9 rounded-lg bg-[#0d2d3a] flex items-center justify-center"><svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg></div><span class="font-semibold force-light-text">List of Personnel</span></div>
            <p class="text-xs card-desc">View and register new personnel records.</p>
          </button>
          <button data-page="report" class="quick-nav bg-[#23272f] rounded-lg p-5 text-left hover:bg-[#2a3040] transition-colors shadow shadow-black/10">
            <div class="flex items-center gap-3 mb-2"><div class="w-9 h-9 rounded-lg bg-[#1a2d1a] flex items-center justify-center"><svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 17H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2h-2M9 17v4h6v-4M9 17h6"/></svg></div><span class="font-semibold force-light-text">Report</span></div>
            <p class="text-xs card-desc">Generate and export renewal reports.</p>
          </button>
          <button data-page="archive" class="quick-nav bg-[#23272f] rounded-lg p-5 text-left hover:bg-[#2a3040] transition-colors shadow shadow-black/10">
            <div class="flex items-center gap-3 mb-2"><div class="w-9 h-9 rounded-lg bg-[#2d1a0a] flex items-center justify-center"><svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg></div><span class="font-semibold force-light-text">Archive Data</span></div>
            <p class="text-xs card-desc">Access archived personnel and records.</p>
          </button>
        </div>
      </div>

      
      <div id="page-personnel" class="page-section">
        <div class="new-status-banner mb-4">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
          <span>As <strong>Staff</strong>, you can register new personnel. Their status will be set to <strong>New</strong> and forwarded to the Admin for manual inspection and final approval.</span>
        </div>
        <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 mb-10">
          <div class="flex flex-wrap items-center justify-between mb-4 gap-2">
            <h2 class="font-semibold text-base force-light-text tracking-tight">List of Personnel</h2>
            <div class="flex gap-2 items-center flex-wrap">
              <input id="personnelNameFilter" type="search" placeholder="Name" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text">
              <select id="personnelRankFilter" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text">
                <option value="">All Ranks</option><option>PVT</option><option>PFC</option><option>CPL</option><option>SGT</option><option>SSG</option><option>SFC</option><option>TSG</option><option>MSG</option><option>1SG</option><option>SGM</option><option>2LT</option><option>1LT</option><option>CPT</option><option>MAJ</option><option>LTC</option><option>COL</option><option>BGen</option><option>Cpl (OS) PA</option><option>Sgt (OS) PA</option>
              </select>
              <input id="personnelUnitFilter" type="search" placeholder="Unit / Office" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text">
              <label for="approvalFilter" class="text-[#b0bac7] text-xs">Status:</label>
              <select id="approvalFilter" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text">
                <option value="">All</option>
                <option value="new">New</option>
                <option value="pending">Pending</option>
                <option value="within">Within Renewal</option>
                <option value="renewed">Renewed</option>
                <option value="expired">Expired</option>
              </select>
              <button id="personnelClearFilters" type="button" class="bg-[#1a2025] text-[#94a3b8] border border-[#363b48] rounded px-3 py-1 text-xs">Clear Filters</button>
              <label for="sortSelect" class="text-[#b0bac7] text-xs">Sort:</label>
              <select id="sortSelect" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-2 py-1 text-xs force-light-text">
                <option value="status-asc" selected>Status (New to Expired)</option>
                <option value="itemNumber-desc">Item # (Desc)</option>
                <option value="lastName-asc">Last Name (A-Z)</option>
                <option value="lastName-desc">Last Name (Z-A)</option>
                <option value="dateOfValidity-asc">Validity (Earliest)</option>
                <option value="dateOfValidity-desc">Validity (Latest)</option>
              </select>
              <button id="exportBtn" class="bg-[#0d3325] text-[#33b481] border border-[#1a5c3a] rounded px-3 py-1 text-xs font-semibold hover:bg-[#154d32] transition-colors">Export Excel</button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-xs text-left force-light-text">
              <thead>
                <tr class="border-b border-[#252b32] text-[#b0bac7]">
                  <th class="py-2 px-2 font-semibold">Item #</th>
                  <th class="py-2 px-2 font-semibold">Date of Validity</th>
                  <th class="py-2 px-2 font-semibold">Last Name | Rank</th>
                  <th class="py-2 px-2 font-semibold">First Name</th>
                  <th class="py-2 px-2 font-semibold">Middle Name</th>
                  <th class="py-2 px-2 font-semibold">AFP Serial #</th>
                  <th class="py-2 px-2 font-semibold">Date of Birth</th>
                  <th class="py-2 px-2 font-semibold">Nomenclature of Pistol</th>
                  <th class="py-2 px-2 font-semibold">Pistol Serial #</th>
                  <th class="py-2 px-2 font-semibold">Qty Ammo</th>
                  <th class="py-2 px-2 font-semibold">Status</th>
                </tr>
              </thead>
              <tbody id="personnelTableBody"></tbody>
            </table>
          </div>
          <div class="flex items-center justify-between mt-4 flex-wrap gap-2">
            <span id="tableInfo" class="text-xs text-[#64748b]">Showing 0 records</span>
            <div class="flex gap-1" id="pagination"></div>
          </div>
        </div>
      </div>

    
      <div id="page-ics" class="page-section">

        
        <div id="ics-list-view">
          <div class="flex flex-wrap items-start justify-between mb-5 gap-3 no-print">
            <div>
              <h2 class="font-semibold text-base force-light-text tracking-tight">ICS / Personnel List</h2>
              <p class="text-xs text-[#64748b] mt-0.5">Manage and process the Individual Clearance Sheets (ICS) of personnel. Send for inspection to Admin and process ICS once inspection is approved.</p>
            </div>
          </div>

          
          <div class="grid grid-cols-3 gap-4 mb-5 no-print">
            <button onclick="icsSetTab('inspection')" class="text-left rounded-xl p-4 border" style="background:#1c2c18;border-color:#3a2800;">
              <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;background:#2c2000;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <svg width="16" height="16" fill="none" stroke="#d4a017" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                </div>
                <span style="font-size:0.7rem;font-weight:700;color:#d4a017;text-transform:uppercase;letter-spacing:0.06em;">For Inspection</span>
              </div>
              <p id="ics-sum-inspection" style="font-size:1.8rem;font-weight:800;color:#d4a017;margin:0 0 2px;"><?php echo e($staffIcsCounts->get('inspection', 0)); ?></p>
              <p style="font-size:0.68rem;color:#7a6020;margin:0;">Waiting to be sent to Admin</p>
            </button>
            <button onclick="icsSetTab('under')" class="text-left rounded-xl p-4 border" style="background:#0f1e2e;border-color:#1a3a5c;">
              <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;background:#0a1f38;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <svg width="16" height="16" fill="none" stroke="#3ec6ff" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </div>
                <span style="font-size:0.7rem;font-weight:700;color:#3ec6ff;text-transform:uppercase;letter-spacing:0.06em;">Under Inspection</span>
              </div>
              <p id="ics-sum-under" style="font-size:1.8rem;font-weight:800;color:#3ec6ff;margin:0 0 2px;"><?php echo e($staffIcsCounts->get('under', 0)); ?></p>
              <p style="font-size:0.68rem;color:#1e5070;margin:0;">Under Admin Inspection</p>
            </button>
            <button onclick="icsSetTab('ready')" class="text-left rounded-xl p-4 border" style="background:#0c2418;border-color:#1a5c3a;">
              <div class="flex items-center gap-2 mb-2">
                <div style="width:32px;height:32px;background:#0c2e1a;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <svg width="16" height="16" fill="none" stroke="#33b481" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span style="font-size:0.7rem;font-weight:700;color:#33b481;text-transform:uppercase;letter-spacing:0.06em;">Ready for Renewal</span>
              </div>
              <p id="ics-sum-ready" style="font-size:1.8rem;font-weight:800;color:#33b481;margin:0 0 2px;"><?php echo e($staffIcsCounts->get('ready', 0)); ?></p>
              <p style="font-size:0.68rem;color:#1a5c3a;margin:0;">Inspection passed. Ready for ICS processing</p>
            </button>
          </div>

          <div class="flex justify-end mb-3 no-print">
            <input id="icsListSearch" type="text" placeholder="Search by name / AFP serial…"
              class="bg-[#23272f] text-white border border-[#363b48] rounded px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-accent w-64 force-light-text" />
          </div>

          
          <div class="bg-[#23272f] rounded-lg shadow shadow-black/10 overflow-hidden no-print">
            <div class="overflow-x-auto">
              <table class="w-full text-xs text-left force-light-text" style="border-collapse:collapse;">
                <thead>
                  <tr style="background:#13181e;border-bottom:1px solid #23272f;">
                    <th class="py-2 px-3 font-semibold text-[#64748b]" style="text-transform:uppercase;letter-spacing:0.05em;font-size:0.68rem;">Name</th>
                    <th class="py-2 px-3 font-semibold text-[#64748b]" style="text-transform:uppercase;letter-spacing:0.05em;font-size:0.68rem;">AFP Serial #</th>
                    <th class="py-2 px-3 font-semibold text-[#64748b]" style="text-transform:uppercase;letter-spacing:0.05em;font-size:0.68rem;">Rank</th>
                    <th class="py-2 px-3 font-semibold text-[#64748b]" style="text-transform:uppercase;letter-spacing:0.05em;font-size:0.68rem;">Unit / Organization</th>
                    <th class="py-2 px-3 font-semibold text-[#64748b]" style="text-transform:uppercase;letter-spacing:0.05em;font-size:0.68rem;">Pistol</th>
                    <th class="py-2 px-3 font-semibold text-[#64748b]" style="text-transform:uppercase;letter-spacing:0.05em;font-size:0.68rem;">Status</th>
                    <th class="py-2 px-3 font-semibold text-[#64748b]" style="text-transform:uppercase;letter-spacing:0.05em;font-size:0.68rem;" data-ics-column="result">Inspection Result</th>
                    <th class="py-2 px-3 font-semibold text-[#64748b]" style="text-transform:uppercase;letter-spacing:0.05em;font-size:0.68rem;">Date Updated</th>
                    <th class="py-2 px-3 font-semibold text-[#64748b]" style="text-transform:uppercase;letter-spacing:0.05em;font-size:0.68rem;" data-ics-column="action">Action</th>
                  </tr>
                </thead>
                <tbody id="ics-tbody">
                  <?php $__currentLoopData = $staffInitialPersonnel->where('icsStatus', 'inspection')->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr style="border-bottom:1px solid #1a2025;">
                      <td class="py-2 px-3 force-light-text"><?php echo e(trim(($person['lastName'] ?? '').', '.($person['firstName'] ?? '').' '.($person['middleName'] ?? ''))); ?></td>
                      <td class="py-2 px-3 force-light-text"><?php echo e($person['afpSerialNumber'] ?? '—'); ?></td>
                      <td class="py-2 px-3 force-light-text"><?php echo e($person['rank'] ?? '—'); ?></td>
                      <td class="py-2 px-3 force-light-text"><?php echo e($person['unit'] ?? '—'); ?></td>
                      <td class="py-2 px-3 force-light-text"><?php echo e($person['pistolNomenclature'] ?? '—'); ?></td>
                      <td class="py-2 px-3"><span style="color:#d4a017;font-weight:700;">For Inspection</span></td>
                      <td class="py-2 px-3 force-light-text"><?php echo e($person['inspectionResult'] ?? '—'); ?></td>
                      <td class="py-2 px-3 force-light-text"><?php echo e($person['inspectionUpdatedAt'] ?? '—'); ?></td>
                      <td class="py-2 px-3"><button class="ics-action-btn ics-action-send" onclick="icsSendForInspection(<?php echo e(Js::from($person['itemNumber'])); ?>, this)">Send for Inspection</button></td>
                    </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
              </table>
            </div>
            <div class="flex items-center justify-between px-4 py-3 flex-wrap gap-2" style="border-top:1px solid #1e2530;">
              <span id="ics-tbl-info" class="text-xs text-[#64748b]"></span>
              <div id="ics-tbl-pages" class="flex gap-1"></div>
            </div>
          </div>
        </div>

        
        <div id="ics-doc-view" style="display:none;">
          <div class="ics-doc-toolbar mb-5 no-print">
            <div class="flex items-center gap-3">
              <button onclick="icsShowList()" style="display:inline-flex;align-items:center;gap:6px;background:#1a2025;color:#94a3b8;border:1px solid #2e3748;border-radius:7px;padding:6px 14px;font-size:0.76rem;font-weight:600;cursor:pointer;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back to List
              </button>
              <span id="ics-doc-crumb" class="text-xs text-[#64748b]"></span>
            </div>
            <div class="ics-doc-toolbar-actions">
              <input id="icsPersonnelSearch" type="text" placeholder="Search personnel by name / serial…"
                class="bg-[#23272f] text-white border border-[#363b48] rounded px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-accent force-light-text" />
              <button onclick="printICS()" class="ics-print-btn bg-[#0a1f2d] text-[#3ec6ff] border border-[#1a3a4f] rounded px-4 py-1.5 text-xs font-semibold hover:bg-[#112840] transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                Print ICS
              </button>
            </div>
          </div>
          <div id="ics-layout" class="flex flex-row gap-6 items-start">
            <div class="flex-1 flex justify-center">
              <div class="ics-paper ics-print-area">
                <div class="ics-paper-grid">
                  <div class="ics-header-row">
                    <div class="ics-doc-header">
                      <img class="ics-logo" src="<?php echo e(asset('images/logo.png')); ?>" alt="Logo" onerror="this.style.display='none'" />
                      <div class="small">PROPERTY ACCOUNTABILITY OFFICE, GENERAL SUPPORT (GS)</div>
                      <div class="small">ARMY PROPERTY ACCOUNTABILITY OFFICE</div>
                      <div class="tiny">PHILIPPINE ARMY</div>
                      <div class="tiny">Fort Andres Bonifacio, Taguig City</div>
                      <div class="main-title">INVENTORY CUSTODIAN SLIP</div>
                    </div>
                    <div class="ics-photo-2x2"><img id="previewPaperPhoto" src="<?php echo e(asset('images/logo.png')); ?>" alt="Personnel Photo" /></div>
                  </div>
                  <table class="ics-meta">
                    <tr><td class="label">ICS No:</td><td class="value" id="previewIcsNo">ICS-0001</td><td class="label">Rank:</td><td class="value" id="previewRank">CPT</td></tr>
                    <tr><td class="label">ICS Validity:</td><td class="value" id="previewIcsValidity">01 June 2027</td><td class="label">Name:</td><td class="value" id="previewPersonnelName">Juan D. Cruz</td></tr>
                    <tr><td class="label">Unit:</td><td class="value" id="previewUnit">8IB, 4ID, PA</td><td class="label">Serial No:</td><td class="value" id="previewSerial">AFP023947</td></tr>
                  </table>
                  <table class="ics-main-table">
                    <colgroup><col style="width:8%"><col style="width:7%"><col style="width:13%"><col style="width:13%"><col style="width:26%"><col style="width:17%"><col style="width:16%"></colgroup>
                    <thead>
                      <tr><th rowspan="2">Quantity</th><th rowspan="2">Unit</th><th colspan="2">Amount</th><th rowspan="2">Description</th><th rowspan="2">Inventory Item No.</th><th rowspan="2">Estimated Useful Life</th></tr>
                      <tr><th>Unit Cost</th><th>Total Cost</th></tr>
                    </thead>
                    <tbody>
    <tr>
      <td class="text-center" contenteditable="true">1</td>
      <td class="text-center" contenteditable="true">eu</td>
      <td class="text-center" id="previewFirearmUnitCost" contenteditable="true">P 16,450.00</td>
      <td class="text-center" id="previewFirearmTotal" contenteditable="true">P 16,450.00</td>
      <td>
        <span id="previewFirearm" contenteditable="true">9mm Glock17</span><br>
        FASN: <strong id="previewSerialDesc" contenteditable="true">AFP023947</strong><br>
        Custodian: <strong id="previewRankName" contenteditable="true">CPT Juan D. Cruz</strong>
      </td>
      <td class="text-center" id="previewInventoryItem" contenteditable="true">AFP023947</td>
      <td class="text-center" contenteditable="true">5 yrs</td>
    </tr>
    <tr><td contenteditable="true">&nbsp;</td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true">Back Straps</td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true">&nbsp;</td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true">Magazine (17 rds Cap)</td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true">&nbsp;</td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true">Cleaning Kit</td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true">&nbsp;</td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true">Speed Loader</td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true">&nbsp;</td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true">User's Manual</td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true">&nbsp;</td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true">Gun Case</td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr><td contenteditable="true">&nbsp;</td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true"></td><td contenteditable="true">Magazine Pouch (3 mag capacity)</td><td contenteditable="true"></td><td contenteditable="true"></td></tr>
    <tr>
      <td class="text-center" id="previewAmmo" contenteditable="true">68</td>
      <td class="text-center" contenteditable="true">rds</td>
      <td class="text-center" id="previewAmmoUnitCost" contenteditable="true">P 15.07</td>
      <td class="text-center" id="previewAmmoTotal" contenteditable="true">P 1,024.76</td>
      <td id="previewAmmoDescription" contenteditable="true">Ctg, 9mm, Ball</td>
      <td contenteditable="true"></td>
      <td contenteditable="true"></td>
    </tr>
  </tbody>
                  </table>
                  <table class="ics-signatures">
                    <tr>
                      <td><div class="ics-sign-wrap"><div style="font-size:0.74rem;font-weight:700;color:#101826;">Received by:</div><div style="display:flex;flex-direction:column;align-items:center;flex:1;justify-content:center;padding:4px 0;"><div id="previewCustodianSigWrap" style="width:100%;max-height:52px;display:none;align-items:center;justify-content:center;margin-bottom:2px;"><img id="previewCustodianSig" src="" alt="Custodian Signature" style="max-height:52px;max-width:90%;object-fit:contain;" /></div><div class="ics-sign-name" id="previewReceivedBy">JOHN DOE</div><div class="ics-sign-sub">Signature Over Printed Name</div><div class="ics-sign-sub" id="previewReceivedOffice">8IB, 4ID, PA</div></div><div class="ics-sign-date" id="previewSignDateLeft">—</div></div></td>
                      <td><div class="ics-sign-wrap"><div style="font-size:0.74rem;font-weight:700;color:#101826;">Received from:</div><div style="display:flex;flex-direction:column;align-items:center;flex:1;justify-content:center;padding:4px 0;"><div id="previewIssuingSigWrap" style="width:100%;max-height:52px;display:none;align-items:center;justify-content:center;margin-bottom:2px;"><img id="previewIssuingSig" src="" alt="Rosemarie O. Vilbar Signature" style="max-height:52px;max-width:90%;object-fit:contain;" /></div><div class="ics-sign-name" id="previewIssuedBy">MS ROSEMARIE O VILBAR</div><div class="ics-sign-sub">Signature Over Printed Name</div><div class="ics-sign-sub" id="previewIssuedOffice">Chief, PAOGS, APAO, PA</div></div><div class="ics-sign-date" id="previewSignDateRight">—</div></div></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
            <aside id="ics-panel" class="w-72 min-w-[260px] flex flex-col no-print" style="max-height:calc(100vh - 140px);overflow-y:auto;scrollbar-width:thin;">
              <div class="ics-side-box">
                <div class="ics-side-title">Paper Details</div>
                <label class="ics-label">ICS No. <input id="icsNoField" class="ics-mini-input" type="text" placeholder="ICS-0001" /></label>
                <label class="ics-label">ICS Validity <input id="icsValidityField" class="ics-mini-input" type="date" /></label>
                <label class="ics-label">Rank <input id="rankField" class="ics-mini-input" type="text" placeholder="CPT" /></label>
                <label class="ics-label">Personnel Name <input id="personnelNameField" class="ics-mini-input" type="text" placeholder="Juan D. Cruz" /></label>
                <label class="ics-label">Unit <input id="unitField" class="ics-mini-input" type="text" placeholder="8IB, 4ID, PA" /></label>
                <label class="ics-label">Firearm / Pistol <input id="icsFirearmField" class="ics-mini-input" type="text" placeholder="9mm Glock17" /></label>
                <label class="ics-label">AFP Serial # <input id="icsSerialField" class="ics-mini-input" type="text" placeholder="AFP023947" /></label>
                <label class="ics-label">Ammo Qty (rds) <input id="icsAmmoField" class="ics-mini-input" type="number" min="0" placeholder="68" /></label>
                <label class="ics-label">Received By <input id="receivedByField" class="ics-mini-input" type="text" placeholder="John Doe" /></label>
                <label class="ics-label">Issued By <input id="issuedByField" class="ics-mini-input" type="text" value="MS ROSEMARIE O VILBAR" placeholder="MS ROSEMARIE O VILBAR" /></label>
                <button id="icsResetBtn" class="ics-btn-secondary">Reset to Defaults</button>
              </div>
              <div class="ics-side-box">
                <div class="ics-side-title">Signatures</div>
                <label class="ics-label">Custodian Signature</label>
                <div id="custodianSigPreviewWrap" class="ics-sig-box mt-2" style="min-height:64px;padding:4px;background:#0d1117;"><img id="custodianSigPreview" src="" alt="" style="max-height:56px;max-width:100%;object-fit:contain;display:none;" /><span id="custodianSigPlaceholder" class="text-xs" style="color:#4b5a72;">No signature uploaded</span></div>
                <input id="custodianSigInput" type="file" accept="image/*" class="hidden" />
                <div class="flex gap-2 mt-2"><button id="uploadCustodianSigBtn" class="ics-btn-primary" style="flex:1;">Upload</button><button id="clearCustodianSigBtn" class="ics-btn-secondary" style="flex:1;margin-top:0;">Clear</button></div>
                <label class="ics-label mt-4">Issuing Officer Signature</label>
                <div id="issuingSigPreviewWrap" class="ics-sig-box mt-2" style="min-height:64px;padding:4px;background:#0d1117;"><img id="issuingSigPreview" src="" alt="" style="max-height:56px;max-width:100%;object-fit:contain;display:none;" /><span id="issuingSigPlaceholder" class="text-xs" style="color:#4b5a72;">No signature uploaded</span></div>
                <input id="issuingSigInput" type="file" accept="image/*" class="hidden" />
                <div class="flex gap-2 mt-2"><button id="uploadIssuingSigBtn" class="ics-btn-primary" style="flex:1;">Upload</button><button id="clearIssuingSigBtn" class="ics-btn-secondary" style="flex:1;margin-top:0;">Clear</button></div>
              </div>
              <div class="ics-side-box">
                <div class="ics-side-title">Personnel Photo (2×2)</div>
                <div class="ics-photo-preview"><img id="icsPhotoSidePreview" src="<?php echo e(asset('images/logo.png')); ?>" alt="Personnel photo preview" /></div>
                <input id="icsPhotoInput" type="file" accept="image/*" class="hidden" />
                <button id="icsUploadPhotoBtn" class="ics-btn-primary">Upload Photo</button>
                <button id="icsResetPhotoBtn" class="ics-btn-secondary">Reset Photo</button>
              </div>
            </aside>
          </div>
        </div>

      </div>
  <?php echo $__env->make('par.module', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  
  <div id="page-renewal" class="page-section">
    <div class="mb-5">
      <h2 class="font-semibold text-base force-light-text tracking-tight">Renewal Overview</h2>
      <p class="text-xs text-[#64748b] mt-0.5">Track ICS renewal status. Notify personnel whose license is within the renewal period or already expired.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <button onclick="renewalSetTab('renewed')" class="text-left rounded-xl p-4 border" style="background:#0c2418;border-color:#1a5c3a;">
        <div class="flex items-center gap-3 mb-1">
          <div style="width:38px;height:38px;background:#0c2e1a;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="18" height="18" fill="none" stroke="#33b481" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <span style="font-size:0.85rem;font-weight:700;color:#33b481;">Renewed</span>
        </div>
        <p id="renewal-sum-renewed" style="font-size:1.9rem;font-weight:800;color:#e5eaf2;margin:0;"><?php echo e($staffRenewalCounts->get('renewed', 0)); ?></p>
        <p style="font-size:0.7rem;color:#1a5c3a;margin:2px 0 0;">ICS already renewed</p>
      </button>
      <button onclick="renewalSetTab('within')" class="text-left rounded-xl p-4 border" style="background:#241c06;border-color:#5c4a1a;">
        <div class="flex items-center gap-3 mb-1">
          <div style="width:38px;height:38px;background:#2c2000;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="18" height="18" fill="none" stroke="#f6e05e" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <span style="font-size:0.85rem;font-weight:700;color:#f6e05e;">Within Renewal Period</span>
        </div>
        <p id="renewal-sum-within" style="font-size:1.9rem;font-weight:800;color:#e5eaf2;margin:0;"><?php echo e($staffRenewalCounts->get('within', 0)); ?></p>
        <p style="font-size:0.7rem;color:#5c4a1a;margin:2px 0 0;">Expiring within 60 days</p>
      </button>
      <button onclick="renewalSetTab('expired')" class="text-left rounded-xl p-4 border" style="background:#2d0a0a;border-color:#5c1a1a;">
        <div class="flex items-center gap-3 mb-1">
          <div style="width:38px;height:38px;background:#3a0d0d;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="18" height="18" fill="none" stroke="#fc8181" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
          </div>
          <span style="font-size:0.85rem;font-weight:700;color:#fc8181;">Expired</span>
        </div>
        <p id="renewal-sum-expired" style="font-size:1.9rem;font-weight:800;color:#e5eaf2;margin:0;"><?php echo e($staffRenewalCounts->get('expired', 0)); ?></p>
        <p style="font-size:0.7rem;color:#5c1a1a;margin:2px 0 0;">ICS already expired</p>
      </button>
    </div>

    <div class="bg-[#23272f] rounded-lg shadow shadow-black/10 p-5">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="font-semibold text-base force-light-text tracking-tight">Renewal List</h2>
        <button id="renewalNotifyAllBtn" onclick="renewalNotifyAll()" disabled style="display:none;align-items:center;gap:6px;background:#241b07;color:#f4b63f;border:1px solid #b77913;border-radius:7px;padding:7px 14px;font-size:0.78rem;font-weight:700;cursor:pointer;">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:2px;"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
          Notify Selected (<span id="renewalSelectedCount">0</span>)
        </button>
      </div>

      <div class="flex flex-wrap gap-2 mb-4">
        <button id="renewaltab-renewed" onclick="renewalSetTab('renewed')" style="display:flex;align-items:center;gap:6px;border-radius:7px;padding:8px 16px;font-size:0.78rem;font-weight:700;cursor:pointer;border:1px solid #1a5c3a;background:#0c2418;color:#33b481;">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Renewed (<span id="renewaltab-count-renewed"><?php echo e($staffRenewalCounts->get('renewed', 0)); ?></span>)
        </button>
        <button id="renewaltab-within" onclick="renewalSetTab('within')" style="display:flex;align-items:center;gap:6px;border-radius:7px;padding:8px 16px;font-size:0.78rem;font-weight:700;cursor:pointer;border:1px solid #2a3748;background:transparent;color:#64748b;">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Within Renewal Period (<span id="renewaltab-count-within"><?php echo e($staffRenewalCounts->get('within', 0)); ?></span>)
        </button>
        <button id="renewaltab-expired" onclick="renewalSetTab('expired')" style="display:flex;align-items:center;gap:6px;border-radius:7px;padding:8px 16px;font-size:0.78rem;font-weight:700;cursor:pointer;border:1px solid #2a3748;background:transparent;color:#64748b;">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
          Expired (<span id="renewaltab-count-expired"><?php echo e($staffRenewalCounts->get('expired', 0)); ?></span>)
        </button>
      </div>

      <div class="flex flex-wrap gap-2 mb-4">
        <select id="renewalUnitFilter" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-2 py-1.5 text-xs force-light-text">
          <option value="">All Units</option>
        </select>
        <select id="renewalDurationSort" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-2 py-1.5 text-xs force-light-text" aria-label="Sort by duration">
          <option value="soonest">Duration: Soonest first</option>
          <option value="latest">Duration: Latest first</option>
          <option value="name">Name: A to Z</option>
        </select>
        <div class="relative flex-1 min-w-[180px]">
          <input id="renewalSearch" type="text" placeholder="Search personnel..." class="bg-[#1a2025] text-white border border-[#363b48] rounded px-3 py-1.5 text-xs w-full focus:outline-none focus:ring-2 focus:ring-accent force-light-text" />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left force-light-text" style="border-collapse:collapse;">
          <thead>
            <tr class="border-b border-[#252b32] text-[#b0bac7]">
              <th class="py-2 px-2 font-semibold" style="width:34px;"><input id="renewalSelectAll" type="checkbox" aria-label="Select all filtered personnel" style="accent-color:#33b481;"></th>
              <th class="py-2 px-2 font-semibold">No.</th>
              <th class="py-2 px-2 font-semibold">Name</th>
              <th class="py-2 px-2 font-semibold">Rank</th>
              <th class="py-2 px-2 font-semibold">AFP Serial #</th>
              <th class="py-2 px-2 font-semibold">Unit / Organization</th>
              <th class="py-2 px-2 font-semibold">Duration</th>
              <th class="py-2 px-2 font-semibold">Action</th>
            </tr>
          </thead>
          <tbody id="renewalTbody">
            <?php $__currentLoopData = $staffInitialPersonnel->where('approvedStatus', 'renewed')->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr class="border-b border-[#1a2025]">
                <td class="py-2 px-2"></td>
                <td class="py-2 px-2 force-light-text"><?php echo e($loop->iteration); ?></td>
                <td class="py-2 px-2 force-light-text"><?php echo e(trim(($person['lastName'] ?? '').', '.($person['firstName'] ?? '').' '.($person['middleName'] ?? ''))); ?></td>
                <td class="py-2 px-2 force-light-text"><?php echo e($person['rank'] ?? '—'); ?></td>
                <td class="py-2 px-2 force-light-text"><?php echo e($person['afpSerialNumber'] ?? '—'); ?></td>
                <td class="py-2 px-2 force-light-text"><?php echo e($person['unit'] ?? '—'); ?></td>
                <td class="py-2 px-2 force-light-text"><?php echo e($person['dateOfValidity'] ?? 'No validity date'); ?></td>
                <td class="py-2 px-2"><button onclick="renewalOpenDetails(<?php echo e(Js::from($person['itemNumber'])); ?>)" class="renewal-action-renewed" style="background:transparent;color:#33b481;border:1px solid #1a5c3a;border-radius:6px;padding:5px 12px;font-size:.72rem;font-weight:700;">View Details</button></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between mt-4 flex-wrap gap-2">
        <span id="renewalTblInfo" class="text-xs text-[#64748b]">Showing 0 records</span>
        <div class="flex gap-1" id="renewalTblPages"></div>
      </div>

      <p id="renewalFootnote" class="text-xs text-[#64748b] mt-4" style="display:none;">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
        Notify personnel who are <span style="color:#fc8181;font-weight:700;">Expired</span> or <span style="color:#f6e05e;font-weight:700;">Within Renewal Period</span> to ensure timely renewal of their ICS.
      </p>
    </div>
  </div>
      
      <div id="page-report" class="page-section">

        <div class="mb-5">
          <h2 class="font-semibold text-base force-light-text tracking-tight">Report Center</h2>
          <p class="text-xs text-[#64748b] mt-0.5">Generate renewal reports and the Report on the Physical Count of Semi-Expendable Property (RPCSP).</p>
        </div>

        
        <div class="staff-report-tabs">
          <button type="button" id="staffReportRenewalTab" class="staff-report-tab active">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>
            </svg>
            Personnel Renewal Report
          </button>

          <button type="button" id="staffReportRpcspTab" class="staff-report-tab">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M4 4h16v16H4z"/><path d="M4 9h16M9 4v16"/>
            </svg>
            RPCSP
          </button>
        </div>

        
        <div id="staffRenewalReportPanel" class="staff-report-panel active">
          <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 mb-6">
            <h3 class="font-semibold text-base force-light-text tracking-tight mb-1">Personnel Renewal Report</h3>
            <p class="text-xs card-desc mb-6">Generate and export personnel renewal status reports.</p>

            <div class="grid md:grid-cols-3 gap-4 mb-6">
              <div>
                <label for="reportFrom" class="text-xs text-[#94a3b8] mb-1 block">Validity Date From</label>
                <input type="date" id="reportFrom" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-3 py-2 text-xs w-full force-light-text">
              </div>

              <div>
                <label for="reportTo" class="text-xs text-[#94a3b8] mb-1 block">Validity Date To</label>
                <input type="date" id="reportTo" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-3 py-2 text-xs w-full force-light-text">
              </div>

              <div>
                <label class="text-xs text-[#94a3b8] mb-1 block">Status Filter</label>
                <select id="reportStatus" class="bg-[#1a2025] text-white border border-[#363b48] rounded px-3 py-2 text-xs w-full force-light-text">
                  <option value="">All</option>
                  <option value="new">New</option>
                  <option value="pending">Pending</option>
                  <option value="within">Within Renewal</option>
                  <option value="renewed">Renewed</option>
                  <option value="expired">Expired</option>
                </select>
              </div>
            </div>

            <div class="flex gap-2 mb-6 flex-wrap">
              <button id="generateReportBtn" class="bg-[#0d3325] text-[#33b481] border border-[#1a5c3a] rounded px-4 py-2 text-xs font-semibold hover:bg-[#154d32] transition-colors">
                Generate Report
              </button>

              <button id="exportReportBtn" class="bg-[#0a1f2d] text-[#3ec6ff] border border-[#1a3a4f] rounded px-4 py-2 text-xs font-semibold hover:bg-[#112840] transition-colors">
                Export Excel
              </button>
            </div>

            <p id="reportFilterFeedback" class="text-xs text-[#64748b] mb-4" role="status" aria-live="polite"></p>

            <div class="overflow-x-auto">
              <table class="w-full text-xs text-left force-light-text">
                <thead>
                  <tr class="border-b border-[#252b32] text-[#b0bac7]">
                    <th class="py-2 px-2 font-semibold">Item #</th>
                    <th class="py-2 px-2 font-semibold">Name</th>
                    <th class="py-2 px-2 font-semibold">AFP Serial #</th>
                    <th class="py-2 px-2 font-semibold">Date of Validity</th>
                    <th class="py-2 px-2 font-semibold">Status</th>
                  </tr>
                </thead>
                <tbody id="reportTableBody">
                  <tr>
                    <td colspan="5" class="text-center py-8 text-[#64748b]">Set filters and click "Generate Report".</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        
        <div id="staffRpcspReportPanel" class="staff-report-panel">

          <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 mb-6">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
              <div>
                <h3 class="font-semibold text-base force-light-text tracking-tight">Report on the Physical Count of Semi-Expendable Property</h3>
                <p class="text-xs text-[#64748b] mt-1">Prepare the consolidated RPCSP from the personnel firearm records stored in the system.</p>
              </div>
              <span class="text-xs text-[#64748b]">Staff Report Center</span>
            </div>

            <div class="rpcsp-config-grid">
              <div class="rpcsp-field">
                <label for="staffRpcspAsOfDate">As of Date</label>
                <input type="date" id="staffRpcspAsOfDate">
              </div>

              <div class="rpcsp-field">
                <label for="staffRpcspFundCluster">Fund Cluster</label>
                <input type="text" id="staffRpcspFundCluster" value="General Fund">
              </div>

              <div class="rpcsp-field">
                <label for="staffRpcspOfficer">Accountable Officer</label>
                <input type="text" id="staffRpcspOfficer" value="MS ROSEMARIE O VILBAR">
              </div>

              <div class="rpcsp-field">
                <label for="staffRpcspDesignation">Designation / Office</label>
                <input type="text" id="staffRpcspDesignation" value="Chief, PAOGS, APAO, PA">
              </div>

              <div class="rpcsp-field">
                <label for="staffRpcspAssumptionDate">Date of Assumption</label>
                <input type="date" id="staffRpcspAssumptionDate" value="2022-03-24">
              </div>

              <div class="rpcsp-field">
                <label for="staffRpcspUnitValue">Default Unit Value (₱)</label>
                <input type="number" id="staffRpcspUnitValue" min="0" step="0.01" value="16450.00">
              </div>

              <div class="rpcsp-field">
                <label for="staffRpcspUnitFilter">Unit / Organization</label>
                <select id="staffRpcspUnitFilter">
                  <option value="">All Units</option>
                </select>
              </div>

              <div class="rpcsp-field">
                <label for="staffRpcspRemarks">Default Remarks</label>
                <select id="staffRpcspRemarks">
                  <option value="Serviceable">Serviceable</option>
                  <option value="Unserviceable">Unserviceable</option>
                  <option value="For Repair">For Repair</option>
                </select>
              </div>
            </div>

            <div class="rpcsp-actions">
              <button type="button" id="staffPreviewRpcspBtn" class="rpcsp-btn rpcsp-btn-preview">Preview RPCSP</button>
              <button type="button" id="staffPrintRpcspBtn" class="rpcsp-btn rpcsp-btn-print">Print / Save PDF</button>
              <button type="button" id="staffExportRpcspBtn" class="rpcsp-btn rpcsp-btn-export">Export Excel</button>
            </div>
          </div>

          <div class="rpcsp-preview-shell mb-10">
            <div class="rpcsp-document" id="staffRpcspDocument">
              <h1 class="rpcsp-title">REPORT ON THE PHYSICAL COUNT OF SEMI-EXPENDABLE PROPERTY</h1>
              <p class="rpcsp-subtitle">MILITARY, POLICE &amp; SECURITY EQUIPMENT (ACCOUNT CODE: 1-04-05-090-00)</p>
              <p class="rpcsp-subtitle">(Type of Property, Plant and Equipment)</p>
              <p class="rpcsp-asof">As of <span id="staffRpcspPreviewAsOf">—</span></p>

              <p class="rpcsp-fund">Fund Cluster: <span id="staffRpcspPreviewFund">General Fund</span></p>

              <p class="rpcsp-forwhich">
                For which:
                <span class="accountable-name" id="staffRpcspPreviewOfficer">MS ROSEMARIE O VILBAR</span>,
                <span id="staffRpcspPreviewDesignation">Chief, PAOGS, APAO, PA</span>
                is accountable, having assumed such accountability on
                <span id="staffRpcspPreviewAssumption">24 Mar 2022</span>.
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
                <tbody id="staffRpcspTableBody">
                  <tr>
                    <td colspan="11" class="rpcsp-empty">Click “Preview RPCSP” to generate the report.</td>
                  </tr>
                </tbody>
              </table>

              <div class="rpcsp-summary">
                <span>Total Property Count: <strong id="staffRpcspTotalCount">0</strong></span>
                <span>Total Value: <strong id="staffRpcspTotalValue">₱0.00</strong></span>
              </div>

              <p class="rpcsp-note">
                Generated from the current personnel/firearm records stored in the APAO system.
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

        </div>

      </div>

      
      <div id="page-archive" class="page-section">
        <div class="bg-[#23272f] rounded-lg p-6 shadow shadow-black/10 mb-6">
          <h2 class="font-semibold text-base force-light-text tracking-tight mb-1">Archive Data</h2>
          <p class="text-xs card-desc mb-6">View archived personnel records.</p>
          <div class="flex gap-2 mb-4 flex-wrap">
            <div class="relative flex-1 min-w-[200px]">
              <input id="archiveSearch" type="text" placeholder="Search archive..." class="bg-[#1a2025] text-white border border-[#363b48] rounded px-4 py-2 w-full text-xs focus:outline-none focus:ring-2 focus:ring-accent pr-8 force-light-text" />
              <svg class="absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M21 21l-4.35-4.35M5 11a6 6 0 1112 0 6 6 0 01-12 0z"/></svg>
            </div>
            <button id="refreshArchiveBtn" class="bg-[#1a2025] text-[#94a3b8] border border-[#363b48] rounded px-4 py-2 text-xs font-semibold hover:bg-[#23272f] transition-colors">Refresh</button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-xs text-left force-light-text">
              <thead><tr class="border-b border-[#252b32] text-[#b0bac7]"><th class="py-2 px-2 font-semibold">Item #</th><th class="py-2 px-2 font-semibold">Name</th><th class="py-2 px-2 font-semibold">AFP Serial #</th><th class="py-2 px-2 font-semibold">Date of Validity</th><th class="py-2 px-2 font-semibold">Archived On</th></tr></thead>
              <tbody id="archiveTableBody"><tr><td colspan="5" class="text-center py-8 text-[#64748b]">No archived records.</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
      
      <input type="hidden" id="rpStoreUrl" value="<?php echo e(route('staff.personnel.store')); ?>">
      <input type="hidden" id="rpAvailabilityUrl" value="<?php echo e(route('staff.personnel.availability')); ?>">
      <div id="page-registration" class="page-section">
        <div class="mb-4">
          <h1 class="text-2xl font-bold force-light-text">New Personnel Registration</h1>
          <p class="text-sm mt-1" style="color:#64748b;">Register a new personnel and firearm record. After submission, the record will have a status of Pending Inspection</p>
        </div>
        <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:28px;">
          <div id="regStep1" style="display:flex;flex-direction:column;align-items:center;gap:5px;"><div id="regCircle1" class="reg-step-circle is-active" style="width:34px;height:34px;border-radius:50%;border:2px solid #d4a017;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.82rem;color:#d4a017;background:#1e2128;">1</div><div id="regLabel1" style="font-size:.7rem;color:#d4a017;font-weight:600;white-space:nowrap;">Personal Information</div></div>
          <div id="regLine1" class="reg-step-line" style="flex:1;height:2px;background:#2a2d35;min-width:50px;margin-bottom:20px;"></div>
          <div id="regStep2" style="display:flex;flex-direction:column;align-items:center;gap:5px;"><div id="regCircle2" class="reg-step-circle" style="width:34px;height:34px;border-radius:50%;border:2px solid #2a2d35;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.82rem;color:#64748b;background:#1e2128;">2</div><div id="regLabel2" style="font-size:.7rem;color:#64748b;white-space:nowrap;">Firearm Information</div></div>
          <div id="regLine2" class="reg-step-line" style="flex:1;height:2px;background:#2a2d35;min-width:50px;margin-bottom:20px;"></div>
          <div id="regStep3" style="display:flex;flex-direction:column;align-items:center;gap:5px;"><div id="regCircle3" class="reg-step-circle" style="width:34px;height:34px;border-radius:50%;border:2px solid #2a2d35;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.82rem;color:#64748b;background:#1e2128;">3</div><div id="regLabel3" style="font-size:.7rem;color:#64748b;white-space:nowrap;">Documents & Uploads</div></div>
          <div id="regLine4" class="reg-step-line" style="flex:1;height:2px;background:#2a2d35;min-width:50px;margin-bottom:20px;"></div>
          <div id="regStep5" style="display:flex;flex-direction:column;align-items:center;gap:5px;"><div id="regCircle5" class="reg-step-circle" style="width:34px;height:34px;border-radius:50%;border:2px solid #2a2d35;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.82rem;color:#64748b;background:#1e2128;">4</div><div id="regLabel5" style="font-size:.7rem;color:#64748b;white-space:nowrap;">Review & Submit</div></div>
        </div>
        <div style="display:flex;gap:24px;align-items:flex-start;">
          <div style="flex:1;min-width:0;">
            <div id="regFormStep1">
              <div class="bg-[#23272f] rounded-xl border border-[#2a2d35] p-6 mb-5">
                <div style="display:flex;align-items:center;gap:10px;font-weight:700;font-size:.85rem;color:#e5eaf2;margin-bottom:18px;text-transform:uppercase;letter-spacing:.04em;"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="18" height="18"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Personal Information</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                  <div><label class="reg-label">Last Name <span style="color:#ef4444;">*</span></label><input type="text" id="rp_lastName" class="reg-input" placeholder="Enter last name"></div>
                  <div><label class="reg-label">First Name <span style="color:#ef4444;">*</span></label><input type="text" id="rp_firstName" class="reg-input" placeholder="Enter first name"></div>
                  <div><label class="reg-label">Middle Name</label><input type="text" id="rp_middleName" class="reg-input" placeholder="Enter middle name"></div>
                  <div><label class="reg-label">Rank <span style="color:#ef4444;">*</span></label><select id="rp_rank" class="reg-input"><option value="">Select rank</option><option>PVT</option><option>PFC</option><option>CPL</option><option>SGT</option><option>SSG</option><option>SFC</option><option>MSG</option><option>1SG</option><option>SGM</option><option>2LT</option><option>1LT</option><option>CPT</option><option>MAJ</option><option>LTC</option><option>COL</option><option>BGen</option><option>Cpl (OS) PA</option><option>Sgt (OS) PA</option></select></div>
                  <div><label class="reg-label">AFP Serial Number <span style="color:#ef4444;">*</span></label><input type="text" id="rp_afpSerial" class="reg-input" placeholder="Enter AFP serial number"></div>
                  <div><label class="reg-label">Unit / Organization <span style="color:#ef4444;">*</span></label><select id="rp_unit" class="reg-input"><option value="">Select unit / organization</option><option>8IB, 4ID, PA</option><option>9IB, 4ID, PA</option><option>10IB, 4ID, PA</option><option>62IB, 4ID, PA</option><option>901Bde, 9ID, PA</option><option>4ID, PA</option><option>APAO, PA</option><option>10FPAO, APAO, PA</option><option>Other</option></select></div>
                  <div><label class="reg-label">AFOS/MOS</label><input type="text" id="rp_afosMos" class="reg-input" placeholder="e.g. 11A"></div>
                  <div><label class="reg-label">Branch</label><input type="text" id="rp_branch" class="reg-input" placeholder="e.g. Infantry"></div>
                  <div><label class="reg-label">Date of Birth <span style="color:#ef4444;">*</span></label><input type="date" id="rp_dob" class="reg-input" max="<?php echo e(now()->toDateString()); ?>"></div>
                  <div><label class="reg-label">Email Address <span style="color:#ef4444;">*</span></label><input type="email" id="rp_email" class="reg-input" placeholder="Enter email address"></div>
                  <div><label class="reg-label">Contact Number</label><input type="text" id="rp_contact" class="reg-input" inputmode="numeric" pattern="[0-9]*" maxlength="20" placeholder="Numbers only" oninput="this.value=this.value.replace(/\D/g,'')"></div>
                  <div><label class="reg-label">Civil Status</label><select id="rp_civil" class="reg-input"><option value="">Select civil status</option><option>Single</option><option>Married</option><option>Widowed</option><option>Separated</option></select></div>
                  <div><label class="reg-label">Gender</label><select id="rp_gender" class="reg-input"><option value="">Select gender</option><option>Male</option><option>Female</option></select></div>
                  <div><label class="reg-label">Citizenship</label><select id="rp_citizenship" class="reg-input" onchange="rpToggleOtherCitizenship()"><option value="Filipino">Filipino</option><option value="Other">Other</option></select></div>
                  <div id="rp_otherCitizenshipWrap" style="display:none;"><label class="reg-label">Specify Citizenship <span style="color:#ef4444;">*</span></label><input id="rp_otherCitizenship" class="reg-input" placeholder="Enter citizenship"></div>
                </div>
              </div>
              <div id="rp_err1" style="color:#fc8181;font-size:.8rem;margin-bottom:8px;display:none;"></div>
              <div style="display:flex;justify-content:space-between;">
                <button type="button" onclick="window._rpNavigate('dashboard')" style="background:transparent;border:1px solid #2a2d35;color:#64748b;border-radius:8px;padding:10px 24px;font-size:.85rem;font-weight:600;cursor:pointer;">Cancel</button>
                <button type="button" id="rpNextPersonalBtn" onclick="rpNext(1)" style="background:#d4a017;color:#13151a;border:none;border-radius:8px;padding:10px 24px;font-size:.85rem;font-weight:700;cursor:pointer;">Next: Firearm Info →</button>
              </div>
            </div>
            <div id="regFormStep2" style="display:none;">
              <div class="bg-[#23272f] rounded-xl border border-[#2a2d35] p-6 mb-5">
                <div style="display:flex;align-items:center;gap:10px;font-weight:700;font-size:.85rem;color:#e5eaf2;margin-bottom:18px;text-transform:uppercase;letter-spacing:.04em;">Firearm Information</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                  <div><label class="reg-label">Nomenclature of Pistol <span style="color:#ef4444;">*</span></label><select id="rp_pistolNomenclature" class="reg-input"><option value="">Select nomenclature</option><option>Pistol 9mm, Glock 17</option><option>Pistol Cal .45</option><option>Pistol 9mm</option><option>Glock 17</option></select></div>
                  <div><label class="reg-label">Pistol Type <span style="color:#ef4444;">*</span></label><input id="rp_pistolType" class="reg-input" value="Pistol" readonly></div>
                  <div><label class="reg-label">Pistol Serial Number <span style="color:#ef4444;">*</span></label><input type="text" id="rp_pistolSerial" class="reg-input" placeholder="Enter pistol serial number"></div>
                  <div><label class="reg-label">Quantity of Ammo Issued <span style="color:#ef4444;">*</span></label><input type="number" id="rp_ammo" class="reg-input" placeholder="Enter quantity" min="0" step="1" oninput="if (this.value !== '' && Number(this.value) < 0) this.value = 0;"></div>
                  <div><label class="reg-label">Date Issued</label><input type="date" id="rp_dateIssued" class="reg-input"></div>
                  <div><label class="reg-label">Issued By <span style="color:#ef4444;">*</span></label><datalist id="registrationIssuers"><option value="MS ROSEMARIE O VILBAR"></option><option value="MS EVANGELINE M SINGUEO, Ph.D."></option></datalist><input id="rp_issuedBy" class="reg-input" list="registrationIssuers" placeholder="Select or enter a new issuing officer"><small class="reg-hint">Enter the current issuing officer's complete name if they are not listed.</small></div>
                  <div><label class="reg-label">Armory / Issuing Unit</label><input type="text" id="rp_armory" class="reg-input" placeholder="Enter armory / unit"></div>
                </div>
              </div>
              <div id="rp_err2" style="color:#fc8181;font-size:.8rem;margin-bottom:8px;display:none;"></div>
              <div style="display:flex;justify-content:space-between;">
                <button type="button" onclick="rpPrev(2)" style="background:transparent;border:1px solid #2a2d35;color:#64748b;border-radius:8px;padding:10px 24px;font-size:.85rem;font-weight:600;cursor:pointer;">← Back</button>
                <button type="button" id="rpNextFirearmBtn" onclick="rpNext(2)" style="background:#d4a017;color:#13151a;border:none;border-radius:8px;padding:10px 24px;font-size:.85rem;font-weight:700;cursor:pointer;">Next: Documents →</button>
              </div>
            </div>
            <div id="regFormStep3" style="display:none;">
              <div class="bg-[#23272f] rounded-xl border border-[#2a2d35] p-6 mb-5">
                <div style="display:flex;align-items:center;gap:10px;font-weight:700;font-size:.85rem;color:#e5eaf2;margin-bottom:18px;text-transform:uppercase;letter-spacing:.04em;">Documents & Uploads</div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
                  <div>
                    <label class="reg-label" style="margin-bottom:8px;display:block;">2x2 Photo <span style="color:#ef4444;">*</span></label>
                    <div style="border:2px dashed #2a2d35;border-radius:8px;padding:16px;background:#13151a;text-align:center;">
                      <p style="font-size:.75rem;color:#64748b;margin:0 0 10px;">Upload a softcopy or take a photo now</p>
                      <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                        <button type="button" id="rpAttachPhotoBtn" onclick="document.getElementById('rp_photo').click()" style="border:1px solid #64748b;background:#334155;color:#ffffff;border-radius:7px;padding:8px 12px;font-size:.72rem;font-weight:700;cursor:pointer;transition:background .15s,border-color .15s;">Attach Photo</button>
                        <button type="button" onclick="rpOpenCamera()" style="border:none;background:#d4a017;color:#13151a;border-radius:7px;padding:8px 12px;font-size:.72rem;font-weight:800;cursor:pointer;">Capture Photo</button>
                      </div>
                      <p style="font-size:.68rem;color:#4b5563;margin:9px 0 0;">Use a clear, front-facing 2x2 ID photo</p>
                    </div>
                    <input type="file" id="rp_photo" accept="image/*" style="display:none;" onchange="rpPreviewPhoto(this)">
                    <div id="rp_photoPreview" style="display:none;margin-top:8px;text-align:center;"><img id="rp_photoImg" src="" alt="2x2 personnel photo preview" style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #2a2d35;"><p id="rp_photoSource" style="font-size:.68rem;color:#33b481;margin:5px 0 0;"></p></div>
                  </div>
                  <div><label class="reg-label" style="margin-bottom:8px;display:block;">Digital Signature <span style="color:#ef4444;">*</span></label><div id="rp_sigBox" onclick="rpOpenSig()" style="border:2px dashed #2a2d35;border-radius:8px;padding:20px;display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer;background:#13151a;text-align:center;"><p style="font-size:.75rem;color:#64748b;margin:0;">Click to capture signature</p></div></div>
                  <div><label class="reg-label" style="margin-bottom:8px;display:block;">Supporting Documents</label><div onclick="document.getElementById('rp_docs').click()" style="border:2px dashed #2a2d35;border-radius:8px;padding:20px;display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer;background:#13151a;text-align:center;"><p style="font-size:.75rem;color:#64748b;margin:0;">Click to upload files</p><p style="font-size:.7rem;color:#4b5563;margin:0;">PDF, JPG, PNG (Max 5MB)</p></div><input type="file" id="rp_docs" accept=".pdf,.jpg,.jpeg,.png" multiple style="display:none;" onchange="document.getElementById('rp_docNames').textContent=Array.from(this.files).map(f=>f.name).join(', ')"><div id="rp_docNames" style="font-size:.7rem;color:#64748b;margin-top:4px;"></div></div>
                </div>
                <div style="margin-top:20px;"><label class="reg-label" style="margin-bottom:6px;display:block;">Remarks (Optional)</label><textarea id="rp_remarks" class="reg-input" rows="3" placeholder="Enter remarks here..." style="resize:vertical;"></textarea></div>
              </div>
              <div style="display:flex;justify-content:space-between;">
                <button type="button" onclick="rpPrev(3)" style="background:transparent;border:1px solid #2a2d35;color:#64748b;border-radius:8px;padding:10px 24px;font-size:.85rem;font-weight:600;cursor:pointer;">← Back</button>
                <button type="button" onclick="rpNext(3)" style="background:#d4a017;color:#13151a;border:none;border-radius:8px;padding:10px 24px;font-size:.85rem;font-weight:700;cursor:pointer;">Next: Review &amp; Submit →</button>
              </div>
            </div>
            <div id="regFormStep4" style="display:none;">
              <style>.par-process-stack{display:grid;gap:16px}.par-process-card{background:#23272f;border:1px solid #2f3540;border-radius:12px;padding:20px}.par-process-title{color:#eef2f7;font-size:.84rem;font-weight:800;text-transform:uppercase;letter-spacing:.045em;margin:0 0 16px}.par-process-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.par-package-head{display:grid;grid-template-columns:1.4fr .75fr .75fr;gap:12px}.par-package-summary{background:#1b1f26;border:1px solid #343b47;border-radius:9px;padding:14px}.par-package-summary span{display:block;color:#7f8a9a;font-size:.65rem;text-transform:uppercase;margin-bottom:5px}.par-package-summary strong{color:#edf2f7;font-size:.8rem}.par-equipment-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:14px}.par-equipment-item{display:flex;align-items:center;gap:9px;background:#1b1f26;border:1px solid #343b47;border-radius:8px;padding:10px 12px;color:#cbd3df;font-size:.74rem}.par-equipment-item:before{content:'✓';display:grid;place-items:center;width:18px;height:18px;border-radius:50%;background:#3a3115;color:#e4b83d;font-weight:800}.par-cost-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.par-cost-box{background:#1b1f26;border:1px solid #343b47;border-radius:9px;padding:14px}.par-cost-box span{color:#7f8a9a;font-size:.68rem}.par-cost-box strong{display:block;color:#eef2f7;font-size:1rem;margin-top:5px}@media(max-width:800px){.par-process-grid,.par-package-head,.par-cost-row{grid-template-columns:1fr 1fr}.par-equipment-list{grid-template-columns:1fr}}@media(max-width:560px){.par-process-grid,.par-package-head,.par-cost-row{grid-template-columns:1fr}}</style>
              <style>.par-equipment-item{display:grid!important;grid-template-columns:auto 1fr auto;align-items:center;gap:9px}.par-equipment-item:before{display:none!important}.par-equipment-check{width:17px;height:17px;accent-color:#d4a017}.par-equipment-remove,.par-equipment-add{border:1px solid #465164;background:transparent;color:#b8c2cf;border-radius:6px;padding:7px 10px;cursor:pointer}.par-equipment-add{color:#e2b632;border-color:#7b651f;margin-top:10px}body.light-mode .par-process-card{background:#fff!important;border-color:#d7dee8!important}body.light-mode .par-process-title{color:#1e293b!important}body.light-mode .par-package-summary,body.light-mode .par-equipment-item,body.light-mode .par-cost-box{background:#f8fafc!important;border-color:#d7dee8!important}body.light-mode .par-package-summary strong,body.light-mode .par-cost-box strong{color:#1e293b!important}body.light-mode .par-equipment-remove,body.light-mode .par-equipment-add{background:#fff!important;color:#475569!important;border-color:#cbd5e1!important}body.light-mode #rp_parPersonnel{color:#334155!important}body.light-mode #rp_parPreview{border:1px solid #d7dee8}</style>
              <div class="par-process-stack">
                <section class="par-process-card"><h3 class="par-process-title">Personnel Information</h3><div id="rp_parPersonnel" style="font-size:.82rem;color:#d6dde7;"></div></section>
                <section class="par-process-card"><h3 class="par-process-title">PAR Information</h3><div class="par-process-grid"><div><label class="reg-label">PAR Number</label><input class="reg-input" value="Generated upon submission" readonly></div><div><label class="reg-label">Date Issued *</label><input id="rp_parIssuedDate" type="date" class="reg-input"></div><div><label class="reg-label">Valid Until</label><input id="rp_parValidUntil" type="date" class="reg-input"></div><div><label class="reg-label">Issued By *</label><input id="rp_parIssuedBy" class="reg-input" list="registrationIssuers" placeholder="Select or enter a new issuing officer"><small class="reg-hint">Enter the current issuing officer's complete name if they are not listed.</small></div><div><label class="reg-label">Approved By *</label><input id="rp_parApprovedBy" class="reg-input" placeholder="Enter complete name"></div></div></section>
              </div>
              <div id="rp_err4" style="color:#fc8181;font-size:.8rem;margin-bottom:8px;display:none;"></div><div style="display:flex;justify-content:space-between;"><button type="button" onclick="rpPrev(4)" class="par-btn">Back</button><button type="button" onclick="rpNext(4)" class="par-btn par-btn-gold">Next: Review & Submit</button></div>
            </div>
            <div id="regFormStep5" style="display:none;">
              <div class="bg-[#23272f] rounded-xl border border-[#2a2d35] p-6 mb-5">
                <div style="font-weight:700;font-size:.85rem;color:#e5eaf2;margin-bottom:6px;">Review & Confirm</div>
                <p style="color:#64748b;font-size:.8rem;margin-bottom:18px;">Please review all information before submitting.</p>
                <p style="color:#d4a017;font-size:.72rem;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Personal Information</p>
                <div id="rp_reviewPersonal" style="margin-bottom:16px;"></div>
                <p style="color:#d4a017;font-size:.72rem;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Firearm Information</p>
                <div id="rp_reviewFirearm" style="margin-bottom:16px;"></div>
              <p style="color:#d4a017;font-size:.72rem;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Remarks</p>
                <div id="rp_reviewRemarks" style="margin-bottom:16px;"></div>
                <p style="color:#d4a017;font-size:.72rem;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Photo & Signature</p>
                <div id="rp_reviewAttachments" style="display:flex;gap:20px;align-items:flex-start;"></div>
              </div>
              <div id="rp_submitError" style="color:#fc8181;font-size:.8rem;margin-bottom:8px;display:none;"></div>
              <div id="rp_submitSuccess" style="color:#33b481;font-size:.8rem;margin-bottom:8px;display:none;"></div>
              <div style="display:flex;justify-content:space-between;">
                <button type="button" onclick="rpPrev(5)" style="background:transparent;border:1px solid #2a2d35;color:#64748b;border-radius:8px;padding:10px 24px;font-size:.85rem;font-weight:600;cursor:pointer;">← Back to Edit</button>
                <button type="button" id="rpSubmitBtn" onclick="rpSubmit()" style="background:#d4a017;color:#13151a;border:none;border-radius:8px;padding:10px 24px;font-size:.85rem;font-weight:700;cursor:pointer;">Submit Registration ✓</button>
              </div>
            </div>
          </div>
          <div style="width:260px;flex-shrink:0;position:sticky;top:20px;">
            <div class="bg-[#23272f] rounded-xl border border-[#2a2d35] p-5">
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;"><span style="font-weight:700;font-size:.82rem;color:#e5eaf2;">REGISTRATION STATUS</span></div>
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;"><span style="font-size:.75rem;color:#64748b;">Status after submission</span><span style="background:#d4a017;color:#13151a;border-radius:6px;font-weight:700;font-size:.75rem;padding:4px 10px;">Pending Inspection</span></div>
              <p style="font-size:.75rem;color:#64748b;line-height:1.6;">The record will be submitted for admin inspection.</p>
            </div>
          </div>
        </div>
        <div id="rp_sigModal" style="display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.75);align-items:center;justify-content:center;">
          <div style="background:#1e2128;border:1px solid #2a2d35;border-radius:12px;padding:24px;width:100%;max-width:440px;">
            <h3 style="font-weight:700;font-size:.9rem;color:#e5eaf2;margin-bottom:12px;">Draw Your Signature</h3>
            <canvas id="rp_sigCanvas" width="380" height="150" style="background:#13151a;border:1px solid #2a2d35;border-radius:8px;cursor:crosshair;touch-action:none;width:100%;"></canvas>
            <div style="display:flex;gap:10px;margin-top:12px;">
              <button type="button" onclick="rpClearSig()" style="flex:1;background:transparent;border:1px solid #2a2d35;color:#64748b;border-radius:7px;padding:8px;font-size:.82rem;cursor:pointer;">Clear</button>
              <button type="button" onclick="rpSaveSig()" style="flex:1;background:#d4a017;color:#13151a;border:none;border-radius:7px;padding:8px;font-size:.82rem;font-weight:700;cursor:pointer;">Save Signature</button>
            </div>
          </div>
        </div>
        <div id="rp_cameraModal" style="display:none;position:fixed;inset:0;z-index:2100;background:rgba(0,0,0,.82);align-items:center;justify-content:center;padding:16px;">
          <div style="background:#1e2128;border:1px solid #2a2d35;border-radius:12px;padding:20px;width:100%;max-width:520px;">
            <h3 style="font-weight:700;font-size:.95rem;color:#e5eaf2;margin:0 0 6px;">Capture 2x2 Photo</h3>
            <p id="rp_cameraStatus" style="font-size:.75rem;color:#94a3b8;margin:0 0 12px;">Allow camera access, then center the personnel's face.</p>
            <div style="aspect-ratio:1/1;background:#090b0f;border:1px solid #2a2d35;border-radius:10px;overflow:hidden;position:relative;">
              <video id="rp_cameraVideo" autoplay muted playsinline style="width:100%;height:100%;object-fit:cover;"></video>
              <div style="position:absolute;inset:8%;border:2px solid rgba(255,255,255,.7);border-radius:50%;pointer-events:none;"></div>
            </div>
            <canvas id="rp_cameraCanvas" width="600" height="600" style="display:none;"></canvas>
            <div style="display:flex;gap:10px;margin-top:14px;">
              <button type="button" onclick="rpCloseCamera()" style="flex:1;background:transparent;border:1px solid #465164;color:#94a3b8;border-radius:7px;padding:9px;cursor:pointer;">Cancel</button>
              <button type="button" id="rp_cameraCaptureBtn" onclick="rpCapturePhoto()" style="flex:1;background:#d4a017;border:none;color:#13151a;border-radius:7px;padding:9px;font-weight:800;cursor:pointer;">Take Photo</button>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>

  
  <div id="registerModalOverlay" class="register-modal-overlay" style="display:none;">
    <div class="register-modal-box">
      <button class="register-modal-close" id="registerModalClose">&times;</button>
      <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem;">
        <div style="width:38px;height:38px;background:#0a1f3a;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <svg width="18" height="18" fill="none" stroke="#3ec6ff" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
        </div>
        <div>
          <h3 style="color:#e5eaf2;font-size:0.95rem;font-weight:700;margin:0;">Register New Personnel</h3>
          <p style="color:#64748b;font-size:0.75rem;margin:0;margin-top:2px;">Status will be set to <span style="color:#3ec6ff;font-weight:700;">New</span></p>
        </div>
      </div>
      <form id="registerPersonnelForm">
        <div class="reg-section">
          <div class="reg-section-title">Personal Information</div>
          <div class="reg-field-grid">
            <div><label class="reg-label">Rank <span style="color:#ef4444;">*</span></label><select name="rank" class="reg-input" required><option value="" disabled selected>Select rank</option><option>LTC</option><option>MAJ</option><option>CPT</option><option>1LT</option><option>2LT</option><option>MSG</option><option>TSG</option><option>SSG</option><option>SGT</option><option>CPL</option><option>PFC</option><option>PVT</option></select></div>
            <div><label class="reg-label">Date of Birth <span style="color:#ef4444;">*</span></label><input name="dateOfBirth" type="date" class="reg-input" max="<?php echo e(now()->toDateString()); ?>" required /></div>
            <div><label class="reg-label">Last Name <span style="color:#ef4444;">*</span></label><input name="lastName" type="text" id="regLastName" class="reg-input" placeholder="Cruz" required /></div>
            <div><label class="reg-label">First Name <span style="color:#ef4444;">*</span></label><input name="firstName" type="text" id="regFirstName" class="reg-input" placeholder="Juan" required /></div>
            <div class="span-2"><label class="reg-label">Middle Name</label><input name="middleName" type="text" id="regMiddleName" class="reg-input" placeholder="Dela" /></div>
          </div>
        </div>
        <div class="reg-section">
          <div class="reg-section-title">Service Information</div>
          <div class="reg-field-grid">
            <div><label class="reg-label">AFP Serial # <span style="color:#ef4444;">*</span></label><input name="afpSerialNumber" type="text" class="reg-input" placeholder="AFP023947" required /></div>
            <div><label class="reg-label">AFOS/MOS</label><input name="afosMos" type="text" class="reg-input" placeholder="11A" /></div>
            <div><label class="reg-label">Branch</label><input name="branch" type="text" class="reg-input" placeholder="Infantry" /></div>
            <div><label class="reg-label">Unit <span style="color:#ef4444;">*</span></label><input name="unit" type="text" class="reg-input" placeholder="8IB, 4ID, PA" required /></div>
            <div class="span-2"><label class="reg-label">Date of Validity <span style="color:#ef4444;">*</span></label><input name="dateOfValidity" type="date" class="reg-input" required /></div>
          </div>
        </div>
        <div class="reg-section">
          <div class="reg-section-title">Firearm Details</div>
          <div class="reg-field-grid">
            <div><label class="reg-label">Nomenclature of Pistol <span style="color:#ef4444;">*</span></label><input name="pistolNomenclature" type="text" class="reg-input" placeholder="9mm Glock17" required /></div>
            <div><label class="reg-label">Pistol Type <span style="color:#ef4444;">*</span></label><input name="pistolType" class="reg-input" value="Pistol" readonly required></div>
            <div><label class="reg-label">Pistol Serial # <span style="color:#ef4444;">*</span></label><input name="pistolSerialNumber" type="text" class="reg-input" placeholder="GK-12345" required /></div>
            <div><label class="reg-label">Qty Ammo (rds)</label><input name="qtyAmmo" type="number" min="0" class="reg-input" placeholder="68" /></div>
          </div>
        </div>
        <div class="email-section">
          <div class="email-section-title">Email Address</div>
          <div class="email-manual-row">
            <label class="reg-label">Email Address <span style="color:#ef4444;">*</span></label>
            <input name="email" id="regEmailInput" type="email" class="reg-input" placeholder="e.g. juan.cruz@army.mil.ph" required />
          </div>
          <div class="email-divider">or auto-generate</div>
          <div style="margin-bottom:0.4rem;">
            <span style="font-size:0.72rem;color:#64748b;font-weight:600;display:block;margin-bottom:0.35rem;">Format:</span>
            <div class="email-gen-controls">
              <button type="button" class="email-fmt-btn active" data-fmt="firstname.lastname">firstname.lastname</button>
              <button type="button" class="email-fmt-btn" data-fmt="f.lastname">f.lastname</button>
              <button type="button" class="email-fmt-btn" data-fmt="lastname.firstname">lastname.firstname</button>
              <button type="button" class="email-fmt-btn" data-fmt="firstnamelastname">firstnamelastname</button>
            </div>
          </div>
          <div class="email-domain-row">
            <span class="email-domain-label">Domain:</span>
            <input type="text" id="emailDomainInput" class="email-domain-input" value="army.mil.ph" placeholder="army.mil.ph" />
            <button type="button" id="generateEmailBtn" class="email-gen-btn">⚡ Generate</button>
          </div>
          <div class="email-preview-wrap">
            <div id="emailPreviewBox" class="email-preview empty">Fill in First &amp; Last Name, then click Generate</div>
            <button type="button" id="emailUseBtn" class="email-use-btn" title="Apply to email field">Use ↑</button>
          </div>
          <p style="font-size:0.7rem;color:#374151;margin-top:0.4rem;">Clicking <strong style="color:#33b481;">⚡ Generate</strong> will also auto-fill the email field above.</p>
        </div>
        <div id="registerFeedback" style="font-size:0.78rem;min-height:1.2rem;margin-bottom:0.5rem;"></div>
        <div style="display:flex;justify-content:flex-end;gap:0.6rem;">
          <button type="button" class="reg-cancel-btn" id="registerModalCancel">Cancel</button>
          <button type="submit" class="reg-submit-btn" id="registerSubmitBtn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:5px;"><path d="M12 4v16m8-8H4"/></svg>
            Register Personnel
          </button>
        </div>
      </form>
    </div>
  </div>

  
  <div id="notifyModalOverlay" class="notify-modal-overlay" style="display:none;">
    <div class="notify-modal-box">
      <button class="notify-modal-close" id="notifyModalClose">&times;</button>
      <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem;">
        <div style="width:38px;height:38px;background:#2d0a0a;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <svg width="18" height="18" fill="none" stroke="#fc8181" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
          <h3 style="color:#e5eaf2;font-size:0.95rem;font-weight:700;margin:0;">Send Notification Email</h3>
          <p id="notifyModalSubtitle" style="color:#64748b;font-size:0.75rem;margin:0;margin-top:2px;">Notify personnel about license status</p>
        </div>
      </div>
      <div style="background:#111827;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem;border-left:3px solid #fc8181;">
        <p style="color:#94a3b8;font-size:0.75rem;margin:0;">Sending to: <strong id="notifyPersonnelName" style="color:#fc8181;">—</strong></p>
        <p style="color:#64748b;font-size:0.72rem;margin:0;margin-top:3px;" id="notifyStatusLine">Status: —</p>
      </div>
      <div style="margin-bottom:0.85rem;">
        <label class="notify-label">Registered Personnel Email</label>
        <input id="notifyEmailInput" class="notify-input" type="email" placeholder="No registered email" readonly aria-readonly="true" title="This email comes from the personnel registration record and cannot be edited here." />
      </div>
      <div style="margin-bottom:1rem;">
        <label class="notify-label">Message <span style="color:#ef4444;">*</span></label>
        <textarea id="notifyMessageInput" class="notify-input" rows="5" style="resize:vertical;"></textarea>
      </div>
      <div id="notifyFeedback" style="font-size:0.78rem;min-height:1.2rem;margin-bottom:0.75rem;"></div>
      <div style="display:flex;justify-content:flex-end;gap:0.6rem;">
        <button class="notify-cancel-btn" id="notifyModalCancel">Cancel</button>
        <button class="notify-send-btn" id="notifyModalSend">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
          Send Email
        </button>
      </div>
    </div>
  </div>
  
  <div id="systemModalOverlay" class="system-modal-overlay" style="display:none;">
    <div class="system-modal-box" role="dialog" aria-modal="true" aria-labelledby="systemModalTitle">
      <h3 id="systemModalTitle" class="system-modal-title">Notification</h3>
      <p id="systemModalMessage" class="system-modal-message"></p>
      <div class="system-modal-actions">
        <button type="button" id="systemModalCancel" class="notify-cancel-btn">Cancel</button>
        <button type="button" id="systemModalConfirm" class="system-modal-confirm">OK</button>
      </div>
    </div>
  </div>
  
  <div id="personnelDetailsOverlay" class="notify-modal-overlay" style="display:none;">
    <div class="notify-modal-box" style="max-width:520px;">
      <button class="notify-modal-close" id="personnelDetailsClose">&times;</button>
      <h3 style="color:#e5eaf2;font-size:1rem;font-weight:700;margin:0 0 1.1rem;">Personnel Details</h3>
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:1.2rem;">
        <div style="width:56px;height:56px;border-radius:50%;background:#1a2025;border:1px solid #2e3748;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
          <img id="pd_photo" src="" style="width:100%;height:100%;object-fit:cover;display:none;" />
          <svg id="pd_photoFallback" width="26" height="26" fill="none" stroke="#4b5a72" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div>
          <p id="pd_name" style="color:#e5eaf2;font-size:1.05rem;font-weight:700;margin:0;">—</p>
          <span id="pd_statusBadge" class="badge badge-renewed" style="margin-top:4px;display:inline-block;">Renewed</span>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:1.2rem;">
        <div><p class="notify-label" style="margin-bottom:2px;">Rank</p><p id="pd_rank" style="color:#e5eaf2;font-size:0.82rem;font-weight:600;margin:0;">—</p></div>
        <div><p class="notify-label" style="margin-bottom:2px;">AFP Serial #</p><p id="pd_serial" style="color:#e5eaf2;font-size:0.82rem;font-weight:600;margin:0;">—</p></div>
        <div><p class="notify-label" style="margin-bottom:2px;">Unit / Organization</p><p id="pd_unit" style="color:#e5eaf2;font-size:0.82rem;font-weight:600;margin:0;">—</p></div>
      </div>
      <div style="border-top:1px solid #2e3748;padding-top:0.9rem;margin-bottom:0.9rem;">
        <p style="color:#e5eaf2;font-size:0.8rem;font-weight:700;margin:0 0 8px;">📅 Renewal Information</p>
        <div id="pd_renewalInfo" style="font-size:0.8rem;color:#94a3b8;"></div>
      </div>
      <div style="border-top:1px solid #2e3748;padding-top:0.9rem;margin-bottom:0.9rem;">
        <p style="color:#e5eaf2;font-size:0.8rem;font-weight:700;margin:0 0 8px;">📋 ICS Information</p>
        <div id="pd_icsInfo" style="font-size:0.8rem;color:#94a3b8;"></div>
      </div>
      <div style="border-top:1px solid #2e3748;padding-top:0.9rem;margin-bottom:1.2rem;">
        <p style="color:#e5eaf2;font-size:0.8rem;font-weight:700;margin:0 0 6px;">Notes</p>
        <p id="pd_notes" style="font-size:0.8rem;color:#94a3b8;margin:0;">—</p>
      </div>
      <div style="display:flex;justify-content:flex-end;">
        <button class="notify-cancel-btn" id="personnelDetailsCloseBtn">Close</button>
      </div>
    </div>
  </div>
<script src="<?php echo e(asset('js/rpcsp_excel.js')); ?>"></script>
<script src="<?php echo e(asset('js/staff_report_filter.js')); ?>"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {

    const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
    const STORE_URL  = "<?php echo e(route('staff.personnel.store')); ?>";
    const NOTIFY_URL = (id) => `/staff/personnel/${id}/notify`;

    // ── THEME ──────────────────────────────────────────────────────────────
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

    // ── SIDEBAR ────────────────────────────────────────────────────────────
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

    // ── SPA NAV ────────────────────────────────────────────────────────────
    let currentPage = "dashboard";
    window.staffScrollToTop = function(behavior = "auto") {
      const mainContent = document.querySelector("main");
      if (mainContent) mainContent.scrollTo({ top: 0, left: 0, behavior });
      else window.scrollTo({ top: 0, left: 0, behavior });
    };
    function navigateTo(page) {
      document.querySelectorAll(".page-section").forEach(s => s.classList.remove("active"));
      document.querySelectorAll(".nav-link, .nav-item").forEach(l => l.classList.remove("active"));
      const target = document.getElementById("page-" + page);
      if (!target) return;
      target.classList.add("active");
      currentPage = page;
      document.querySelectorAll(`[data-page="${page}"]`).forEach(el => el.classList.add("active"));
      window.staffScrollToTop();
      if (page === "personnel") renderTable();
      if (page === "renewal" && window.renewalRenderAll) window.renewalRenderAll();
      if (page === "ics" && window.icsRefresh) window.icsRefresh();
      if (page === "report" && typeof staffPrepareReports === "function") staffPrepareReports();
      if (["personnel", "ics", "renewal"].includes(page)) loadDashboard();
    }
    document.addEventListener('rp-navigate', function(e) { navigateTo(e.detail); });
    document.querySelectorAll(".nav-link, .nav-item").forEach(btn => {
      btn.addEventListener("click", function (e) { e.preventDefault(); navigateTo(this.dataset.page); });
    });
    document.querySelectorAll(".quick-nav").forEach(btn => {
      btn.addEventListener("click", function () { navigateTo(this.dataset.page); });
    });

    // ── NOTIFICATIONS ──────────────────────────────────────────────────────
    const bell        = document.getElementById("notificationBell");
    const badge       = document.getElementById("notificationBadge");
    const dropdown    = document.getElementById("notifDropdown");
    const notifList   = document.getElementById("notifList");
    const markAllRead = document.getElementById("markAllRead");
    const notifFooter = document.getElementById("notifFooter");
    const NOTIF_URL_BELL = "<?php echo e(route('staff.notifications')); ?>";
    const NOTIF_READ_URL = "<?php echo e(route('staff.notifications.read')); ?>";
    let notificationCache = [];
    let notificationRequest = 0;
    let notificationUnreadCount = 0;

    function escapeNotificationText(value) {
      return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'
      })[character]);
    }

    function getNotifIcon(type) {
      if (type === 'expired')        return `<svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`;
      if (type === 'within_renewal') return `<svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
      if (type === 'renewed')        return `<svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
      return `<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>`;
    }
    function timeAgo(dateStr) {
      const diff = Math.floor((new Date() - new Date(dateStr)) / 1000);
      if (diff < 60)    return "Just now";
      if (diff < 3600)  return `${Math.floor(diff/60)}m ago`;
      if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
      return new Date(dateStr).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
    }
    function renderNotifications(unreadCount) {
      const count = Number(unreadCount) || 0;
      notificationUnreadCount = count;
      if (count > 0) { badge.style.display = 'flex'; badge.textContent = count > 99 ? '99+' : String(count); bell.classList.add('has-unread'); }
      else           { badge.style.display = 'none'; badge.textContent = ''; bell.classList.remove('has-unread'); }
      if (notifFooter) notifFooter.textContent = count > 0 ? `${count} unread notification${count > 1 ? 's' : ''}` : 'All caught up!';
      if (!notificationCache.length) { notifList.innerHTML = `<div class="notif-empty">No notifications yet.</div>`; return; }
      notifList.innerHTML = notificationCache.map(n => `
        <div class="notif-item ${!n.read ? 'unread' : ''}" data-notification-id="${Number(n.id)}" role="button" tabindex="0">
          <div class="notif-icon">${getNotifIcon(n.type)}</div>
          <div class="notif-content">
            <div class="notif-title ${escapeNotificationText(n.type)}">${escapeNotificationText(n.title)}</div>
            <div class="notif-message">${escapeNotificationText(n.message)}</div>
            <div class="notif-time">${escapeNotificationText(timeAgo(n.createdAt))}</div>
          </div>
          ${!n.read ? `<div class="notif-dot ${escapeNotificationText(n.type)}"></div>` : ''}
        </div>`).join('');
    }

    async function loadNotifications() {
      const request = ++notificationRequest;
      try {
        const res  = await fetch(NOTIF_URL_BELL, { credentials:'same-origin', cache:'no-store', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
        if (!res.ok) throw new Error(`Notification request failed with status ${res.status}`);
        const json = await res.json();
        if (!json.success || request !== notificationRequest) return;
        notificationCache = Array.isArray(json.notifications) ? json.notifications : [];
        renderNotifications(json.unreadCount);
      } catch (e) {
        if (request === notificationRequest && !notificationCache.length) notifList.innerHTML = `<div class="notif-empty" style="color:#fc8181;">Failed to load notifications.</div>`;
      }
    }
    async function markNotificationsRead(id = null) {
      const options = { method:'POST', credentials:'same-origin', headers:{ 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' } };
      if (id !== null) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify({ id });
      }
      const response = await fetch(NOTIF_READ_URL, options);
      if (!response.ok) throw new Error(`Notification update failed with status ${response.status}`);
      return response.json();
    }
    bell.addEventListener("click", function (e) {
      e.stopPropagation();
      const isOpen = dropdown.classList.contains("open");
      dropdown.classList.toggle("open");
      if (!isOpen) loadNotifications();
    });
    document.addEventListener("click", function (e) {
      if (!document.getElementById("notifWrapper").contains(e.target)) dropdown.classList.remove("open");
    });
    notifList.addEventListener('click', async function (event) {
      const item = event.target.closest('[data-notification-id]');
      if (!item || !item.classList.contains('unread')) return;
      const id = Number(item.dataset.notificationId);
      notificationCache = notificationCache.map(notification => notification.id === id ? { ...notification, read:true } : notification);
      renderNotifications(Math.max(0, notificationUnreadCount - 1));
      try { await markNotificationsRead(id); await loadNotifications(); } catch (error) { await loadNotifications(); }
    });
    notifList.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); event.target.click(); }
    });
    markAllRead && markAllRead.addEventListener("click", async function (event) {
      event.stopPropagation();
      notificationCache = notificationCache.map(notification => ({ ...notification, read:true }));
      renderNotifications(0);
      try { await markNotificationsRead(); await loadNotifications(); } catch (error) { await loadNotifications(); }
    });
    loadNotifications();
    setInterval(loadNotifications, 15000);
    window.addEventListener('focus', loadNotifications);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) loadNotifications(); });

    // ── DATA ───────────────────────────────────────────────────────────────
    const initialDashboardData = <?php echo json_encode($initialDashboardData ?? ['success' => true, 'metrics' => [], 'personnel' => []]) ?>;
    let personnel = Array.isArray(initialDashboardData.personnel) ? initialDashboardData.personnel : [];
    let chartsInitialized = false;
    let statusCharts = [];

    function applyDashboardMetrics(data) {
      const m = data.metrics || {};
      document.getElementById("totalNew").innerText      = m.totalNew      ?? '0';
      document.getElementById("totalRenewed").innerText  = m.totalRenewed  ?? '0';
      document.getElementById("withinRenewal").innerText = m.withinRenewal ?? '0';
      document.getElementById("expired").innerText       = m.expired       ?? '0';
      document.getElementById("pending").innerText       = m.pending       ?? '0';
      document.getElementById("totalPersonnel").innerText = personnel.length;
    }

    applyDashboardMetrics(initialDashboardData);

    async function loadDashboard(attempt = 0) {
      try {
        const res = await fetch("<?php echo e(route('staff.dashboard.data')); ?>?refresh=" + Date.now(), {
          credentials: "same-origin",
          cache: "no-store",
          headers: { "Accept":"application/json", "X-CSRF-TOKEN":CSRF }
        });
        if (res.status === 401 || res.status === 403) { window.location.href = "<?php echo e(route('login')); ?>"; return; }
        if (!res.ok) throw new Error("Dashboard request failed with status " + res.status);
        const json = await res.json();
        if (!json.success) throw new Error("Failed");
        personnel = json.personnel || [];
        const m = json.metrics || {};
        applyDashboardMetrics(json);
        try {
          if (typeof staffPrepareReports === 'function') staffPrepareReports();
        } catch (reportError) {
          console.error('Report preparation failed:', reportError);
        }
        try {
          chartsInitialized = initCharts(m.totalNew??0, m.totalRenewed??0, m.withinRenewal??0, m.expired??0, m.pending??0);
        } catch (chartError) {
          console.error('Dashboard charts failed:', chartError);
        }
        try {
          if (currentPage === 'personnel') renderTable();
          if (currentPage === 'ics' && typeof window.icsRefresh === 'function') window.icsRefresh();
          if (currentPage === 'renewal' && typeof window.renewalRenderAll === 'function') window.renewalRenderAll();
        } catch (moduleError) {
          console.error('Active module refresh failed:', moduleError);
        }
  } catch (e) {
      console.error('Dashboard load failed:', e);
      if (attempt < 2) {
        window.setTimeout(function(){ loadDashboard(attempt + 1); }, 500 * (attempt + 1));
        return;
      }
      ["totalPersonnel","totalNew","totalRenewed","withinRenewal","expired","pending"].forEach(id => {
          const el = document.getElementById(id); if(el) el.innerText='0';
      });
      // Still render empty charts so the page isn't broken
      if (!chartsInitialized) {
        chartsInitialized = initCharts(0, 0, 0, 0, 0);
      }
    }
    }
    loadDashboard();

    // ── CHARTS ─────────────────────────────────────────────────────────────
    const CHART_COLORS = ['#3ec6ff','#33b481','#ecc94b','#e53e3e','#64748b'];
    const CHART_LABELS = ['New','Renewed','Within Renewal','Expired','Pending'];
    const gridColor    = 'rgba(255,255,255,0.07)';
    const tickColor    = '#94a3b8';
    const tooltipOpts  = { backgroundColor:'#181c21', borderColor:'#363b48', borderWidth:1, titleColor:'#e5eaf2', bodyColor:'#94a3b8', padding:10 };

    function initCharts(newCount, renewed, within, expired, pending) {
      if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded; dashboard counts will still display.');
        return false;
      }

      const data = [newCount, renewed, within, expired, pending];
      if (statusCharts.length === 2) {
        statusCharts.forEach(chart => {
          chart.data.datasets[0].data = data;
          chart.update();
        });
        return true;
      }
      const lineCanvas = document.getElementById("lineChart");
      const distributionCanvas = document.getElementById("pieChart");
      if (!lineCanvas || !distributionCanvas) return false;
      statusCharts = [];
      statusCharts.push(new Chart(lineCanvas.getContext("2d"), {
        type:"bar", data:{ labels:CHART_LABELS, datasets:[{ label:"Personnel Count", data, backgroundColor:CHART_COLORS, borderRadius:6, borderSkipped:false, barPercentage:0.55 }] },
        options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false }, tooltip:tooltipOpts }, scales:{ x:{ grid:{ color:gridColor }, ticks:{ color:tickColor, font:{size:11} }, border:{color:'transparent'} }, y:{ grid:{ color:gridColor }, ticks:{ color:tickColor, stepSize:1 }, beginAtZero:true, border:{color:'transparent'} } } }
      }));
      statusCharts.push(new Chart(distributionCanvas.getContext("2d"), {
        type:"bar", data:{ labels:CHART_LABELS, datasets:[{ label:"Count", data, backgroundColor:CHART_COLORS, borderRadius:6, borderSkipped:false, barPercentage:0.6 }] },
        options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false }, tooltip:tooltipOpts }, scales:{ x:{ grid:{ color:gridColor }, ticks:{ color:tickColor, stepSize:1 }, beginAtZero:true, border:{color:'transparent'} }, y:{ grid:{ color:'transparent' }, ticks:{ color:tickColor, font:{size:11} }, border:{color:'transparent'} } } }
      }));
      return true;
    }

    // ── TABLE ──────────────────────────────────────────────────────────────
    let currentSort = "status-asc";
    const ROWS_PER_PAGE = 15;
    let currentTablePage = 1;

    function statusBadge(status) {
      const map = {
        new:     '<span class="badge badge-new">New</span>',
        renewed: '<span class="badge badge-renewed">Renewed</span>',
        within:  '<span class="badge badge-within">Within Renewal</span>',
        expired: '<span class="badge badge-expired">Expired</span>',
        pending: '<span class="badge badge-pending">Pending</span>'
      };
      return map[status] || map['pending'];
    }

    function sortList(list) {
      const [key, dir] = currentSort.split("-");
      return list.slice().sort((a, b) => {
        if (key === 'status') {
          const statusOrder = {new:0, pending:1, within:2, renewed:3, expired:4};
          const statusA = String(a.approvedStatus || 'pending').toLowerCase();
          const statusB = String(b.approvedStatus || 'pending').toLowerCase();
          const statusDifference = (statusOrder[statusA] ?? 1) - (statusOrder[statusB] ?? 1);
          if (statusDifference !== 0) return dir === 'asc' ? statusDifference : -statusDifference;
          return Number(b.itemNumber || 0) - Number(a.itemNumber || 0);
        }
        let av = ["itemNumber","qtyAmmo"].includes(key) ? Number(a[key]) : (a[key]||"").toString().toLowerCase();
        let bv = ["itemNumber","qtyAmmo"].includes(key) ? Number(b[key]) : (b[key]||"").toString().toLowerCase();
        if (av < bv) return dir === "asc" ? -1 : 1;
        if (av > bv) return dir === "asc" ? 1 : -1;
        return 0;
      });
    }

    function renderTable() {
      const q       = (document.getElementById('personnelNameFilter')?.value || '').trim().toLowerCase();
      const rankF   = (document.getElementById('personnelRankFilter')?.value || '').trim().toLowerCase();
      const unitF   = (document.getElementById('personnelUnitFilter')?.value || '').trim().toLowerCase();
      const statusF = document.getElementById("approvalFilter")?.value || "";
      let filtered  = [...new Map(personnel.map(p => [p.itemNumber, p])).values()].filter(p => {
        const nameMatch   = [p.firstName, p.middleName, p.lastName].some(v => (v||"").toLowerCase().includes(q));
        const statusMatch = statusF ? (p.approvedStatus||'pending').toLowerCase() === statusF.toLowerCase() : true;
        const rankMatch = !rankF || (p.rank || '').trim().toLowerCase() === rankF;
        const unitMatch = !unitF || (p.unit || '').toLowerCase().includes(unitF);
        return nameMatch && statusMatch && rankMatch && unitMatch;
      });
      filtered = sortList(filtered);
      const totalPages = Math.max(1, Math.ceil(filtered.length / ROWS_PER_PAGE));
      if (currentTablePage > totalPages) currentTablePage = 1;
      const start    = (currentTablePage - 1) * ROWS_PER_PAGE;
      const pageData = filtered.slice(start, start + ROWS_PER_PAGE);
      const tbody    = document.getElementById("personnelTableBody");
      const tableInfo= document.getElementById("tableInfo");

      if (!filtered.length) {
    tbody.innerHTML = `<tr><td colspan="11" class="text-center py-6 text-gray-400">No records found.</td></tr>`;
        if (tableInfo) tableInfo.textContent = "Showing 0 records";
        renderPagination(0); return;
      }

      tbody.innerHTML = pageData.map(r => {
    const approved = r.approvedStatus || 'pending';
    return `<tr class="border-b border-[#1a2025] hover:bg-[#1a2025] transition-colors">
      <td class="py-2 px-2 force-light-text">${r.itemNumber??''}</td>
      <td class="py-2 px-2 force-light-text">${r.dateOfValidity??''}</td>
      <td class="py-2 px-2 force-light-text">${(r.lastName??'').toUpperCase()} <span class="text-[#64748b]">|</span> ${r.rank??''}</td>
      <td class="py-2 px-2 force-light-text">${r.firstName??''}</td>
      <td class="py-2 px-2 force-light-text">${r.middleName??''}</td>
      <td class="py-2 px-2 force-light-text">${r.afpSerialNumber??''}</td>
      <td class="py-2 px-2 force-light-text">${r.dateOfBirth??''}</td>
      <td class="py-2 px-2 force-light-text">${r.pistolNomenclature??''}</td>
      <td class="py-2 px-2 force-light-text">${r.pistolSerialNumber??''}</td>
      <td class="py-2 px-2 force-light-text">${r.qtyAmmo??''}</td>
      <td class="py-2 px-2">${statusBadge(approved)}</td>
    </tr>`;
  }).join("");

  if (tableInfo) tableInfo.textContent = `Showing ${start+1}–${Math.min(start+ROWS_PER_PAGE, filtered.length)} of ${filtered.length} records`;
  renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
      const container = document.getElementById("pagination");
      if (!container || totalPages <= 1) { if (container) container.innerHTML = ""; return; }
      let html = "";
      for (let i = 1; i <= totalPages; i++) {
        const active = i === currentTablePage ? "bg-[#33b481] text-white" : "bg-[#1a2025] text-[#94a3b8] hover:bg-[#23272f]";
        html += `<button class="px-2 py-1 rounded text-xs font-semibold transition-colors ${active}" data-p="${i}">${i}</button>`;
      }
      container.innerHTML = html;
      container.querySelectorAll("button").forEach(btn => {
        btn.addEventListener("click", function () { currentTablePage = parseInt(this.dataset.p); renderTable(); });
      });
    }

    document.getElementById("sortSelect")?.addEventListener("change", e => { currentSort = e.target.value; renderTable(); });
    document.getElementById("approvalFilter")?.addEventListener("change", () => { currentTablePage = 1; renderTable(); });
    ['personnelNameFilter','personnelUnitFilter'].forEach(id => document.getElementById(id)?.addEventListener('input', () => { currentTablePage = 1; renderTable(); }));
    document.getElementById('personnelRankFilter')?.addEventListener('change', () => { currentTablePage = 1; renderTable(); });
    document.getElementById('personnelClearFilters')?.addEventListener('click', () => {
      ['personnelNameFilter','personnelRankFilter','personnelUnitFilter','approvalFilter'].forEach(id => { document.getElementById(id).value = ''; });
      currentTablePage = 1;
      renderTable();
    });

    // ── REGISTER MODAL ─────────────────────────────────────────────────────
    const registerOverlay  = document.getElementById('registerModalOverlay');
    const registerForm     = document.getElementById('registerPersonnelForm');
    const registerFeedback = document.getElementById('registerFeedback');
    let activeEmailFmt     = 'firstname.lastname';

    function resetEmailSection() {
      const preview = document.getElementById('emailPreviewBox');
      if (preview) {
        preview.textContent = 'Fill in First & Last Name, then click Generate';
        preview.classList.add('empty');
        preview.style.color = '';
      }
      const useBtn = document.getElementById('emailUseBtn');
      if (useBtn) useBtn.style.display = 'none';
      document.querySelectorAll('.email-fmt-btn').forEach((b,i) => b.classList.toggle('active', i === 0));
      activeEmailFmt = 'firstname.lastname';
      const domainInput = document.getElementById('emailDomainInput');
      if (domainInput) domainInput.value = 'army.mil.ph';
    }

    function openRegisterModal() {
      registerForm.reset();
      registerFeedback.textContent = '';
      registerFeedback.style.color = '';
      const btn = document.getElementById('registerSubmitBtn');
      btn.disabled = false;
      btn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:5px;"><path d="M12 4v16m8-8H4"/></svg>Register Personnel`;
      btn.style.background = '';
      resetEmailSection();
      registerOverlay.style.display = 'flex';
    }

    function closeRegisterModal() { registerOverlay.style.display = 'none'; }

    document.getElementById('registerPersonnelBtn')?.addEventListener('click', openRegisterModal);
    document.getElementById('registerModalClose').addEventListener('click', closeRegisterModal);
    document.getElementById('registerModalCancel').addEventListener('click', closeRegisterModal);
    registerOverlay.addEventListener('click', function(e) { if (e.target === this) closeRegisterModal(); });

    // ── EMAIL GENERATOR ────────────────────────────────────────────────────
    document.querySelectorAll('.email-fmt-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.email-fmt-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeEmailFmt = this.dataset.fmt;
      });
    });

    function slugify(str) {
      return (str || '').toLowerCase().trim().replace(/\s+/g, '').replace(/[^a-z0-9]/g, '');
    }

    function generateEmail() {
      const firstName = slugify(document.getElementById('regFirstName').value);
      const lastName  = slugify(document.getElementById('regLastName').value);
      const domain    = (document.getElementById('emailDomainInput').value || 'army.mil.ph').trim().replace(/^@/, '');
      const preview   = document.getElementById('emailPreviewBox');
      const useBtn    = document.getElementById('emailUseBtn');

      if (!firstName || !lastName) {
        preview.textContent = '⚠ Enter First and Last Name first.';
        preview.classList.add('empty');
        preview.style.color = '#f87171';
        useBtn.style.display = 'none';
        return null;
      }

      let local = '';
      switch (activeEmailFmt) {
        case 'firstname.lastname': local = `${firstName}.${lastName}`;          break;
        case 'f.lastname':         local = `${firstName.charAt(0)}.${lastName}`; break;
        case 'lastname.firstname': local = `${lastName}.${firstName}`;          break;
        case 'firstnamelastname':  local = `${firstName}${lastName}`;           break;
        default:                   local = `${firstName}.${lastName}`;
      }

      const email = `${local}@${domain}`;
      preview.textContent = email;
      preview.classList.remove('empty');
      preview.style.color = '';
      useBtn.style.display = 'inline-block';
      document.getElementById('regEmailInput').value = email;
      return email;
    }

    document.getElementById('generateEmailBtn').addEventListener('click', generateEmail);
    document.getElementById('emailUseBtn').addEventListener('click', function () {
      const val = document.getElementById('emailPreviewBox').textContent;
      if (val && !val.includes('⚠') && !val.includes('Fill in')) {
        document.getElementById('regEmailInput').value = val;
        this.textContent = '✓ Applied!';
        setTimeout(() => { this.textContent = 'Use ↑'; }, 1500);
      }
    });
    ['regFirstName', 'regLastName'].forEach(id => {
      document.getElementById(id)?.addEventListener('input', function () {
        const preview = document.getElementById('emailPreviewBox');
        if (!preview.classList.contains('empty')) generateEmail();
      });
    });

    // ── REGISTER FORM SUBMIT ───────────────────────────────────────────────
    registerForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const submitBtn = document.getElementById('registerSubmitBtn');
      submitBtn.disabled = true;
      submitBtn.innerHTML = `<span class="saving-spinner"></span> Registering...`;
      registerFeedback.style.color = '#94a3b8';
      registerFeedback.textContent = 'Saving record, please wait...';

      const emailValue = document.getElementById('regEmailInput').value.trim();
      const data = {};
      new FormData(registerForm).forEach((val, key) => { data[key] = val; });
      data.approvedStatus = 'new';
      data.email = emailValue;

      try {
        const res  = await fetch(STORE_URL, { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF }, body:JSON.stringify(data) });
        const json = await res.json();
        if (json.success) {
          registerFeedback.style.color = '#33b481';
          registerFeedback.textContent = '✓ Personnel registered successfully!';
          submitBtn.innerHTML = '✓ Registered!';
          submitBtn.style.background = '#276749';
          const newRecord = json.data || { ...data, itemNumber: personnel.length + 1 };
          newRecord.approvedStatus = 'new';
          newRecord.email = emailValue;
          personnel.unshift(newRecord);
          const newEl = document.getElementById('totalNew');
          if (newEl) newEl.innerText = parseInt(newEl.innerText || 0) + 1;
          document.getElementById("totalPersonnel").innerText = personnel.length;
          setTimeout(() => { closeRegisterModal(); if (currentPage === 'personnel') renderTable(); }, 1800);
        } else {
          registerFeedback.style.color = '#fc8181';
          registerFeedback.textContent = '✗ ' + (json.error || 'Failed to register personnel.');
          submitBtn.disabled = false;
          submitBtn.innerHTML = `+ Register Personnel`;
        }
      } catch (err) {
        registerFeedback.style.color = '#fc8181';
        registerFeedback.textContent = '✗ Network error. Please try again.';
        submitBtn.disabled = false;
        submitBtn.innerHTML = `+ Register Personnel`;
      }
    });

    // ── NOTIFY MODAL (FIXED — complete function, no ellipsis) ──────────────
    let currentNotifyId = null;

    function openNotifyModal(id, name, status) {
      currentNotifyId = id;


      // Set personnel name display
      document.getElementById('notifyPersonnelName').textContent = name;
      document.getElementById('notifyPersonnelName').style.color =   status === 'expired' ? '#fc8181' :
    status === 'renewed' ? '#68d391' : '#fde68a';

      document.getElementById('notifyStatusLine').textContent =  status === 'expired'  ? 'Status: ✕ Expired — Immediate renewal required' :
    status === 'renewed'  ? 'Status: ✓ Renewed — License approved and active' :
                            'Status: ⏱ Within Renewal Period — Renewal due soon';

      // Always use the address saved during personnel registration.
      const registeredPersonnel = personnel.find(function(p) { return p.itemNumber == id; });
      const savedEmail = registeredPersonnel ? registeredPersonnel.email : '';
      const cleanEmail = (savedEmail && savedEmail !== 'undefined' && savedEmail !== 'null') ? String(savedEmail).trim() : '';

      const emailInput = document.getElementById('notifyEmailInput');
      emailInput.value = cleanEmail;
      emailInput.readOnly = true;
      emailInput.title = 'Registered email address. Edit the personnel record to change it.';

      // Reset feedback
      document.getElementById('notifyFeedback').textContent = '';
      document.getElementById('notifyFeedback').style.color = '';

      // Set send button
      const sendBtn = document.getElementById('notifyModalSend');
      sendBtn.disabled = !cleanEmail;
      sendBtn.className =   status === 'expired' ? 'notify-send-btn' :
    status === 'renewed' ? 'notify-send-btn' : 'notify-within-btn';
      sendBtn.style.background = status === 'renewed' ? '#276749' : '';
      sendBtn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>Send Email`;

      // Default message
    document.getElementById('notifyMessageInput').value =
    status === 'expired'
      ? `Dear ${name},\n\nThis is to inform you that your pistol license has already EXPIRED. Please coordinate immediately with the Property Accountability Office to process your renewal at the earliest possible time.\n\nFailure to renew may result in administrative action.\n\nRegards,\nAPAO Renewal System`
      : status === 'renewed'
      ? `Dear ${name},\n\nWe are pleased to inform you that your pistol license has been successfully RENEWED and approved by the Administrator. Your license is now active.\n\nPlease keep this as your official notification of renewal.\n\nRegards,\nAPAO Renewal System`
      : `Dear ${name},\n\nThis is a reminder that your pistol license is within the renewal period and will expire soon. Please process your renewal before the expiration date.\n\nPlease coordinate with the Property Accountability Office at the soonest possible time.\n\nRegards,\nAPAO Renewal System`;

      // Show modal
      document.getElementById('notifyModalOverlay').style.display = 'flex';

      // Show warning if no email
      if (!cleanEmail) {
        setTimeout(() => {
          document.getElementById('notifyFeedback').style.color = '#f6e05e';
          document.getElementById('notifyFeedback').textContent = '⚠ No registered email found. Update the personnel record before sending.';
        }, 100);
      }
    }
    
    window.openNotifyModal = openNotifyModal;

    function closeNotifyModal() {
      document.getElementById('notifyModalOverlay').style.display = 'none';
      currentNotifyId = null;
    }

    document.getElementById('notifyModalClose').addEventListener('click', closeNotifyModal);
    document.getElementById('notifyModalCancel').addEventListener('click', closeNotifyModal);
    document.getElementById('notifyModalOverlay').addEventListener('click', function(e) { if (e.target === this) closeNotifyModal(); });

    document.getElementById('notifyModalSend').addEventListener('click', async function () {
      const notifyPersonnel = personnel.find(function(p) { return p.itemNumber == currentNotifyId; });
      const email    = notifyPersonnel && notifyPersonnel.email ? String(notifyPersonnel.email).trim() : '';
      const message  = document.getElementById('notifyMessageInput').value.trim();
      const feedback = document.getElementById('notifyFeedback');
      if (!email)   { feedback.style.color = '#fc8181'; feedback.textContent = '⚠ Please enter the personnel email address.'; return; }
      if (!message) { feedback.style.color = '#fc8181'; feedback.textContent = '⚠ Please enter a message.'; return; }
      this.disabled = true;
      this.innerHTML = `Sending...`;
      feedback.style.color = '#94a3b8'; feedback.textContent = 'Sending email, please wait...';
      try {
        const res  = await fetch(NOTIFY_URL(currentNotifyId), { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF}, body:JSON.stringify({message}) });
        const json = await res.json();
        if (json.success) {
          feedback.style.color = '#33b481'; feedback.textContent = '✓ Email sent successfully!';
          this.innerHTML = '✓ Sent!'; this.style.background = '#276749';
          setTimeout(() => closeNotifyModal(), 1800);
        } else {
          feedback.style.color = '#fc8181'; feedback.textContent = '✗ ' + (json.error || 'Failed to send email.');
          this.disabled = false;
          this.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>Send Email`;
        }
      } catch (e) {
        feedback.style.color = '#fc8181'; feedback.textContent = '✗ Network error.';
        this.disabled = false;
        this.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>Send Email`;
      }
    });

    // ── PERSONNEL EXCEL EXPORT ─────────────────────────────────────────────
    document.getElementById("exportBtn")?.addEventListener("click", () => {
      if (!personnel.length) return;
      exportPersonnelListExcel({
        filename: "APAO_Personnel_List_" + new Date().toISOString().slice(0, 10) + ".xls",
        generatedDate: new Date().toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" }),
        rows: personnel
      });
    });

    // ── REPORT ─────────────────────────────────────────────────────────────
    const reportFrom = document.getElementById("reportFrom");
    const reportTo = document.getElementById("reportTo");
    const reportStatus = document.getElementById("reportStatus");
    const reportFeedback = document.getElementById("reportFilterFeedback");

    function getFilteredReportRows() {
      return window.StaffReportFilter.filter(personnel, {
        from: reportFrom?.value || '',
        to: reportTo?.value || '',
        status: reportStatus?.value || ''
      });
    }

    function showReportFeedback(message, isError = false) {
      if (!reportFeedback) return;
      reportFeedback.textContent = message;
      reportFeedback.style.color = isError ? '#fc8181' : '#64748b';
    }

    function syncReportDateLimits() {
      if (reportFrom && reportTo) {
        reportTo.min = reportFrom.value || '';
        reportFrom.max = reportTo.value || '';
      }
      showReportFeedback('');
    }

    reportFrom?.addEventListener('change', syncReportDateLimits);
    reportTo?.addEventListener('change', syncReportDateLimits);

    function renderStaffRenewalReport() {
      const result = getFilteredReportRows();
      const tbody = document.getElementById("reportTableBody");

      if (result.error) {
        showReportFeedback(result.error, true);
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-red-400">${result.error}</td></tr>`;
        return;
      }

      const filtered = result.rows;
      const rangeLabel = [reportFrom?.value, reportTo?.value].filter(Boolean).join(' to ');
      showReportFeedback(`${filtered.length} record${filtered.length === 1 ? '' : 's'} found${rangeLabel ? ` for validity dates ${rangeLabel}` : ''}.`);

      if (!filtered.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-400">No records match the selected validity dates and status.</td></tr>`;
        return;
      }

      const badgeMap = {
        new:     `<span class="badge report-status-badge badge-new">New</span>`,
        renewed: `<span class="badge report-status-badge badge-renewed">Renewed</span>`,
        within:  `<span class="badge report-status-badge badge-within">Within Renewal</span>`,
        expired: `<span class="badge report-status-badge badge-expired">Expired</span>`,
        pending: `<span class="badge report-status-badge badge-pending">Pending</span>`
      };
      tbody.innerHTML = filtered.map(r => {
        const s = String(r.approvedStatus || 'pending').trim().toLowerCase();
        return `<tr class="border-b border-[#1a2025] hover:bg-[#1a2025] transition-colors">
          <td class="py-2 px-2 force-light-text">${r.itemNumber??''}</td>
          <td class="py-2 px-2 force-light-text">${r.lastName??''}, ${r.firstName??''} ${r.middleName??''}</td>
          <td class="py-2 px-2 force-light-text">${r.afpSerialNumber??''}</td>
          <td class="py-2 px-2 force-light-text">${r.dateOfValidity??''}</td>
          <td class="py-2 px-2">${badgeMap[s]||badgeMap['pending']}</td>
        </tr>`;
      }).join("");
    }

    document.getElementById("generateReportBtn")?.addEventListener("click", renderStaffRenewalReport);

    document.getElementById("exportReportBtn")?.addEventListener("click", () => {
      const result = getFilteredReportRows();
      if (result.error) {
        showReportFeedback(result.error, true);
        return;
      }

      const filtered = result.rows;
      if (!filtered.length) {
        showReportFeedback('There are no matching records to export.', true);
        return;
      }

      const from = reportFrom?.value || '';
      const to = reportTo?.value || '';
      const statusLabel = reportStatus?.selectedOptions?.[0]?.textContent?.trim() || 'All';
      const period = from || to
        ? `${from || 'Beginning'} to ${to || 'Present'}`
        : 'All Validity Dates';

      window.exportPersonnelRenewalExcel({
        filename: "APAO_Personnel_Renewal_Report_" + new Date().toISOString().slice(0, 10) + ".xls",
        generatedDate: new Date().toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" }),
        period: period + ' | Status: ' + statusLabel,
        rows: filtered.map(r => ({
          itemNumber: r.itemNumber ?? '',
          dateOfValidity: r.dateOfValidity ?? '',
          status: String(r.approvedStatus || 'pending').replace(/_/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase()),
          lastName: r.lastName ?? '',
          firstName: r.firstName ?? '',
          middleName: r.middleName ?? '',
          afpSerialNumber: r.afpSerialNumber ?? '',
          dateOfBirth: r.dateOfBirth ?? '',
          pistolNomenclature: r.pistolNomenclature ?? '',
          pistolSerialNumber: r.pistolSerialNumber ?? '',
          qtyAmmo: r.qtyAmmo ?? 0
        }))
      });
    });


    // ── STAFF REPORT CENTER / RPCSP ────────────────────────────────────────
    const staffReportRenewalTab = document.getElementById('staffReportRenewalTab');
    const staffReportRpcspTab = document.getElementById('staffReportRpcspTab');
    const staffRenewalReportPanel = document.getElementById('staffRenewalReportPanel');
    const staffRpcspReportPanel = document.getElementById('staffRpcspReportPanel');

    const staffRpcspAsOfDate = document.getElementById('staffRpcspAsOfDate');
    const staffRpcspFundCluster = document.getElementById('staffRpcspFundCluster');
    const staffRpcspOfficer = document.getElementById('staffRpcspOfficer');
    const staffRpcspDesignation = document.getElementById('staffRpcspDesignation');
    const staffRpcspAssumptionDate = document.getElementById('staffRpcspAssumptionDate');
    const staffRpcspUnitValue = document.getElementById('staffRpcspUnitValue');
    const staffRpcspUnitFilter = document.getElementById('staffRpcspUnitFilter');
    const staffRpcspRemarks = document.getElementById('staffRpcspRemarks');

    if (staffRpcspAsOfDate && !staffRpcspAsOfDate.value) {
      staffRpcspAsOfDate.value = new Date().toISOString().slice(0, 10);
    }

    function staffSwitchReport(type) {
      const rpcsp = type === 'rpcsp';

      staffReportRenewalTab?.classList.toggle('active', !rpcsp);
      staffReportRpcspTab?.classList.toggle('active', rpcsp);
      staffRenewalReportPanel?.classList.toggle('active', !rpcsp);
      staffRpcspReportPanel?.classList.toggle('active', rpcsp);

      if (rpcsp) {
        staffPrepareReports();
        staffRenderRpcsp();
      }
    }

    staffReportRenewalTab?.addEventListener('click', () => staffSwitchReport('renewal'));
    staffReportRpcspTab?.addEventListener('click', () => staffSwitchReport('rpcsp'));

    function staffRpcspEscape(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function staffRpcspMoney(value) {
      const amount = Number(value || 0);
      return '₱' + amount.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    function staffRpcspDate(value) {
      if (!value) return '—';

      const raw = String(value);
      const d = new Date(raw.length === 10 ? raw + 'T00:00:00' : raw);

      if (isNaN(d.getTime())) return raw;

      return d.toLocaleDateString('en-US', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      });
    }

    function staffPopulateRpcspUnits() {
      if (!staffRpcspUnitFilter) return;

      const current = staffRpcspUnitFilter.value;

      const units = [...new Set(
        personnel
          .map(p => (p.unit || '').trim())
          .filter(Boolean)
      )].sort((a, b) => a.localeCompare(b));

      staffRpcspUnitFilter.innerHTML =
        '<option value="">All Units</option>' +
        units.map(unit =>
          `<option value="${staffRpcspEscape(unit)}">${staffRpcspEscape(unit)}</option>`
        ).join('');

      if (units.includes(current)) {
        staffRpcspUnitFilter.value = current;
      }
    }

    function staffPrepareReports() {
      staffPopulateRpcspUnits();
    }

    function staffGetRpcspRows() {
      const selectedUnit = (staffRpcspUnitFilter?.value || '').trim().toLowerCase();

      return personnel.filter(p => {
        if (!selectedUnit) return true;
        return (p.unit || '').trim().toLowerCase() === selectedUnit;
      });
    }

    function staffRenderRpcsp() {
      const body = document.getElementById('staffRpcspTableBody');
      if (!body) return;

      const rows = staffGetRpcspRows();
      const unitValue = Number(staffRpcspUnitValue?.value || 0);
      const defaultRemarks = staffRpcspRemarks?.value || 'Serviceable';

      document.getElementById('staffRpcspPreviewAsOf').textContent =
        staffRpcspDate(staffRpcspAsOfDate?.value);

      document.getElementById('staffRpcspPreviewFund').textContent =
        staffRpcspFundCluster?.value.trim() || 'General Fund';

      document.getElementById('staffRpcspPreviewOfficer').textContent =
        staffRpcspOfficer?.value.trim() || '—';

      document.getElementById('staffRpcspPreviewDesignation').textContent =
        staffRpcspDesignation?.value.trim() || '—';

      document.getElementById('staffRpcspPreviewAssumption').textContent =
        staffRpcspDate(staffRpcspAssumptionDate?.value);

      if (!rows.length) {
        body.innerHTML =
          '<tr><td colspan="11" class="rpcsp-empty">No personnel/firearm records match the selected filter.</td></tr>';

        document.getElementById('staffRpcspTotalCount').textContent = '0';
        document.getElementById('staffRpcspTotalValue').textContent = staffRpcspMoney(0);
        return;
      }

      body.innerHTML = rows.map(p => {
        const description =
          (p.pistolNomenclature || '').trim() || 'Pistol';

        // The current system does not yet have a dedicated
        // semi_expendable_property_number field.
        // Use firearm/pistol serial number first, then AFP serial as fallback.
        const propertyNumber =
          (p.pistolSerialNumber || '').trim() ||
          (p.afpSerialNumber || '').trim() ||
          '—';

        const balance = 1;
        const onHand = 1;
        const shortageQuantity = Math.max(0, balance - onHand);
        const shortageValue = shortageQuantity * unitValue;

        return `
          <tr>
            <td>Pistol</td>
            <td>${staffRpcspEscape(description)}</td>
            <td>${staffRpcspEscape(propertyNumber)}</td>
            <td>ea</td>
            <td>${staffRpcspMoney(unitValue)}</td>
            <td>${balance}</td>
            <td>${onHand}</td>
            <td>${shortageQuantity ? shortageQuantity : ''}</td>
            <td>${shortageValue ? staffRpcspMoney(shortageValue) : ''}</td>
            <td>${staffRpcspEscape(defaultRemarks)}</td>
            <td>${staffRpcspEscape(staffRpcspDate(p.dateOfValidity))}</td>
          </tr>
        `;
      }).join('');

      document.getElementById('staffRpcspTotalCount').textContent =
        String(rows.length);

      document.getElementById('staffRpcspTotalValue').textContent =
        staffRpcspMoney(rows.length * unitValue);
    }

    document.getElementById('staffPreviewRpcspBtn')?.addEventListener('click', staffRenderRpcsp);
    staffRpcspUnitFilter?.addEventListener('change', staffRenderRpcsp);
    staffRpcspRemarks?.addEventListener('change', staffRenderRpcsp);

    document.getElementById('staffPrintRpcspBtn')?.addEventListener('click', function () {
      staffRenderRpcsp();

      const documentEl = document.getElementById('staffRpcspDocument');
      if (!documentEl) return;

      const printWindow = window.open('', '_blank');

      if (!printWindow) {
        showSystemMessage?.('Please allow pop-ups to print the RPCSP.', 'RPCSP');
        return;
      }

      printWindow.document.write(`<!doctype html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>RPCSP - ${staffRpcspEscape(staffRpcspAsOfDate?.value || '')}</title>
          <style>
            @page{size:A4 landscape;margin:8mm;}
            *{box-sizing:border-box;}
            body{margin:0;background:#fff;color:#111;font-family:Arial,Helvetica,sans-serif;}
            .rpcsp-document{width:100%;background:#fff;color:#111;padding:0;}
            .rpcsp-title{text-align:center;font-weight:700;font-size:14px;line-height:1.15;margin:0;}
            .rpcsp-subtitle{text-align:center;font-size:10px;line-height:1.2;margin:1px 0;}
            .rpcsp-asof{text-align:center;font-size:10px;margin:2px 0 10px;}
            .rpcsp-fund{font-size:10px;text-decoration:underline;margin:0 0 9px;}
            .rpcsp-forwhich{font-size:10px;line-height:1.3;margin:0 0 8px;}
            .accountable-name{font-weight:700;text-transform:uppercase;}
            .rpcsp-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:8px;}
            .rpcsp-table th,.rpcsp-table td{border:1px solid #222;padding:3px 4px;vertical-align:middle;}
            .rpcsp-table th{text-align:center;font-weight:700;}
            .rpcsp-table td{text-align:center;}
            .rpcsp-table td:nth-child(1),.rpcsp-table td:nth-child(2),.rpcsp-table td:nth-child(10){text-align:left;}
            .rpcsp-summary{display:flex;justify-content:flex-end;gap:25px;margin-top:8px;font-size:9px;}
            .rpcsp-note{font-size:8px;color:#555;margin-top:7px;}
            .rpcsp-certification{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:36px;margin-top:30px;font-size:10px;line-height:1.3;}
            .rpcsp-certification-title{font-weight:700;margin:0 0 34px;}
            .rpcsp-certification-column{break-inside:avoid;page-break-inside:avoid;}
            .rpcsp-signatory{break-inside:avoid;page-break-inside:avoid;}
            .rpcsp-signatory + .rpcsp-signatory{margin-top:28px;}
            .rpcsp-signatory p{margin:0;}
            .rpcsp-signatory-name{font-weight:700;}
          </style>
          
</head>
        <body>
          <div class="rpcsp-document">${documentEl.innerHTML}</div>
        </body>
        </html>`);

      printWindow.document.close();

      setTimeout(() => {
        printWindow.focus();
        printWindow.print();
      }, 350);
    });

    document.getElementById('staffExportRpcspBtn')?.addEventListener('click', function () {
      const rows = staffGetRpcspRows();
      if (!rows.length) {
        showSystemMessage?.('No RPCSP records are available to export.', 'RPCSP');
        return;
      }
      const unitValue = Number(staffRpcspUnitValue?.value || 0);
      const defaultRemarks = staffRpcspRemarks?.value || 'Serviceable';

      exportRpcspExcel({
        filename: 'RPCSP_' + (staffRpcspAsOfDate?.value || new Date().toISOString().slice(0, 10)) + '.xls',
        asOf: staffRpcspDate(staffRpcspAsOfDate?.value),
        fund: staffRpcspFundCluster?.value.trim() || 'General Fund',
        officer: staffRpcspOfficer?.value.trim() || '—',
        designation: staffRpcspDesignation?.value.trim() || '—',
        assumption: staffRpcspDate(staffRpcspAssumptionDate?.value),
        totalValue: rows.length * unitValue,
        rows: rows.map(p => ({
          article: 'Pistol',
          description: p.pistolNomenclature || 'Pistol',
          propertyNumber: (p.pistolSerialNumber || '').trim() || (p.afpSerialNumber || '').trim() || '—',
          unit: 'ea', unitValue: unitValue, balance: 1, onHand: 1,
          shortageQuantity: '', shortageValue: '', remarks: defaultRemarks,
          date: staffRpcspDate(p.dateOfValidity)
        }))
      });
    });

  // ── RENEWAL MODULE ──────────────────────────────────────────────────────
    (function initRenewal() {
      var renewalTab = 'renewed';
      var renewalPage = 1;
      var RENEWAL_PER_PAGE = 10;
      var renewalSelected = new Set();
      var renewalFiltered = [];
      var TAB_STYLES = {
        renewed: { border:'#1a5c3a', bg:'#0c2418', color:'#33b481' },
        within:  { border:'#5c4a1a', bg:'#241c06', color:'#f6e05e' },
        expired: { border:'#5c1a1a', bg:'#2d0a0a', color:'#fc8181' }
      };

      window.renewalSetTab = function(tab) {
        renewalTab = tab; renewalPage = 1;
        renewalSelected.clear();
        ['renewed','within','expired'].forEach(function(t) {
          var el = document.getElementById('renewaltab-' + t);
          if (!el) return;
          var s = TAB_STYLES[t];
          if (t === tab) { el.style.background = s.bg; el.style.borderColor = s.border; el.style.color = s.color; }
          else { el.style.background = 'transparent'; el.style.borderColor = '#2a3748'; el.style.color = '#64748b'; }
        });
        var notifyAllBtn = document.getElementById('renewalNotifyAllBtn');
        var footnote = document.getElementById('renewalFootnote');
        if (tab === 'renewed') {
          if (notifyAllBtn) notifyAllBtn.style.display = 'none';
          if (footnote) footnote.style.display = 'none';
        } else {
          if (notifyAllBtn) {
            notifyAllBtn.style.display = 'inline-flex';
            if (tab === 'expired') { notifyAllBtn.style.background = '#2d0a0a'; notifyAllBtn.style.color = '#fc8181'; notifyAllBtn.style.borderColor = '#7f1d1d'; }
            else { notifyAllBtn.style.background = '#241b07'; notifyAllBtn.style.color = '#f4b63f'; notifyAllBtn.style.borderColor = '#b77913'; }
          }
          if (footnote) footnote.style.display = 'block';
        }
        renewalRenderTable();
      };

      function renewalPopulateUnits() {
        var sel = document.getElementById('renewalUnitFilter');
        if (!sel) return;
        var current = sel.value;
        var units = Array.from(new Set(personnel.map(function(p){ return p.unit; }).filter(Boolean))).sort();
        sel.innerHTML = '<option value="">All Units</option>' + units.map(function(u){ return '<option value="'+u+'">'+u+'</option>'; }).join('');
        sel.value = current || '';
      }

      function renewalSummary() {
        ['renewed','within','expired'].forEach(function(t) {
          var cnt = personnel.filter(function(p){ return (p.approvedStatus||'pending') === t; }).length;
          var a = document.getElementById('renewal-sum-' + t); if (a) a.textContent = cnt;
          var b = document.getElementById('renewaltab-count-' + t); if (b) b.textContent = cnt;
        });
      }

      function daysRemaining(dateStr) {
        if (!dateStr) return null;
        var parts = String(dateStr).slice(0, 10).split('-');
        var target = parts.length === 3
          ? new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]))
          : new Date(dateStr);
        if (isNaN(target)) return null;
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        return Math.round((target - today) / (1000*60*60*24));
      }

      function renewalDurationBadge(r) {
        var days = daysRemaining(r.dateOfValidity);
        if (days === null) return '<span class="text-[#94a3b8]">No validity date</span>';
        if (days < 0) return '<span class="badge badge-text badge-expired">' + Math.abs(days) + ' day' + (Math.abs(days) === 1 ? '' : 's') + ' overdue</span>';
        if (days === 0) return '<span class="badge badge-text badge-within">Expires today</span>';
        return '<span class="badge badge-text ' + (renewalTab === 'within' ? 'badge-within' : 'badge-renewed') + '">' + days + ' day' + (days === 1 ? '' : 's') + ' remaining</span>';
      }

      function renewalUpdateSelection() {
        var count = document.getElementById('renewalSelectedCount');
        var button = document.getElementById('renewalNotifyAllBtn');
        var selectedCount = renewalSelected.size;
        if (count) count.textContent = selectedCount;
        if (button) { button.disabled = selectedCount === 0; button.style.opacity = selectedCount === 0 ? '0.55' : '1'; }
        var selectAll = document.getElementById('renewalSelectAll');
        if (selectAll) {
          var ids = renewalFiltered.map(function(p){ return String(p.itemNumber); });
          selectAll.checked = ids.length > 0 && ids.every(function(id){ return renewalSelected.has(id); });
          selectAll.indeterminate = ids.some(function(id){ return renewalSelected.has(id); }) && !selectAll.checked;
          selectAll.disabled = renewalTab === 'renewed' || ids.length === 0;
        }
      }

      window.renewalToggleSelection = function(itemNum, checked) {
        var id = String(itemNum);
        if (checked) renewalSelected.add(id); else renewalSelected.delete(id);
        renewalUpdateSelection();
      };

      function fmtDateShort(dateStr) {
        if (!dateStr) return '—';
        var d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return d.toLocaleDateString('en-US', { month:'short', day:'2-digit', year:'numeric' });
      }

      function renewalActionCell(r, status) {
        if (status === 'renewed') {
          return '<button onclick="renewalOpenDetails(' + JSON.stringify(r.itemNumber) + ')" '
            + 'class="renewal-action-renewed" style="background:transparent;color:#33b481;border:1px solid #1a5c3a;border-radius:6px;padding:5px 12px;font-size:0.72rem;font-weight:700;cursor:pointer;">View Details</button>';
        }
        var color = status === 'expired'
          ? 'background:#2d0a0a;color:#fc8181;border:1px solid #7f1d1d;'
          : 'background:#241b07;color:#f4b63f;border:1px solid #92400e;';
        return '<button onclick="renewalNotifyOne(' + JSON.stringify(r.itemNumber) + ')" '
          + 'style="' + color + 'border-radius:6px;padding:5px 12px;font-size:0.72rem;font-weight:700;cursor:pointer;">✉ Notify</button>';
      }

      function renewalRenderTable() {
        renewalPopulateUnits();
        renewalSummary();
        var q = ((document.getElementById('renewalSearch')||{}).value || '').trim().toLowerCase();
        var unitF = (document.getElementById('renewalUnitFilter')||{}).value || '';
        var filtered = personnel.filter(function(p) {
          if ((p.approvedStatus||'pending') !== renewalTab) return false;
          if (unitF && p.unit !== unitF) return false;
          if (!q) return true;
          return [p.lastName, p.firstName, p.middleName, p.afpSerialNumber].some(function(v){ return (v||'').toLowerCase().includes(q); });
        });
        var sort = (document.getElementById('renewalDurationSort')||{}).value || 'soonest';
        filtered.sort(function(a, b) {
          if (sort === 'name') return String(a.lastName||'').localeCompare(String(b.lastName||''));
          var ad = daysRemaining(a.dateOfValidity), bd = daysRemaining(b.dateOfValidity);
          ad = ad === null ? Number.POSITIVE_INFINITY : ad;
          bd = bd === null ? Number.POSITIVE_INFINITY : bd;
          return sort === 'latest' ? bd - ad : ad - bd;
        });
        renewalFiltered = filtered;
        var total = filtered.length;
        var pages = Math.max(1, Math.ceil(total / RENEWAL_PER_PAGE));
        if (renewalPage > pages) renewalPage = 1;
        var start = (renewalPage - 1) * RENEWAL_PER_PAGE;
        var pageData = filtered.slice(start, start + RENEWAL_PER_PAGE);
        var tbody = document.getElementById('renewalTbody');
        var info = document.getElementById('renewalTblInfo');

        if (!pageData.length) {
          tbody.innerHTML = '<tr><td colspan="8" class="text-center py-6 text-gray-400">No records found.</td></tr>';
          if (info) info.textContent = 'Showing 0 records';
          renewalPagination(0);
          renewalUpdateSelection();
          return;
        }

      tbody.innerHTML = pageData.map(function(r, i) {
    var name = (r.lastName||'') + ', ' + (r.firstName||'') + (r.middleName ? ' ' + r.middleName : '');

    var badge = renewalDurationBadge(r);
    var checkbox = renewalTab === 'renewed' ? '' : '<input type="checkbox" aria-label="Select personnel" onchange="renewalToggleSelection(' + JSON.stringify(r.itemNumber) + ', this.checked)" ' + (renewalSelected.has(String(r.itemNumber)) ? 'checked' : '') + ' style="accent-color:#33b481;">';

    return '<tr class="border-b border-[#1a2025] hover:bg-[#1a2025] transition-colors">'
      + '<td class="py-2 px-2">' + checkbox + '</td>'
      + '<td class="py-2 px-2 force-light-text">' + (start + i + 1) + '</td>'
      + '<td class="py-2 px-2 force-light-text">' + name + '</td>'
      + '<td class="py-2 px-2 force-light-text">' + (r.rank||'—') + '</td>'
      + '<td class="py-2 px-2 force-light-text">' + (r.afpSerialNumber||'—') + '</td>'
      + '<td class="py-2 px-2 force-light-text">' + (r.unit||'—') + '</td>'
      + '<td class="py-2 px-2">' + badge + '</td>'
      + '<td class="py-2 px-2">' + renewalActionCell(r, renewalTab) + '</td>'
      + '</tr>';
  }).join('');

        if (info) info.textContent = 'Showing ' + (start+1) + '–' + Math.min(start+RENEWAL_PER_PAGE, total) + ' of ' + total + ' entries';
        renewalPagination(pages);
        renewalUpdateSelection();
      }

      function renewalPagination(pages) {
        var c = document.getElementById('renewalTblPages'); if (!c) return;
        if (pages <= 1) { c.innerHTML = ''; return; }
        var active = 'background:#33b481;color:#fff;border-color:#33b481;';
        var inactive = 'background:#1a2025;color:#94a3b8;border-color:#363b48;';
        var html = '';
        for (var i = 1; i <= pages; i++) {
          html += '<button onclick="renewalGoPage(' + i + ')" style="border-radius:5px;padding:4px 9px;font-size:0.7rem;font-weight:600;cursor:pointer;border:1px solid;' + (i === renewalPage ? active : inactive) + '">' + i + '</button>';
        }
        c.innerHTML = html;
      }
      window.renewalGoPage = function(n) { renewalPage = n; renewalRenderTable(); };

      document.getElementById('renewalSearch')?.addEventListener('input', function(){ renewalPage = 1; renewalRenderTable(); });
      document.getElementById('renewalUnitFilter')?.addEventListener('change', function(){ renewalPage = 1; renewalRenderTable(); });
      document.getElementById('renewalDurationSort')?.addEventListener('change', function(){ renewalPage = 1; renewalRenderTable(); });
      document.getElementById('renewalSelectAll')?.addEventListener('change', function() {
        var checked = this.checked;
        renewalFiltered.forEach(function(p) {
          var id = String(p.itemNumber);
          if (checked) renewalSelected.add(id); else renewalSelected.delete(id);
        });
        renewalRenderTable();
      });

      window.renewalOpenDetails = function(itemNum) {
        var r = personnel.find(function(x){ return x.itemNumber == itemNum; });
        if (!r) return;
        var name = ((r.rank||'') + ' ' + (r.lastName||'') + ', ' + (r.firstName||'') + ' ' + (r.middleName||'')).replace(/\s+/g,' ').trim();
        document.getElementById('pd_name').textContent = name;
        document.getElementById('pd_rank').textContent = r.rank || '—';
        document.getElementById('pd_serial').textContent = r.afpSerialNumber || '—';
        document.getElementById('pd_unit').textContent = r.unit || '—';
        var img = document.getElementById('pd_photo'), fb = document.getElementById('pd_photoFallback');
        if (r.photo) { img.src = /^(data:image\/|https?:\/\/|\/)/i.test(r.photo) ? r.photo : 'data:image/jpeg;base64,' + r.photo; img.style.display='block'; fb.style.display='none'; }
        else { img.style.display='none'; fb.style.display='block'; }

        var dr = daysRemaining(r.dateOfValidity);
        var renewalRows = [
          ['Date Renewed', r.dateRenewed ? fmtDateShort(r.dateRenewed) : '—'],
          ['Renewed By', r.renewedBy || 'Staff User'],
          ['Next Expiration Date', r.dateOfValidity ? fmtDateShort(r.dateOfValidity) : '—'],
          ['Days Remaining', dr !== null ? (dr >= 0 ? dr + ' days' : 'Overdue') : '—']
        ];
        document.getElementById('pd_renewalInfo').innerHTML = renewalRows.map(function(x){
          return '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>' + x[0] + '</span><span style="color:#e5eaf2;font-weight:600;">' + x[1] + '</span></div>';
        }).join('');

        var icsRows = [
          ['Date Issued (Original)', r.icsDateIssued ? fmtDateShort(r.icsDateIssued) : '—'],
          ['Valid Until', r.dateOfValidity ? fmtDateShort(r.dateOfValidity) : '—'],
          ['ICS Number', r.icsNumber || ('ICS-' + String(r.itemNumber).padStart(4,'0'))],
          ['Status', 'Active']
        ];
        document.getElementById('pd_icsInfo').innerHTML = icsRows.map(function(x){
          return '<div style="display:flex;justify-content:space-between;padding:4px 0;"><span>' + x[0] + '</span><span style="color:#e5eaf2;font-weight:600;">' + x[1] + '</span></div>';
        }).join('');

        document.getElementById('pd_notes').textContent = r.renewalNotes || r.notes || 'No additional notes on file.';
        document.getElementById('personnelDetailsOverlay').style.display = 'flex';
      };
      document.getElementById('personnelDetailsClose')?.addEventListener('click', function(){ document.getElementById('personnelDetailsOverlay').style.display='none'; });
      document.getElementById('personnelDetailsCloseBtn')?.addEventListener('click', function(){ document.getElementById('personnelDetailsOverlay').style.display='none'; });
      document.getElementById('personnelDetailsOverlay')?.addEventListener('click', function(e){ if (e.target === this) this.style.display='none'; });

      window.renewalNotifyOne = function(itemNum) {
        var r = personnel.find(function(x){ return x.itemNumber == itemNum; });
        if (!r) return;
        var name = ((r.rank||'') + ' ' + (r.lastName||'') + ', ' + (r.firstName||'') + ' ' + (r.middleName||'')).replace(/\s+/g,' ').trim();
        window.openNotifyModal(r.itemNumber, name, r.approvedStatus);
      };
    
      window.renewalNotifyAll = function() {
        var status = renewalTab;
        if (status !== 'within' && status !== 'expired') return;
        var list = personnel.filter(function(p){ return (p.approvedStatus||'pending') === status && renewalSelected.has(String(p.itemNumber)); });
        var withEmail = list.filter(function(p){ return p.email && p.email !== 'undefined' && p.email !== 'null'; });
        var withoutEmail = list.length - withEmail.length;
        if (!withEmail.length) {
          showSystemModal('No email addresses', 'No personnel in this list have an email on record.', false);
          return;
        }
        var confirmation = 'Send notification email to the ' + withEmail.length + ' selected personnel?' +
          (withoutEmail ? ' ' + withoutEmail + ' record(s) will be skipped (no email).' : '');
        showSystemModal('Confirm notification', confirmation, true).then(function(confirmed) {
          if (!confirmed) return;

        var btn = document.getElementById('renewalNotifyAllBtn');
        var origHtml = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = 'Sending…';
        var CSRF = document.querySelector('meta[name="csrf-token"]').content;
        var sent = 0, failed = 0;

        function defaultMessage(r) {
          var name = ((r.rank||'') + ' ' + (r.lastName||'') + ', ' + (r.firstName||'') + ' ' + (r.middleName||'')).replace(/\s+/g,' ').trim();
          return status === 'expired'
            ? 'Dear ' + name + ',\n\nThis is to inform you that your pistol license has already EXPIRED. Please coordinate immediately with the Property Accountability Office to process your renewal at the earliest possible time.\n\nFailure to renew may result in administrative action.\n\nRegards,\nAPAO Renewal System'
            : 'Dear ' + name + ',\n\nThis is a reminder that your pistol license is within the renewal period and will expire soon. Please process your renewal before the expiration date.\n\nPlease coordinate with the Property Accountability Office at the soonest possible time.\n\nRegards,\nAPAO Renewal System';
        }

        var chain = Promise.resolve();
        withEmail.forEach(function(r) {
          chain = chain.then(function() {
            return fetch('/staff/personnel/' + r.itemNumber + '/notify', {
              method: 'POST',
              headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF },
              body: JSON.stringify({ message: defaultMessage(r) })
            }).then(function(res){ return res.json(); }).then(function(json){ if (json.success) sent++; else failed++; })
              .catch(function(){ failed++; });
          });
        });

        chain.then(function() {
          btn.disabled = false; btn.innerHTML = origHtml;
          renewalSelected.clear();
          renewalRenderTable();
          showSystemModal('Notifications complete', 'Selected notifications: ' + sent + ' sent, ' + failed + ' failed' + (withoutEmail ? ', ' + withoutEmail + ' skipped (no email)' : '') + '.', false);
        });
        });
      };

      window.renewalRenderAll = function() {
        if (!document.getElementById('renewaltab-renewed')) return;
        renewalSetTab(renewalTab);
      };
    })();
    document.getElementById("refreshArchiveBtn")?.addEventListener("click", () => {
      document.getElementById("archiveTableBody").innerHTML = `<tr><td colspan="5" class="text-center py-6 text-[#64748b]">Archive endpoint not yet connected.</td></tr>`;
    });

    // ── ICS MODULE (NEW) ───────────────────────────────────────────────────
      let systemModalResolver = null;
      function closeSystemModal(result) {
        document.getElementById('systemModalOverlay').style.display = 'none';
        if (systemModalResolver) {
          const resolve = systemModalResolver;
          systemModalResolver = null;
          resolve(result);
        }
      }
      function showSystemModal(title, message, isConfirm) {
        return new Promise(function(resolve) {
          systemModalResolver = resolve;
          document.getElementById('systemModalTitle').textContent = title;
          document.getElementById('systemModalMessage').textContent = message;
          document.getElementById('systemModalCancel').style.display = isConfirm ? '' : 'none';
          document.getElementById('systemModalOverlay').style.display = 'flex';
          document.getElementById(isConfirm ? 'systemModalCancel' : 'systemModalConfirm').focus();
        });
      }
      document.getElementById('systemModalConfirm').addEventListener('click', function(){ closeSystemModal(true); });
      document.getElementById('systemModalCancel').addEventListener('click', function(){ closeSystemModal(false); });
      document.getElementById('systemModalOverlay').addEventListener('click', function(e){ if (e.target === this) closeSystemModal(false); });

    (function initICS() {

      var icsActiveTab = 'inspection';
      var icsPage      = 1;
      var ICS_PER_PAGE = 10;

      function getIcsStatus(p) {
        if (p.approvedStatus === 'expired' && p.icsStatus !== 'under') return 'expired';
        return p.icsStatus || 'inspection';
      }

      function personnelSignatureSrc(signature) {
        if (!signature) return null;
        var value = String(signature).trim().replace(/^['"]|['"]$/g, '');
        if (!value) return null;
        if (/^(data:image\/|https?:\/\/|\/)/i.test(value)) return value;
        if (/^(storage|images)\//i.test(value)) return '/' + value;
        return 'data:image/png;base64,' + value.replace(/\s/g, '');
      }

      window.icsSetTab = function(tab) {
        icsActiveTab = tab; icsPage = 1;
        var styles = { inspection:{bg:'#1c2c18',color:'#d4a017'}, under:{bg:'#0f1e2e',color:'#3ec6ff'}, ready:{bg:'#0c2418',color:'#33b481'} };
        ['inspection','under','ready'].forEach(function(t) {
          var el = document.getElementById('icstab-' + t); if (!el) return;
          el.style.background = t === tab ? styles[t].bg : 'transparent';
          el.style.color      = t === tab ? styles[t].color : '#64748b';
        });
        icsRenderTable();
      };

      function icsRenderTable() {
        function formatDateUpdated(value) {
          if (!value) return '—';
          var parsed = new Date(String(value).replace(' ', 'T'));
          if (Number.isNaN(parsed.getTime())) return '—';
          return parsed.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
        }
        var hideInspectionColumns = icsActiveTab === 'under';
        document.querySelectorAll('[data-ics-column]').forEach(function(column) {
          column.hidden = hideInspectionColumns;
        });
        var q = ((document.getElementById('icsListSearch') || {}).value || '').trim().toLowerCase();
        ['inspection','under','ready'].forEach(function(t) {
          var cnt = personnel.filter(function(p){ return getIcsStatus(p) === t; }).length;
          var s = document.getElementById('ics-sum-' + t); if(s) s.textContent = cnt;
          var c = document.getElementById('icstab-count-' + t); if(c) c.textContent = cnt;
        });
        var filtered = personnel.filter(function(p) {
          if (getIcsStatus(p) !== icsActiveTab) return false;
          if (!q) return true;
          return [p.lastName, p.firstName, p.middleName, p.afpSerialNumber].some(function(v){ return (v||'').toLowerCase().includes(q); });
        });
        var total = filtered.length;
        var pages = Math.max(1, Math.ceil(total / ICS_PER_PAGE));
        if (icsPage > pages) icsPage = 1;
        var start = (icsPage - 1) * ICS_PER_PAGE;
        var pageData = filtered.slice(start, start + ICS_PER_PAGE);
        var tbody = document.getElementById('ics-tbody');
        var info  = document.getElementById('ics-tbl-info');

        if (!pageData.length) {
          tbody.innerHTML = '<tr><td colspan="' + (hideInspectionColumns ? 7 : 9) + '" style="text-align:center;padding:28px;color:#4b5563;font-size:0.78rem;">No records found for this status.</td></tr>';
          if (info) info.textContent = 'Showing 0 records';
          icsPagination(0); return;
        }

        tbody.innerHTML = pageData.map(function(r) {
          var name = (r.lastName||'') + ', ' + (r.firstName||'') + (r.middleName ? ' ' + r.middleName : '');
          var st = getIcsStatus(r);
          var pill = st === 'inspection'
            ? '<span class="ics-inspection-pill" style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:99px;font-size:0.68rem;font-weight:700;background:#2c2000;color:#d4a017;"><span style="width:6px;height:6px;border-radius:50%;background:#d4a017;flex-shrink:0;"></span>For Inspection</span><br><small style="color:#7a6020;">Send to Admin</small>'
            : st === 'under'
            ? '<span class="ics-under-pill" style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:99px;font-size:0.68rem;font-weight:700;background:#0a1f38;color:#3ec6ff;"><span style="width:6px;height:6px;border-radius:50%;background:#3ec6ff;flex-shrink:0;"></span>Under Inspection</span><br><small style="color:#1e5070;">Being inspected</small>'
            : '<span class="ics-ready-pill" style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:99px;font-size:0.68rem;font-weight:700;background:#0c2e1a;color:#33b481;"><span style="width:6px;height:6px;border-radius:50%;background:#33b481;flex-shrink:0;"></span>Ready for Renewal</span><br><small class="ics-ready-note" style="color:#1a5c3a;">Inspection passed</small>';
          var inspectionResult = st === 'ready' ? 'Passed' : r.inspectionResult;
          var result = inspectionResult
            ? '<span style="color:#33b481;font-weight:700;font-size:0.75rem;">✓ ' + inspectionResult + '</span>'
            : '<span style="color:#374151;">—</span>';
          var action = '';
          if (st === 'inspection') {
            action = '<button class="ics-action-btn ics-action-send" onclick="icsSendForInspection(' + JSON.stringify(r.itemNumber) + ', this)" '
              + 'style="display:inline-flex;align-items:center;gap:5px;white-space:nowrap;">'
              + '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>Send for Inspection</button>';
          } else if (st === 'under') {
            action = '<span style="color:#374151;font-size:0.72rem;">—</span>';
          } else {
            action = '<button onclick="icsOpenDoc(' + JSON.stringify(r.itemNumber) + ')" '
              + 'class="ics-process-btn" style="display:inline-flex;align-items:center;gap:5px;background:#0c2e1a;color:#33b481;border:1px solid #1a5c3a;border-radius:6px;padding:5px 11px;font-size:0.7rem;font-weight:700;cursor:pointer;white-space:nowrap;" '
              + 'onmouseover="this.style.background=\'#154d32\'" onmouseout="this.style.background=\'#0c2e1a\'">'
              + '<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>Process ICS</button>';
          }
          return '<tr style="border-bottom:1px solid #1a2025;" onmouseover="this.style.background=\'#1a2530\'" onmouseout="this.style.background=\'\'">'
            + '<td class="py-2 px-3" style="color:#e5eaf2;font-weight:500;">' + name + '</td>'
            + '<td class="py-2 px-3" style="font-family:monospace;color:#94a3b8;">' + (r.afpSerialNumber||'—') + '</td>'
            + '<td class="py-2 px-3 force-light-text">' + (r.rank||'—') + '</td>'
            + '<td class="py-2 px-3 force-light-text">' + (r.unit||'—') + '</td>'
            + '<td class="py-2 px-3 force-light-text">' + (r.pistolNomenclature||'—') + '</td>'
            + '<td class="py-2 px-3">' + pill + '</td>'
            + (hideInspectionColumns ? '' : '<td class="py-2 px-3">' + result + '</td>')
            + '<td class="py-2 px-3" style="color:#64748b;">' + (r.dateUpdated || formatDateUpdated(r.inspectionUpdatedAt || r.personnelUpdatedAt)) + '</td>'
            + (hideInspectionColumns ? '' : '<td class="py-2 px-3">' + action + '</td>')
            + '</tr>';
        }).join('');

        if (info) info.textContent = 'Showing ' + (start+1) + '–' + Math.min(start+ICS_PER_PAGE, total) + ' of ' + total + ' entries';
        icsPagination(pages);
      }

      function icsPagination(pages) {
        var c = document.getElementById('ics-tbl-pages'); if (!c) return;
        if (pages <= 1) { c.innerHTML = ''; return; }
        var bs = 'border-radius:5px;padding:4px 9px;font-size:0.7rem;font-weight:600;cursor:pointer;border:1px solid #363b48;';
        var html = '';
        if (icsPage > 1) html += '<button onclick="icsGoPage(' + (icsPage-1) + ')" style="' + bs + 'background:#1a2025;color:#94a3b8;">‹</button>';
        for (var i = 1; i <= pages; i++) {
          html += '<button onclick="icsGoPage(' + i + ')" style="' + bs + (i === icsPage ? 'background:#33b481;color:#fff;border-color:#33b481;' : 'background:#1a2025;color:#94a3b8;') + '">' + i + '</button>';
        }
        if (icsPage < pages) html += '<button onclick="icsGoPage(' + (icsPage+1) + ')" style="' + bs + 'background:#1a2025;color:#94a3b8;">›</button>';
        c.innerHTML = html;
      }

      window.icsGoPage = function(n) { icsPage = n; icsRenderTable(); };

      var icsSearchEl = document.getElementById('icsListSearch');
      if (icsSearchEl) icsSearchEl.addEventListener('input', function(){ icsPage = 1; icsRenderTable(); });

      window.icsSendForInspection = function(itemNum, btn) {
        var p = personnel.find(function(x){ return x.itemNumber == itemNum; });
        if (!p || (btn && btn.disabled)) return;
        var personnelName = ((p.lastName || '') + ', ' + (p.firstName || '')).replace(/^,\s*|,\s*$/g, '').trim();
        showSystemModal(
          'Send for Admin inspection?',
          'Send ' + (personnelName || ('Item #' + itemNum)) + ' to the administrator for inspection?',
          true
        ).then(function(confirmed) {
          if (!confirmed) return;
          var originalHtml = btn ? btn.innerHTML : '';
          if (btn) { btn.disabled = true; btn.textContent = 'Sending...'; }
          fetch('/staff/ics/' + encodeURIComponent(itemNum) + '/send-inspection', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
          }).then(function(response) {
            return response.json().catch(function() {
              return { success:false, message:'The server returned an invalid response.' };
            }).then(function(json) {
              if (!response.ok || !json.success) throw new Error(json.message || 'Unable to send the inspection request.');
              return json;
            });
          }).then(function(json) {
            p.icsStatus = 'under';
            p.dateUpdated = new Date().toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
            icsSetTab('under');
            icsShowToast('Sent to Admin for inspection - ' + personnelName);
            showSystemModal(
              json.alreadySent ? 'Already sent for inspection' : 'Successfully sent for inspection',
              json.alreadySent
                ? (personnelName + ' is already waiting for Admin inspection.')
                : (personnelName + ' was successfully sent to the Admin for inspection.'),
              false
            );
          }).catch(function(error) {
            if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
            showSystemModal('Unable to send inspection', error.message || 'Please try again.', false);
          });
        });
      };

    window.icsOpenDoc = function(itemNum) {
    var p = personnel.find(function(x){ return x.itemNumber == itemNum; });
    if (!p) return;

    var fn  = p.firstName  || '';
    var mid = p.middleName ? p.middleName.charAt(0) + '.' : '';
    var ln  = p.lastName   || '';
    var fullName = (fn + (mid ? ' ' + mid : '') + ' ' + ln).trim();
    var rank = (p.rank || '').trim().toUpperCase();

    var set = function(id, v) { var el = document.getElementById(id); if (el) el.value = v || ''; };
    set('icsNoField',         'ICS-' + String(p.itemNumber).padStart(4, '0'));
    set('icsValidityField',   p.dateOfValidity || '');
    set('rankField',          rank);
    set('personnelNameField', fullName);
    set('unitField',          p.unit || '');
    set('icsFirearmField',    p.pistolNomenclature || '');
    set('icsSerialField',     p.afpSerialNumber || '');
    set('icsAmmoField',       p.qtyAmmo != null ? String(p.qtyAmmo) : '');
    set('receivedByField',    ln.toUpperCase() + ', ' + fn.toUpperCase());
    set('issuedByField',      'MS ROSEMARIE O VILBAR');

    var paperPhoto = document.getElementById('previewPaperPhoto');
    var sidePhoto = document.getElementById('icsPhotoSidePreview');
    var personnelPhoto = p.photo && p.photo.trim() !== ''
      ? (/^(data:image\/|https?:\/\/|\/)/i.test(p.photo) ? p.photo : 'data:image/jpeg;base64,' + p.photo)
      : "<?php echo e(asset('images/logo.png')); ?>";
    if (paperPhoto) paperPhoto.src = personnelPhoto;
    if (sidePhoto) sidePhoto.src = personnelPhoto;

    // ── Auto-load personnel's own signature from registration (base64) ──
    applySig('custodian', personnelSignatureSrc(p.signature));
    applySig('issuing', ICS_DEFAULT_ISSUER_SIGNATURE);

    var crumb = document.getElementById('ics-doc-crumb');
    if (crumb) crumb.textContent = '→ ICS for ' + ln + ', ' + fn + ' (' + (p.afpSerialNumber || '') + ')';

    icsData = collectForm();
    renderICSPreview(icsData);

    document.getElementById('ics-list-view').style.display = 'none';
    document.getElementById('ics-doc-view').style.display  = 'block';
    window.staffScrollToTop();
  };
      window.printICS = function() {
        const paper = document.querySelector('#ics-doc-view .ics-print-area');
        if (!paper) return;
        const printWindow = window.open('', '_blank', 'width=900,height=1100');
        if (!printWindow) { window.print(); return; }
        const pageStyles = document.querySelector('style')?.innerHTML || '';
        printWindow.document.open();
        printWindow.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>Inventory Custodian Slip</title><style>
          ${pageStyles}
          @page{size:A4 portrait;margin:0;}
          html,body{margin:0!important;padding:0!important;background:#fff!important;}
          .ics-paper{width:210mm!important;max-width:210mm!important;min-height:0!important;height:auto!important;margin:0!important;border:0!important;border-radius:0!important;box-shadow:none!important;overflow:visible!important;}
          .ics-paper-grid{width:210mm!important;min-height:0!important;padding:10mm!important;background-image:none!important;box-sizing:border-box!important;}
        </style>  
</head><body>${paper.outerHTML}</body></html>`);
        printWindow.document.close();
        const images = Array.from(printWindow.document.images);
        Promise.all(images.map(img => img.complete ? Promise.resolve() : new Promise(resolve => { img.onload = resolve; img.onerror = resolve; })))
          .then(() => setTimeout(() => { printWindow.focus(); printWindow.print(); }, 150));
      };

      window.icsShowList = function() {
        document.getElementById('ics-doc-view').style.display  = 'none';
        document.getElementById('ics-list-view').style.display = 'block';
        window.staffScrollToTop();
      };

      function icsShowToast(msg) {
        var old = document.getElementById('ics-toast'); if(old) old.remove();
        var t = document.createElement('div');
        t.id = 'ics-toast';
        t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#0c2e1a;color:#33b481;border:1px solid #1a5c3a;border-radius:10px;padding:11px 18px;font-size:0.78rem;font-weight:600;display:flex;align-items:center;gap:8px;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.4);';
        t.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' + msg;
        document.body.appendChild(t);
        setTimeout(function(){ t.style.opacity='0'; t.style.transition='opacity 0.35s'; setTimeout(function(){ t.remove(); },350); }, 3000);
      }

      const ICS_KEY="staff_ics_details_v1", PHOTO_KEY="staff_ics_photo_v1", FIREARM_COST=16450, AMMO_COST=15.07;
      const PLACEHOLDER="<?php echo e(asset('images/logo.png')); ?>";
      const ICS_DEFAULT_ISSUER="MS ROSEMARIE O VILBAR";
      const ICS_DEFAULT_ISSUER_SIGNATURE=<?php echo json_encode(asset('images/ROSEMARIE VILBAR.png'), 15, 512) ?>;
      const defaults={icsNo:"ICS-0001",icsValidity:"",rank:"CPT",personnelName:"Juan D. Cruz",unit:"8IB, 4ID, PA",firearm:"9mm Glock17",serial:"AFP023947",ammo:"68",receivedBy:"John Doe",issuedBy:ICS_DEFAULT_ISSUER};
      const fieldMap={icsNo:"icsNoField",icsValidity:"icsValidityField",rank:"rankField",personnelName:"personnelNameField",unit:"unitField",firearm:"icsFirearmField",serial:"icsSerialField",ammo:"icsAmmoField",receivedBy:"receivedByField",issuedBy:"issuedByField"};
      function fmtDate(val){if(!val)return"";const d=new Date(val);if(isNaN(d))return val;const mo=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];return`${String(d.getDate()).padStart(2,"0")}/${mo[d.getMonth()]}/${d.getFullYear()}`;}
      function todayStr(){const d=new Date();return`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}-${String(d.getDate()).padStart(2,"0")}`;}
      function setText(id,v){const el=document.getElementById(id);if(el)el.textContent=v||"";}
      function renderICSPreview(d){
        const rank=(d.rank||"").trim().toUpperCase(),name=(d.personnelName||"").trim(),serial=(d.serial||"").trim();
        const ammoQty=parseInt(d.ammo)||0;
        const money=value=>`P ${Number(value).toLocaleString("en-PH",{minimumFractionDigits:2,maximumFractionDigits:2})}`;
        const ammoTotal=ammoQty*AMMO_COST;
        setText("previewIcsNo",d.icsNo||"");
        setText("previewIcsValidity",fmtDate(d.icsValidity));
        setText("previewRank",rank);
        setText("previewPersonnelName",name);
        setText("previewUnit",d.unit||"");
        setText("previewSerial",serial);
        setText("previewFirearm",d.firearm||"");
        setText("previewSerialDesc",serial||"—");
        setText("previewInventoryItem",serial||"—");
        setText("previewRankName",`${rank} ${name}`.trim()||"—");
        setText("previewFirearmUnitCost",money(FIREARM_COST));
        setText("previewFirearmTotal",money(FIREARM_COST));
        setText("previewAmmo",String(ammoQty));
        setText("previewAmmoUnitCost",money(AMMO_COST));
        setText("previewAmmoTotal",money(ammoTotal));
        setText("previewAmmoDescription",/\.45|cal\s*45/i.test(d.firearm||"")?"Ctg, Cal .45, Ball":"Ctg, 9mm, Ball");
        setText("previewReceivedBy",(d.receivedBy||"").toUpperCase()||"—");
        setText("previewIssuedBy",(d.issuedBy||"").toUpperCase()||"—");
        setText("previewReceivedOffice",d.unit||"—");
        setText("previewIssuedOffice","Chief, PAOGS, APAO, PA");
        const sd=fmtDate(todayStr());setText("previewSignDateLeft",sd);setText("previewSignDateRight",sd);
      }
      function fillForm(d){Object.entries(fieldMap).forEach(([key,fId])=>{const el=document.getElementById(fId);if(el)el.value=d[key]!=null?String(d[key]):"";});}
      function collectForm(){const d={};Object.entries(fieldMap).forEach(([key,fId])=>{const el=document.getElementById(fId);d[key]=el?el.value.trim():"";});return d;}
      function saveICS(d){try{localStorage.setItem(ICS_KEY,JSON.stringify(d));}catch(e){}}
      function loadICS(){try{const r=localStorage.getItem(ICS_KEY);if(r){const saved=JSON.parse(r);if(!saved.issuedBy||/juan\s+d\.?\s*(la\s*)?cruz/i.test(saved.issuedBy))saved.issuedBy=ICS_DEFAULT_ISSUER;return{...defaults,...saved};}}catch(e){}return{...defaults};}
      let icsData=loadICS();fillForm(icsData);renderICSPreview(icsData);
      Object.values(fieldMap).forEach(fId=>{const el=document.getElementById(fId);if(!el)return;el.addEventListener(el.type==="date"?"change":"input",()=>{icsData=collectForm();renderICSPreview(icsData);saveICS(icsData);});});
      document.getElementById("icsResetBtn")?.addEventListener("click",()=>{icsData={...defaults};fillForm(icsData);renderICSPreview(icsData);saveICS(icsData);});

      function populateICSSelect(){const sel=document.getElementById("icsPersonnelSelect");if(!sel||!personnel.length)return;sel.innerHTML='<option value="">— Select Personnel —</option>';personnel.forEach((p,i)=>{const name=`${p.lastName||""}, ${p.firstName||""} ${p.middleName||""}`.trim();const opt=document.createElement("option");opt.value=i;opt.textContent=`${name} — ${p.afpSerialNumber||""}`;sel.appendChild(opt);});}
      document.getElementById("icsPersonnelSearch")?.addEventListener("input",function(){const q=this.value.trim().toLowerCase();const sel=document.getElementById("icsPersonnelSelect");if(!sel)return;Array.from(sel.options).forEach(opt=>{opt.hidden=q?!opt.textContent.toLowerCase().includes(q):false;});});

      const icsSection=document.getElementById("page-ics");
      window.icsRefresh=function(){populateICSSelect();icsRenderTable();};
      new MutationObserver(()=>{if(icsSection.classList.contains("active")){populateICSSelect();icsRenderTable();}}).observe(icsSection,{attributes:true,attributeFilter:["class"]});
      if(icsSection.classList.contains("active")){populateICSSelect();icsRenderTable();}

      document.getElementById("icsAutoFillBtn")?.addEventListener("click",()=>{const sel=document.getElementById("icsPersonnelSelect");if(!sel||sel.value==="")return;const p=personnel[parseInt(sel.value)];if(!p)return;icsData={...icsData,personnelName:`${p.firstName||""} ${p.middleName?p.middleName[0]+".":""} ${p.lastName||""}`.replace(/\s+/g," ").trim(),serial:p.afpSerialNumber||icsData.serial,icsValidity:p.dateOfValidity||icsData.icsValidity,ammo:p.qtyAmmo!=null?String(p.qtyAmmo):icsData.ammo,firearm:p.pistolNomenclature||icsData.firearm};fillForm(icsData);renderICSPreview(icsData);applySig("custodian",personnelSignatureSrc(p.signature));saveICS(icsData);});

      const ICS_SIG_C_KEY="staff_ics_sig_custodian_v1",ICS_SIG_I_KEY="staff_ics_sig_issuing_v1";
      function applySig(side,src){const isC=side==="custodian";const previewImg=document.getElementById(isC?"custodianSigPreview":"issuingSigPreview");const placeholder=document.getElementById(isC?"custodianSigPlaceholder":"issuingSigPlaceholder");const paperWrap=document.getElementById(isC?"previewCustodianSigWrap":"previewIssuingSigWrap");const paperImg=document.getElementById(isC?"previewCustodianSig":"previewIssuingSig");const key=isC?ICS_SIG_C_KEY:ICS_SIG_I_KEY;if(src){if(previewImg){previewImg.src=src;previewImg.style.display="block";}if(placeholder)placeholder.style.display="none";if(paperWrap)paperWrap.style.display="flex";if(paperImg)paperImg.src=src;try{localStorage.setItem(key,src);}catch(e){}}else{if(previewImg){previewImg.src="";previewImg.style.display="none";}if(placeholder)placeholder.style.display="inline";if(paperWrap)paperWrap.style.display="none";if(paperImg)paperImg.src="";try{localStorage.removeItem(key);}catch(e){}}}
      // Custodian signature is now loaded per-personnel in icsOpenDoc() — not from a global key
      try{const s=localStorage.getItem(ICS_SIG_I_KEY);applySig("issuing",s||ICS_DEFAULT_ISSUER_SIGNATURE);}catch(e){applySig("issuing",ICS_DEFAULT_ISSUER_SIGNATURE);}
      function wireSignature(side){const isC=side==="custodian";const inputEl=document.getElementById(isC?"custodianSigInput":"issuingSigInput");const uploadBtn=document.getElementById(isC?"uploadCustodianSigBtn":"uploadIssuingSigBtn");const clearBtn=document.getElementById(isC?"clearCustodianSigBtn":"clearIssuingSigBtn");uploadBtn?.addEventListener("click",()=>inputEl?.click());clearBtn?.addEventListener("click",()=>{applySig(side,null);if(inputEl)inputEl.value="";});inputEl?.addEventListener("change",function(){const file=this.files?.[0];if(!file||!file.type.startsWith("image/"))return;const reader=new FileReader();reader.onload=ev=>applySig(side,ev.target.result);reader.readAsDataURL(file);});}
      wireSignature("custodian");wireSignature("issuing");

      const photoInput=document.getElementById("icsPhotoInput");
      document.getElementById("icsUploadPhotoBtn")?.addEventListener("click",()=>photoInput?.click());
      function applyICSPhoto(src){const safe=src||PLACEHOLDER;const side=document.getElementById("icsPhotoSidePreview");const paper=document.getElementById("previewPaperPhoto");if(side)side.src=safe;if(paper)paper.src=safe;}
      try{const saved=localStorage.getItem(PHOTO_KEY);if(saved)applyICSPhoto(saved);}catch(e){}
      photoInput?.addEventListener("change",function(){const file=this.files?.[0];if(!file||!file.type.startsWith("image/"))return;const reader=new FileReader();reader.onload=ev=>{const src=ev.target.result;applyICSPhoto(src);try{localStorage.setItem(PHOTO_KEY,src);}catch(e){}};reader.readAsDataURL(file);});
      document.getElementById("icsResetPhotoBtn")?.addEventListener("click",()=>{applyICSPhoto(PLACEHOLDER);if(photoInput)photoInput.value="";try{localStorage.removeItem(PHOTO_KEY);}catch(e){}});

      })();

  });

  </script>

  <script>
  // ═══ GLOBAL SCOPE — registration step functions ═══
  var rpSigDrawing = false;

  window._rpNavigate = function(page) {
    document.dispatchEvent(new CustomEvent('rp-navigate', { detail: page }));
  };

  function rpSetStep(n) {
    [1,2,3,4,5].forEach(function(i) {
      var circle = document.getElementById('regCircle'+i);
      var label  = document.getElementById('regLabel'+i);
      if (!circle || !label) return;
      if (i < n) {
        circle.style.borderColor='#d4a017'; circle.style.background='#d4a017'; circle.style.color='#13151a';
        label.style.color='#d4a017'; label.style.fontWeight='600';
        circle.classList.add('is-complete'); circle.classList.remove('is-active');
      } else if (i === n) {
        circle.style.borderColor='#d4a017'; circle.style.background='#1e2128'; circle.style.color='#d4a017';
        label.style.color='#d4a017'; label.style.fontWeight='600';
        circle.classList.add('is-active'); circle.classList.remove('is-complete');
      } else {
        circle.style.borderColor='#2a2d35'; circle.style.background='#1e2128'; circle.style.color='#64748b';
        label.style.color='#64748b'; label.style.fontWeight='400';
        circle.classList.remove('is-active','is-complete');
      }
      if (i < 5) { var line=document.getElementById('regLine'+i); if(line) { line.style.background = i < n ? '#d4a017' : '#2a2d35'; line.classList.toggle('is-complete', i < n); } }
    });
    [1,2,3,4,5].forEach(function(i) {
      var el = document.getElementById('regFormStep'+i);
      if (el) el.style.display = (i === n) ? 'block' : 'none';
    });
    window.staffScrollToTop();
  }

  function rpToggleOtherCitizenship() {
    var isOther = document.getElementById('rp_citizenship').value === 'Other';
    document.getElementById('rp_otherCitizenshipWrap').style.display = isOther ? 'block' : 'none';
    if (!isOther) document.getElementById('rp_otherCitizenship').value = '';
  }

  function rpCitizenshipValue() {
    return document.getElementById('rp_citizenship').value === 'Other'
      ? document.getElementById('rp_otherCitizenship').value.trim()
      : document.getElementById('rp_citizenship').value;
  }

  async function rpNext(step) {
    if (step === 1) {
      var fields = [
        {id:'rp_lastName',  label:'Last Name'},
        {id:'rp_firstName', label:'First Name'},
        {id:'rp_rank',      label:'Rank'},
        {id:'rp_afpSerial', label:'AFP Serial Number'},
        {id:'rp_unit',      label:'Unit / Organization'},
        {id:'rp_dob',       label:'Date of Birth'},
        {id:'rp_email',     label:'Email Address'}
      ];
      var errEl = document.getElementById('rp_err1');
      if (document.getElementById('rp_citizenship').value === 'Other') {
        fields.push({id:'rp_otherCitizenship', label:'Citizenship'});
      }
      for (var i=0; i<fields.length; i++) {
        var el = document.getElementById(fields[i].id);
        if (!el || !el.value.trim()) {
          if (el) { el.focus(); el.style.borderColor='#e53e3e'; }
          errEl.textContent = 'Please fill in: ' + fields[i].label;
          errEl.style.display = 'block';
          return;
        }
        if (el) el.style.borderColor = '';
      }
      var dobInput = document.getElementById('rp_dob');
      if (dobInput.value > dobInput.max) {
        dobInput.focus();
        dobInput.style.borderColor = '#e53e3e';
        errEl.textContent = 'Date of Birth cannot be in the future.';
        errEl.style.display = 'block';
        return;
      }
      var emailInput = document.getElementById('rp_email');
      if (!emailInput.checkValidity()) {
        emailInput.focus();
        emailInput.style.borderColor = '#e53e3e';
        errEl.textContent = 'Please enter a valid email address.';
        errEl.style.display = 'block';
        return;
      }

      var availabilityButton = document.getElementById('rpNextPersonalBtn');
      availabilityButton.disabled = true;
      availabilityButton.textContent = 'Checking...';

      try {
        var availabilityResponse = await fetch(document.getElementById('rpAvailabilityUrl').value, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            afpSerialNumber: document.getElementById('rp_afpSerial').value,
            email: emailInput.value,
            contactNumber: document.getElementById('rp_contact').value
          })
        });
        var availabilityData = await availabilityResponse.json();

        ['rp_afpSerial', 'rp_email', 'rp_contact'].forEach(function(id) {
          document.getElementById(id).style.borderColor = '';
        });

        if (!availabilityResponse.ok || !availabilityData.available) {
          var fieldMap = {
            afpSerialNumber: 'rp_afpSerial',
            email: 'rp_email',
            contactNumber: 'rp_contact'
          };
          var messages = [];
          Object.keys(availabilityData.errors || {}).forEach(function(field) {
            var input = document.getElementById(fieldMap[field]);
            if (input) input.style.borderColor = '#e53e3e';
            messages = messages.concat(availabilityData.errors[field]);
          });
          errEl.textContent = messages.join(' ') || availabilityData.message || 'Unable to verify these details.';
          errEl.style.display = 'block';
          var firstInvalidField = Object.keys(availabilityData.errors || {})[0];
          if (firstInvalidField && fieldMap[firstInvalidField]) {
            document.getElementById(fieldMap[firstInvalidField]).focus();
          }
          return;
        }
      } catch (error) {
        errEl.textContent = 'Unable to check for existing records. Please try again.';
        errEl.style.display = 'block';
        return;
      } finally {
        availabilityButton.disabled = false;
        availabilityButton.textContent = 'Next: Firearm Info →';
      }

      errEl.style.display = 'none';
      rpSetStep(2);
    } else if (step === 2) {
      var fields2 = [
        {id:'rp_pistolNomenclature', label:'Nomenclature of Pistol'},
        {id:'rp_pistolType',         label:'Pistol Type'},
        {id:'rp_pistolSerial',       label:'Pistol Serial Number'},
        {id:'rp_ammo',               label:'Quantity of Ammo'},
        {id:'rp_issuedBy',            label:'Issued By'}
      ];
      var errEl2 = document.getElementById('rp_err2');
      for (var j=0; j<fields2.length; j++) {
        var el2 = document.getElementById(fields2[j].id);
        if (!el2 || !String(el2.value).trim()) {
          if (el2) { el2.focus(); el2.style.borderColor='#e53e3e'; }
          errEl2.textContent = 'Please fill in: ' + fields2[j].label;
          errEl2.style.display = 'block';
          return;
        }
        if (el2) el2.style.borderColor = '';
      }
      var ammoInput = document.getElementById('rp_ammo');
      var ammoQuantity = Number(ammoInput.value);
      if (!Number.isInteger(ammoQuantity) || ammoQuantity < 0) {
        ammoInput.focus();
        ammoInput.style.borderColor = '#e53e3e';
        errEl2.textContent = 'Quantity of Ammo must be a whole number of 0 or more.';
        errEl2.style.display = 'block';
        return;
      }
      var firearmButton = document.getElementById('rpNextFirearmBtn');
      firearmButton.disabled = true;
      firearmButton.textContent = 'Checking...';

      try {
        var pistolResponse = await fetch(document.getElementById('rpAvailabilityUrl').value, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            pistolSerialNumber: document.getElementById('rp_pistolSerial').value
          })
        });
        var pistolData = await pistolResponse.json();
        document.getElementById('rp_pistolSerial').style.borderColor = '';

        if (!pistolResponse.ok || !pistolData.available) {
          document.getElementById('rp_pistolSerial').style.borderColor = '#e53e3e';
          errEl2.textContent = pistolData.errors?.pistolSerialNumber?.[0] || pistolData.message || 'Unable to verify the pistol serial number.';
          errEl2.style.display = 'block';
          document.getElementById('rp_pistolSerial').focus();
          return;
        }
      } catch (error) {
        errEl2.textContent = 'Unable to check the pistol serial number. Please try again.';
        errEl2.style.display = 'block';
        return;
      } finally {
        firearmButton.disabled = false;
        firearmButton.textContent = 'Next: Documents →';
      }
      errEl2.style.display = 'none';
      rpSetStep(3);
    } else if (step === 3) {
      document.getElementById('rp_parPersonnel').textContent = [document.getElementById('rp_rank').value, document.getElementById('rp_firstName').value, document.getElementById('rp_middleName').value, document.getElementById('rp_lastName').value].filter(Boolean).join(' ') + ' · ' + document.getElementById('rp_afpSerial').value;
      if (!document.getElementById('rp_parIssuedDate').value) document.getElementById('rp_parIssuedDate').value = new Date().toISOString().slice(0,10);
      rpNext(4);
    } else if (step === 4) {
      function row(l,v) { return '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #2a2d35;font-size:.82rem;"><span style="color:#64748b;">'+l+'</span><span style="color:#e5eaf2;font-weight:500;">'+(v||'—')+'</span></div>'; }
      var rp=[
        ['Last Name',document.getElementById('rp_lastName').value],
        ['First Name',document.getElementById('rp_firstName').value],
        ['Middle Name',document.getElementById('rp_middleName').value],
        ['Rank',document.getElementById('rp_rank').value],
        ['AFP Serial #',document.getElementById('rp_afpSerial').value],
        ['Unit',document.getElementById('rp_unit').value],
        ['AFOS/MOS',document.getElementById('rp_afosMos').value],
        ['Branch',document.getElementById('rp_branch').value],
        ['Date of Birth',document.getElementById('rp_dob').value],
        ['Email',document.getElementById('rp_email').value],
        ['Contact #',document.getElementById('rp_contact').value],
        ['Civil Status',document.getElementById('rp_civil').value],
        ['Gender',document.getElementById('rp_gender').value],
        ['Citizenship',rpCitizenshipValue()]
      ];
      document.getElementById('rp_reviewPersonal').innerHTML = rp.map(function(x){return row(x[0],x[1]);}).join('');
      var rf=[
        ['Nomenclature of Pistol',document.getElementById('rp_pistolNomenclature').value],
        ['Pistol Type',document.getElementById('rp_pistolType').value],
        ['Pistol Serial #',document.getElementById('rp_pistolSerial').value],
        ['Qty Ammo',document.getElementById('rp_ammo').value],
        ['Date Issued',document.getElementById('rp_dateIssued').value],
        ['Issued By',document.getElementById('rp_issuedBy').value],
        ['Armory / Unit',document.getElementById('rp_armory').value]
      ];
      document.getElementById('rp_reviewFirearm').innerHTML = rf.map(function(x){return row(x[0],x[1]);}).join('');
    document.getElementById('rp_reviewRemarks').innerHTML = row('Remarks', document.getElementById('rp_remarks').value||'None');

      var attachHtml = '';
      attachHtml += '<div style="text-align:center;"><p style="font-size:.7rem;color:#64748b;margin:0 0 6px;">2x2 Photo</p>';
      attachHtml += rpPhotoBase64
        ? '<img src="'+rpPhotoBase64+'" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #2a2d35;">'
        : '<div style="width:80px;height:80px;border:1px dashed #2a2d35;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#4b5563;font-size:.65rem;">None</div>';
      attachHtml += '</div>';

      attachHtml += '<div style="text-align:center;"><p style="font-size:.7rem;color:#64748b;margin:0 0 6px;">Signature</p>';
      attachHtml += rpSignatureBase64
        ? '<img src="'+rpSignatureBase64+'" style="height:60px;max-width:160px;border-radius:6px;border:1px solid #2a2d35;background:#fff;padding:4px;">'
        : '<div style="width:160px;height:60px;border:1px dashed #2a2d35;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#4b5563;font-size:.65rem;">None</div>';
      attachHtml += '</div>';

      document.getElementById('rp_reviewAttachments').innerHTML = attachHtml;

      document.getElementById('rp_reviewFirearm').innerHTML = rf.map(function(x){return row(x[0],x[1]);}).join('');
      rpSetStep(5);
    }
  }

  function rpPrev(step) {
    // The PAR package/cost screen is not part of personnel registration.
    // Back from Review returns directly to Documents & Uploads.
    rpSetStep(step === 5 ? 3 : step - 1);
  }

  function rpSubmit() {
    var CSRF      = document.querySelector('meta[name="csrf-token"]').content;
    var STORE_URL = document.getElementById('rpStoreUrl').value;
    var btn       = document.getElementById('rpSubmitBtn');
    var errEl     = document.getElementById('rp_submitError');
    var sucEl     = document.getElementById('rp_submitSuccess');
    var ammoInput = document.getElementById('rp_ammo');
    var ammoQuantity = Number(ammoInput.value);
    if (ammoInput.value === '' || !Number.isInteger(ammoQuantity) || ammoQuantity < 0) {
      ammoInput.focus();
      ammoInput.style.borderColor = '#e53e3e';
      errEl.textContent = 'Quantity of Ammo must be a whole number of 0 or more.';
      errEl.style.display = 'block';
      return;
    }
    btn.disabled  = true; btn.textContent = 'Submitting...';
    errEl.style.display='none'; sucEl.style.display='none';
  var body = {
      lastName:           document.getElementById('rp_lastName').value,
      firstName:          document.getElementById('rp_firstName').value,
      middleName:         document.getElementById('rp_middleName').value,
      rank:               document.getElementById('rp_rank').value,
      afpSerialNumber:    document.getElementById('rp_afpSerial').value,
      unit:               document.getElementById('rp_unit').value,
      dateOfBirth:        document.getElementById('rp_dob').value,
      email:              document.getElementById('rp_email').value,
      contactNumber:      document.getElementById('rp_contact').value,
      pistolNomenclature: document.getElementById('rp_pistolNomenclature').value,
      pistolType:         document.getElementById('rp_pistolType').value,
      pistolSerialNumber: document.getElementById('rp_pistolSerial').value,
      issuedBy:           document.getElementById('rp_issuedBy').value,
      qtyAmmo:            ammoQuantity,
    photo:              rpPhotoBase64 || null,
      signature:          rpSignatureBase64 || null,
      dateOfValidity:     null,
      afosMos:            document.getElementById('rp_afosMos').value,
      branch:             document.getElementById('rp_branch').value,
      civilStatus:        document.getElementById('rp_civil').value,
      citizenship:        rpCitizenshipValue(),
      remarks:            document.getElementById('rp_remarks').value
    };
    fetch(STORE_URL, {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},body:JSON.stringify(body)})
      .then(function(r){return r.json();})
      .then(function(data){
        if (data.success) {
          sucEl.textContent='✓ Registration successful! Item #'+data.data.itemNumber+' submitted for admin inspection.';
          sucEl.style.display='block'; btn.textContent='✓ Submitted!';
        setTimeout(function(){
            rpSetStep(1);
            rpPhotoBase64 = null;
            rpSignatureBase64 = null;
            rpCloseCamera();
            document.getElementById('rp_sigBox').innerHTML = '<p style="font-size:.75rem;color:#64748b;margin:0;">Click to capture signature</p>';
            document.getElementById('rp_photoPreview').style.display = 'none';
            document.getElementById('rp_photo').value = '';
            document.getElementById('rp_photoSource').textContent = '';
            ['rp_lastName','rp_firstName','rp_middleName','rp_afpSerial','rp_email','rp_contact','rp_pistolSerial','rp_ammo','rp_issuedBy','rp_armory','rp_remarks','rp_afosMos','rp_branch','rp_otherCitizenship'].forEach(function(id){var el=document.getElementById(id);if(el)el.value='';});
            ['rp_rank','rp_unit','rp_pistolNomenclature','rp_civil','rp_gender','rp_citizenship','rp_dob','rp_dateIssued'].forEach(function(id){var el=document.getElementById(id);if(el)el.value='';});
            document.getElementById('rp_citizenship').value='Filipino';rpToggleOtherCitizenship();
            ['rp_parIssuedBy','rp_parApprovedBy','rp_parValidUntil'].forEach(function(id){document.getElementById(id).value='';});
            window._rpNavigate('dashboard');
          }, 2000);
        } else {
          var validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
          throw new Error(validationMessage || data.message || data.error || 'Submission failed.');
        }
      })
      .catch(function(e){
        errEl.textContent='✗ '+e.message; errEl.style.display='block';
        btn.disabled=false; btn.textContent='Submit Registration';
      });
  }

  var rpPhotoBase64 = null;
  var rpSignatureBase64 = null;
  var rpCameraStream = null;

  function rpSetPhoto(dataUrl, sourceLabel) {
    rpPhotoBase64 = dataUrl;
    document.getElementById('rp_photoImg').src = dataUrl;
    document.getElementById('rp_photoPreview').style.display = 'block';
    document.getElementById('rp_photoSource').textContent = sourceLabel || 'Photo ready';
  }

  function rpPreviewPhoto(input) {
    if (input.files && input.files[0]) {
      if (!input.files[0].type.startsWith('image/')) return;
      var reader = new FileReader();
      reader.onload = function(e) {
        rpSetPhoto(e.target.result, 'Attached photo ready');
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  async function rpOpenCamera() {
    var modal = document.getElementById('rp_cameraModal');
    var status = document.getElementById('rp_cameraStatus');
    var captureBtn = document.getElementById('rp_cameraCaptureBtn');
    modal.style.display = 'flex';
    status.textContent = 'Requesting camera access...';
    status.style.color = '#94a3b8';
    captureBtn.disabled = true;

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      status.textContent = 'Camera access is unavailable. Use HTTPS or attach a photo instead.';
      status.style.color = '#fc8181';
      return;
    }

    try {
      rpStopCameraStream();
      rpCameraStream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 1280 } },
        audio: false
      });
      if (modal.style.display === 'none') {
        rpStopCameraStream();
        return;
      }
      var video = document.getElementById('rp_cameraVideo');
      video.srcObject = rpCameraStream;
      await video.play();
      status.textContent = 'Center the face and shoulders inside the guide.';
      captureBtn.disabled = false;
    } catch (error) {
      rpStopCameraStream();
      var cameraErrors = {
        NotAllowedError: 'Camera access is blocked. Check site permission and your device camera privacy settings, then try again.',
        SecurityError: 'Camera access is blocked by browser or site security settings.',
        NotFoundError: 'No camera was found. Connect a camera or attach a photo instead.',
        NotReadableError: 'The camera is unavailable or being used by another app. Close other camera apps and try again.',
        OverconstrainedError: 'The camera does not support the requested settings. Attach a photo instead.'
      };
      status.textContent = cameraErrors[error.name] || 'The camera could not be opened. Try again or attach a photo instead.';
      status.style.color = '#fc8181';
    }
  }

  function rpStopCameraStream() {
    if (rpCameraStream) {
      rpCameraStream.getTracks().forEach(function(track) { track.stop(); });
      rpCameraStream = null;
    }
    var video = document.getElementById('rp_cameraVideo');
    if (video) video.srcObject = null;
  }

  function rpCloseCamera() {
    rpStopCameraStream();
    var modal = document.getElementById('rp_cameraModal');
    if (modal) modal.style.display = 'none';
  }

  function rpCapturePhoto() {
    var video = document.getElementById('rp_cameraVideo');
    if (!video.videoWidth || !video.videoHeight) return;

    var canvas = document.getElementById('rp_cameraCanvas');
    var ctx = canvas.getContext('2d');
    var sourceSize = Math.min(video.videoWidth, video.videoHeight);
    var sourceX = (video.videoWidth - sourceSize) / 2;
    var sourceY = (video.videoHeight - sourceSize) / 2;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(video, sourceX, sourceY, sourceSize, sourceSize, 0, 0, canvas.width, canvas.height);
    rpSetPhoto(canvas.toDataURL('image/jpeg', 0.9), 'Camera photo ready');
    document.getElementById('rp_photo').value = '';
    rpCloseCamera();
  }

  window.addEventListener('pagehide', rpStopCameraStream);

  function rpOpenSig() {
    var modal=document.getElementById('rp_sigModal'); modal.style.display='flex';
    var canvas=document.getElementById('rp_sigCanvas'), ctx=canvas.getContext('2d');
    ctx.clearRect(0,0,canvas.width,canvas.height);
    ctx.strokeStyle='#d4a017'; ctx.lineWidth=2; ctx.lineCap='round';
    function pos(e){var r=canvas.getBoundingClientRect(),sx=canvas.width/r.width,sy=canvas.height/r.height,cx=e.touches?e.touches[0].clientX:e.clientX,cy=e.touches?e.touches[0].clientY:e.clientY;return{x:(cx-r.left)*sx,y:(cy-r.top)*sy};}
    canvas.onmousedown=canvas.ontouchstart=function(e){rpSigDrawing=true;var p=pos(e);ctx.beginPath();ctx.moveTo(p.x,p.y);e.preventDefault();};
    canvas.onmousemove=canvas.ontouchmove=function(e){if(!rpSigDrawing)return;var p=pos(e);ctx.lineTo(p.x,p.y);ctx.stroke();e.preventDefault();};
    canvas.onmouseup=canvas.ontouchend=function(){rpSigDrawing=false;};
  }

  function rpClearSig(){var c=document.getElementById('rp_sigCanvas');c.getContext('2d').clearRect(0,0,c.width,c.height);}

  function rpSaveSig(){
    var data=document.getElementById('rp_sigCanvas').toDataURL();
    rpSignatureBase64 = data;
    document.getElementById('rp_sigModal').style.display='none';
    document.getElementById('rp_sigBox').innerHTML='<img src="'+data+'" style="max-height:55px;border-radius:6px;"><p style="font-size:.68rem;color:#64748b;margin:0;">Signature saved ✓</p>';
  }
  </script>

</body>
  </html>
