(function (root) {
  'use strict';

  const TAB_ID_KEY = 'apao:session:tab-id';

  function parseOwner(rawValue) {
    if (!rawValue) return null;
    try {
      const owner = JSON.parse(rawValue);
      if (!owner || typeof owner.tabId !== 'string' || !Number.isFinite(Number(owner.expiresAt))) {
        return null;
      }
      return { tabId: owner.tabId, expiresAt: Number(owner.expiresAt) };
    } catch (_) {
      return null;
    }
  }

  function hasActiveConflict(rawValue, tabId, now) {
    const owner = parseOwner(rawValue);
    return Boolean(owner && owner.expiresAt > now && owner.tabId !== tabId);
  }

  function getTabId(storage, randomUUID) {
    let tabId = storage.getItem(TAB_ID_KEY);
    if (tabId) return tabId;

    tabId = typeof randomUUID === 'function'
      ? randomUUID()
      : Date.now() + ':' + Math.random();
    storage.setItem(TAB_ID_KEY, tabId);
    return tabId;
  }

  root.ApaoSingleTabGuard = {
    TAB_ID_KEY,
    getTabId,
    hasActiveConflict,
    parseOwner,
  };
})(typeof globalThis !== 'undefined' ? globalThis : window);
