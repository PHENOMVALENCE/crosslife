            </div>
        </main>
    </div>
    
    <!-- Vendor JS Files -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS (via CDN for admin tables) -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
            
            // Simple form validation for required fields
            const forms = document.querySelectorAll('form[method="POST"]');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(function(field) {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                    }
                });
            });
            
            // Initialize DataTables on any table with .datatable class
            if (typeof $ !== 'undefined' && $.fn.dataTable) {
                $('.datatable').each(function () {
                    const table = $(this);
                    
                    const defaultOptions = {
                        order: [],
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                        responsive: true,
                        dom: "<'row mb-2'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-end'B>>" +
                             "<'row'<'col-sm-12'tr>>" +
                             "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                        buttons: [
                            {
                                extend: 'csv',
                                className: 'btn btn-sm btn-outline-secondary',
                                text: '<i class=\"bi bi-filetype-csv me-1\"></i>CSV'
                            },
                            {
                                extend: 'excel',
                                className: 'btn btn-sm btn-outline-secondary',
                                text: '<i class=\"bi bi-file-earmark-excel me-1\"></i>Excel'
                            },
                            {
                                extend: 'print',
                                className: 'btn btn-sm btn-outline-secondary',
                                text: '<i class=\"bi bi-printer me-1\"></i>Print'
                            }
                        ]
                    };
                    
                    const customOptions = table.data('dt-options') || {};
                    table.DataTable(Object.assign({}, defaultOptions, customOptions));
                });
            }
        });
    </script>
</body>
</html>

