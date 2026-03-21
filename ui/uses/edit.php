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
  $response['Note'] = $_POST['Note'] ?? '';

  $result = update_response($response);
  if($result === true) {
    $_SESSION['message'] = 'The use was updated successfully.';
  } else {
    $errors = $result;
  }
  $response = find_response_by_id($id);

} else {
  $response = find_response_by_id($id);
}

$page_title = 'Edit Use';
include(SHARED_PATH . '/header.php'); 
?>

<main>

  <div class="object edit">

    <h1><?php echo $page_title; ?></h1>

    <?php echo display_errors($errors); ?>

    <form
      action="<?php echo url_for('/uses/edit.php?id=' . h(u($id))); ?>"
      method="post"
      >
      <?php echo csrf_input(); ?>

      <label for="Title">Artifact</label>
      <select id="Title" name="Title">
        <?php
          $artifact_set = list_artifacts();
          while($artifact = mysqli_fetch_assoc($artifact_set)) {
            echo "<option value=\"" . h($artifact['id']) . "\"";
            if($response["responsetitle"] == $artifact['id']) {
              echo " selected";
            }
            echo ">" . h($artifact['Title']) . "</option>";
          }
          mysqli_free_result($artifact_set);
        ?>
      </select>

      <label for="user">User</dt>
      <select id="user" name="Player">
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
      
      <label for="UseDate">Interaction Date</dt>
      <input 
        type="date" 
        id="UseDate" 
        name="PlayDate" 
        value="<?php echo h($response['PlayDate']); ?>" 
      />

      <label for="Note">Note</label>
      <textarea 
        name="Note" 
        id="Note" 
        cols="30" 
        rows="10"
        ><?php echo h($response['Note']); ?></textarea>
      
      <input type="hidden" name="id" value="<?php echo h($response['id']); ?>" /></dd>

      <input type="submit" value="Save response" />

    </form>

  </div>

  <a 
    class="action" 
    href="<?php echo url_for('/uses/delete.php?id=' . h(u($response['id']))); ?>"
  >
    <button>
      Delete Interaction
    </button>
  </a>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
