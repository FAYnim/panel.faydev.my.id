            </main>
        </div>
    </div>
    
    <div class="toast-container">
        <div id="toast" class="toast" role="alert">
            <div class="toast-header">
                <strong class="me-auto" id="toast-title">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toast-message"></div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        function showToast(title, message, type = 'success') {
            const toast = new bootstrap.Toast(document.getElementById('toast'));
            document.getElementById('toast-title').textContent = title;
            document.getElementById('toast-message').textContent = message;
            document.querySelector('#toast').classList.remove('bg-success', 'bg-danger', 'bg-warning', 'text-white');
            document.querySelector('#toast').classList.add('bg-' + type, 'text-white');
            toast.show();
        }
        
        function confirmDelete(message) {
            return confirm(message || 'Are you sure you want to delete this item?');
        }
        
        $(document).ready(function() {
            $('.sortable').sortable({
                handle: '.handle',
                placeholder: 'ui-state-highlight'
            });
            
            // Sidebar Toggle Functionality
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const hamburgerBtn = document.getElementById('hamburger-btn');
            const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
            const sidebarLinks = document.querySelectorAll('#sidebar .nav-item a');
            
            // Function to open sidebar
            function openSidebar() {
                sidebar.classList.add('show');
                backdrop.classList.add('show');
                document.body.style.overflow = 'hidden'; // Prevent scrolling when sidebar is open
            }
            
            // Function to close sidebar
            function closeSidebar() {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }
            
            // Toggle sidebar when hamburger button is clicked
            if (hamburgerBtn) {
                hamburgerBtn.addEventListener('click', function() {
                    if (sidebar.classList.contains('show')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }
            
            // Close sidebar when close button is clicked
            if (sidebarCloseBtn) {
                sidebarCloseBtn.addEventListener('click', closeSidebar);
            }
            
            // Close sidebar when backdrop is clicked
            if (backdrop) {
                backdrop.addEventListener('click', closeSidebar);
            }
            
            // Auto-close sidebar when a menu item is clicked on mobile
            sidebarLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    // Only auto-close on mobile (screen width < 768px)
                    if (window.innerWidth < 768) {
                        closeSidebar();
                    }
                });
            });
            
            // Close sidebar when window is resized to desktop size
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>
