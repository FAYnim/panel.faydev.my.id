(() => {
    async function loadDashboardMetrics() {
        try {
            const response = await fetch('/api/dashboard.php', {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (!response.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Failed to load dashboard data');
            }

            document.getElementById('projectsCount').textContent = String(result.data.projects_count ?? 0);
            document.getElementById('socialCount').textContent = String(result.data.social_links_count ?? 0);
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Failed to load dashboard data';
            if (typeof window.showToast === 'function') {
                window.showToast(message, 'error');
            }
        }
    }

    loadDashboardMetrics();
})();
