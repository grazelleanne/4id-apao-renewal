(function () {
  'use strict';
  function cell(value) {
    let text = value == null ? '' : String(value);
    // Keep spreadsheet applications from treating exported user input as a formula.
    if (/^[\s]*[=+@-]/.test(text) || /^[\t\r\n]/.test(text)) text = "'" + text;
    return '"' + text.replace(/"/g, '""') + '"';
  }
  window.buildAuditLogCsv = function (logs, label) {
    const headers = ['No.', 'Date & Time', 'User', 'Role', 'Action', 'Action Code', 'Target', 'Module', 'IP Address', 'Details'];
    const rows = logs.map((log, index) => [index + 1, log.createdAt, log.userName, log.userRole,
      label(log.action), log.action, log.target, log.module, log.ipAddress,
      typeof log.details === 'object' && log.details !== null ? JSON.stringify(log.details) : log.details]);
    return '\uFEFF' + [headers, ...rows].map(row => row.map(cell).join(',')).join('\r\n') + '\r\n';
  };
  window.exportAuditLogCsv = function (logs, label) {
    const blob = new Blob([window.buildAuditLogCsv(logs, label)], {type: 'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'APAO_Audit_Log_' + new Date().toLocaleDateString('en-CA', {timeZone: 'Asia/Manila'}) + '.csv';
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
  };
})();
