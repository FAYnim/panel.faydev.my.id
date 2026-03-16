(() => {
    // We cannot use PHP inside the extracted JS file, so we need to grab the projectId from a data attribute or global variable.
    // However, the best practice is to read the URL or get it from a hidden field. Let's get it from the hidden input.
    const idInput = document.getElementById('projectId');
    const projectId = idInput ? Number(idInput.value) : 0;
    const isEdit = projectId > 0;
    const form = document.getElementById('projectForm');
    const saveBtn = document.getElementById('saveProjectBtn');
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

    async function loadProject() {
        if (!isEdit) {
            return;
        }

        try {
            const response = await fetch(`/api/projects.php?id=${projectId}`, {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Failed to load project');
            }

            const project = result.data;
            document.getElementById('title').value = project.title || '';
            document.getElementById('project_date').value = project.project_date || '';
            document.getElementById('demo_link').value = project.demo_link || '';

            if (project.thumbnail) {
                showPreview(`/${String(project.thumbnail).replace(/^\/+/, '')}`);
            }
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to load project', 'error');
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
            const response = await fetch(`/api/projects.php?action=${action}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData,
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to save project');
            }

            if (typeof window.showToast === 'function') {
                window.showToast(`Project ${isEdit ? 'updated' : 'created'} successfully`, 'success');
            }

            window.setTimeout(() => {
                window.location.href = 'projects.php';
            }, 500);
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to save project', 'error');
            }
        } finally {
            saveBtn.disabled = false;
        }
    });

    loadProject();
})();
