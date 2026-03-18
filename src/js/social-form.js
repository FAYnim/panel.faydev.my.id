(() => {
    // Get socialId from the hidden input field instead of PHP injection
    const idInput = document.getElementById('socialId');
    const socialId = idInput ? Number(idInput.value) : 0;
    const isEdit = socialId > 0;
    
    const form = document.getElementById('socialForm');
    const saveBtn = document.getElementById('saveSocialBtn');
    const iconInput = document.getElementById('icon');
    const iconPreviewIcon = document.getElementById('iconPreviewIcon');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function updateIconPreview() {
        const classes = iconInput.value.trim();
        iconPreviewIcon.className = classes;
    }

    iconInput.addEventListener('input', updateIconPreview);

    async function loadSocialLink() {
        if (!isEdit) {
            return;
        }

        try {
            const response = await fetch(`api/social.php?id=${socialId}`, {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Failed to load social link');
            }

            const item = result.data;
            document.getElementById('name').value = item.name || '';
            document.getElementById('icon').value = item.icon || '';
            document.getElementById('url').value = item.url || '';
            updateIconPreview();
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to load social link', 'error');
            }
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(form);
        formData.append('csrf_token', csrfToken);

        const action = isEdit ? 'update' : 'create';
        saveBtn.disabled = true;

        try {
            const response = await fetch(`api/social.php?action=${action}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData,
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to save social link');
            }

            if (typeof window.showToast === 'function') {
                window.showToast(`Social link ${isEdit ? 'updated' : 'created'} successfully`, 'success');
            }

            window.setTimeout(() => {
                window.location.href = 'social.php';
            }, 500);
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to save social link', 'error');
            }
        } finally {
            saveBtn.disabled = false;
        }
    });

    loadSocialLink();
})();
