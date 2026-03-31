<?php if ( ! isset($page_title) ) { $page_title = 'Artifact'; } ?>

<!DOCTYPE html>

<html lang="en">
  <head>
    
    <title>
      <?php echo h($page_title); ?> - Artifact
    </title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1a2345">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <link rel="shortcut icon" type="image/jpg" href="<?php echo url_for('favicon.ico') ?>">
    <link rel="manifest" href="<?php echo url_for('manifest.json') ?>">
    <link rel="apple-touch-icon" href="<?php echo url_for('assets/icon-192x192.png') ?>">

    <link rel="stylesheet" media="all" href="../../style.css?v=6" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-P3N6C9C37N"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-P3N6C9C37N');
    </script>

  </head>

  <body class="<?php echo is_guest() ? 'guest-mode' : 'signed-in-mode'; ?>">
    <header class="site-header">
      <div class="site-header-inner">
        <div class="site-brand">
          <a class="header-link" href="/">Artifact</a>
          <p class="site-tagline">Track what you own. Use what you keep.</p>
        </div>

        <div class="site-status">
          <?php
          if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true && isset($_SESSION['username'])) {
            ?>
            <a class="site-status-link desktop-only" href="<?php echo url_for('/settings/edit'); ?>">
              <?php echo h($_SESSION['username']); ?>
            </a>
            <a class="site-status-link mobile-only" href="<?php echo url_for('/artifacts/useby'); ?>">
              Interact&nbsp;By&nbsp;Date
            </a>
            <?php
          } elseif (is_guest()) {
            ?>
            <span class="site-status-pill desktop-only">Guest</span>
            <a class="site-status-link mobile-only" href="<?php echo url_for('/artifacts/useby'); ?>">
              Interact&nbsp;By&nbsp;Date
            </a>
            <?php
          }
          ?>
          <button class="burger-btn" aria-label="Toggle menu" aria-expanded="false">
            <span class="burger-icon"></span>
          </button>
        </div>
      </div>
    </header>

    <nav class="site-nav hideOnPrint">
      <div class="site-nav-inner">
        <?php
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
          ?>
          <a href="<?php echo url_for('/artifacts/useby'); ?>">
            Interact&nbsp;By&nbsp;Date
          </a>
          
          <a href="<?php echo url_for('/uses/interactions'); ?>">
            Interactions
          </a>
          
          <a href="<?php echo url_for('/uses/1-n-new'); ?>">
            Record&nbsp;Interaction
          </a>

          <a href="<?php echo url_for('/artifacts'); ?>">
            Entities
          </a>

          <a href="<?php echo url_for('/artifacts/to-get-rid-of'); ?>">
            To&nbsp;Get&nbsp;Rid&nbsp;Of
          </a>
        

          <a href="<?php echo url_for('/users'); ?>">
            Users
          </a>

          <a href="<?php echo url_for('/types'); ?>">
            Types
          </a>

          <a href="<?php echo url_for('/support'); ?>">
            Support
          </a>

          <a class="mobile-only" href="<?php echo url_for('/settings/edit'); ?>">
            <?php echo h($_SESSION['username']); ?>
          </a>

          <a href="<?php echo url_for('logout'); ?>">Logout</a>
          
          <?php

        } elseif (is_guest()) {
          ?>
          <a href="<?php echo url_for('/artifacts/useby'); ?>">Interact&nbsp;By&nbsp;Date</a>
          <a href="<?php echo url_for('/uses/interactions'); ?>">Interactions</a>
          <a href="<?php echo url_for('/artifacts'); ?>">Entities</a>
          <a href="<?php echo url_for('/artifacts/to-get-rid-of'); ?>">To&nbsp;Get&nbsp;Rid&nbsp;Of</a>
          <a href="<?php echo url_for('/types'); ?>">Types</a>
          <a href="<?php echo url_for('/login.php?action=logout'); ?>">Exit&nbsp;Guest&nbsp;Mode</a>
          <?php
        }
      ?>
      </div>
    </nav>

    <?php if (is_guest()) { ?>
      <div class="guest-banner">
        You are browsing as a guest.
        <a href="<?php echo url_for('/register.php'); ?>">Create an account</a> to track your own artifacts,
        or <a href="<?php echo url_for('/login.php?action=logout'); ?>">exit guest mode</a>.
      </div>
    <?php } ?>

    <script>
      (function() {
        var btn = document.querySelector('.burger-btn');
        var nav = document.querySelector('.site-nav');
        if (btn && nav) {
          btn.addEventListener('click', function() {
            var open = nav.classList.toggle('nav-open');
            btn.setAttribute('aria-expanded', open);
          });
        }
      })();
    </script>

    <?php echo display_session_message(); ?>
