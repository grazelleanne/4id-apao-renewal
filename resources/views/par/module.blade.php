
<style>
  /* ===== shared cards / pills (also used on the hub + management) ===== */
  .par-card{display:flex;align-items:center;gap:14px;background:#1a1f2b;border:1px solid #2a2d35;border-radius:12px;padding:16px 18px;}
  .par-card-icon{width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .par-card-num{font-size:1.65rem;font-weight:800;color:#e5eaf2;line-height:1.1;}
  .par-card-title{font-size:.82rem;font-weight:700;color:#cbd5e0;margin:0;}
  .par-card-sub{font-size:.72rem;color:#64748b;margin:2px 0 0;}

  .par-crumb{display:inline-flex;align-items:center;gap:6px;background:#1a2025;color:#94a3b8;border:1px solid #2e3748;border-radius:7px;padding:6px 14px;font-size:.76rem;font-weight:600;cursor:pointer;margin-bottom:14px;}
  .par-crumb:hover{background:#212a36;color:#e5eaf2;}

  .par-filter-label{font-size:.68rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:5px;display:block;}
  .par-filter-input{width:100%;background:#1a2025;color:#e5eaf2;border:1px solid #363b48;border-radius:7px;padding:.5rem .7rem;font-size:.8rem;outline:none;box-sizing:border-box;transition:border-color .15s;}
  .par-filter-input:focus{border-color:#3ec6ff;}
  .par-search-wrap{position:relative;}
  .par-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);opacity:.4;pointer-events:none;}
  .par-search-wrap .par-filter-input{padding-left:32px;}

  /* ===== PAR Management — header banner + filter bar ===== */
  .par-mgmt-header{background:#12161d;border-radius:10px;padding:16px 20px;}
  .par-mgmt-header h1{font-size:1.3rem;font-weight:800;margin:0 0 3px;}
  .par-mgmt-header p{font-size:.8rem;color:#8b96a5;margin:0;}
  .par-mgmt-filterbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:14px 16px;}
  .par-filter-btn{display:inline-flex;align-items:center;gap:6px;background:#1a2025;color:#cbd5e0;border:1px solid #363b48;border-radius:7px;padding:.5rem .9rem;font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap;}
  .par-filter-btn:hover{background:#212a36;}
  .par-reset-btn{display:inline-flex;align-items:center;gap:6px;background:transparent;color:#94a3b8;border:1px solid #2e3748;border-radius:7px;padding:.5rem .9rem;font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap;}
  .par-reset-btn:hover{background:#1a2025;color:#e5eaf2;}

  /* ===== PAR Management — records table row selection ===== */
  #par-mgmt-tbody tr{cursor:pointer;border-left:3px solid transparent;}
  #par-mgmt-tbody tr.par-row-selected{background:#1c2530;border-left:3px solid #d4a017;}
  #par-mgmt-tbody tr.par-row-selected td:first-child{color:#f0b90b;font-weight:800;}

  /* ===== PAR Management — Selected PAR Summary panel ===== */
  .par-summary-panel{width:100%;max-width:340px;flex-shrink:0;background:#23272f;border:1px solid #2a2d35;border-radius:12px;padding:18px 20px;position:sticky;top:16px;}
  .par-summary-head{display:flex;align-items:center;gap:8px;color:#e5eaf2;font-size:.86rem;font-weight:800;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #2a2d35;}
  .par-summary-row{margin-bottom:12px;}
  .par-summary-row .lbl{font-size:.68rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;}
  .par-summary-row .val{font-size:.84rem;color:#e5eaf2;font-weight:700;}
  .par-summary-actions{display:flex;flex-direction:column;gap:8px;margin-top:16px;}
  .par-summary-btn{display:flex;align-items:center;justify-content:center;gap:7px;border-radius:8px;padding:.6rem;font-size:.8rem;font-weight:700;cursor:pointer;border:1px solid;}
  .par-summary-btn-outline{background:transparent;color:#cbd5e0;border-color:#3a4252;}
  .par-summary-btn-outline:hover{background:#1a2025;}
  .par-summary-btn-solid{background:#b8860f;color:#fff;border-color:#b8860f;}
  .par-summary-btn-solid:hover{background:#9c700c;}

  body.light-mode .par-mgmt-header{background:#f1f5f9!important;}
  body.light-mode .par-mgmt-header p{color:#64748b!important;}
  body.light-mode .par-summary-panel{background:#fff!important;border-color:#e2e8f0!important;}
  body.light-mode .par-summary-row .val{color:#1e293b!important;}
  body.light-mode #par-mgmt-tbody tr.par-row-selected{background:#fff7e6!important;}

  .par-table-card{background:#23272f;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.15);overflow:hidden;}
  .par-avatar{width:38px;height:38px;border-radius:50%;object-fit:cover;background:#1a2025;border:1px solid #2e3748;flex-shrink:0;}

  .par-status-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:99px;font-size:.68rem;font-weight:800;white-space:nowrap;}
  .par-status-pill span{width:6px;height:6px;border-radius:50%;flex-shrink:0;}
  .par-status-ready{background:#2c2000;color:#d4a017;}
  .par-status-ready span{background:#d4a017;}
  .par-status-issued{background:#0c2e1a;color:#33b481;}
  .par-status-issued span{background:#33b481;}
  .par-status-returned{background:#241033;color:#c084fc;}
  .par-status-returned span{background:#c084fc;}

  .par-process-btn{background:#1a7a3e;color:#fff;border:1px solid #1a7a3e;border-radius:6px;padding:6px 13px;font-size:.72rem;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;transition:background .15s;}
  .par-process-btn:hover{background:#22974d;}
  .par-ghost-btn{background:transparent;color:#94a3b8;border:1px solid #2e3748;border-radius:6px;padding:6px 12px;font-size:.72rem;font-weight:700;cursor:pointer;white-space:nowrap;}
  .par-ghost-btn:hover{background:#1a2025;color:#e5eaf2;}
  .par-kebab-wrap{position:relative;display:inline-block;}
  .par-kebab-btn{background:transparent;border:none;color:#64748b;cursor:pointer;padding:5px 7px;border-radius:6px;line-height:0;}
  .par-kebab-btn:hover{background:#1a2025;color:#e5eaf2;}
  .par-kebab-menu{position:absolute;right:0;top:calc(100% + 4px);background:#23272f;border:1px solid #363b48;border-radius:8px;min-width:190px;box-shadow:0 12px 28px rgba(0,0,0,.45);z-index:80;overflow:hidden;display:none;}
  .par-kebab-menu.open{display:block;}
  .par-kebab-item{padding:9px 13px;font-size:.76rem;color:#cbd5e0;cursor:pointer;display:flex;align-items:center;gap:8px;}
  .par-kebab-item:hover{background:#1a2025;}
  .par-kebab-item.danger{color:#fc8181;}

  .par-badge{display:inline-block;padding:3px 10px;border-radius:6px;font-size:.68rem;font-weight:800;white-space:nowrap;}
  .par-badge-issued{background:#0c2e1a;color:#33b481;}
  .par-badge-updated{background:#0a1f38;color:#3ec6ff;}
  .par-badge-reprinted{background:#2d1a0a;color:#fb923c;}
  .par-badge-replaced{background:#241033;color:#c084fc;}

  /* ===== HUB ===== */
  .par-hub-header h1{font-size:1.7rem;font-weight:800;letter-spacing:.01em;}
  .par-hub-header p{margin:2px 0 0;}
  .par-hub-card{display:flex;flex-direction:column;height:100%;border-radius:14px;overflow:hidden;cursor:pointer;transition:transform .15s,box-shadow .15s;}
  .par-hub-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(0,0,0,.35);}
  .par-hub-card-green{background:#e7f6ec;}
  .par-hub-card-amber{background:#fdf3d8;}
  .par-hub-card-body{display:flex;flex:1;gap:16px;padding:22px 22px 16px;align-items:flex-start;}
  .par-hub-card-body > div:last-child{display:flex;flex-direction:column;align-items:flex-start;flex:1;height:100%;}
  .par-hub-card-body .par-hub-btn{margin-top:auto;}
  .par-hub-icon{width:56px;height:56px;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(0,0,0,.1);}
  .par-hub-title{font-size:1.15rem;font-weight:800;letter-spacing:.02em;margin:2px 0 6px;}
  .par-hub-desc{font-size:.82rem;color:#3f4a43;margin:0 0 14px;line-height:1.5;max-width:380px;}
  .par-hub-btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:8px;padding:10px 18px;font-size:.82rem;font-weight:700;cursor:pointer;color:#fff;}
  .par-hub-btn-green{background:#15803d;}
  .par-hub-btn-green:hover{background:#166534;}
  .par-hub-btn-amber{background:#b8860f;}
  .par-hub-btn-amber:hover{background:#9c700c;}
  .par-hub-footer{display:flex;align-items:center;gap:8px;margin-top:auto;min-height:46px;box-sizing:border-box;padding:11px 22px;font-size:.78rem;font-weight:700;}
  .par-hub-footer-green{background:#cdeed9;color:#15803d;}
  .par-hub-footer-amber{background:#faeab8;color:#8a6108;}

  /* ===== DOC VIEW ===== */
  #par-doc-view .par-process-card{background:#23272f;border:1px solid #2f3540;border-radius:12px;padding:20px;}
  .par-doc-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;}
  .par-doc-field-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px 14px;}
  .par-readonly-field{background:#1b1f26;border:1px solid #343b47;border-radius:7px;padding:.5rem .7rem;font-size:.8rem;color:#e5eaf2;font-weight:600;min-height:2.1rem;box-sizing:border-box;}
  .par-equip-note{background:#0c2418;border:1px solid #1a5c3a;color:#7fe3ac;font-size:.72rem;font-weight:700;border-radius:7px;padding:8px 12px;margin:10px 0;display:flex;align-items:center;gap:7px;}
  .par-equip-check{display:flex;align-items:center;gap:8px;font-size:.76rem;color:#cbd3df;padding:6px 0;}
  .par-equip-check svg{color:#33b481;flex-shrink:0;}
  .par-equip-hint{font-size:.68rem;color:#64748b;margin-top:10px;display:flex;gap:6px;align-items:flex-start;}
  .par-cost-row{display:flex;justify-content:space-between;font-size:.8rem;color:#b7c0cc;padding:6px 0;border-bottom:1px solid #2b313c;}
  .par-cost-row:last-of-type{border-bottom:none;}
  .par-cost-row strong{color:#e5eaf2;}
  .par-net-total{display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding-top:10px;border-top:1px dashed #364052;}
  .par-net-total span{font-size:.78rem;color:#8ea0b3;font-weight:700;text-transform:uppercase;letter-spacing:.04em;}
  .par-net-total strong{font-size:1.4rem;color:#33d17a;font-weight:800;}

  .par-sig-col{text-align:center;}
  .par-sig-col h5{font-size:.7rem;color:#8ea0b3;text-transform:uppercase;letter-spacing:.05em;margin:0 0 8px;font-weight:700;}
  .par-sig-box{border:1.5px dashed #3a4252;border-radius:8px;background:#141820;min-height:70px;display:flex;align-items:center;justify-content:center;margin-bottom:8px;overflow:hidden;cursor:pointer;position:relative;}
  .par-sig-box.disabled{cursor:not-allowed;opacity:.7;}
  .par-sig-box img{max-height:64px;max-width:92%;object-fit:contain;}
  .par-sig-box .empty{color:#4b5a72;font-size:.7rem;}
  .par-sig-name{font-size:.75rem;color:#e5eaf2;font-weight:700;}
  .par-sig-clear{font-size:.68rem;color:#fc8181;background:none;border:none;cursor:pointer;margin-top:4px;}

  .par-preview-card{position:sticky;top:16px;}
  .par-preview-head{display:flex;align-items:center;gap:8px;color:#94a3b8;font-size:.78rem;font-weight:700;margin-bottom:2px;}
  .par-preview-sub{font-size:.7rem;color:#64748b;margin:2px 0 14px;}

  .par-preview-actions{display:flex;gap:8px;margin-top:14px;}
  .par-preview-actions button{flex:1;background:#0a1f2d;color:#3ec6ff;border:1px solid #1a3a4f;border-radius:7px;padding:8px 10px;font-size:.72rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;transition:background .15s;}
  .par-preview-actions button:hover{background:#112840;}

  /* Live preview: reuses the SAME markup + stylesheet as the real PDF
     (par._receipt_styles / par._receipt), just visually shrunk to fit the
     sidebar. Print/PDF always source from the un-scaled node so print
     output is never affected by this scale. */
.par-preview-scale-outer{width:100%;border-radius:10px;border:1px solid #d6dde8;box-shadow:0 12px 36px rgba(0,0,0,.25);background:#fff;overflow:hidden;}
  .par-preview-scale{zoom:.535;width:190mm;}
  .par-preview-scale .par-receipt{min-height:auto;}
  body.light-mode .par-card{background:#fff!important;border-color:#e2e8f0!important;}
  body.light-mode .par-card-title{color:#1e293b!important;}
  body.light-mode .par-table-card{background:#fff!important;border:1px solid #e2e8f0!important;}
  body.light-mode #par-tbody td,body.light-mode #par-mgmt-tbody td{color:#1e293b!important;}
  body.light-mode .par-readonly-field{background:#f1f5f9!important;color:#1e293b!important;border-color:#cbd5e1!important;}
  body.light-mode #par-doc-view .par-process-card{background:#fff!important;border-color:#d7dee8!important;}
  body.light-mode .par-cost-row{color:#334155!important;border-color:#e2e8f0!important;}
  body.light-mode .par-sig-box{background:#f8fafc!important;border-color:#cbd5e1!important;}

  /* ===== COMPLETE PAR LIGHT MODE FIXES ===== */
  body.light-mode #page-par{color:#1e293b!important;}
  body.light-mode #page-par .force-light-text{color:#1e293b!important;}
  body.light-mode #page-par .par-card-num,
  body.light-mode #page-par .par-process-title,
  body.light-mode #page-par .par-summary-head,
  body.light-mode #page-par .par-sig-name{color:#1e293b!important;}
  body.light-mode #page-par .par-card-sub,
  body.light-mode #page-par .par-filter-label,
  body.light-mode #page-par .par-preview-head,
  body.light-mode #page-par .par-preview-sub,
  body.light-mode #page-par .par-equip-hint,
  body.light-mode #page-par .par-summary-row .lbl{color:#64748b!important;}

  body.light-mode #page-par .par-crumb,
  body.light-mode #page-par .par-filter-btn,
  body.light-mode #page-par .par-reset-btn,
  body.light-mode #page-par .par-ghost-btn,
  body.light-mode #page-par .par-preview-actions button{
    background:#ffffff!important;color:#475569!important;border-color:#cbd5e1!important;
  }
  body.light-mode #page-par .par-crumb:hover,
  body.light-mode #page-par .par-filter-btn:hover,
  body.light-mode #page-par .par-reset-btn:hover,
  body.light-mode #page-par .par-ghost-btn:hover,
  body.light-mode #page-par .par-preview-actions button:hover{
    background:#f1f5f9!important;color:#1e293b!important;
  }

  body.light-mode #page-par .par-filter-input,
  body.light-mode #page-par .reg-input{
    background:#ffffff!important;color:#1e293b!important;border-color:#cbd5e1!important;
  }
  body.light-mode #page-par .par-filter-input::placeholder,
  body.light-mode #page-par .reg-input::placeholder{color:#94a3b8!important;}

  body.light-mode #page-par .par-mgmt-filterbar{background:#ffffff!important;}
  body.light-mode #page-par .par-mgmt-header h1{color:#1e293b!important;}
  body.light-mode #page-par .par-summary-head{border-color:#e2e8f0!important;}
  body.light-mode #page-par .par-summary-btn-outline{background:#ffffff!important;color:#334155!important;border-color:#cbd5e1!important;}
  body.light-mode #page-par .par-summary-btn-outline:hover{background:#f8fafc!important;}

  body.light-mode #page-par table thead tr{background:#f1f5f9!important;border-color:#e2e8f0!important;}
  body.light-mode #page-par table th{color:#475569!important;background:#f1f5f9!important;border-color:#e2e8f0!important;}
  body.light-mode #page-par table td{color:#1e293b!important;background:#ffffff!important;border-color:#e2e8f0!important;}
  body.light-mode #page-par tbody tr:hover td{background:#f8fafc!important;}
  body.light-mode #page-par #par-tbody tr,
  body.light-mode #page-par #par-mgmt-tbody tr,
  body.light-mode #page-par #par-hub-activity-tbody tr,
  body.light-mode #page-par #par-mgmt-activity-tbody tr{border-color:#e2e8f0!important;}
  body.light-mode #page-par #par-tbody div[style*="color:#e5eaf2"]{color:#1e293b!important;}
  body.light-mode #page-par #par-tbody div[style*="color:#64748b"]{color:#64748b!important;}

  body.light-mode #page-par .par-kebab-btn{color:#64748b!important;}
  body.light-mode #page-par .par-kebab-btn:hover{background:#f1f5f9!important;color:#1e293b!important;}
  body.light-mode #page-par .par-kebab-menu{background:#ffffff!important;border-color:#d0d7e4!important;box-shadow:0 12px 28px rgba(15,23,42,.16)!important;}
  body.light-mode #page-par .par-kebab-item{color:#334155!important;}
  body.light-mode #page-par .par-kebab-item:hover{background:#f8fafc!important;}
  body.light-mode #page-par .par-kebab-item.danger{color:#b91c1c!important;}

  body.light-mode #page-par .par-status-ready{background:#fffbeb!important;color:#a16207!important;}
  body.light-mode #page-par .par-status-issued{background:#ecfdf5!important;color:#047857!important;}
  body.light-mode #page-par .par-status-returned{background:#faf5ff!important;color:#7e22ce!important;}
  body.light-mode #page-par .par-badge-issued{background:#ecfdf5!important;color:#047857!important;}
  body.light-mode #page-par .par-badge-updated{background:#eff6ff!important;color:#1d4ed8!important;}
  body.light-mode #page-par .par-badge-reprinted{background:#fff7ed!important;color:#c2410c!important;}
  body.light-mode #page-par .par-badge-replaced{background:#faf5ff!important;color:#7e22ce!important;}

  body.light-mode #page-par .par-info-box{background:#eff6ff!important;border-color:#bfdbfe!important;}
  body.light-mode #page-par .par-info-box p[style*="color:#e5eaf2"]{color:#1e293b!important;}
  body.light-mode #page-par .par-info-box p[style*="color:#64748b"]{color:#475569!important;}
  body.light-mode #page-par .par-info-box div[style*="background:#0d2d4a"]{background:#dbeafe!important;}
  body.light-mode #page-par .par-info-box div[style*="color:#2e4a5f"]{color:#94a3b8!important;}

  body.light-mode #page-par [style*="background:#241c06"]{background:#fffbeb!important;border-color:#fcd34d!important;}
  body.light-mode #page-par [style*="color:#f6d789"]{color:#92400e!important;}
  body.light-mode #page-par [style*="color:#f0b90b"]{color:#a16207!important;}

  body.light-mode #page-par .par-equip-note{background:#ecfdf5!important;border-color:#86efac!important;color:#047857!important;}
  body.light-mode #page-par .par-equip-check{color:#334155!important;}
  body.light-mode #page-par .par-cost-row strong{color:#1e293b!important;}
  body.light-mode #page-par .par-net-total{border-color:#cbd5e1!important;}
  body.light-mode #page-par .par-net-total span{color:#475569!important;}
  body.light-mode #page-par .par-net-total strong{color:#047857!important;}
  body.light-mode #page-par .par-sig-col h5{color:#475569!important;}
  body.light-mode #page-par .par-sig-box .empty{color:#94a3b8!important;}
  body.light-mode #page-par .par-preview-scale-outer{border-color:#cbd5e1!important;box-shadow:0 8px 24px rgba(15,23,42,.12)!important;}

  body.light-mode #page-par button[style*="background:#1a2025"],
  body.light-mode #page-par button[style*="background:transparent"]{background:#ffffff!important;color:#475569!important;border-color:#cbd5e1!important;}
  body.light-mode #page-par button[style*="background:#1a2025"]:hover,
  body.light-mode #page-par button[style*="background:transparent"]:hover{background:#f1f5f9!important;color:#1e293b!important;}

  body.light-mode #page-par p[style*="color:#e5eaf2"],
  body.light-mode #page-par span[style*="color:#e5eaf2"],
  body.light-mode #page-par h3[style*="color:#e5eaf2"]{color:#1e293b!important;}
  body.light-mode #page-par p[style*="color:#64748b"],
  body.light-mode #page-par span[style*="color:#64748b"]{color:#64748b!important;}

  body.light-mode #page-par #par-tbl-pages button,
  body.light-mode #page-par #par-mgmt-tbl-pages button{background:#ffffff!important;color:#64748b!important;border-color:#cbd5e1!important;}
  body.light-mode #page-par #par-tbl-pages button[style*="border-color:#3ec6ff"],
  body.light-mode #page-par #par-mgmt-tbl-pages button[style*="border-color:#3ec6ff"]{background:#e0f2fe!important;color:#0369a1!important;border-color:#38bdf8!important;}

  /* ===== DEDICATED REPLACE PAR WORKFLOW ===== */
  .par-replace-banner{background:#241033;border:1px solid #6b21a8;border-radius:12px;padding:16px 18px;margin-bottom:18px;}
  .par-replace-banner h3{color:#d8b4fe;font-size:.9rem;font-weight:800;margin:0 0 4px;}
  .par-replace-banner p{color:#c4b5fd;font-size:.74rem;margin:0;line-height:1.5;}
  .par-replace-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;}
  .par-replace-card{background:#23272f;border:1px solid #2f3540;border-radius:12px;padding:18px;}
  .par-replace-card h3{color:#e5eaf2;font-size:.82rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;margin:0 0 14px;}
  .par-replace-readonly{background:#1b1f26;border:1px solid #343b47;border-radius:7px;padding:.55rem .7rem;font-size:.8rem;color:#e5eaf2;font-weight:600;min-height:2.15rem;}
  .par-replace-note{background:#2d1a0a;border:1px solid #7c4a03;border-radius:8px;padding:10px 12px;color:#f6d789;font-size:.72rem;line-height:1.5;margin-top:10px;}
  .par-replace-actions{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:18px;}
  .par-replace-confirm{background:#9333ea;color:#fff;border:0;border-radius:8px;padding:10px 20px;font-size:.84rem;font-weight:800;cursor:pointer;}
  .par-replace-confirm:hover{background:#7e22ce;}
  .par-replace-confirm:disabled{opacity:.6;cursor:not-allowed;}
  body.light-mode #par-replace-view .par-replace-banner{background:#faf5ff!important;border-color:#d8b4fe!important;}
  body.light-mode #par-replace-view .par-replace-banner h3{color:#7e22ce!important;}
  body.light-mode #par-replace-view .par-replace-banner p{color:#6b21a8!important;}
  body.light-mode #par-replace-view .par-replace-card{background:#fff!important;border-color:#e2e8f0!important;}
  body.light-mode #par-replace-view .par-replace-card h3{color:#1e293b!important;}
  body.light-mode #par-replace-view .par-replace-readonly{background:#f8fafc!important;border-color:#cbd5e1!important;color:#1e293b!important;}
  body.light-mode #par-replace-view .par-replace-note{background:#fffbeb!important;border-color:#fcd34d!important;color:#92400e!important;}
  @media(max-width:800px){.par-replace-grid{grid-template-columns:1fr;}}

</style>


<?php echo $__env->make('par._receipt_styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div id="page-par" class="page-section">


  <div id="par-hub-view">
    <div class="par-hub-header mb-6">
      <h1 class="force-light-text">PAR</h1>
      <p class="text-sm text-[#94a3b8]">Property Acknowledgement Receipt</p>
      <p class="text-xs text-[#64748b]">Manage property acknowledgement receipts for personnel.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">
      <div class="par-hub-card par-hub-card-green" onclick="parShowIssuanceList()">
        <div class="par-hub-card-body">
          <div class="par-hub-icon">
            <svg width="26" height="26" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6M9 15h6"/></svg>
          </div>
          <div>
            <h3 class="par-hub-title" style="color:#15803d;">PAR ISSUANCE</h3>
            <p class="par-hub-desc">Create a new PAR for newly registered personnel who have no PAR yet.</p>
            <button type="button" class="par-hub-btn par-hub-btn-green" onclick="event.stopPropagation();parShowIssuanceList()">
              Open PAR Issuance
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            </button>
          </div>
        </div>
        <div class="par-hub-footer par-hub-footer-green">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          <span id="par-hub-ready-count">0</span>&nbsp;personnel waiting for PAR issuance
        </div>
      </div>

      <div class="par-hub-card par-hub-card-amber" onclick="parShowMgmtList()">
        <div class="par-hub-card-body">
          <div class="par-hub-icon">
            <svg width="26" height="26" fill="none" stroke="#b8860f" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><circle cx="12" cy="16" r="2.4"/><path d="M12 12.6v.7M12 17.9v.7M15.4 16h-.7M9.3 16h-.7M14.4 13.6l-.5.5M10.6 17.9l-.5.5M14.4 18.4l-.5-.5M10.6 14.1l-.5-.5"/></svg>
          </div>
          <div>
            <h3 class="par-hub-title" style="color:#8a6108;">PAR MANAGEMENT</h3>
            <p class="par-hub-desc">View, update, replace, or reprint existing PAR records.</p>
            <button type="button" class="par-hub-btn par-hub-btn-amber" onclick="event.stopPropagation();parShowMgmtList()">
              Open PAR Management
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            </button>
          </div>
        </div>
        <div class="par-hub-footer par-hub-footer-amber">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <span id="par-hub-existing-count">0</span>&nbsp;existing PAR records
        </div>
      </div>
    </div>

    <p style="color:#e5eaf2;font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px;">PAR Summary</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="par-card">
        <div class="par-card-icon" style="background:#0c2e1a;">
          <svg width="22" height="22" fill="none" stroke="#33b481" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
          <p class="par-card-num" id="par-sum-waiting">0</p>
          <p class="par-card-title">Waiting for PAR Issuance</p>
          <p class="par-card-sub">New personnel with no PAR yet</p>
        </div>
      </div>
      <div class="par-card">
        <div class="par-card-icon" style="background:#0a1f38;">
          <svg width="22" height="22" fill="none" stroke="#3ec6ff" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
        </div>
        <div>
          <p class="par-card-num" id="par-sum-existing">0</p>
          <p class="par-card-title">Existing PAR Records</p>
          <p class="par-card-sub">Total issued PAR records</p>
        </div>
      </div>
      <div class="par-card">
        <div class="par-card-icon" style="background:#241033;">
          <svg width="22" height="22" fill="none" stroke="#c084fc" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4z"/></svg>
        </div>
        <div>
          <p class="par-card-num" id="par-sum-updated">0</p>
          <p class="par-card-title">PAR Updated This Month</p>
          <p class="par-card-sub">Replaced or updated PAR</p>
        </div>
      </div>
      <div class="par-card">
        <div class="par-card-icon" style="background:#2d1a0a;">
          <svg width="22" height="22" fill="none" stroke="#fb923c" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
        </div>
        <div>
          <p class="par-card-num" id="par-sum-reprinted">0</p>
          <p class="par-card-title">PAR Reprinted This Month</p>
          <p class="par-card-sub">Reprinted PAR copies</p>
        </div>
      </div>
    </div>

    <div class="par-table-card mb-4">
      <div class="px-5 pt-4 pb-2">
        <span style="color:#e5eaf2;font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;">Recent PAR Activity</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left force-light-text" style="border-collapse:collapse;">
          <thead>
            <tr class="border-b border-[#252b32] text-[#b0bac7]">
              <th class="py-2 px-3 font-semibold">Date &amp; Time</th>
              <th class="py-2 px-3 font-semibold">Personnel</th>
              <th class="py-2 px-3 font-semibold">Action</th>
              <th class="py-2 px-3 font-semibold">PAR Number</th>
              <th class="py-2 px-3 font-semibold">By</th>
            </tr>
          </thead>
          <tbody id="par-hub-activity-tbody"></tbody>
        </table>
      </div>
      <div class="text-center py-3" style="border-top:1px solid #1e2530;">
        <button type="button" onclick="parShowMgmtList()" style="background:none;border:none;color:#3ec6ff;font-size:.78rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
          View all activity
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
        </button>
      </div>
    </div>
  </div>


  <div id="par-issuance-list" style="display:none;">
    <button type="button" class="par-crumb" onclick="parShowHub()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      PAR Home
    </button>
    <div class="mb-5">
      <h1 class="text-xl font-bold force-light-text tracking-tight" style="letter-spacing:.02em;">PAR ISSUANCE</h1>
      <p class="text-xs text-[#64748b] mt-1">List of personnel who are ready for PAR processing.</p>
      <p class="text-xs text-[#64748b]">These personnel have already passed inspection and are waiting for their Property Acknowledgement Receipt (PAR).</p>
    </div>


    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <div>
        <label class="par-filter-label">Search Personnel</label>
        <div class="par-search-wrap">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input id="parSearch" type="text" class="par-filter-input" placeholder="Search by name, serial number, or unit...">
        </div>
      </div>
      <div>
        <label class="par-filter-label">Unit / Battalion</label>
        <select id="parUnitFilter" class="par-filter-input"><option value="">All Units</option></select>
      </div>
      <div>
        <label class="par-filter-label">Status</label>
        <select id="parStatusFilter" class="par-filter-input">
          <option value="ready" selected>Ready for PAR</option>
          <option value="returned">Returned / Replaced</option>
          <option value="">All Statuses</option>
        </select>
      </div>
      <div>
        <label class="par-filter-label">Sort By</label>
        <select id="parSort" class="par-filter-input">
          <option value="date-desc" selected>Date Approved (Newest)</option>
          <option value="date-asc">Date Approved (Oldest)</option>
          <option value="name-asc">Name (A–Z)</option>
          <option value="name-desc">Name (Z–A)</option>
        </select>
      </div>
    </div>


    <div class="par-table-card mb-6">
      <div class="px-5 pt-4 pb-2">
        <span style="color:#d4a017;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;" id="par-table-heading">Personnel Ready for PAR</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left force-light-text" style="border-collapse:collapse;">
          <thead>
            <tr class="border-b border-[#252b32] text-[#b0bac7]">
              <th class="py-2 px-3 font-semibold">#</th>
              <th class="py-2 px-3 font-semibold">Personnel Information</th>
              <th class="py-2 px-3 font-semibold">AFP Serial No.</th>
              <th class="py-2 px-3 font-semibold">Unit / Battalion</th>
              <th class="py-2 px-3 font-semibold">Date Approved</th>
              <th class="py-2 px-3 font-semibold">PAR Status</th>
              <th class="py-2 px-3 font-semibold text-right">Action</th>
            </tr>
          </thead>
          <tbody id="par-tbody"></tbody>
        </table>
      </div>
      <div class="flex items-center justify-between px-5 py-3 flex-wrap gap-2" style="border-top:1px solid #1e2530;">
        <span id="par-tbl-info" class="text-xs text-[#64748b]">Showing 0 of 0 entries</span>
        <div id="par-tbl-pages" class="flex gap-1"></div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
      <div class="par-info-box" style="background:#0a1f2d;border:1px solid #1a3a4f;border-radius:10px;padding:16px 18px;">
        <div style="display:flex;align-items:center;gap:8px;color:#3ec6ff;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;">
          <svg width="16" height="16" fill="none" stroke="#3ec6ff" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
          Process Flow Reminder
        </div>
        <div style="display:flex;align-items:flex-start;gap:6px;flex-wrap:wrap;margin-top:14px;">
          <div style="flex:1;min-width:140px;"><div style="width:26px;height:26px;border-radius:50%;background:#0d2d4a;color:#3ec6ff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;margin-bottom:7px;">1</div><p style="color:#e5eaf2;font-size:.78rem;font-weight:700;margin:0 0 3px;">Select Personnel</p><p style="color:#64748b;font-size:.7rem;margin:0;line-height:1.4;">Choose a personnel from the list</p></div>
          <div style="color:#2e4a5f;font-size:1.2rem;padding-top:4px;">→</div>
          <div style="flex:1;min-width:140px;"><div style="width:26px;height:26px;border-radius:50%;background:#0d2d4a;color:#3ec6ff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;margin-bottom:7px;">2</div><p style="color:#e5eaf2;font-size:.78rem;font-weight:700;margin:0 0 3px;">Process PAR</p><p style="color:#64748b;font-size:.7rem;margin:0;line-height:1.4;">Review information and generate PAR</p></div>
          <div style="color:#2e4a5f;font-size:1.2rem;padding-top:4px;">→</div>
          <div style="flex:1;min-width:140px;"><div style="width:26px;height:26px;border-radius:50%;background:#0d2d4a;color:#3ec6ff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;margin-bottom:7px;">3</div><p style="color:#e5eaf2;font-size:.78rem;font-weight:700;margin:0 0 3px;">Print PAR</p><p style="color:#64748b;font-size:.7rem;margin:0;line-height:1.4;">Print and obtain signatures from responsible personnel</p></div>
          <div style="color:#2e4a5f;font-size:1.2rem;padding-top:4px;">→</div>
          <div style="flex:1;min-width:140px;"><div style="width:26px;height:26px;border-radius:50%;background:#0d2d4a;color:#3ec6ff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;margin-bottom:7px;">4</div><p style="color:#e5eaf2;font-size:.78rem;font-weight:700;margin:0 0 3px;">Proceed to ICS</p><p style="color:#64748b;font-size:.7rem;margin:0;line-height:1.4;">After PAR issuance, proceed to ICS processing</p></div>
        </div>
      </div>
      <div style="background:#241c06;border:1px solid #5c4a1a;border-radius:10px;padding:16px 18px;">
        <div style="display:flex;align-items:center;gap:8px;color:#f0b90b;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;">
          <svg width="16" height="16" fill="none" stroke="#f0b90b" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18h6M10 22h4M12 2a6 6 0 00-4 10.5c.6.6 1 1.5 1 2.5h6c0-1 .4-1.9 1-2.5A6 6 0 0012 2z"/></svg>
          Tips
        </div>
        <ul style="margin:12px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:8px;">
          <li style="display:flex;gap:8px;font-size:.75rem;color:#f6d789;line-height:1.4;"><span style="color:#f0b90b;font-weight:800;">•</span>Make sure the inspection status is <strong>APPROVED</strong>.</li>
          <li style="display:flex;gap:8px;font-size:.75rem;color:#f6d789;line-height:1.4;"><span style="color:#f0b90b;font-weight:800;">•</span>Verify all information before generating the PAR.</li>
          <li style="display:flex;gap:8px;font-size:.75rem;color:#f6d789;line-height:1.4;"><span style="color:#f0b90b;font-weight:800;">•</span>PAR must be signed by the soldier and issuing officer.</li>
        </ul>
      </div>
    </div>
  </div>


  <div id="par-mgmt-list" style="display:none;">
    <button type="button" class="par-crumb" onclick="parShowHub()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      PAR Home
    </button>

    <div class="par-mgmt-header mb-5">
      <h1 class="force-light-text">PAR Management</h1>
      <p>Manage Property Acknowledgement Receipts (PAR) for issued firearms and equipment.</p>
    </div>

    <div class="par-table-card mb-4">
      <div class="par-mgmt-filterbar">
        <div class="par-search-wrap" style="flex:1;min-width:200px;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input id="parMgmtSearch" type="text" class="par-filter-input" placeholder="Search by name or AFP Serial...">
        </div>
        <select id="parMgmtUnitFilter" class="par-filter-input" style="max-width:170px;"><option value="">All Unit</option></select>
        <select id="parMgmtStatusFilter" class="par-filter-input" style="max-width:170px;">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="replaced">Replaced</option>
        </select>
        <button type="button" class="par-filter-btn" onclick="mgmtPage=1;parRenderMgmtTable();">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
          Filter
        </button>
        <button type="button" class="par-reset-btn" onclick="parMgmtResetFilters()">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 005.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 013.51 15"/></svg>
          Reset
        </button>
      </div>
    </div>

    <div class="flex flex-col xl:flex-row gap-5 items-start mb-6">

      <div class="par-table-card" style="flex:1;min-width:0;width:100%;">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left force-light-text" style="border-collapse:collapse;">
            <thead>
              <tr class="border-b border-[#252b32] text-[#b0bac7]">
                <th class="py-2 px-3 font-semibold">PAR No.</th>
                <th class="py-2 px-3 font-semibold">Personnel</th>
                <th class="py-2 px-3 font-semibold">AFP Serial No.</th>
                <th class="py-2 px-3 font-semibold">Unit</th>
                <th class="py-2 px-3 font-semibold">Firearm</th>
                <th class="py-2 px-3 font-semibold">Last Updated</th>
                <th class="py-2 px-3 font-semibold text-center" style="width:56px;">Actions</th>
              </tr>
            </thead>
            <tbody id="par-mgmt-tbody"></tbody>
          </table>
        </div>
        <div class="flex items-center justify-between px-4 py-3 flex-wrap gap-2" style="border-top:1px solid #1e2530;">
          <span id="par-mgmt-tbl-info" class="text-xs text-[#64748b]">Showing 0 of 0 entries</span>
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <div id="par-mgmt-tbl-pages" class="flex gap-1"></div>
            <select id="parMgmtPageSize" class="par-filter-input" style="width:auto;padding:5px 8px;">
              <option value="10" selected>10 / page</option>
              <option value="25">25 / page</option>
              <option value="50">50 / page</option>
            </select>
          </div>
        </div>
      </div>


      <div class="par-summary-panel">
        <div class="par-summary-head">
          <svg width="15" height="15" fill="none" stroke="#d4a017" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Selected PAR Summary
        </div>
        <div id="par-summary-body">
          <p style="color:#4b5563;font-size:.78rem;padding:18px 0;">Select a record from the list to view its details.</p>
        </div>
      </div>
    </div>

    <div class="par-table-card mb-8">
      <div class="px-5 pt-4 pb-2">
        <span style="color:#e5eaf2;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;">Activity Log</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left force-light-text" style="border-collapse:collapse;">
          <thead>
            <tr class="border-b border-[#252b32] text-[#b0bac7]">
              <th class="py-2 px-3 font-semibold">Date &amp; Time</th>
              <th class="py-2 px-3 font-semibold">Personnel</th>
              <th class="py-2 px-3 font-semibold">Action</th>
              <th class="py-2 px-3 font-semibold">PAR Number</th>
              <th class="py-2 px-3 font-semibold">By</th>
            </tr>
          </thead>
          <tbody id="par-mgmt-activity-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>



  <div id="par-replace-view" style="display:none;">
    <button type="button" class="par-crumb" onclick="parBackFromReplace()">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      Back to PAR Management
    </button>

    <div class="mb-5">
      <h1 class="text-xl font-bold force-light-text tracking-tight">Replace / Reissue PAR</h1>
      <p class="text-xs text-[#64748b] mt-1">Create a new Property Acknowledgement Receipt that replaces the currently active PAR while preserving the previous PAR reference.</p>
    </div>

    <div class="par-replace-banner">
      <h3>Replacement Process</h3>
      <p>The existing PAR will be retained as the previous record. A new PAR number will be generated and linked to the old PAR together with the replacement reason.</p>
    </div>

    <div class="par-replace-grid">
      <section class="par-replace-card">
        <h3>1. Current PAR Information</h3>
        <div class="par-doc-field-grid">
          <div><label class="reg-label">Current PAR Number</label><div id="replace_oldParNumber" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">Original Date Issued</label><div id="replace_oldDateIssued" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">Personnel Name</label><div id="replace_personnelName" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">AFP Serial Number</label><div id="replace_afpSerial" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">Rank</label><div id="replace_rank" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">Unit / Organization</label><div id="replace_unit" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">Current Firearm</label><div id="replace_firearm" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">Firearm Serial Number</label><div id="replace_firearmSerial" class="par-replace-readonly">—</div></div>
        </div>
      </section>

      <section class="par-replace-card">
        <h3>2. Reason for Replacement</h3>
        <label class="reg-label">Replacement Reason <span style="color:#ef4444;">*</span></label>
        <select id="replace_reason" class="reg-input">
          <option value="">Select reason</option>
          <option value="Lost PAR">Lost PAR</option>
          <option value="Damaged PAR">Damaged PAR</option>
          <option value="Change / Reassignment of Firearm">Change / Reassignment of Firearm</option>
          <option value="Correction of PAR Information">Correction of PAR Information</option>
          <option value="Renewal / Reissuance">Renewal / Reissuance</option>
          <option value="Other">Other</option>
        </select>

        <div id="replace_otherReasonWrap" style="display:none;margin-top:10px;">
          <label class="reg-label">Specify Other Reason <span style="color:#ef4444;">*</span></label>
          <input id="replace_otherReason" class="reg-input" type="text" maxlength="200" placeholder="Enter replacement reason">
        </div>

        <div class="par-replace-note">
          The old PAR will not be deleted. It will remain referenced as the previous PAR for audit/history purposes.
        </div>
      </section>

      <section class="par-replace-card">
        <h3>3. Replacement PAR Information</h3>
        <div class="par-doc-field-grid">
          <div><label class="reg-label">New PAR Number</label><div id="replace_newParNumber" class="par-replace-readonly">Auto-generated</div></div>
          <div><label class="reg-label">Date Issued <span style="color:#ef4444;">*</span></label><input id="replace_dateIssued" type="date" class="reg-input"></div>
          <div><label class="reg-label">Issued By <span style="color:#ef4444;">*</span></label><input id="replace_issuedBy" type="text" class="reg-input" placeholder="Enter complete name"></div>
          <div><label class="reg-label">Approved By <span style="color:#ef4444;">*</span></label><input id="replace_approvedBy" type="text" class="reg-input" placeholder="Enter complete name"></div>
        </div>
        <div style="margin-top:12px;">
          <label class="reg-label">Remarks</label>
          <textarea id="replace_remarks" class="reg-input" rows="3" maxlength="500" placeholder="Optional notes about this replacement"></textarea>
        </div>
      </section>

      <section class="par-replace-card">
        <h3>4. Assigned Property</h3>
        <div class="par-doc-field-grid">
          <div><label class="reg-label">Firearm</label><div id="replace_propertyFirearm" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">Firearm Serial Number</label><div id="replace_propertySerial" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">Ammunition Quantity</label><div id="replace_ammoQty" class="par-replace-readonly">—</div></div>
          <div><label class="reg-label">Equipment Package</label><div id="replace_package" class="par-replace-readonly">—</div></div>
        </div>
      </section>

      <section class="par-replace-card">
        <h3>5. Digital Signatures</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="par-sig-col">
            <h5>Issued By</h5>
            <div class="par-sig-box" id="replace_sigIssuedBox" onclick="parPickReplaceSig('Issued')">
              <img id="replace_sigIssued" src="" style="display:none;">
              <span class="empty" id="replace_sigIssuedEmpty">Click to upload</span>
            </div>
            <input type="file" id="replace_sigIssuedInput" accept="image/*" class="hidden">
            <button type="button" class="par-sig-clear" onclick="parClearReplaceSig('Issued')">Clear</button>
          </div>
          <div class="par-sig-col">
            <h5>Approved By</h5>
            <div class="par-sig-box" id="replace_sigApprovedBox" onclick="parPickReplaceSig('Approved')">
              <img id="replace_sigApproved" src="" style="display:none;">
              <span class="empty" id="replace_sigApprovedEmpty">Click to upload</span>
            </div>
            <input type="file" id="replace_sigApprovedInput" accept="image/*" class="hidden">
            <button type="button" class="par-sig-clear" onclick="parClearReplaceSig('Approved')">Clear</button>
          </div>
        </div>
      </section>

      <section class="par-replace-card">
        <h3>6. Replacement Summary</h3>
        <div class="par-summary-row"><div class="lbl">Previous PAR</div><div class="val" id="replace_summaryOld">—</div></div>
        <div class="par-summary-row"><div class="lbl">New PAR</div><div class="val" id="replace_summaryNew">—</div></div>
        <div class="par-summary-row"><div class="lbl">Reason</div><div class="val" id="replace_summaryReason">Not selected</div></div>
        <div class="par-summary-row"><div class="lbl">Personnel</div><div class="val" id="replace_summaryPersonnel">—</div></div>
      </section>
    </div>

    <div id="replace_err" style="color:#fc8181;font-size:.8rem;margin-top:14px;display:none;"></div>

    <div class="par-replace-actions">
      <button type="button" class="par-ghost-btn" onclick="parBackFromReplace()">Cancel</button>
      <button type="button" id="replace_confirmBtn" class="par-replace-confirm" onclick="parConfirmReplacement()">
        Confirm Replacement
      </button>
    </div>
  </div>


  <div id="par-doc-view" style="display:none;">
    <div class="flex flex-wrap items-center justify-between mb-5 gap-3">
      <div class="flex items-center gap-3">
        <button onclick="parBackFromDoc()" style="display:inline-flex;align-items:center;gap:6px;background:#1a2025;color:#94a3b8;border:1px solid #2e3748;border-radius:7px;padding:6px 14px;font-size:.76rem;font-weight:600;cursor:pointer;">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
          Back
        </button>
        <div>
          <h2 class="font-bold force-light-text" id="par_docTitle" style="font-size:1.05rem;">Process Property Acknowledgement Receipt (PAR)</h2>
          <p class="text-xs text-[#64748b] mt-0.5" id="par_docSubtitle">Review the automatically assigned equipment package and complete the necessary information before generating the PAR.</p>
        </div>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-5 items-start">


      <div style="flex:1;min-width:0;width:100%;">
        <div class="par-doc-grid mb-4">


          <div class="par-process-card">
            <h3 class="par-process-title" style="display:flex;align-items:center;gap:8px;"><span style="width:22px;height:22px;border-radius:50%;background:#0a1f3a;color:#3ec6ff;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;">1</span>Personnel Information</h3>
            <div class="par-doc-field-grid">
              <div><label class="reg-label">Name</label><div class="par-readonly-field" id="par_name">—</div></div>
              <div><label class="reg-label">AFP Serial Number</label><div class="par-readonly-field" id="par_serial">—</div></div>
              <div><label class="reg-label">Rank</label><div class="par-readonly-field" id="par_rank">—</div></div>
              <div><label class="reg-label">Unit / Organization</label><div class="par-readonly-field" id="par_unit">—</div></div>
              <div><label class="reg-label">Assigned Firearm</label><div class="par-readonly-field" id="par_firearm">—</div></div>
              <div><label class="reg-label">Date of Birth</label><div class="par-readonly-field" id="par_dob">—</div></div>
              <div><label class="reg-label">Civil Status</label><div class="par-readonly-field" id="par_civil">—</div></div>
              <div><label class="reg-label">Citizenship</label><div class="par-readonly-field" id="par_citizenship">Filipino</div></div>
            </div>
          </div>


          <div class="par-process-card">
            <h3 class="par-process-title" style="display:flex;align-items:center;gap:8px;"><span style="width:22px;height:22px;border-radius:50%;background:#0a1f3a;color:#3ec6ff;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;">2</span>PAR Information</h3>
            <div class="par-doc-field-grid">
              <div><label class="reg-label">PAR Number</label><div class="par-readonly-field" id="par_number">Auto-generated</div></div>
              <div><label class="reg-label">Date Issued <span style="color:#ef4444;">*</span></label><input id="par_dateIssued" type="date" class="reg-input"></div>
              <div style="grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div><label class="reg-label">Issued By <span style="color:#ef4444;">*</span></label><input id="par_issuedBy" type="text" class="reg-input" value="MS ROSEMARIE O VILBAR" placeholder="Enter complete name"></div>
                <div><label class="reg-label">Approved By <span style="color:#ef4444;">*</span></label><input id="par_approvedBy" type="text" class="reg-input" value="MS EVANGELINE M SINGUEO, Ph.D." placeholder="Enter complete name"></div>
              </div>
            </div>
          </div>


          <div class="par-process-card">
            <h3 class="par-process-title" style="display:flex;align-items:center;gap:8px;"><span style="width:22px;height:22px;border-radius:50%;background:#0a1f3a;color:#3ec6ff;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;">3</span>Assigned Equipment Package</h3>
            <label class="reg-label">Equipment Package</label>
            <div class="par-readonly-field" id="par_packageName" style="margin-bottom:8px;">—</div>
            <div class="par-equip-note">
              <svg width="13" height="13" fill="none" stroke="#33b481" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Automatically selected based on Unit, Rank, and Firearm
            </div>
            <label class="reg-label" style="margin-bottom:2px;">Included Equipment (Read-only)</label>
            <div id="par_equipmentList" style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px;margin-top:4px;"></div>
            <div class="par-equip-hint">
              <svg width="13" height="13" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
              Equipment package is automatically assigned based on Unit, Rank, and Firearm. The items listed above are standard and not editable.
            </div>
          </div>


          <div class="par-process-card">
            <h3 class="par-process-title" style="display:flex;align-items:center;gap:8px;"><span style="width:22px;height:22px;border-radius:50%;background:#0a1f3a;color:#3ec6ff;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;">4</span>Cost Summary</h3>
            <div class="par-cost-row"><span>Unit Cost</span><strong id="par_costUnit">₱0.00</strong></div>
            <div class="par-cost-row"><span id="par_costAmmoLabel">Ammo Cost</span><strong id="par_costAmmo">₱0.00</strong></div>
            <div class="par-cost-row"><span>TOTAL</span><strong id="par_costTotal">₱0.00</strong></div>
            <div class="par-cost-row"><span>Less: Withholding Tax (1.76%)</span><strong id="par_costTax">₱0.00</strong></div>
            <div class="par-net-total"><span>NET TOTAL</span><strong id="par_costNet">₱0.00</strong></div>
          </div>
        </div>

        <div class="par-process-card mb-4">
          <h3 class="par-process-title">Remarks (Optional)</h3>
          <textarea id="par_remarks" class="reg-input" rows="3" maxlength="500" placeholder="Enter remarks or additional notes here..." style="resize:vertical;" oninput="document.getElementById('par_remarksCount').textContent=this.value.length"></textarea>
          <p style="font-size:.68rem;color:#4b5563;margin-top:4px;text-align:right;"><span id="par_remarksCount">0</span> / 500 characters</p>
        </div>

        <div class="par-process-card mb-4">
          <h3 class="par-process-title">Digital Signatures</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="par-sig-col">
              <h5>Received By (Personnel)</h5>
              <div class="par-sig-box disabled"><img id="par_sigReceived" src="" style="display:none;"><span class="empty" id="par_sigReceivedEmpty">No signature on file</span></div>
              <p class="par-sig-name" id="par_sigReceivedName">—</p>
            </div>
            <div class="par-sig-col">
              <h5>Issued By</h5>
              <div class="par-sig-box" id="par_sigIssuedBox" onclick="parPickSig('Issued')"><img id="par_sigIssued" src="" style="display:none;"><span class="empty" id="par_sigIssuedEmpty">Click to upload</span></div>
              <input type="file" id="par_sigIssuedInput" accept="image/*" class="hidden">
              <button type="button" class="par-sig-clear" id="par_sigIssuedClear" onclick="parClearSig('Issued')">Clear</button>
            </div>
            <div class="par-sig-col">
              <h5>Approved By</h5>
              <div class="par-sig-box" id="par_sigApprovedBox" onclick="parPickSig('Approved')"><img id="par_sigApproved" src="" style="display:none;"><span class="empty" id="par_sigApprovedEmpty">Click to upload</span></div>
              <input type="file" id="par_sigApprovedInput" accept="image/*" class="hidden">
              <button type="button" class="par-sig-clear" id="par_sigApprovedClear" onclick="parClearSig('Approved')">Clear</button>
            </div>
          </div>
        </div>

        <div id="par_err" style="color:#fc8181;font-size:.8rem;margin-bottom:10px;display:none;"></div>
        <div class="flex justify-between items-center">
          <button type="button" onclick="parBackFromDoc()" id="par_cancelBtn" style="background:transparent;border:1px solid #2a2d35;color:#64748b;border-radius:8px;padding:10px 22px;font-size:.85rem;font-weight:600;cursor:pointer;">Cancel</button>
          <button type="button" id="par_confirmBtn" onclick="parConfirmSend()" style="background:#1a9e4d;color:#fff;border:none;border-radius:8px;padding:10px 22px;font-size:.85rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:7px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            <span id="par_confirmBtnLabel">Confirm</span>
          </button>
        </div>
      </div>


      <div style="width:100%;max-width:420px;flex-shrink:0;">
        <div class="par-preview-card">
          <div class="par-preview-head">
            <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            PAR PREVIEW
          </div>
          <p class="par-preview-sub">This is how the PAR will appear when printed.</p>

          <div class="par-preview-scale-outer">
          <div class="par-preview-scale">
          <section class="par-receipt" id="par-print-area" style="border:0;">
            <div class="par-motto">ARMY 2040: WORLD CLASS, MULTI-MISSION READY, CROSS-DOMAIN CAPABLE.</div>
            <header>
              <h1>PROPERTY ACKNOWLEDGEMENT RECEIPT</h1>
              <p>PHILIPPINE ARMY<br>(Agency)</p>
            </header>

            <p class="par-number"><strong>PAR No.:</strong> <span id="pp_parNo">—</span></p>

            <table class="par-items">
              <thead>
                <tr>
                  <th>Quantity</th>
                  <th>Unit</th>
                  <th>Description</th>
                  <th>SERIAL NUMBER</th>
                  <th>UNIT COST</th>
                </tr>
              </thead>
              <tbody>
                <tr class="main-item">
                  <td id="pp_qty">1</td>
                  <td>ea</td>
                  <td class="description" id="pp_desc">—</td>
                  <td id="pp_serial2">—</td>
                  <td id="pp_unitCost">₱0.00</td>
                </tr>
                <tr>
                  <td id="pp_ammoQty">0</td>
                  <td>rds</td>
                  <td class="description" id="pp_ammoDesc">—</td>
                  <td>—</td>
                  <td id="pp_ammoCost">₱0.00</td>
                </tr>
                <tr class="total"><td colspan="4">TOTAL</td><td id="pp_total">₱0.00</td></tr>
                <tr class="total"><td colspan="4">LESS: Withholding Tax (1.76%)</td><td id="pp_tax">₱0.00</td></tr>
                <tr class="total"><td colspan="4">NET TOTAL</td><td id="pp_net">₱0.00</td></tr>
              </tbody>
            </table>

            <table class="par-details">
              <tr>
                <td>
                  <p><strong>Make:</strong> <span id="pp_make">—</span></p>
                  <p><strong>Model:</strong> <span id="pp_model">—</span></p>
                  <p><strong>Serial Number:</strong> <span id="pp_serial3">—</span></p>
                </td>
                <td class="approval">
                  <strong>APPROVED:</strong>
                  <div class="signature-space"><img id="pp_sigApproved" src="" style="display:none;"></div>
                  <b id="pp_approvedName">APPROVING AUTHORITY</b>
                  <small>Chief APAO, PA</small>
                  <p>Date Approved: <span id="pp_dateApproved">—</span></p>
                </td>
              </tr>
            </table>

            <table class="par-signatures">
              <tr>
                <td>
                  <h3>RECEIVED BY</h3>
                  <p>SIGNATURE: <span class="signature-line"><img id="pp_sigReceived" src="" alt="Personnel digital signature" style="display:none;max-height:34px;max-width:150px;object-fit:contain;"></span></p>
                  <b id="pp_receivedName">—</b>
                  <small>(RANK) (NAME) (MI) (LNAME) (AFPSN) (BR of SVC)</small>
                  <p>Unit Assignment: <strong id="pp_unit2">—</strong></p>
                  <p>Date of Birth: <strong id="pp_dob2">—</strong></p>
                  <p>Valid up to: <strong id="pp_validUntil">—</strong></p>
                </td>
                <td>
                  <h3>ISSUED BY</h3>
                  <div class="signature-space"><img id="pp_sigIssued" src="" style="display:none;"></div>
                  <b id="pp_issuedName">ISSUING OFFICER</b>
                  <small>Signature Over Printed Name</small>
                  <p class="office"><strong>Chief, PAOGS, APAO PA</strong><br>Position/Office</p>
                  <p>Date Issued: <strong id="pp_dateIssued2">—</strong></p>
                </td>
              </tr>
            </table>

            <p id="pp_replacementNote" class="replacement-note" style="display:none;"></p>

            <footer>
              <div class="footer-badges">
                <img src="<?php echo e(asset('images/footer/pgs.png')); ?>" alt="PGS" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';"><span class="badge-fallback" style="display:none;">PGS</span>
                <img src="<?php echo e(asset('images/footer/seal.png')); ?>" alt="" onerror="this.style.display='none';">
                <img src="<?php echo e(asset('images/footer/ac.png')); ?>" alt="AC" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';"><span class="badge-fallback" style="display:none;">AC</span>
              </div>
              <strong>HONOR.PATRIOTISM. DUTY.</strong>
              <div class="footer-badges footer-badges-right">
                <img src="<?php echo e(asset('images/footer/atr.png')); ?>" alt="atr" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';"><span class="badge-fallback" style="display:none;">atr</span>
                <span>ISO 9001:2015<br>CERTIFIED</span>
              </div>
            </footer>
          </section>
          </div>
          </div>

          <div class="par-preview-actions">
            <button type="button" onclick="parFullscreenPreview()">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/></svg>
              Preview
            </button>
            <button type="button" onclick="parPrintPAR()">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
              Print PAR
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>

<script>
(function initPAR() {
  var PAR_DATA_URL   = "<?php echo e(route('staff.dashboard.data')); ?>";
  var STATE_KEY       = "staff_par_state_v1";     // TODO: replace with real backend persistence
  var ACTIVITY_KEY    = "staff_par_activity_v1";  // TODO: replace with real backend persistence
  var CURRENT_USER    = "<?php echo e($user->name ?? 'Staff User'); ?>";
  var AMMO_UNIT_COST  = 22.00;
  var TAX_RATE        = 0.0176;

  var parAll   = [];        // all personnel fetched from server (enriched with PAR state)
  var parPage  = 1;
  var mgmtPage = 1;
  var PAR_PER_PAGE = 10;
  var parCurrentItem  = null;  // record currently open in the shared doc view
  var parDocMode      = 'issue'; // 'issue' | 'view' | 'update' | 'replace'
  var parDocReturnTo  = 'issuance'; // 'issuance' | 'management'
  var PAR_DEFAULT_ISSUED_SIGNATURE = <?php echo json_encode(asset('images/ROSEMARIE VILBAR.png'), 15, 512) ?>;
  var PAR_DEFAULT_APPROVED_SIGNATURE = <?php echo json_encode(asset('images/SINGUEO EVAGELINE.png'), 15, 512) ?>;
  var parSigIssuedBase64 = PAR_DEFAULT_ISSUED_SIGNATURE;
  var parSigApprovedBase64 = PAR_DEFAULT_APPROVED_SIGNATURE;

  var EQUIPMENT_PACKAGES = {
    default: {
      name: "Glock 17 Standard Issue",
      unitCost: 35000.00,
      items: ["4 pcs Back Straps","4 pcs Magazine (17 rds Cap)","1 set Cleaning Kit","1 pc Speed Loader",
              "1 pc Gun Case","1 pc Holster w/ Hanger","1 pc Magazine Pouch (3 mag capacity)","1 pc User's Manual"]
    }
  };

  // ── STATE / ACTIVITY PERSISTENCE (placeholder — swap for real API) ──
  var parServerState = {};
  function loadState() { return parServerState; }
  function saveState(state) { parServerState = state; }
  function getOverride(itemNumber) { return loadState()[itemNumber] || {}; }
  function setOverride(itemNumber, patch) {
    var state = loadState();
    state[itemNumber] = Object.assign({}, state[itemNumber] || {}, patch);
    saveState(state);
  }
  var parServerActivity = [];
  function loadActivity() { return parServerActivity; }
  function saveActivity(list) { parServerActivity = list.slice(0,200); }
  function logActivity(personnelName, action, parNumber) {
    if (action === 'PAR Reprinted' && parCurrentItem) {
      fetch('/staff/par/' + encodeURIComponent(parCurrentItem.itemNumber) + '/reprint', {
        method:'POST',headers:{'Accept':'application/json',
          'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}
      }).catch(function () {});
    }
    var list = loadActivity();
    list.unshift({ ts: new Date().toISOString(), personnel: personnelName, action: action, parNumber: parNumber || '—', by: CURRENT_USER });
    saveActivity(list);
  }

  function fmtMoney(n) {
    n = Number(n) || 0;
    return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function fmtDateShort(dateStr) {
    if (!dateStr) return '—';
    var d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
  }
  function fmtDateTime(dateStr) {
    if (!dateStr) return '—';
    var d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ', ' +
           d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
  }
  function setText(id, v) { var el = document.getElementById(id); if (el) el.textContent = v; }

  // Normalizes the personnel digital signature returned by staff.dashboard.data.
  // New Registration stores the canvas as a data:image/png;base64 URL, but this
  // also supports records that contain only the raw base64 payload.
  function parSignatureSrc(signature) {
    if (!signature) return null;

    var value = String(signature).trim().replace(/^['"]|['"]$/g, '');
    if (!value) return null;

    // Already a complete browser-readable source.
    if (
      /^data:image\//i.test(value) ||
      /^https?:\/\//i.test(value) ||
      value.startsWith('/')
    ) {
      return value;
    }

    if (/^(storage|images)\//i.test(value)) return '/' + value;

    // Raw base64 saved in the database.
    return 'data:image/png;base64,' + value.replace(/\s/g, '');
  }

  function parSetSignatureImage(imageId, signature) {
    var image = document.getElementById(imageId);
    if (!image) return;

    var src = parSignatureSrc(signature);

    if (src) {
      image.src = src;
      image.style.display = 'block';
    } else {
      image.removeAttribute('src');
      image.style.display = 'none';
    }
  }

  function parSeedNumber(itemNumber) {
    // Deterministic placeholder PAR number for personnel who, per their records
    // approvedStatus (i.e. they are "renewed"/"within"/"expired", not brand-new),
    // are assumed to already hold a PAR even though this demo has no historical
    // PAR table to read from yet.
    // TODO: replace with the real PAR number from the backend once a par_records
    // table / endpoint exists — this seeding step goes away entirely then.
    var y = new Date().getFullYear();
    return 'PAR-' + y + '-' + String(itemNumber).padStart(3, '0');
  }

  function parEnrich(list) {
    return list.map(function (p) {
      var ov = getOverride(p.itemNumber);

      // A personnel record becomes eligible for its first PAR as soon as the
      // administrator approves the firearm inspection. Existing PARs stay in Management.
      var hasExistingPar = !!(ov.parNumber || p.parNumber);
      var inspectionApproved = p.inspectionStatus === 'approved' || p.icsStatus === 'ready';
      var awaitingRenewalPar = inspectionApproved && !hasExistingPar && ov.parStatus !== 'issued';
      var derivedStatus = awaitingRenewalPar ? 'ready' : (hasExistingPar ? 'issued' : 'ineligible');
      var seededNumber = ov.parNumber || p.parNumber || null;
      var seededDate = ov.dateIssued || p.dateIssued || null;

      return Object.assign({}, p, {
        parStatus: derivedStatus,
        parNumber: seededNumber,
        dateApproved: p.dateApproved || p.inspectionUpdatedAt || p.updated_at || null,
        dateIssued: seededDate,
        issuedBy: ov.issuedBy || p.issuedBy || null,
        approvedBy: ov.approvedBy || p.approvedBy || null,

        // Keep the uploaded officer signatures with the PAR state so
        // Update -> Summary -> Generate PDF does not lose them.
        issuedBySignature: ov.issuedBySignature || p.issuedBySignature || null,
        approvedBySignature: ov.approvedBySignature || p.approvedBySignature || null,

        wasReplaced: !!(ov.wasReplaced || p.wasReplaced),
        previousParNumber: ov.previousParNumber || p.previousParNumber || null,
        replacementReason: ov.replacementReason || p.replacementReason || null,
        replacementRemarks: ov.replacementRemarks || p.replacementRemarks || null
      });
    });
  }

  async function parFetchData() {
    try {
      var res = await fetch(PAR_DATA_URL, { headers: { 'Accept': 'application/json' } });
      var json = await res.json();
      var stateResponse = await fetch('/staff/par-data', { headers: { 'Accept': 'application/json' } });
      if (!stateResponse.ok) throw new Error('Unable to load PAR records.');
      var savedRecords = await stateResponse.json();
      parServerState = savedRecords.state || {};
      parServerActivity = savedRecords.activity || [];
      parAll = parEnrich(json.personnel || []);
    } catch (e) {
      parAll = [];
    }
    parPopulateUnits();
    parRenderHub();
    parRenderIssuanceTable();
    parRenderMgmtTable();
    parRenderMgmtActivity();
  }

  function parPopulateUnits() {
    [['parUnitFilter'], ['parMgmtUnitFilter']].forEach(function (arr) {
      var sel = document.getElementById(arr[0]);
      if (!sel) return;
      var current = sel.value;
      var units = Array.from(new Set(parAll.map(function (p) { return p.unit; }).filter(Boolean))).sort();
      sel.innerHTML = '<option value="">All Units</option>' + units.map(function (u) { return '<option value="' + u + '">' + u + '</option>'; }).join('');
      sel.value = current || '';
    });
  }

  // ── HUB ────────────────────────────────────────────────────────────
  function parRenderHub() {
    var readyCount = parAll.filter(function (p) { return p.parStatus === 'ready'; }).length;
    var existingCount = parAll.filter(function (p) { return !!p.parNumber; }).length;
    var now = new Date();
    var activity = loadActivity();
    var updatedThisMonth = activity.filter(function (a) {
      var d = new Date(a.ts);
      return (a.action === 'PAR Updated' || a.action === 'PAR Replaced') && d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
    }).length;
    var reprintedThisMonth = activity.filter(function (a) {
      var d = new Date(a.ts);
      return a.action === 'PAR Reprinted' && d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
    }).length;

    setText('par-hub-ready-count', readyCount);
    setText('par-hub-existing-count', existingCount);
    setText('par-sum-waiting', readyCount);
    setText('par-sum-existing', existingCount);
    setText('par-sum-updated', updatedThisMonth);
    setText('par-sum-reprinted', reprintedThisMonth);

    var tbody = document.getElementById('par-hub-activity-tbody');
    if (tbody) tbody.innerHTML = parActivityRows(activity.slice(0, 5));
  }

  function parActivityBadge(action) {
    if (action === 'PAR Issued') return '<span class="par-badge par-badge-issued">PAR Issued</span>';
    if (action === 'PAR Updated') return '<span class="par-badge par-badge-updated">PAR Updated</span>';
    if (action === 'PAR Reprinted') return '<span class="par-badge par-badge-reprinted">PAR Reprinted</span>';
    if (action === 'PAR Replaced') return '<span class="par-badge par-badge-replaced">PAR Replaced</span>';
    return '<span class="par-badge">' + action + '</span>';
  }
  function parActivityRows(list) {
    if (!list.length) return '<tr><td colspan="5" style="text-align:center;padding:22px;color:#4b5563;font-size:.78rem;">No activity yet.</td></tr>';
    return list.map(function (a) {
      return '<tr class="border-b border-[#1a2025]">'
        + '<td class="py-2 px-3 force-light-text">' + fmtDateTime(a.ts) + '</td>'
        + '<td class="py-2 px-3 force-light-text">' + (a.personnel || '—') + '</td>'
        + '<td class="py-2 px-3">' + parActivityBadge(a.action) + '</td>'
        + '<td class="py-2 px-3 force-light-text" style="font-family:monospace;">' + (a.parNumber || '—') + '</td>'
        + '<td class="py-2 px-3 force-light-text">' + (a.by || '—') + '</td>'
        + '</tr>';
    }).join('');
  }
  function parRenderMgmtActivity() {
    var tbody = document.getElementById('par-mgmt-activity-tbody');
    if (tbody) tbody.innerHTML = parActivityRows(loadActivity().slice(0, 25));
  }

  // ── NAVIGATION ────────────────────────────────────────────────────
  window.parShowHub = function () {
    var rv = document.getElementById('par-replace-view'); if (rv) rv.style.display = 'none';
    document.getElementById('par-hub-view').style.display = 'block';
    document.getElementById('par-issuance-list').style.display = 'none';
    document.getElementById('par-mgmt-list').style.display = 'none';
    document.getElementById('par-doc-view').style.display = 'none';
    parRenderHub();
    window.staffScrollToTop?.();
  };
  window.parShowIssuanceList = function () {
    var rv = document.getElementById('par-replace-view'); if (rv) rv.style.display = 'none';
    document.getElementById('par-hub-view').style.display = 'none';
    document.getElementById('par-mgmt-list').style.display = 'none';
    document.getElementById('par-doc-view').style.display = 'none';
    document.getElementById('par-issuance-list').style.display = 'block';
    parRenderIssuanceTable();
    window.staffScrollToTop?.();
  };
  window.parShowMgmtList = function () {
    var rv = document.getElementById('par-replace-view'); if (rv) rv.style.display = 'none';
    document.getElementById('par-hub-view').style.display = 'none';
    document.getElementById('par-issuance-list').style.display = 'none';
    document.getElementById('par-doc-view').style.display = 'none';
    document.getElementById('par-mgmt-list').style.display = 'block';
    parRenderMgmtTable();
    parRenderMgmtActivity();
    window.staffScrollToTop?.();
  };
  window.parBackFromDoc = function () {
    document.getElementById('par-doc-view').style.display = 'none';
    var rv = document.getElementById('par-replace-view'); if (rv) rv.style.display = 'none';
    if (parDocReturnTo === 'management') parShowMgmtList(); else parShowIssuanceList();
  };

  // ── ISSUANCE LIST ─────────────────────────────────────────────────
  function parStatusPill(status) {
    if (status === 'issued') return '<span class="par-status-pill par-status-issued"><span></span>PAR Issued</span>';
    if (status === 'returned') return '<span class="par-status-pill par-status-returned"><span></span>Returned / Replaced</span>';
    return '<span class="par-status-pill par-status-ready"><span></span>Ready for PAR</span>';
  }

  function parIssuanceFiltered() {
    var q = (document.getElementById('parSearch').value || '').trim().toLowerCase();
    var unitF = document.getElementById('parUnitFilter').value;
    var statusF = document.getElementById('parStatusFilter').value || 'ready';
    var sort = document.getElementById('parSort').value;

    var list = parAll.filter(function (p) {
      if (statusF && p.parStatus !== statusF) return false;
      if (!statusF && p.parStatus === 'issued') return false; // "All" still excludes already-issued (that's Management's job)
      if (unitF && p.unit !== unitF) return false;
      if (!q) return true;
      return [p.firstName, p.lastName, p.middleName, p.afpSerialNumber, p.unit].some(function (v) {
        return (v || '').toString().toLowerCase().includes(q);
      });
    });

    list.sort(function (a, b) {
      if (sort === 'name-asc' || sort === 'name-desc') {
        var an = (a.lastName || '').toLowerCase(), bn = (b.lastName || '').toLowerCase();
        return sort === 'name-asc' ? an.localeCompare(bn) : bn.localeCompare(an);
      }
      var ad = a.dateApproved ? new Date(a.dateApproved).getTime() : 0;
      var bd = b.dateApproved ? new Date(b.dateApproved).getTime() : 0;
      return sort === 'date-asc' ? ad - bd : bd - ad;
    });
    return list;
  }

  function parRenderIssuanceTable() {
    var filtered = parIssuanceFiltered();
    var total = filtered.length;
    var pages = Math.max(1, Math.ceil(total / PAR_PER_PAGE));
    if (parPage > pages) parPage = 1;
    var start = (parPage - 1) * PAR_PER_PAGE;
    var pageData = filtered.slice(start, start + PAR_PER_PAGE);
    var tbody = document.getElementById('par-tbody');
    var info = document.getElementById('par-tbl-info');
    var heading = document.getElementById('par-table-heading');
    var statusF = document.getElementById('parStatusFilter').value;
    if (heading) heading.textContent = statusF === 'returned' ? 'Returned / Replaced Personnel' : statusF === '' ? 'All Personnel Awaiting PAR' : 'Personnel Ready for PAR';

    if (!pageData.length) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:28px;color:#4b5563;font-size:.78rem;">No records found.</td></tr>';
      if (info) info.textContent = 'Showing 0 of 0 entries';
      parPagination('par-tbl-pages', 0, parPage, function (n) { parPage = n; parRenderIssuanceTable(); });
      return;
    }

    tbody.innerHTML = pageData.map(function (r, i) {
      var name = (r.firstName || '') + ' ' + (r.lastName || '');
      var photo = r.photo ? (/^(data:image\/|https?:\/\/|\/)/i.test(r.photo) ? r.photo : 'data:image/jpeg;base64,' + r.photo) : "<?php echo e(asset('images/logo.png')); ?>";
      return '<tr class="border-b border-[#1a2025] hover:bg-[#1a2025] transition-colors">'
        + '<td class="py-3 px-3 force-light-text">' + (start + i + 1) + '</td>'
        + '<td class="py-3 px-3">'
          + '<div style="display:flex;align-items:center;gap:10px;">'
            + '<img class="par-avatar" src="' + photo + '" onerror="this.style.visibility=\'hidden\'">'
            + '<div><div style="color:#e5eaf2;font-weight:700;font-size:.8rem;">' + name + '</div>'
            + '<div style="color:#64748b;font-size:.7rem;">Rank: ' + (r.rank || '—') + '</div></div>'
          + '</div>'
        + '</td>'
        + '<td class="py-3 px-3 force-light-text" style="font-family:monospace;">' + (r.afpSerialNumber || '—') + '</td>'
        + '<td class="py-3 px-3 force-light-text">' + (r.unit || '—') + '</td>'
        + '<td class="py-3 px-3 force-light-text">' + fmtDateShort(r.dateApproved) + '</td>'
        + '<td class="py-3 px-3">' + parStatusPill(r.parStatus) + '</td>'
        + '<td class="py-3 px-3 text-right">'
          + `<button class="par-process-btn" onclick="parOpenProcess(${JSON.stringify(r.itemNumber)}, 'issue')">Process PAR <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg></button>`
        + '</td>'
        + '</tr>';
    }).join('');

    if (info) info.textContent = 'Showing ' + (start + 1) + ' to ' + Math.min(start + PAR_PER_PAGE, total) + ' of ' + total + ' entries';
    parPagination('par-tbl-pages', pages, parPage, function (n) { parPage = n; parRenderIssuanceTable(); });
  }

  function parPagination(containerId, pages, current, onGo) {
    var c = document.getElementById(containerId);
    if (!c) return;
    if (pages <= 1) { c.innerHTML = ''; return; }
    var bs = 'border-radius:6px;padding:5px 10px;font-size:.72rem;font-weight:700;cursor:pointer;border:1px solid #363b48;';
    var dotStyle = 'padding:5px 4px;font-size:.72rem;color:#4b5563;';

    // Build a compact page list: first, last, current-1..current+1, with … gaps.
    var nums = [];
    var range = { start: Math.max(1, current - 1), end: Math.min(pages, current + 1) };
    nums.push(1);
    if (range.start > 2) nums.push('…');
    for (var i = range.start; i <= range.end; i++) if (i !== 1 && i !== pages) nums.push(i);
    if (range.end < pages - 1) nums.push('…');
    if (pages > 1) nums.push(pages);

    var html = '<button data-p="' + Math.max(1, current - 1) + '" style="' + bs + 'background:#1a2025;color:#94a3b8;">‹</button>';
    nums.forEach(function (n) {
      if (n === '…') { html += '<span style="' + dotStyle + '">…</span>'; return; }
      html += '<button data-p="' + n + '" style="' + bs + (n === current ? 'background:#1a2025;color:#e5eaf2;border-color:#3ec6ff;' : 'background:#1a2025;color:#94a3b8;') + '">' + n + '</button>';
    });
    html += '<button data-p="' + Math.min(pages, current + 1) + '" style="' + bs + 'background:#1a2025;color:#94a3b8;">›</button>';

    c.innerHTML = html;
    c.querySelectorAll('button').forEach(function (b) { b.addEventListener('click', function () { onGo(parseInt(this.dataset.p)); }); });
  }

  ['parSearch'].forEach(function (id) { document.getElementById(id)?.addEventListener('input', function () { parPage = 1; parRenderIssuanceTable(); }); });
  ['parUnitFilter', 'parStatusFilter', 'parSort'].forEach(function (id) { document.getElementById(id)?.addEventListener('change', function () { parPage = 1; parRenderIssuanceTable(); }); });

  // ── MANAGEMENT LIST ───────────────────────────────────────────────
  var mgmtPerPage = 10;
  var mgmtSelectedItem = null;

  function parMgmtFiltered() {
    var q = (document.getElementById('parMgmtSearch').value || '').trim().toLowerCase();
    var unitF = document.getElementById('parMgmtUnitFilter').value;
    var statusF = document.getElementById('parMgmtStatusFilter').value;

    var list = parAll.filter(function (p) {
      if (!p.parNumber) return false;
      if (unitF && p.unit !== unitF) return false;
      if (statusF === 'replaced' && !p.wasReplaced) return false;
      if (statusF === 'active' && p.wasReplaced) return false;
      if (!q) return true;
      return [p.firstName, p.lastName, p.middleName, p.afpSerialNumber, p.unit, p.parNumber].some(function (v) {
        return (v || '').toString().toLowerCase().includes(q);
      });
    });

    list.sort(function (a, b) {
      var ad = a.dateIssued ? new Date(a.dateIssued).getTime() : 0;
      var bd = b.dateIssued ? new Date(b.dateIssued).getTime() : 0;
      return bd - ad; // newest last-updated first
    });
    return list;
  }

  window.parMgmtResetFilters = function () {
    document.getElementById('parMgmtSearch').value = '';
    document.getElementById('parMgmtUnitFilter').value = '';
    document.getElementById('parMgmtStatusFilter').value = '';
    document.getElementById('parMgmtPageSize').value = '10';
    mgmtPerPage = 10;
    mgmtPage = 1;
    parRenderMgmtTable();
  };

  function parRenderMgmtTable() {
    var filtered = parMgmtFiltered();
    var total = filtered.length;
    var pages = Math.max(1, Math.ceil(total / mgmtPerPage));
    if (mgmtPage > pages) mgmtPage = 1;
    var start = (mgmtPage - 1) * mgmtPerPage;
    var pageData = filtered.slice(start, start + mgmtPerPage);
    var tbody = document.getElementById('par-mgmt-tbody');
    var info = document.getElementById('par-mgmt-tbl-info');

    if (!pageData.length) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:28px;color:#4b5563;font-size:.78rem;">No PAR records found.</td></tr>';
      if (info) info.textContent = 'Showing 0 of 0 entries';
      parPagination('par-mgmt-tbl-pages', 0, mgmtPage, function (n) { mgmtPage = n; parRenderMgmtTable(); });
      parRenderSummaryPanel(null);
      return;
    }

    // keep a selection: default to the first row of the current page if nothing (valid) is selected
    if (!mgmtSelectedItem || !pageData.some(function (r) { return r.itemNumber === mgmtSelectedItem; })) {
      mgmtSelectedItem = pageData[0].itemNumber;
    }

    tbody.innerHTML = pageData.map(function (r) {
      var name = (r.firstName || '') + ' ' + (r.lastName || '');
      var selected = (r.itemNumber === mgmtSelectedItem);
      return `<tr class="border-b border-[#1a2025]${selected ? ' par-row-selected' : ''}" onclick="parSelectMgmtRow(${JSON.stringify(r.itemNumber)})">`
        + `<td class="py-3 px-3 force-light-text" style="font-family:monospace;">
            <div style="font-weight:${r.wasReplaced ? '800' : '500'};">${r.parNumber || '—'}</div>
            ${r.wasReplaced && r.previousParNumber
              ? `<div style="font-family:inherit;font-size:.63rem;color:#a855f7;margin-top:3px;">Replacement of ${r.previousParNumber}</div>`
              : ''}
          </td>`
        + `<td class="py-3 px-3 force-light-text">${name}</td>`
        + `<td class="py-3 px-3 force-light-text" style="font-family:monospace;">${r.afpSerialNumber || '—'}</td>`
        + `<td class="py-3 px-3 force-light-text">${r.unit || '—'}</td>`
        + `<td class="py-3 px-3 force-light-text">${r.pistolNomenclature || '—'}</td>`
        + `<td class="py-3 px-3 force-light-text">${fmtDateTime(r.dateIssued)}</td>`
        + '<td class="py-3 px-3 text-center">'
          + '<div class="par-kebab-wrap" onclick="event.stopPropagation();">'
            + `<button class="par-kebab-btn" onclick="parToggleKebab(event, ${JSON.stringify(r.itemNumber)})"><svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg></button>`
            + `<div class="par-kebab-menu" id="par-kebab-${r.itemNumber}">`
              + `<div class="par-kebab-item" onclick="parViewReceipt(${JSON.stringify(r.itemNumber)})"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>View Receipt</div>`
              + `<div class="par-kebab-item" onclick="parOpenProcess(${JSON.stringify(r.itemNumber)}, 'update')"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4z"/></svg>Update PAR</div>`
              + `<div class="par-kebab-item" onclick="parOpenReplace(${JSON.stringify(r.itemNumber)})"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 005.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 013.51 15"/></svg>Replace PAR</div>`
              + `<div class="par-kebab-item" onclick="parReprint(${JSON.stringify(r.itemNumber)})"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>Reprint PAR</div>`
            + '</div>'
          + '</div>'
        + '</td>'
        + '</tr>';
    }).join('');

    if (info) info.textContent = 'Showing ' + (start + 1) + ' to ' + Math.min(start + mgmtPerPage, total) + ' of ' + total + ' entries';
    parPagination('par-mgmt-tbl-pages', pages, mgmtPage, function (n) { mgmtPage = n; parRenderMgmtTable(); });

    var selectedRecord = parAll.find(function (p) { return p.itemNumber === mgmtSelectedItem; });
    parRenderSummaryPanel(selectedRecord || null);
  }

  window.parSelectMgmtRow = function (itemNumber) {
    mgmtSelectedItem = itemNumber;
    parRenderMgmtTable();
  };

  function parRenderSummaryPanel(r) {
    var body = document.getElementById('par-summary-body');

    if (!r) {
      body.innerHTML = '<p style="color:#4b5563;font-size:.78rem;padding:18px 0;">Select a record from the list to view its details.</p>';
      return;
    }

    var name = (r.firstName || '') + ' ' + (r.lastName || '');

    function row(label, value, extraStyle) {
      return '<div class="par-summary-row">'
        + '<div class="lbl">' + label + '</div>'
        + '<div class="val"' + (extraStyle ? ' style="' + extraStyle + '"' : '') + '>'
        + (value || '—')
        + '</div>'
        + '</div>';
    }

    var isReplacement = !!(r.wasReplaced && r.previousParNumber);

    var statusHtml = isReplacement
      ? '<div class="par-summary-row">'
          + '<div class="lbl">PAR Status</div>'
          + '<div class="val">'
            + '<span style="display:inline-flex;align-items:center;gap:6px;background:#f3e8ff;color:#7e22ce;border-radius:999px;padding:5px 12px;font-size:.72rem;font-weight:800;">'
              + '<span style="width:7px;height:7px;border-radius:50%;background:#a855f7;"></span>'
              + 'Replacement · Active'
            + '</span>'
          + '</div>'
        + '</div>'
      : '<div class="par-summary-row">'
          + '<div class="lbl">PAR Status</div>'
          + '<div class="val"><span class="par-status-pill par-status-issued"><span></span>Active</span></div>'
        + '</div>';

    var replacementHtml = '';

    if (isReplacement) {
      replacementHtml =
          '<div style="margin:12px 0 4px;padding:12px 13px;border:1px solid #d8b4fe;background:#faf5ff;border-radius:9px;">'
            + '<div style="font-size:.68rem;font-weight:800;color:#7e22ce;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Replacement Information</div>'
            + '<div class="par-summary-row" style="margin-bottom:7px;">'
              + '<div class="lbl">Previous PAR Number</div>'
              + '<div class="val" style="color:#7e22ce;font-weight:800;">' + (r.previousParNumber || '—') + '</div>'
            + '</div>'
            + '<div class="par-summary-row" style="margin-bottom:7px;">'
              + '<div class="lbl">Replacement Reason</div>'
              + '<div class="val">' + (r.replacementReason || '—') + '</div>'
            + '</div>'
            + (r.replacementRemarks
                ? '<div class="par-summary-row">'
                    + '<div class="lbl">Replacement Remarks</div>'
                    + '<div class="val">' + r.replacementRemarks + '</div>'
                  + '</div>'
                : '')
          + '</div>';
    }

    body.innerHTML =
        row('Personnel Name', name)
      + row('AFP Serial Number', r.afpSerialNumber)
      + row('Unit / Organization', r.unit)
      + row('Assigned Firearm', r.pistolNomenclature)
      + row(isReplacement ? 'New / Current PAR Number' : 'Current PAR Number', r.parNumber)
      + statusHtml
      + replacementHtml
      + row('Last Updated', fmtDateTime(r.dateIssued))
      + row('Issued Date', fmtDateShort(r.dateIssued))
      + row('Issued By', r.issuedBy)
      + row('Approved By', r.approvedBy)
      + '<div class="par-summary-actions">'
        + `<button type="button" class="par-summary-btn par-summary-btn-outline" onclick="parQuickAction(${JSON.stringify(r.itemNumber)}, 'pdf')"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>Generate PDF</button>`
        + `<button type="button" class="par-summary-btn par-summary-btn-solid" onclick="parQuickAction(${JSON.stringify(r.itemNumber)}, 'print')"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>Print PAR</button>`
      + '</div>';
  }

  // Print / Generate PDF directly from the summary panel, without opening the full doc view.
  window.parQuickAction = function (itemNumber, kind) {
    var r = parAll.find(function (p) { return p.itemNumber === itemNumber; });
    if (!r) return;
    parCurrentItem = r;

    // Reuse the saved signatures for PDF/Print instead of clearing them.
    parSigIssuedBase64 = r.issuedBySignature || PAR_DEFAULT_ISSUED_SIGNATURE;
    parSigApprovedBase64 = r.approvedBySignature || PAR_DEFAULT_APPROVED_SIGNATURE;

    document.getElementById('par_number').textContent = r.parNumber || '—';
    document.getElementById('par_dateIssued').value = r.dateIssued ? r.dateIssued.slice(0, 10) : '';
    document.getElementById('par_issuedBy').value = r.issuedBy || 'MS ROSEMARIE O VILBAR';
    document.getElementById('par_approvedBy').value = r.approvedBy || 'MS EVANGELINE M SINGUEO, Ph.D.';
    var recvImg = document.getElementById('par_sigReceived');
    var receiverSignature = parSignatureSrc(r.signature);
    if (receiverSignature) {
      recvImg.src = receiverSignature;
      recvImg.style.display = 'block';
    }

    parApplySigInput('Issued', parSigIssuedBase64);
    parApplySigInput('Approved', parSigApprovedBase64);

    parRenderPreview();
    if (kind === 'print') parPrintPAR(); else parGeneratePDF();
  };

  ['parMgmtSearch'].forEach(function (id) { document.getElementById(id)?.addEventListener('input', function () { mgmtPage = 1; parRenderMgmtTable(); }); });
  ['parMgmtUnitFilter', 'parMgmtStatusFilter'].forEach(function (id) { document.getElementById(id)?.addEventListener('change', function () { mgmtPage = 1; parRenderMgmtTable(); }); });
  document.getElementById('parMgmtPageSize')?.addEventListener('change', function () { mgmtPerPage = parseInt(this.value) || 10; mgmtPage = 1; parRenderMgmtTable(); });

  window.parToggleKebab = function (ev, itemNumber) {
    ev.stopPropagation();
    document.querySelectorAll('.par-kebab-menu').forEach(function (m) { if (m.id !== 'par-kebab-' + itemNumber) m.classList.remove('open'); });
    var menu = document.getElementById('par-kebab-' + itemNumber);
    if (menu) menu.classList.toggle('open');
  };
  document.addEventListener('click', function () { document.querySelectorAll('.par-kebab-menu').forEach(function (m) { m.classList.remove('open'); }); });

  // ── VIEW EXISTING PAR AS THE ACTUAL RECEIPT ─────────────────────
  // The previous "View PAR" action opened the editable/read-only processing
  // workspace. Existing PAR records should instead open the receipt itself.
  // We reuse the same receipt markup (#par-print-area) and the same
  // par._receipt_styles partial used by printing/PDF, so View Receipt,
  // Preview, and Print all have one consistent appearance.
  window.parViewReceipt = function (itemNumber) {
    var r = parAll.find(function (p) { return p.itemNumber == itemNumber; });
    if (!r) return;

    // Populate the existing receipt preview using the saved PAR/personnel data.
    parOpenProcess(itemNumber, 'view');

    // Open the actual receipt immediately while this function is still running
    // from the user's click, avoiding popup blockers.
    parFullscreenPreview();

    // Return the dashboard itself to PAR Management; the receipt stays open
    // in its own tab/window.
    parShowMgmtList();
  };


  // ── DEDICATED REPLACE PAR WORKFLOW ────────────────────────────────
  var replaceSigIssuedBase64 = PAR_DEFAULT_ISSUED_SIGNATURE;
  var replaceSigApprovedBase64 = PAR_DEFAULT_APPROVED_SIGNATURE;
  var replaceNewParNumber = null;

  window.parBackFromReplace = function () {
    var view = document.getElementById('par-replace-view');
    if (view) view.style.display = 'none';
    parShowMgmtList();
  };

  function parReplacementReasonValue() {
    var reason = document.getElementById('replace_reason')?.value || '';
    if (reason === 'Other') {
      return (document.getElementById('replace_otherReason')?.value || '').trim();
    }
    return reason;
  }

  function parUpdateReplacementSummary() {
    setText('replace_summaryReason', parReplacementReasonValue() || 'Not selected');
    setText('replace_summaryNew', replaceNewParNumber || '—');
  }

  window.parOpenReplace = function (itemNumber) {
    var r = parAll.find(function (p) { return p.itemNumber == itemNumber; });
    if (!r) return;

    parCurrentItem = r;
    parDocMode = 'replace';
    parDocReturnTo = 'management';
    replaceNewParNumber = parGenerateNumber(r.itemNumber, true);

    var fullName = [r.firstName, r.middleName, r.lastName].filter(Boolean).join(' ');

    setText('replace_oldParNumber', r.parNumber || '—');
    setText('replace_oldDateIssued', fmtDateShort(r.dateIssued));
    setText('replace_personnelName', fullName || '—');
    setText('replace_afpSerial', r.afpSerialNumber || '—');
    setText('replace_rank', r.rank || '—');
    setText('replace_unit', r.unit || '—');
    setText('replace_firearm', r.pistolNomenclature || '—');
    setText('replace_firearmSerial', r.pistolSerialNumber || r.afpSerialNumber || '—');

    setText('replace_newParNumber', replaceNewParNumber);
    document.getElementById('replace_dateIssued').value = new Date().toISOString().slice(0, 10);
    document.getElementById('replace_issuedBy').value = r.issuedBy || 'MS ROSEMARIE O VILBAR';
    document.getElementById('replace_approvedBy').value = r.approvedBy || 'MS EVANGELINE M SINGUEO, Ph.D.';
    document.getElementById('replace_remarks').value = '';
    document.getElementById('replace_reason').value = '';
    document.getElementById('replace_otherReason').value = '';
    document.getElementById('replace_otherReasonWrap').style.display = 'none';

    setText('replace_propertyFirearm', r.pistolNomenclature || '—');
    setText('replace_propertySerial', r.pistolSerialNumber || r.afpSerialNumber || '—');
    setText('replace_ammoQty', (Number(r.qtyAmmo) || 0) + ' rounds');
    setText('replace_package', EQUIPMENT_PACKAGES.default.name);

    setText('replace_summaryOld', r.parNumber || '—');
    setText('replace_summaryNew', replaceNewParNumber);
    setText('replace_summaryReason', 'Not selected');
    setText('replace_summaryPersonnel', fullName || '—');

    replaceSigIssuedBase64 = r.issuedBySignature || PAR_DEFAULT_ISSUED_SIGNATURE;
    replaceSigApprovedBase64 = r.approvedBySignature || PAR_DEFAULT_APPROVED_SIGNATURE;
    parApplyReplaceSig('Issued', null);
    parApplyReplaceSig('Approved', null);

    var err = document.getElementById('replace_err');
    if (err) { err.style.display = 'none'; err.textContent = ''; }

    document.getElementById('par-hub-view').style.display = 'none';
    document.getElementById('par-issuance-list').style.display = 'none';
    document.getElementById('par-mgmt-list').style.display = 'none';
    document.getElementById('par-doc-view').style.display = 'none';
    document.getElementById('par-replace-view').style.display = 'block';

    window.staffScrollToTop?.();
  };

  document.getElementById('replace_reason')?.addEventListener('change', function () {
    document.getElementById('replace_otherReasonWrap').style.display = this.value === 'Other' ? 'block' : 'none';
    parUpdateReplacementSummary();
  });
  document.getElementById('replace_otherReason')?.addEventListener('input', parUpdateReplacementSummary);

  function parApplyReplaceSig(which, src) {
    var img = document.getElementById('replace_sig' + which);
    var empty = document.getElementById('replace_sig' + which + 'Empty');
    if (!img || !empty) return;
    if (src) {
      img.src = src;
      img.style.display = 'block';
      empty.style.display = 'none';
    } else {
      img.src = '';
      img.style.display = 'none';
      empty.style.display = 'block';
    }
  }

  window.parPickReplaceSig = function (which) {
    document.getElementById('replace_sig' + which + 'Input')?.click();
  };

  window.parClearReplaceSig = function (which) {
    if (which === 'Issued') replaceSigIssuedBase64 = null;
    else replaceSigApprovedBase64 = null;
    parApplyReplaceSig(which, null);
    var input = document.getElementById('replace_sig' + which + 'Input');
    if (input) input.value = '';
  };

  ['Issued', 'Approved'].forEach(function (which) {
    document.getElementById('replace_sig' + which + 'Input')?.addEventListener('change', function () {
      var file = this.files && this.files[0];
      if (!file || !file.type.startsWith('image/')) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        if (which === 'Issued') replaceSigIssuedBase64 = e.target.result;
        else replaceSigApprovedBase64 = e.target.result;
        parApplyReplaceSig(which, e.target.result);
      };
      reader.readAsDataURL(file);
    });
  });

  window.parConfirmReplacement = function () {
    var r = parCurrentItem;
    if (!r) return;

    var reason = parReplacementReasonValue();
    var dateIssued = document.getElementById('replace_dateIssued').value;
    var issuedBy = document.getElementById('replace_issuedBy').value.trim();
    var approvedBy = document.getElementById('replace_approvedBy').value.trim();
    var remarks = document.getElementById('replace_remarks').value.trim();
    var errEl = document.getElementById('replace_err');

    if (!reason) {
      errEl.textContent = 'Please select or enter a reason for replacing the PAR.';
      errEl.style.display = 'block';
      return;
    }
    if (!dateIssued || !issuedBy || !approvedBy) {
      errEl.textContent = 'Please complete Date Issued, Issued By, and Approved By.';
      errEl.style.display = 'block';
      return;
    }

    errEl.style.display = 'none';

    var oldParNumber = r.parNumber || '—';
    var newParNumber = replaceNewParNumber || parGenerateNumber(r.itemNumber, true);
    var fullName = [r.firstName, r.lastName].filter(Boolean).join(' ');

    var btn = document.getElementById('replace_confirmBtn');
    btn.disabled = true;
    btn.textContent = 'Replacing…';

    setOverride(r.itemNumber, {
      parStatus: 'issued',
      parNumber: newParNumber,
      previousParNumber: oldParNumber,
      replacementReason: reason,
      replacementRemarks: remarks,
      dateIssued: new Date(dateIssued).toISOString(),
      issuedBy: issuedBy,
      approvedBy: approvedBy,
      issuedBySignature: replaceSigIssuedBase64,
      approvedBySignature: replaceSigApprovedBase64,
      wasReplaced: true
    });

    r.previousParNumber = oldParNumber;
    r.replacementReason = reason;
    r.replacementRemarks = remarks;
    r.parNumber = newParNumber;
    r.dateIssued = dateIssued;
    r.issuedBy = issuedBy;
    r.approvedBy = approvedBy;
    r.issuedBySignature = replaceSigIssuedBase64;
    r.approvedBySignature = replaceSigApprovedBase64;
    r.wasReplaced = true;

    // Keep uploaded replacement signatures available for the receipt preview.
    parSigIssuedBase64 = replaceSigIssuedBase64;
    parSigApprovedBase64 = replaceSigApprovedBase64;

    logActivity(fullName, 'PAR Replaced', newParNumber);

    setTimeout(function () {
      btn.disabled = false;
      btn.textContent = 'Confirm Replacement';

      // Keep the replaced personnel selected so the user immediately sees
      // the NEW PAR number, previous PAR number, reason, and replacement status.
      mgmtSelectedItem = r.itemNumber;

      parRenderHub();
      parRenderMgmtActivity();
      parRenderMgmtTable();
      parRenderSummaryPanel(r);
      parShowMgmtList();
    }, 350);
  };

  // ── SHARED DOC VIEW ───────────────────────────────────────────────
  function parGenerateNumber(itemNumber, forceNew) {
    var y = new Date().getFullYear();
    var suffix = forceNew ? String(Date.now()).slice(-4) : String(itemNumber).padStart(6, '0');
    return 'PAR-' + y + '-' + suffix;
  }

  function parPickSig(which) {
    if (parDocMode === 'view') return;
    document.getElementById('par_sig' + which + 'Input').click();
  }
  window.parPickSig = parPickSig;

  function parSetDocMode(mode) {
    parDocMode = mode;
    var titleEl = document.getElementById('par_docTitle');
    var subEl = document.getElementById('par_docSubtitle');
    var confirmBtn = document.getElementById('par_confirmBtn');
    var confirmLabel = document.getElementById('par_confirmBtnLabel');
    var cancelBtn = document.getElementById('par_cancelBtn');
    var editableIds = ['par_dateIssued', 'par_issuedBy', 'par_approvedBy', 'par_remarks'];

    var cfg = {
      issue:   { title: 'Process Property Acknowledgement Receipt (PAR)', sub: 'Review the automatically assigned equipment package and complete the necessary information before generating the PAR.', btn: 'Confirm', color: '#1a9e4d', show: true, cancel: 'Cancel' },
      view:    { title: 'View Property Acknowledgement Receipt (PAR)', sub: 'Reference copy of the issued PAR. Use Print PAR to reprint.', btn: '', color: '', show: false, cancel: 'Close' },
      update:  { title: 'Update Property Acknowledgement Receipt (PAR)', sub: 'Edit the details of this issued PAR record.', btn: 'Save Changes', color: '#0ea5e9', show: true, cancel: 'Cancel' },
      replace: { title: 'Replace / Reissue Property Acknowledgement Receipt (PAR)', sub: 'This will generate a new PAR number for this personnel.', btn: 'Confirm Replacement', color: '#a855f7', show: true, cancel: 'Cancel' }
    }[mode];

    titleEl.textContent = cfg.title;
    subEl.textContent = cfg.sub;
    cancelBtn.textContent = cfg.cancel;
    if (cfg.show) {
      confirmBtn.style.display = 'inline-flex';
      confirmBtn.style.background = cfg.color;
      confirmLabel.textContent = cfg.btn;
    } else {
      confirmBtn.style.display = 'none';
    }

    editableIds.forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.disabled = (mode === 'view');
      el.style.opacity = (mode === 'view') ? .65 : 1;
      el.style.cursor = (mode === 'view') ? 'not-allowed' : '';
    });
    ['Issued', 'Approved'].forEach(function (which) {
      var box = document.getElementById('par_sig' + which + 'Box');
      var clearBtn = document.getElementById('par_sig' + which + 'Clear');
      if (box) box.classList.toggle('disabled', mode === 'view');
      if (clearBtn) clearBtn.style.display = (mode === 'view') ? 'none' : 'inline-block';
    });
  }

  window.parOpenProcess = function (itemNumber, mode) {
    mode = mode || 'issue';
    var r = parAll.find(function (p) { return p.itemNumber == itemNumber; });
    if (!r) return;
    parCurrentItem = r;
    parDocReturnTo = (mode === 'issue') ? 'issuance' : 'management';
    parSetDocMode(mode);

    var fullName = [r.firstName, r.middleName, r.lastName].filter(Boolean).join(' ');
    var pkg = EQUIPMENT_PACKAGES.default;
    var qtyAmmo = Number(r.qtyAmmo) || 0;
    var ammoCost = qtyAmmo * AMMO_UNIT_COST;
    var total = pkg.unitCost + ammoCost;
    var tax = Math.round(total * TAX_RATE * 100) / 100;
    var net = total - tax;

    setText('par_name', fullName || '—');
    setText('par_serial', r.afpSerialNumber || '—');
    setText('par_rank', r.rank || '—');
    setText('par_unit', r.unit || '—');
    setText('par_firearm', r.pistolNomenclature || '—');
    setText('par_dob', fmtDateShort(r.dateOfBirth));
    setText('par_civil', r.civilStatus || '—');

    var isNewNumber = (mode === 'issue' || mode === 'replace');
    var displayNumber = isNewNumber ? (mode === 'replace' ? parGenerateNumber(r.itemNumber, true) : parGenerateNumber(r.itemNumber)) : (r.parNumber || parGenerateNumber(r.itemNumber));
    setText('par_number', displayNumber + (mode === 'replace' ? '  (new — replaces ' + (r.parNumber || '—') + ')' : ''));
    document.getElementById('par_dateIssued').value = (mode === 'issue' || mode === 'replace') ? new Date().toISOString().slice(0, 10) : (r.dateIssued ? r.dateIssued.slice(0, 10) : new Date().toISOString().slice(0, 10));
    document.getElementById('par_issuedBy').value = r.issuedBy || 'MS ROSEMARIE O VILBAR';
    document.getElementById('par_approvedBy').value = r.approvedBy || 'MS EVANGELINE M SINGUEO, Ph.D.';
    document.getElementById('par_remarks').value = '';
    setText('par_remarksCount', 0);

    setText('par_packageName', pkg.name);
    document.getElementById('par_equipmentList').innerHTML = pkg.items.map(function (item) {
      return '<div class="par-equip-check"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>' + item + '</div>';
    }).join('') + '<div class="par-equip-check"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>' + qtyAmmo + ' rounds ' + (r.pistolNomenclature || '') + ' Ammunition</div>';

    setText('par_costUnit', fmtMoney(pkg.unitCost));
    setText('par_costAmmoLabel', 'Ammo Cost (' + qtyAmmo + ' rds @ ' + fmtMoney(AMMO_UNIT_COST) + '/rd)');
    setText('par_costAmmo', fmtMoney(ammoCost));
    setText('par_costTotal', fmtMoney(total));
    setText('par_costTax', fmtMoney(tax));
    setText('par_costNet', fmtMoney(net));

    var recvImg = document.getElementById('par_sigReceived'), recvEmpty = document.getElementById('par_sigReceivedEmpty');
    var sigSrc = parSignatureSrc(r.signature);
    if (sigSrc) {
      recvImg.src = sigSrc; recvImg.style.display = 'block'; recvEmpty.style.display = 'none';
    } else { recvImg.removeAttribute('src'); recvImg.style.display = 'none'; recvEmpty.style.display = 'block'; }
    setText('par_sigReceivedName', (r.rank || '') + ' ' + fullName);

    // Restore signatures already saved with this PAR.
    parSigIssuedBase64 = r.issuedBySignature || PAR_DEFAULT_ISSUED_SIGNATURE;
    parSigApprovedBase64 = r.approvedBySignature || PAR_DEFAULT_APPROVED_SIGNATURE;

    parApplySigInput('Issued', parSigIssuedBase64);
    parApplySigInput('Approved', parSigApprovedBase64);

    document.getElementById('par_err').style.display = 'none';
    parRenderPreview();

    document.getElementById('par-hub-view').style.display = 'none';
    document.getElementById('par-issuance-list').style.display = 'none';
    document.getElementById('par-mgmt-list').style.display = 'none';
    document.getElementById('par-doc-view').style.display = 'block';
    window.staffScrollToTop?.();
  };

  function parApplySigInput(which, src) {
    var img = document.getElementById('par_sig' + which);
    var empty = document.getElementById('par_sig' + which + 'Empty');
    if (src) { img.src = src; img.style.display = 'block'; empty.style.display = 'none'; }
    else { img.src = ''; img.style.display = 'none'; empty.style.display = 'block'; }
  }
  window.parClearSig = function (which) {
    if (parDocMode === 'view') return;
    if (which === 'Issued') parSigIssuedBase64 = null; else parSigApprovedBase64 = null;
    parApplySigInput(which, null);
    document.getElementById('par_sig' + which + 'Input').value = '';
    parRenderPreview();
  };
  ['Issued', 'Approved'].forEach(function (which) {
    document.getElementById('par_sig' + which + 'Input')?.addEventListener('change', function () {
      var file = this.files && this.files[0];
      if (!file || !file.type.startsWith('image/')) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        if (which === 'Issued') parSigIssuedBase64 = e.target.result; else parSigApprovedBase64 = e.target.result;
        parApplySigInput(which, e.target.result);
        parRenderPreview();
      };
      reader.readAsDataURL(file);
    });
  });

  ['par_dateIssued', 'par_issuedBy', 'par_approvedBy'].forEach(function (id) {
    document.getElementById(id)?.addEventListener('input', parRenderPreview);
    document.getElementById(id)?.addEventListener('change', parRenderPreview);
  });

  function parRenderPreview() {
    var r = parCurrentItem;
    if (!r) return;
    var fullName = [r.firstName, r.middleName, r.lastName].filter(Boolean).join(' ');
    var pkg = EQUIPMENT_PACKAGES.default;
    var qtyAmmo = Number(r.qtyAmmo) || 0;
    var ammoCost = qtyAmmo * AMMO_UNIT_COST;
    var total = pkg.unitCost + ammoCost;
    var tax = Math.round(total * TAX_RATE * 100) / 100;
    var net = total - tax;

    var issuedBy = document.getElementById('par_issuedBy').value.trim();
    var approvedBy = document.getElementById('par_approvedBy').value.trim();
    var dateIssued = document.getElementById('par_dateIssued').value;
    var parNoDisplay = document.getElementById('par_number').textContent.split('  (')[0];

    // Make/Model — mirror the same "last comma segment -> last word is model"
    // parsing used server-side in par/_receipt.php, so the live preview
    // and the real PDF always agree (e.g. "Pistol, 9mm, Glock 17" -> GLOCK / 17).
    var firearmStr = r.pistolNomenclature || '';
    var lastSegment = firearmStr.split(',').pop().trim();
    var segParts = lastSegment.split(/\s+/).filter(Boolean);
    var modelStr = segParts.length > 1 ? segParts.pop() : '';
    var makeStr = segParts.join(' ') || lastSegment;

    setText('pp_parNo', parNoDisplay);
    setText('pp_qty', 1);
    document.getElementById('pp_desc').innerHTML =
      (r.pistolNomenclature || 'Pistol') + ' with the following accessories:<br>'
      + '<ul style="margin:2px 0 0;padding-left:14px;">' + pkg.items.map(function (it) { return '<li>' + it + '</li>'; }).join('') + '</ul>';
    setText('pp_serial2', r.afpSerialNumber || '—');
    setText('pp_unitCost', fmtMoney(pkg.unitCost));
    setText('pp_ammoQty', qtyAmmo);
    setText('pp_ammoDesc', 'Ctg. 9mm, Ball (' + fmtMoney(AMMO_UNIT_COST) + '/rd)');
    setText('pp_ammoCost', fmtMoney(ammoCost));
    setText('pp_total', fmtMoney(total));
    setText('pp_tax', fmtMoney(tax));
    setText('pp_net', fmtMoney(net));
    setText('pp_make', makeStr ? makeStr.toUpperCase() : '—');
    setText('pp_model', modelStr || '—');
    setText('pp_serial3', r.afpSerialNumber || '—');
    setText('pp_approvedName', (approvedBy || 'APPROVING AUTHORITY').toUpperCase());
    setText('pp_dateApproved', fmtDateShort(dateIssued));
    var ppSigApproved = document.getElementById('pp_sigApproved');
    if (parSigApprovedBase64) { ppSigApproved.src = parSigApprovedBase64; ppSigApproved.style.display = 'block'; } else ppSigApproved.style.display = 'none';

    setText('pp_receivedName', ((r.rank || '') + ' ' + fullName).trim().toUpperCase());
    setText('pp_unit2', r.unit || '—');
    setText('pp_dob2', fmtDateShort(r.dateOfBirth));
    setText('pp_validUntil', fmtDateShort(r.dateOfValidity));
    setText('pp_issuedName', (issuedBy || 'ISSUING OFFICER').toUpperCase());
    setText('pp_dateIssued2', fmtDateShort(dateIssued));

    var replacementNote = document.getElementById('pp_replacementNote');
    if (replacementNote) {
      var previousNumber = r.previousParNumber || null;
      var replacementReason = r.replacementReason || null;
      if (previousNumber) {
        replacementNote.textContent = 'Replacement for ' + previousNumber + (replacementReason ? ' — ' + replacementReason : '');
        replacementNote.style.display = 'block';
      } else {
        replacementNote.textContent = '';
        replacementNote.style.display = 'none';
      }
    }

    // Automatically use the digital signature captured during personnel registration.
    parSetSignatureImage('pp_sigReceived', r.signature);
    var pi = document.getElementById('pp_sigIssued');
    if (parSigIssuedBase64) { pi.src = parSigIssuedBase64; pi.style.display = 'block'; } else pi.style.display = 'none';
  }

  // Grabs the actual par._receipt_styles <style> block (included once at the
  // top of this file) so the popup/print window is styled by the SAME
  // stylesheet as the real PDF/document view — not a hand-copied duplicate.
  function parReceiptStylesHTML() {
    var el = document.getElementById('par-receipt-styles');
    return el ? el.outerHTML : (document.querySelector('style')?.outerHTML || '');
  }

  window.parFullscreenPreview = function () {
    var w = window.open('', '_blank');
    if (!w) return;
    var paper = document.getElementById('par-print-area').outerHTML;
    w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>Property Acknowledgement Receipt</title>' + parReceiptStylesHTML() + '<style>body{background:#20242b;padding:32px;display:flex;justify-content:center;}</style></head><body>' + paper + '</body></html>');
    w.document.close();
  };

  window.parPrintPAR = function () {
    var paper = document.getElementById('par-print-area');
    if (!paper) return;
    var w = window.open('', '_blank', 'width=900,height=1100');
    if (!w) { window.print(); return; }
    w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>PAR</title>' + parReceiptStylesHTML() + '<style>@page{size:A4 portrait;margin:8mm}body{margin:0}.par-receipt{border:0;min-height:auto;padding:7mm}</style></head><body>' + paper.outerHTML + '</body></html>');
    w.document.close();
    var images = Array.from(w.document.images);
    Promise.all(images.map(function (img) { return img.complete ? Promise.resolve() : new Promise(function (res) { img.onload = res; img.onerror = res; }); }))
      .then(function () { setTimeout(function () { w.focus(); w.print(); }, 150); });

    // Reprint logging: only for records that are already actively issued.
    if (parCurrentItem && parCurrentItem.parStatus === 'issued') {
      var fullName = [parCurrentItem.firstName, parCurrentItem.lastName].filter(Boolean).join(' ');
      logActivity(fullName, 'PAR Reprinted', parCurrentItem.parNumber);
      parRenderHub(); parRenderMgmtActivity();
    }
  };

  // "Generate PDF" opens the same print-ready document in a new tab, using the
  // browser's native "Save as PDF" print destination — no client-side PDF
  // library required. Unlike Print PAR, it does not auto-open the print dialog
  // and does not count as a "reprint" in the activity log.
  window.parGeneratePDF = function () {
    var paper = document.getElementById('par-print-area');
    if (!paper) return;
    var w = window.open('', '_blank', 'width=900,height=1100');
    if (!w) return;
    w.document.write('<!doctype html><html><head><meta charset="utf-8"><title>PAR</title>' + parReceiptStylesHTML() + '<style>@page{size:A4 portrait;margin:8mm} body{background:#e8ebef;padding:24px;display:flex;justify-content:center;}</style></head><body>' + paper.outerHTML + '</body></html>');
    w.document.close();
    w.document.title = 'PAR — use Ctrl/Cmd+P and choose "Save as PDF" to download';
  };

  window.parReprint = function (itemNumber) {
    parOpenProcess(itemNumber, 'view');
    setTimeout(function () { parPrintPAR(); }, 300);
  };

  window.parConfirmSend = async function () {
    var r = parCurrentItem;
    var errEl = document.getElementById('par_err');
    var dateIssued = document.getElementById('par_dateIssued').value;
    var issuedBy = document.getElementById('par_issuedBy').value.trim();
    var approvedBy = document.getElementById('par_approvedBy').value.trim();

    if (!dateIssued || !issuedBy || !approvedBy) {
      errEl.textContent = 'Please fill in Date Issued, Issued By, and Approved By before confirming.';
      errEl.style.display = 'block';
      return;
    }
    errEl.style.display = 'none';

    var btn = document.getElementById('par_confirmBtn');
    var label = document.getElementById('par_confirmBtnLabel');
    btn.disabled = true;
    label.textContent = 'Saving…';

    var fullName = [r.firstName, r.lastName].filter(Boolean).join(' ');
    var isReplace = (parDocMode === 'replace');
    var parNumber = isReplace ? parGenerateNumber(r.itemNumber, true) : (r.parNumber || parGenerateNumber(r.itemNumber));

    try {
      var result = await fetch('/staff/par/' + encodeURIComponent(r.itemNumber) + '/save', {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ mode: parDocMode, parNumber: parNumber, dateIssued: dateIssued,
          issuedBy: issuedBy, approvedBy: approvedBy, issuedBySignature: parSigIssuedBase64,
          approvedBySignature: parSigApprovedBase64, remarks: document.getElementById('par_remarks').value })
      });
      var saved = await result.json();
      if (!result.ok || !saved.success) throw new Error(saved.message || 'Unable to save PAR.');
    } catch (error) {
      errEl.textContent = error.message; errEl.style.display = 'block';
      btn.disabled = false; parSetDocMode(parDocMode); return;
    }
    setOverride(r.itemNumber, {
      parStatus: 'issued',
      parNumber: parNumber,
      dateIssued: new Date(dateIssued).toISOString(),
      issuedBy: issuedBy,
      approvedBy: approvedBy,
      issuedBySignature: parSigIssuedBase64,
      approvedBySignature: parSigApprovedBase64,
      wasReplaced: isReplace ? true : (getOverride(r.itemNumber).wasReplaced || false)
    });

    r.parStatus = 'issued';
    r.parNumber = parNumber;
    r.dateIssued = dateIssued;
    r.issuedBy = issuedBy;
    r.approvedBy = approvedBy;
    r.issuedBySignature = parSigIssuedBase64;
    r.approvedBySignature = parSigApprovedBase64;
    if (isReplace) r.wasReplaced = true;

    var actionLabel = parDocMode === 'issue' ? 'PAR Issued' : parDocMode === 'update' ? 'PAR Updated' : 'PAR Replaced';
    logActivity(fullName, actionLabel, parNumber);

    setTimeout(function () {
      btn.disabled = false;
      parSetDocMode(parDocMode); // restore label
      parRenderHub();

      // Refresh the table and Selected PAR Summary immediately so the
      // newly entered Issued By / Approved By values appear at once.
      parRenderMgmtTable();
      if (mgmtSelectedItem == r.itemNumber) {
        parRenderSummaryPanel(r);
      }

      if (parDocReturnTo === 'management') parShowMgmtList(); else parShowIssuanceList();
    }, 450);
  };

  // ── Reset to Hub whenever the PAR nav item is (re-)selected ────────
  var parSection = document.getElementById('page-par');
  var parWasActive = false;
  var parLoadedOnce = false;
  new MutationObserver(function () {
    var isActive = parSection.classList.contains('active');
    if (isActive && !parWasActive) {
      parWasActive = true;
      if (!parLoadedOnce) { parLoadedOnce = true; parFetchData(); }
      else { parFetchData(); }
      parShowHub();
    } else if (!isActive) {
      parWasActive = false;
    }
  }).observe(parSection, { attributes: true, attributeFilter: ['class'] });

  if (parSection.classList.contains('active')) { parLoadedOnce = true; parFetchData(); }
})();
</script>
