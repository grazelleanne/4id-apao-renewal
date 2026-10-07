window.actionOtpFetch = async function (url, options) {
  const response = await fetch(url, options);
  const data = await response.clone().json().catch(() => ({}));
  if (!data.otpRequired) return response;
  const code = await new Promise(resolve => {
    const dialog = document.createElement('dialog');
    dialog.style.cssText = 'width:min(420px,90vw);padding:24px;border:1px solid #dce7df;border-radius:12px;color:#1d3025;background:white;';
    dialog.innerHTML = '<form><h2 style="margin-top:0">Email verification</h2><p class="otp-message"></p><label for="actionOtpCode">Six-digit code</label><input id="actionOtpCode" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required style="display:block;width:100%;box-sizing:border-box;margin:12px 0;padding:12px;font-size:20px;color:#1d3025;background:white;border:1px solid #b6c8bd;border-radius:6px"><button type="submit" style="padding:10px 16px;background:#197544;color:white;border:0;border-radius:6px">Verify</button> <button type="button" class="otp-cancel" style="padding:10px 16px">Cancel</button></form>';
    dialog.querySelector('.otp-message').textContent = data.message + ' The code expires in 5 minutes.';
    function finish(value) { dialog.close(); dialog.remove(); resolve(value); }
    dialog.querySelector('form').addEventListener('submit', event => { event.preventDefault(); finish(dialog.querySelector('input').value.trim()); });
    dialog.querySelector('.otp-cancel').addEventListener('click', () => finish(null));
    dialog.addEventListener('cancel', event => { event.preventDefault(); finish(null); });
    document.body.appendChild(dialog);
    dialog.showModal();
    dialog.querySelector('input').focus();
  });
  if (!code) return new Response(JSON.stringify({success:false,message:'Verification cancelled. No changes were saved.'}), {status:422,headers:{'Content-Type':'application/json'}});
  if (options.body instanceof FormData) options.body.set('otp_code', code);
  else options.body = JSON.stringify({...JSON.parse(options.body), otp_code:code});
  return fetch(url, options);
};
