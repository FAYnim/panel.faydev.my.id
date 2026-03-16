(() => {
    const tableBody = document.getElementById('socialTableBody');
    const emptyState = document.getElementById('socialEmptyState');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let socialData = [];

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function renderTable() {
        if (socialData.length === 0) {
            tableBody.innerHTML = '';
            emptyState.hidden = false;
            return;
        }

        emptyState.hidden = true;
        tableBody.innerHTML = socialData.map((item, index) => `
            <tr data-id="${Number(item.id)}">
                <td><i class="${escapeHtml(item.icon)}" aria-hidden="true"></i></td>
                <td>${escapeHtml(item.name)}</td>
                <td><a href="${escapeHtml(item.url)}" target="_blank" rel="noopener">${escapeHtml(item.url)}</a></td>
                <td>
                    <div class="order-controls">
                        <button class="btn btn-sm btn-icon js-move-up" type="button" ${index === 0 ? 'disabled' : ''} title="Move up">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-icon js-move-down" type="button" ${index === socialData.length - 1 ? 'disabled' : ''} title="Move down">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-sm btn-secondary" href="social-form.php?id=${Number(item.id)}">Edit</a>
                        <button class="btn btn-sm btn-danger js-delete" type="button" data-id="${Number(item.id)}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async function loadSocialLinks() {
        tableBody.innerHTML = '<tr><td colspan="5" class="table-loading">Loading social links...</td></tr>';
        emptyState.hidden = true;

        try {
            const response = await fetch('/api/social.php', {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success || !Array.isArray(result.data)) {
                throw new Error(result.message || 'Failed to load social links');
            }

            socialData = result.data;
            renderTable();
        } catch (error) {
            tableBody.innerHTML = '<tr><td colspan="5" class="table-error">Failed to load social links.</td></tr>';
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to load social links', 'error');
            }
        }
    }

    async function saveOrder() {
        const order = socialData.map((item, index) => ({
            id: Number(item.id),
            display_order: index,
        }));

        try {
            const response = await fetch('/api/social.php?action=reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                },
                body: JSON.stringify({ order }),
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to save order');
            }

            if (typeof window.showToast === 'function') {
                window.showToast('Order saved', 'success');
            }
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to save order', 'error');
            }
            loadSocialLinks();
        }
    }

    function swapItems(indexA, indexB) {
        if (indexA < 0 || indexB < 0 || indexA >= socialData.length || indexB >= socialData.length) {
            return;
        }
        const temp = socialData[indexA];
        socialData[indexA] = socialData[indexB];
        socialData[indexB] = temp;
        renderTable();
        saveOrder();
    }

    async function deleteSocialLink(id) {
        const confirmed = window.confirm('Delete this social link? This action cannot be undone.');
        if (!confirmed) {
            return;
        }

        const formData = new FormData();
        formData.append('id', String(id));
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch('/api/social.php?action=delete', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData,
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to delete social link');
            }

            if (typeof window.showToast === 'function') {
                window.showToast('Social link deleted', 'success');
            }

            loadSocialLinks();
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to delete social link', 'error');
            }
        }
    }

    tableBody.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const deleteBtn = target.closest('.js-delete');
        if (deleteBtn instanceof HTMLButtonElement) {
            const id = Number(deleteBtn.dataset.id);
            if (Number.isInteger(id) && id > 0) {
                deleteSocialLink(id);
            }
            return;
        }

        const row = target.closest('tr');
        if (!row) {
            return;
        }
        const rowIndex = Array.from(tableBody.children).indexOf(row);

        if (target.closest('.js-move-up')) {
            swapItems(rowIndex, rowIndex - 1);
        } else if (target.closest('.js-move-down')) {
            swapItems(rowIndex, rowIndex + 1);
        }
    });

    loadSocialLinks();
})();
