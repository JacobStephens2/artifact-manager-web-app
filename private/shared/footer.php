    <footer class="site-footer">
      <div class="site-footer-inner">
        <div>
          <p class="footer-label">Artifact</p>
          <h2>Curated tracking for active collections and recurring use.</h2>
        </div>
        <p class="footer-meta">&copy; <?php echo date('Y'); ?> <a href="https://resume.jacobstephens.net" target="_blank">Jacob Stephens</a></p>
      </div>
    </footer>

    <script>
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
      }
    </script>
  </body>
</html>

<?php db_disconnect($db); ?>
