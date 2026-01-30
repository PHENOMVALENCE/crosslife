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
        // Wait for all scripts to load, then initialize DataTables
        function waitForDataTables(callback, maxAttempts) {
            maxAttempts = maxAttempts || 50;
            let attempts = 0;
            
            function check() {
                attempts++;
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.dataTable !== 'undefined') {
                    callback();
                } else if (attempts < maxAttempts) {
                    setTimeout(check, 100);
                } else {
                    console.error('DataTables failed to load after', maxAttempts * 100, 'ms');
                }
            }
            check();
        }
        
        // Initialize DataTables function
        function initDataTables() {
            jQuery('.datatable').each(function() {
                const table = jQuery(this);
                
                // Skip if already initialized
                if (table.hasClass('dataTable')) {
                    return;
                }
                
                const defaultOptions = {
                    order: [],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                    responsive: true,
                    // l = length, f = search filter, B = buttons, t = table, i = info, p = pagination
                    dom:
                        "<'row mb-2'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4'f><'col-sm-12 col-md-4 text-md-end'B>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [
                        {
                            extend: 'csv',
                            className: 'btn btn-sm btn-outline-secondary',
                            text: '<i class="bi bi-filetype-csv me-1"></i>CSV'
                        },
                        {
                            extend: 'excel',
                            className: 'btn btn-sm btn-outline-secondary',
                            text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel'
                        },
                        {
                            extend: 'print',
                            className: 'btn btn-sm btn-outline-secondary',
                            text: '<i class="bi bi-printer me-1"></i>Print'
                        }
                    ],
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                };
                
                // Parse custom options
                let customOptions = {};
                const dtOptionsAttr = table.attr('data-dt-options');
                if (dtOptionsAttr) {
                    try {
                        customOptions = JSON.parse(dtOptionsAttr);
                    } catch (e) {
                        console.warn('Invalid data-dt-options JSON:', dtOptionsAttr);
                    }
                }
                
                // Merge and initialize
                const finalOptions = jQuery.extend(true, {}, defaultOptions, customOptions);
                try {
                    table.DataTable(finalOptions);
                } catch (e) {
                    console.error('DataTables error:', e);
                }
            });
        }
        
        // Admin mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.getElementById('adminSidebar');
            var overlay = document.getElementById('adminSidebarOverlay');
            var toggle = document.getElementById('adminSidebarToggle');
            function openSidebar() {
                if (sidebar) sidebar.classList.add('show');
                if (overlay) {
                    overlay.classList.add('show');
                    overlay.setAttribute('aria-hidden', 'false');
                }
                document.body.style.overflow = 'hidden';
            }
            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('show');
                if (overlay) {
                    overlay.classList.remove('show');
                    overlay.setAttribute('aria-hidden', 'true');
                }
                document.body.style.overflow = '';
            }
            if (toggle) toggle.addEventListener('click', function() {
                if (sidebar && sidebar.classList.contains('show')) closeSidebar();
                else openSidebar();
            });
            if (overlay) overlay.addEventListener('click', closeSidebar);
            var closeBtn = document.getElementById('adminSidebarClose');
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (sidebar) {
                sidebar.querySelectorAll('.menu-item').forEach(function(link) {
                    link.addEventListener('click', closeSidebar);
                });
            }
        });

        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function(alert) {
                setTimeout(function() {
                    if (typeof bootstrap !== 'undefined') {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });
        });
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[method="POST"]').forEach(function(form) {
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
        });
        
        // Initialize DataTables after all scripts load
        waitForDataTables(function() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    initDataTables();
                });
            } else {
                initDataTables();
            }
        });
    </script>
</body>
</html>

