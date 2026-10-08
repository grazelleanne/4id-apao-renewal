window.showUiDialog = function ({title, message, confirmText = 'OK', cancelText = null, danger = false}) {
  return new Promise(resolve => {
    const dialog = document.createElement('dialog');
    dialog.style.cssText = 'width:min(440px,90vw);padding:24px;background:var(--ui-panel,#fff);color:var(--ui-text,#1d3025);border:1px solid var(--ui-border,#dce7df);border-radius:12px;box-shadow:0 16px 50px #0004';
    const heading = document.createElement('h2'); heading.textContent = title; heading.style.cssText = 'margin:0 0 12px;font-size:20px;font-weight:700;color:inherit';
    const text = document.createElement('p'); text.textContent = message; text.style.cssText = 'color:var(--ui-muted,#607567);line-height:1.5;margin-bottom:20px';
    const actions = document.createElement('div'); actions.style.cssText = 'display:flex;gap:10px;justify-content:flex-end';
    const confirm = document.createElement('button'); confirm.type = 'button'; confirm.textContent = confirmText;
    confirm.style.cssText = 'padding:10px 16px;border:0;border-radius:7px;font:inherit;font-weight:600;cursor:pointer;color:#fff;background:' + (danger ? '#dc2626' : '#197544');
    function finish(value) {dialog.close();dialog.remove();resolve(value);}
    confirm.addEventListener('click', () => finish(true)); actions.appendChild(confirm);
    let initialFocus = confirm;
    if (cancelText) {
      const cancel = document.createElement('button'); cancel.type = 'button'; cancel.textContent = cancelText;
      cancel.style.cssText = 'padding:10px 16px;border:1px solid var(--ui-border,#dce7df);border-radius:7px;font:inherit;cursor:pointer;background:var(--ui-panel-soft,#f5f8f6);color:var(--ui-text,#1d3025)';
      cancel.addEventListener('click', () => finish(false));actions.appendChild(cancel);initialFocus=cancel;
    }
    dialog.addEventListener('cancel', event => {event.preventDefault();finish(false);});
    dialog.append(heading,text,actions);document.body.appendChild(dialog);dialog.showModal();initialFocus.focus();
  });
};
