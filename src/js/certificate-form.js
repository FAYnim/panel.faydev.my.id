(() => {
    const idInput = document.getElementById('certificateId');
    const certificateId = idInput ? Number(idInput.value) : 0;
    const isEdit = certificateId > 0;
    const form = document.getElementById('certificateForm');
    const saveBtn = document.getElementById('saveCertificateBtn');
    const fileInput = document.getElementById('thumbnail');
    const dropzone = document.getElementById('thumbnailDropzone');
    const previewWrap = document.getElementById('thumbnailPreviewWrap');
    const previewImage = document.getElementById('thumbnailPreview');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function showPreview(src) {
        previewImage.src = src;
        previewWrap.hidden = false;
    }

    function handleFilePreview(file) {
        if (!file || !file.type.startsWith('image/')) {
            return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            if (typeof reader.result === 'string') {
                showPreview(reader.result);
            }
        };
        reader.readAsDataURL(file);
    }

    fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files[0]) {
            handleFilePreview(fileInput.files[0]);
        }
    });

    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('is-dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('is-dragover');
    });

    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragover');

        const files = event.dataTransfer?.files;
        if (!files || files.length === 0) {
            return;
        }

        fileInput.files = files;
        handleFilePreview(files[0]);
    });

    async function loadCertificate() {
        if (!isEdit) {
            return;
        }

        try {
            const response = await fetch(`api/certificates.php?id=${certificateId}`, {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Failed to load certificate');
            }

            const cert = result.data;
            document.getElementById('title').value = cert.title || '';
            document.getElementById('issuer').value = cert.issuer || '';
            document.getElementById('issue_date').value = cert.issue_date || '';
            document.getElementById('credential_link').value = cert.credential_link || '';

            if (cert.thumbnail) {
                showPreview(String(cert.thumbnail).replace(/^\/+/, ''));
            }
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to load certificate', 'error');
            }
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(form);
        formData.append('csrf_token', csrfToken);

        if (!isEdit && (!fileInput.files || fileInput.files.length === 0)) {
            if (typeof window.showToast === 'function') {
                window.showToast('Thumbnail is required', 'error');
            }
            return;
        }

        const action = isEdit ? 'update' : 'create';
        saveBtn.disabled = true;

        try {
            const response = await fetch(`api/certificates.php?action=${action}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData,
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to save certificate');
            }

            if (typeof window.showToast === 'function') {
                window.showToast(`Certificate ${isEdit ? 'updated' : 'created'} successfully`, 'success');
            }

            window.setTimeout(() => {
                window.location.href = 'certificates.php';
            }, 500);
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to save certificate', 'error');
            }
        } finally {
            saveBtn.disabled = false;
        }
    });

    loadCertificate();
})();
