<?php
  require_once('../../private/initialize.php');
  require_login();
  if(!isset($_GET['id'])) {
    redirect_to(url_for('/artifacts/index.php'));
  }
  $id = $_GET['id'];

  $user_id = $_SESSION['user_id'];
  $stmt = mysqli_prepare($db, "SELECT default_use_interval FROM users WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $default_interval_result = mysqli_stmt_get_result($stmt);
  $default_interval_row = mysqli_fetch_array($default_interval_result);
  $default_interval = ($default_interval_row !== null) ? $default_interval_row[0] : null;
  mysqli_stmt_close($stmt);

  if(is_post_request()) {
    // Handle form values sent by new.php
    $artifact = [];
    $artifact['id'] = $id ?? '';
    $artifact['Title'] = $_POST['Title'] ?? '';
    $artifact['InSecondaryCollection'] = $_POST['InSecondaryCollection'] ?? 'no';
    $artifact['Acq'] = $_POST['Acq'] ?? date('Y-m-d');
    $artifact['age'] = $_POST['age'] ?? 0;
    if ($artifact['age'] == '') {
      $artifact['age'] = 0;
    }

    if ($artifact['Acq'] == '') {
      $artifact['Acq'] = date('Y-m-d');
    }
    $artifact['type'] = $_POST['type'] ?? '';

    $artifact['interaction_frequency_days'] = $_POST['interaction_frequency_days'] ?? $default_interval;
    $artifact['KeptCol'] = $_POST['KeptCol'] ?? '';
    $artifact['to_get_rid_of'] = $_POST['to_get_rid_of'] ?? '0';
    $artifact['Candidate'] = $_POST['Candidate'] ?? '';
    $artifact['CandidateGroupDate'] = date('Y-m-d');
    $artifact['UsedRecUserCt'] = '0';
    $artifact['Notes'] = $_POST['Notes'] ?? '';
    ($_POST['MnT'] == '') ? $artifact['MnT'] = 5 : $artifact['MnT'] = $_POST['MnT'];
    ($_POST['MxT'] == '') ? $artifact['MxT'] = 240 : $artifact['MxT'] = $_POST['MxT'];
    ($_POST['MnP'] == '') ? $artifact['MnP'] = 5 : $artifact['MnP'] = $_POST['MnP'];
    ($_POST['MxP'] == '') ? $artifact['MxP'] = 240 : $artifact['MxP'] = $_POST['MxP'];
    ($_POST['SS'] == '') ? $artifact['SS'] = 1 : $artifact['SS'] = $_POST['SS'];
    $result = update_artifact($artifact);
    if($result === true) {
      $_SESSION['message'] = 'The entity was updated successfully.';
      redirect_to(url_for('/artifacts/edit.php?id=' . $id));
    } else {
      $errors = $result;
    }
  }

  $artifact = find_artifact_by_id($id);

  $sweetSpotsResultObject = find_sweet_spots_by_artifact_id($id);

  $page_title = h($artifact['Title']); 
  include(SHARED_PATH . '/header.php'); 
?>

<main>

  <div id="editArtifact" class="object edit">
    <h1>Edit <?php echo h($artifact['Title']); ?></h1>

    <?php echo display_errors($errors); ?>

    <li style="margin-bottom: 0.4rem;">
      <a class="back-link" 
        href="<?php echo url_for('/uses/1-n-new.php?artifact_id=' . h(u($id))); ?>"
        >
        Record Use
      </a>
    </li>

    <button id="editFormDisplayButton">
      Toggle Edit Form Display
    </button>

    <form id="editForm"
      action="<?php echo url_for('/artifacts/edit?id=' . h(u($id))); ?>"
      method="post"
      >
      <?php echo csrf_input(); ?>

      <label for="Title">Title</label>
      <input type="text" name="Title" id="Title" value="<?php echo h($artifact['Title']); ?>" />

      <label for="type">Type</label>
      <select name="type" id="type">
        <?php 
          $type_id = $artifact['type_id'];
          require_once(SHARED_PATH . '/artifact_type_options.php'); 
        ?>
      </select>

      <label for="Acq">Tracking Start Date</label>
      <input type="date" name="Acq" id="Acq" value="<?php echo h($artifact['Acq']); ?>" />

      <label for="KeptCol" >Tracked? (Checked means yes)</label>
      <input type="hidden" name="KeptCol" value="0" />
      <input type="checkbox" name="KeptCol" id="KeptCol" value="1"<?php if($artifact['KeptCol'] == "1") { echo " checked"; } ?> />

      <label for="to_get_rid_of">To Get Rid Of? (Checked means yes)</label>
      <input type="hidden" name="to_get_rid_of" value="0" />
      <input type="checkbox" name="to_get_rid_of" id="to_get_rid_of" value="1"<?php if($artifact['to_get_rid_of'] == "1") { echo " checked"; } ?> />

      <label for="interaction_frequency_days">Interaction Frequency (Days)</label>
      <input type="number" step="0.1" name="interaction_frequency_days" id="interaction_frequency_days"
        onwheel="this.blur()"
        value="<?php 
          if ($artifact['interaction_frequency_days'] === null) {
            echo $default_interval; 
          } else {
            echo h($artifact['interaction_frequency_days']); 
          }
          ?>"
      >

      <label for="SS">Sweet Spot(s)</label>
      <input type="text" name="SS" id="SS" value="<?php echo $artifact['SS']; ?>">

      <?php 
      if (SWEET_SPOT_BUTTONS_ON == true) {
        ?>
        <section id="sweetSpots">
          <?php
          $i = 0;
          foreach ($sweetSpotsResultObject as $row) {
            ?>
            <div>
              <input 
                class="sweetSpot"
                type="number" 
                name="SwS[<?php echo $i; ?>]" 
                id="SS<?php echo $row['id']; ?>" 
                value="<?php echo $row['SwS']; ?>"
              >
              <button class="sweetSpot">-</button>
            </div>
            <?php
            $i++;
          }
          ?>
        </section>
        <button 
          id="addSweetSpot"
          class="sweetSpot"
          style="display: block;"
          >
          +
        </button>
        <?php
      }
      ?>

      <script defer src="edit.js?v=2"></script>

      <label for="MnP">Minimum User Count</label>
      <input type="number" name="MnP" id="MnP" value="<?php echo $artifact['MnP']; ?>">

      <label for="MxP">Maximum User Count</label>
      <input type="number" name="MxP" id="MxP" value="<?php echo $artifact['MxP']; ?>">

      <label for="MnT">Minimum Time</label>
      <input type="number" name="MnT" id="MnT" value="<?php echo $artifact['MnT']; ?>">

      <label for="MxT">Maxiumum Time</label>
      <input type="number" name="MxT" id="MxT" value="<?php echo $artifact['MxT']; ?>">

      <label for="age">Minimum Age</label>
      <input type="number" name="age" id="age" value="<?php echo $artifact['Age']; ?>">

      <label for="InSecondaryCollection" >Kept in Secondary Collection? (Checked means yes)</label>
      <input type="checkbox" name="InSecondaryCollection" id="InSecondaryCollection" value="yes" 
        <?php if($artifact['InSecondaryCollection'] == "yes") { echo " checked"; } ?>
      />
      
      <?php 
      if (!isset($artifact['Notes'])) { 
        $artifact['Notes'] = '';
      }
      ?>

      <label for="Notes">Notes</label>
      <textarea 
        name="Notes" 
        id="Notes" 
        cols="30" 
        rows="10"
        ><?php echo h($artifact['Notes']); ?></textarea>

      <input type="submit" value="Save Edits" />
    </form>

  </div>

  <section id="oneToManyUsesList">
    <?php
      $usesOfArtifactByUserResultObject = find_one_to_many_uses_by_artifact_id($artifact['id']);
    ?>
    <h2>
      You have recorded
      <?php echo $usesOfArtifactByUserResultObject->num_rows; ?> 
      one to many uses of
      <?php echo h($artifact['Title']); ?>
    </h2>
    <table>
      <tr>
        <th>Interaction Date (<?php echo $usesOfArtifactByUserResultObject->num_rows; ?>)</th>
      <tr>
      <?php 
        foreach ($usesOfArtifactByUserResultObject as $usesOfArtifactByUserArray) { 
          ?>        
          <tr>
            <td>
              <a href="/uses/1-n-edit.php?id=<?php echo $usesOfArtifactByUserArray['id']; ?>">
                <?php echo $usesOfArtifactByUserArray['use_date']; ?>
              </a>
            </td>
          </tr>
          <?php 
        } 
      ?>
    </table>
  </section>

  <section id="recordedUseList">
    <?php
      $usesOfArtifactByUserResultObject = find_one_to_one_uses_by_artifact_id($artifact['id']);
    ?>
    <h2>
      You have recorded
      <?php echo $usesOfArtifactByUserResultObject->num_rows; ?> 
      one to one uses of
      <?php echo h($artifact['Title']); ?>
    </h2>
    <table>
      <tr>
        <th>Interaction Date (<?php echo $usesOfArtifactByUserResultObject->num_rows; ?>)</th>
        <th>Person</th>
      <tr>
      <?php foreach ($usesOfArtifactByUserResultObject as $usesOfArtifactByUserArray) { ?>        
        <tr>
          <td>
            <a href="/uses/edit.php?id=<?php echo $usesOfArtifactByUserArray['id']; ?>">
              <?php echo $usesOfArtifactByUserArray['PlayDate']; ?>
            </a>
          </td>
          <td>
            <?php 
              echo 
                $usesOfArtifactByUserArray['FirstName'] . 
                " " . 
                $usesOfArtifactByUserArray['LastName']
              ; 
            ?>
          </td>
        </tr>
      <?php } ?>
    </table>
  </section>

  <p id="deleteArtifact">
    <a class="action" href="<?php echo url_for('/artifacts/delete.php?id=' . h(u($_REQUEST['id']))); ?>">
      Delete 
      <?php echo h($artifact['Title']); ?>
    </a>
  </p>

</main>

<script>
  let editForm = document.querySelector('#editForm');

  function toggleEditFormDisplay() {
    if (editForm.style.display == 'none') {
        editForm.style.display = 'block';
    } else {
        editForm.style.display = 'none';
    }
  }

  let editFormDisplayButton = document.querySelector('#editFormDisplayButton');

  editFormDisplayButton.addEventListener('click', toggleEditFormDisplay);
</script>

<?php include(SHARED_PATH . '/footer.php'); ?>
