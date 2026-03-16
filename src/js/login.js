document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn      = document.getElementById('loginBtn');
    const errorEl  = document.getElementById('loginError');
    const formData = new FormData(this);

    btn.disabled = true;
    btn.textContent = 'Signing in...';
    errorEl.classList.remove('visible');

    try {
        const res = await fetch(this.action, {
            method: 'POST',
            body: formData,
        });

        const json = await res.json();

        if (json.success) {
            window.location.href = 'index.php';
        } else {
            errorEl.textContent = json.message || 'Login failed';
            errorEl.classList.add('visible');
        }
    } catch {
        errorEl.textContent = 'Network error. Please try again.';
        errorEl.classList.add('visible');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Sign In';
    }
});
