        </main>
        <!-- End Page Wrapper -->

    </div>
    <!-- End Main Content Area -->
</div>
<!-- End App Container -->

<script>
// ─── Global WhatsApp Click Tracker ──────────────────────────────────────────
$(document).on('click', 'a', function(e) {
    var href = $(this).attr('href') || '';
    if (href.indexOf('wa.me') !== -1 || href.indexOf('api.whatsapp.com') !== -1 || href.indexOf('wa=1') !== -1 || $(this).hasClass('wa-track-click')) {
        var moduleName = $(this).data('log-module') || document.title || window.location.pathname;
        $.post('ajax/whatsapp_log_ajax.php', { module: moduleName, target_url: href });
    }
});
</script>
</body>
</html>
