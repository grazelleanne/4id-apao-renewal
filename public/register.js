/**
 * Registration form — posts to api/register.php, shows success modal, then login.
 */
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('registerForm');
  const err = document.getElementById('regError');
  const successModal = document.getElementById('registerSuccessModal');
  const successMsg = document.getElementById('registerSuccessMsg');
  const successDesc = document.getElementById('registerSuccessDesc');
  const goLoginBtn = document.getElementById('registerGoLoginBtn');

  if (!form) return;

  function showError(text) {
    err.textContent = text;
    err.style.display = 'block';
  }

  function hideError() {
    err.style.display = 'none';
    err.textContent = '';
  }

  function showSuccess(message, description) {
    if (successMsg) successMsg.textContent = message || 'Registration successful';
    if (successDesc) successDesc.textContent = description || '';
    if (successModal) successModal.classList.add('active');
  }

  function goToLogin() {
    window.location.href = '/login';
  }

  if (goLoginBtn) {
    goLoginBtn.addEventListener('click', goToLogin);
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideError();

    const nameEl = document.getElementById('name');
    const emailEl = document.getElementById('email');
    const passwordEl = document.getElementById('password');
    const confirmEl = document.getElementById('confirm_password');
    const termsEl = document.getElementById('terms');

    const name = nameEl ? nameEl.value.trim() : '';
    const email = emailEl ? emailEl.value.trim() : '';
    const password = passwordEl ? passwordEl.value : '';
    const confirm = confirmEl ? confirmEl.value : '';
    const terms = termsEl ? termsEl.checked : false;

    if (!name) {
      showError('Name is required.');
      return;
    }
    if (!email) {
      showError('Email is required.');
      return;
    }
    if (password.length < 6) {
      showError('Password must be at least 6 characters.');
      return;
    }
    if (password !== confirm) {
      showError('Passwords do not match.');
      return;
    }
    if (!terms) {
      showError('You must agree to the terms.');
      return;
    }

    const fd = new FormData();
    fd.append('name', name);
    fd.append('email', email);
    fd.append('password', password);
    fd.append('confirm_password', confirm);
    fd.append('terms', terms ? '1' : '');

    try {
      const res = awaitfetch('/register', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  },
  body: fd
});
      const data = await res.json().catch(function () {
        return { success: false, error: 'Invalid response from server.' };
      });

      if (data.success) {
        const msg = data.message || 'Registration successful.';
        const desc =
          'You can now sign in with your email and password to access the dashboard.';
        showSuccess(msg, desc);
        var auto = setTimeout(goToLogin, 4500);
        if (goLoginBtn) {
          goLoginBtn.addEventListener(
            'click',
            function () {
              clearTimeout(auto);
            },
            { once: true }
          );
        }
      } else {
        showError(data.error || data.message || 'Registration failed.');
      }
    } catch (x) {
      showError('Something went wrong. Please try again.');
    }
  });
});

