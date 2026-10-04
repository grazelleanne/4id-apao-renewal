
    document.getElementById('loginForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const errorBox = document.getElementById('loginError');
        const modal    = document.getElementById('successModal');
        const hintEl   = document.getElementById('redirectHint');
        const btn      = document.getElementById('loginBtn');
        const emailEl  = document.getElementById('email');
        const passEl   = document.getElementById('password');

        // Reset
        errorBox.textContent = '';
        errorBox.classList.remove('visible');
        emailEl.classList.remove('error');
        passEl.classList.remove('error');

        // Basic client validation
        if (!emailEl.value.trim()) {
            errorBox.textContent = 'Email is required.';
            errorBox.classList.add('visible');
            emailEl.classList.add('error');
            return;
        }

        if (!passEl.value.trim()) {
            errorBox.textContent = 'Password is required.';
            errorBox.classList.add('visible');
            passEl.classList.add('error');
            return;
        }

        btn.disabled    = true;
        btn.textContent = 'Signing in…';

        // Get the CSRF token from the meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept':       'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email:    emailEl.value.trim(),
                    password: passEl.value,
                    remember: document.getElementById('remember').checked,
                    _token:   csrfToken,
                }),
            });

            // Handle CSRF mismatch specifically
            if (response.status === 419) {
                errorBox.textContent = 'Session expired. Please refresh the page and try again.';
                errorBox.classList.add('visible');
                btn.disabled    = false;
                btn.textContent = 'Login';
                return;
            }

            const data = await response.json();

            if (data.success) {
                modal.classList.add('active');

                let count = 3;
                hintEl.textContent = 'Redirecting in ' + count + '…';

                const timer = setInterval(function () {
                    count--;
                    if (count > 0) {
                        hintEl.textContent = 'Redirecting in ' + count + '…';
                    } else {
                        clearInterval(timer);
                        window.location.replace(data.redirect);
                    }
                }, 1000);

            } else {
                errorBox.textContent = data.message || 'Login failed. Please try again.';
                errorBox.classList.add('visible');
                emailEl.classList.add('error');
                passEl.classList.add('error');
            }

        } catch (err) {
            errorBox.textContent = 'A network error occurred. Please try again.';
            errorBox.classList.add('visible');
            console.error('Login error:', err);
        } finally {
            btn.disabled    = false;
            btn.textContent = 'Login';
        }
    });
