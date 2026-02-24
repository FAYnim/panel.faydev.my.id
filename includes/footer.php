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
        });
    </script>
</body>
</html>
