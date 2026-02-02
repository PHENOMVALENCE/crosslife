        </div><!-- /.elms-content -->
    </div><!-- /.elms-main-wrap -->
    <footer class="py-3 mt-auto" style="background: var(--footer-bg, #2c3e50); color: var(--footer-text, #adb5bd); margin-left: var(--sidebar-width, 260px);">
        <div class="container text-center small">
            <span>© <?php echo date('Y'); ?> <?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'CrossLife'); ?></span>
            <span class="mx-2">·</span>
            <a href="../index.html" class="text-decoration-none" style="color: var(--footer-text);">Back to site</a>
        </div>
    </footer>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
(function() {
    var sidebar = document.getElementById('elmsSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggle = document.getElementById('sidebarToggle');
    if (sidebar && toggle) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        });
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    }
})();
    </script>
</body>
</html>
