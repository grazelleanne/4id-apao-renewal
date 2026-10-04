(function (root, factory) {
  const api = factory();

  if (typeof module === 'object' && module.exports) {
    module.exports = api;
  }

  root.StaffReportFilter = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
  'use strict';

  function normalizeDate(value) {
    if (value === null || value === undefined || value === '') return null;

    const raw = String(value).trim();
    const isoMatch = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (isoMatch) {
      const iso = `${isoMatch[1]}-${isoMatch[2]}-${isoMatch[3]}`;
      const parsed = new Date(`${iso}T00:00:00`);
      const isExactDate = !Number.isNaN(parsed.getTime())
        && parsed.getFullYear() === Number(isoMatch[1])
        && parsed.getMonth() + 1 === Number(isoMatch[2])
        && parsed.getDate() === Number(isoMatch[3]);
      return isExactDate ? iso : null;
    }

    const parsed = new Date(raw);
    if (Number.isNaN(parsed.getTime())) return null;

    const year = parsed.getFullYear();
    const month = String(parsed.getMonth() + 1).padStart(2, '0');
    const day = String(parsed.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function filter(personnel, filters) {
    const from = normalizeDate(filters && filters.from);
    const to = normalizeDate(filters && filters.to);
    const status = String((filters && filters.status) || '').trim().toLowerCase();

    if (from && to && from > to) {
      return {
        rows: [],
        error: 'Date From must be on or before Date To.',
      };
    }

    const rows = (Array.isArray(personnel) ? personnel : []).filter(function (record) {
      const recordStatus = String(record.approvedStatus || 'pending').trim().toLowerCase();
      if (status && recordStatus !== status) return false;

      if (!from && !to) return true;

      const validityDate = normalizeDate(record.dateOfValidity);
      if (!validityDate) return false;
      if (from && validityDate < from) return false;
      if (to && validityDate > to) return false;

      return true;
    });

    return { rows, error: null };
  }

  return { filter, normalizeDate };
});
