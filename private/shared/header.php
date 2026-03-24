<?php if ( ! isset($page_title) ) { $page_title = 'Artifact Manager'; } ?>

<!DOCTYPE html>

<html lang="en">
  <head>
    
    <title>
      <?php echo h($page_title); ?> - Artifact Manager
    </title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#30395c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <link rel="shortcut icon" type="image/jpg" href="<?php echo url_for('favicon.ico') ?>">
    <link rel="manifest" href="<?php echo url_for('manifest.json') ?>">
    <link rel="apple-touch-icon" href="<?php echo url_for('assets/icon-192x192.png') ?>">

    <link rel="stylesheet" media="all" href="../../style.css?v=4" />

    <link href="https://fonts.googleapis.com/css2?family=Raleway&display=swap" rel="stylesheet">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VW7XFDFLF9"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-VW7XFDFLF9');
    </script>

  </head>

  <body>
    <header>
      <a class="header-link" href="/">Artifact Manager</a>
    </header>

    <nav class="hideOnPrint">
        <?php
        if(isset($_SESSION['logged_in']) && isset($_SESSION['FullName'])) {
          ?>
          <a href="<?php echo url_for('/settings/edit'); ?>">
            <?php echo '<span>' . $_SESSION['username'] . '</span>'; ?>
          </a>
          <?php
        }

        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
          ?>
          
          <a href="<?php echo url_for('/artifacts/useby'); ?>">
            Interact&nbsp;By&nbsp;Date
          </a>
          
          <a href="<?php echo url_for('/uses/1-n-uses'); ?>">
            Interactions
          </a>
          
          <a href="<?php echo url_for('/uses/1-n-new'); ?>">
            Record&nbsp;Interaction
          </a>

          <a href="<?php echo url_for('/artifacts'); ?>">
            Entities
          </a>
        
          <a href="<?php echo url_for('/artifacts/new'); ?>">
            Create&nbsp;Entity
          </a>

          <a href="<?php echo url_for('/users'); ?>">
            Users
          </a>

          <a href="<?php echo url_for('/users/new'); ?>">
            Create&nbsp;User
          </a>
         
          <a href="<?php echo url_for('/types'); ?>">
            Types
          </a>

          <a href="<?php echo url_for('/support'); ?>">
            Support
          </a>

          <span><a href="<?php echo url_for('logout'); ?>">Logout</a></span>
          
          <?php

        }
      ?>
        
    </nav>

    <?php echo display_session_message(); ?>
