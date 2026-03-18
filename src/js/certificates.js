(() => {
    const tableBody = document.getElementById('certificatesTableBody');
    const emptyState = document.getElementById('certificatesEmptyState');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    async function loadCertificates() {
        tableBody.innerHTML = '<tr><td colspan="6" class="table-loading">Loading certificates...</td></tr>';
        emptyState.hidden = true;

        try {
            const response = await fetch('api/certificates.php', {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success || !Array.isArray(result.data)) {
                throw new Error(result.message || 'Failed to load certificates');
            }

            if (result.data.length === 0) {
                tableBody.innerHTML = '';
                emptyState.hidden = false;
                return;
            }

            tableBody.innerHTML = result.data.map((cert) => {
                const thumbnail = cert.thumbnail ? `${String(cert.thumbnail)}` : '';
                const credentialLink = cert.credential_link
                    ? `<a href="${escapeHtml(cert.credential_link)}" target="_blank" rel="noopener">View</a>`
                    : '<span class="text-muted">-</span>';

                return `
                    <tr data-id="${Number(cert.id)}">
                        <td>
                            <img src="${escapeHtml(thumbnail)}" alt="${escapeHtml(cert.title)}" class="thumb-48">
                        </td>
                        <td>${escapeHtml(cert.title)}</td>
                        <td>${escapeHtml(cert.issuer)}</td>
                        <td>${escapeHtml(cert.issue_date)}</td>
                        <td>${credentialLink}</td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-sm btn-secondary" href="certificate-form.php?id=${Number(cert.id)}">Edit</a>
                                <button class="btn btn-sm btn-danger js-delete" type="button" data-id="${Number(cert.id)}">Delete</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (error) {
            tableBody.innerHTML = '<tr><td colspan="6" class="table-error">Failed to load certificates.</td></tr>';
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to load certificates', 'error');
            }
        }
    }

    async function deleteCertificate(id) {
        const confirmed = window.confirm('Delete this certificate? This action cannot be undone.');
        if (!confirmed) {
            return;
        }

        const formData = new FormData();
        formData.append('id', String(id));
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch('api/certificates.php?action=delete', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData,
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to delete certificate');
            }

            if (typeof window.showToast === 'function') {
                window.showToast('Certificate deleted successfully', 'success');
            }

            loadCertificates();
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to delete certificate', 'error');
            }
        }
    }

    tableBody.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const button = target.closest('.js-delete');
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        const id = Number(button.dataset.id);
        if (Number.isInteger(id) && id > 0) {
            deleteCertificate(id);
        }
    });

    loadCertificates();
})();
