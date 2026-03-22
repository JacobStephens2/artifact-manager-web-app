    <footer>
      <h2>&copy; <?php echo date('Y'); ?> <a style="color: white" href="https://resume.jacobstephens.net" target='_blank'>Jacob Stephens</a></h2>
    </footer>

    <script>
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
      }
    </script>
  </body>
</html>

<?php db_disconnect($db); ?>
