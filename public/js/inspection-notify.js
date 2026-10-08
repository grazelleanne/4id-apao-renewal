// Reuse the inspection notification submission and its persisted list refresh.
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('notifyModal');
  const open = window.openNotifyModal;
  if (!modal || typeof open !== 'function') return;
  let sending = false;
  const error = document.getElementById('notifyError');
  if (error) new MutationObserver(() => {
    if (!sending || error.classList.contains('hidden') || !error.textContent.trim()) return;
    sending = false;
    window.showUiDialog({ title: 'Unable to notify staff', message: error.textContent.trim() });
  }).observe(error, { attributes: true, childList: true, subtree: true });
  window.openNotifyModal = async function (personnel) {
    if (sending) return;
    open(personnel);
    const submit = modal.querySelector('button[onclick*="sendNotify"]');
    modal.style.display = 'none';
    const handlerName = submit?.getAttribute('onclick')?.match(/\b(sendNotify\w*)\s*\(/)?.[1];
    const send = handlerName && window[handlerName];
    if (typeof send !== 'function') {
      modal.classList.remove('open', 'active');
      window.showUiDialog({ title: 'Unable to notify staff', message: 'Please refresh the page and try again.' });
      return;
    }
    sending = true;
    try {
      // Existing handler sends the prepared message, refreshes the list and shows success.
      await send();
    } finally {
      sending = false;
    }
  };
});
