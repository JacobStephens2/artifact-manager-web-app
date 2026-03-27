<?php require_once('../../private/initialize.php'); ?>

<?php require_login_or_guest();

$id = $_GET['id'] ?? '1'; // PHP > 7.0

$response = find_response_by_id($id);

?>

<?php $page_title = 'Show use'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <li><a class="action" href="<?php echo url_for('/uses/edit.php?id=' . h(u($response['id']))); ?>">Edit</a></li>

  <div class="use show">

    <div class="attributes">
      
      <h2>Game: <?php echo h($response['Title']); ?></h2>

      <h2>Date of play: <?php echo h($response['PlayDate']); ?></h2>

      <h2>Note</h2>
      <p id="note">
        <?php echo h($response['Note']); ?>
      </p>
      <!-- GET variable approach to passing player id from new page to show -->
      <a href="/uses/edit.php?id=<?php echo $_REQUEST['id']; ?>">
        Edit Response
      </a>
    </div>
    <br />
    <hr />

  </div>


  </div>

</main>
