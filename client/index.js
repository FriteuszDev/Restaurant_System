const loginForm = document.getElementById('login_form');

if (loginForm) {
    const errorBox = document.getElementById('login_error');
    const loginButton = document.getElementById('login_button');

    loginForm.addEventListener('submit', (event) => {
        event.preventDefault();

        errorBox.classList.add('hidden');
        loginButton.disabled = true;

        const formData = new FormData();
        formData.append('employee_id', document.getElementById('employee_id').value);
        formData.append('password', document.getElementById('password').value);

        fetch('../php/login.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => null);

                if (!response.ok || !payload || payload.success !== true) {
                    throw new Error((payload && payload.message) || 'Login failed.');
                }

                window.location.replace(payload.redirect);
            })
            .catch((error) => {
                errorBox.textContent = error.message;
                errorBox.classList.remove('hidden');
                loginButton.disabled = false;
            });
    });
}
