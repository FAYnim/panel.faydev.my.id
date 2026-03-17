(() => {
    const tableBody = document.getElementById('projectsTableBody');
    const emptyState = document.getElementById('projectsEmptyState');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    async function loadProjects() {
        tableBody.innerHTML = '<tr><td colspan="5" class="table-loading">Loading projects...</td></tr>';
        emptyState.hidden = true;

        try {
            const response = await fetch('api/projects.php', {
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success || !Array.isArray(result.data)) {
                throw new Error(result.message || 'Failed to load projects');
            }

            if (result.data.length === 0) {
                tableBody.innerHTML = '';
                emptyState.hidden = false;
                return;
            }

            tableBody.innerHTML = result.data.map((project) => {
                const thumbnail = project.thumbnail ? `${String(project.thumbnail)}` : '';
                const demoLink = project.demo_link ? `<a href="${escapeHtml(project.demo_link)}" target="_blank" rel="noopener">Open</a>` : '<span class="text-muted">-</span>';

                return `
                    <tr data-id="${Number(project.id)}">
                        <td>
                            <img src="${escapeHtml(thumbnail)}" alt="${escapeHtml(project.title)}" class="thumb-48">
                        </td>
                        <td>${escapeHtml(project.title)}</td>
                        <td>${escapeHtml(project.project_date)}</td>
                        <td>${demoLink}</td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-sm btn-secondary" href="project-form.php?id=${Number(project.id)}">Edit</a>
                                <button class="btn btn-sm btn-danger js-delete" type="button" data-id="${Number(project.id)}">Delete</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (error) {
            tableBody.innerHTML = '<tr><td colspan="5" class="table-error">Failed to load projects.</td></tr>';
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to load projects', 'error');
            }
        }
    }

    async function deleteProject(id) {
        const confirmed = window.confirm('Delete this project? This action cannot be undone.');
        if (!confirmed) {
            return;
        }

        const formData = new FormData();
        formData.append('id', String(id));
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch('api/projects.php?action=delete', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData,
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Failed to delete project');
            }

            if (typeof window.showToast === 'function') {
                window.showToast('Project deleted successfully', 'success');
            }

            loadProjects();
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error instanceof Error ? error.message : 'Failed to delete project', 'error');
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
            deleteProject(id);
        }
    });

    loadProjects();
})();
