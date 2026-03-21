<?php
require_once('../../private/initialize.php');
require_login();

if(!isset($_GET['id'])) {
  redirect_to(url_for('/uses/index.php'));
}
$id = $_GET['id'];

if(is_post_request()) {
  // handle post requests sent by this page
  $response = [];
  $response['id'] = $id ?? '';
  $response['Title'] = $_POST['Title'] ?? '';
  $response['PlayDate'] = $_POST['PlayDate'] ?? '';
  $response['Player'] = $_POST['Player'] ?? '';

  $result = update_response($response);
  if($result === true) {
    $_SESSION['message'] = 'The object was updated successfully.';
    redirect_to(url_for('/uses/show.php?id=' . $id));
  } else {
    $errors = $result;
  }
} else {
  $response = find_response_by_id($id);
}

$page_title = 'Edit Aversion';
include(SHARED_PATH . '/header.php');

?>

<main>

  <div class="object edit">
    <h1><?php echo $page_title; ?></h1>

    <?php echo display_errors($errors); ?>

    <form action="<?php echo url_for('/uses/edit.php?id=' . h(u($id))); ?>" method="post">
      <?php echo csrf_input(); ?>
      <label for="Title">Artifact</label>
        <select id="Title" name="Title">
        <?php
          $type_set = list_games();
          while($type = mysqli_fetch_assoc($type_set)) {
            echo "<option value=\"" . h($type['id']) . "\"";
            if($response["responsetitle"] == $type['id']) {
              echo " selected";
            }
            echo ">" . h($type['Title']) . "</option>";
          }
          mysqli_free_result($type_set);
        ?>
        </select>
      <label for="User">User</label>
      <select id="User" name="Player">
        <option value='Invalid'>Choose a User</option>
        <?php
          $player_set = list_players();
          while($player = mysqli_fetch_assoc($player_set)) {
            echo "<option value=\"" . h($player['id']) . "\"";
            if($response["Player"] == $player['id']) {
              echo " selected";
            }
            echo ">" . h($player['FirstName']) . ' ' . h($player['LastName']) . "</option>";
          }
          mysqli_free_result($player_set);
        ?>
      </select>
    
      <label for="AversionDate">Aversion Date</label>
      <input type="date" name="AversionDate" id="AversionDate" value="<?php echo h($response['AversionDate']); ?>" />

      <input type="hidden" name="id" value="<?php echo h($response['id']); ?>" />

      <input type="submit" value="Save Aversion" />
    </form>

    <a 
      class="action" 
      href="<?php echo url_for('/aversions/delete.php?id=' . h(u($response['id']))); ?>"
    >
      <button>
        Delete Aversion
      </button>
    </a>

  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
