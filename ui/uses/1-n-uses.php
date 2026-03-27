<?php 
  require_once('../../private/initialize.php');
  require_login_or_guest();
  $page_title = 'Entity Interactions';
  include(SHARED_PATH . '/header.php');
  include(SHARED_PATH . '/dataTable.html');

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['type'])) {
      $type = $_POST['type'];
    } else {
      $type = [];
    }
  } else {
    if (isset($_SESSION['type']) && count($_SESSION['type']) > 0) {
      $type = $_SESSION['type'];
    } else {
      include(SHARED_PATH . '/artifact_type_array.php'); 
      global $typesArray;
      $type = $typesArray;
    }
  }

  $minimumDate = $_POST['minimumDate'] ?? '';
  $showAttributes = $_POST['showAttributes'] ?? 'no';
  $hide_duplicate_group_settings = $_POST['hide_duplicate_group_settings'] ?? 'no';
  $hide_online_setting = $_POST['hide_online_setting'] ?? 'no';

  $use_set = find_uses_by_user_id($type, $minimumDate);
?>

<script defer src="/shared/filter_button.js"></script>

<main>
    <div style="display: flex;
        justify-content: space-between;"
        >
        <h1><?php echo $page_title; ?></h1>
        <button id="display_filters" style="display: block">Show filters</button>
    </div>

    <form method="POST" style="display: none">
      <?php echo csrf_input(); ?>
      <label for="artifactType">Artifact type</label>
      <section id="artifactType" style="display: flex; flex-wrap: wrap">
        <?php require_once SHARED_PATH . '/artifact_type_checkboxes.php'; ?>
      </section>

      <label for="minimumDate">Minimum Date (<?php echo DEFAULT_USE_INTERVAL; ?> days ago: <?php echo date('m/d/Y', strtotime(DEFAULT_USE_INTERVAL . ' days ago')); ?>)</label>
      <input type="date" name="minimumDate" id="minimumDate" value="<?php echo $minimumDate; ?>">

      <label for="showAttributes">Show artifact attributes</label>
      <input type="hidden" name="showAttributes" value="no">
      <input type="checkbox" name="showAttributes" id="showAttributes" value="yes"
        <?php 
          if ($showAttributes === 'yes') {
            echo ' checked ';
          }
        ?>
      >
      
      <label for="hide_duplicate_group_settings">Hide Duplicate Group Settings (Date Agnostic)</label>
      <input name="hide_duplicate_group_settings" 
        type="checkbox"
        id="hide_duplicate_group_settings" 
        value="yes"
        <?php if ($hide_duplicate_group_settings === 'yes') { echo ' checked '; } ?>
      >

      <label for="hide_online_setting">Hide Online Setting</label>
      <input name="hide_online_setting" 
        type="checkbox"
        id="hide_online_setting" 
        value="yes"
        <?php if ($hide_online_setting === 'yes') { echo ' checked '; } ?>
      >

      <button type="submit">Submit</button>
    </form>

  	<table class="list" id="uses" data-page-length='100'>
      <thead>
        <tr id="headerRow">
          <th>Interaction Date <?php if ($hide_duplicate_group_settings === 'no') { echo '(' . $use_set->num_rows . ')'; } ?></th>
          <th>Entity</th>
          <th class="group_setting">Group Setting</th>
          <th>Type</th>
          <th>Setting</th>
          <?php
            if ($showAttributes === 'yes') {
              ?>
              <th>Candidate</th>
              <th>SwS</th>
              <th>User Count</th>
              <th>Candidate Spot Used</th>
              <?php
            }
          ?>
        </tr>
      </thead>

      <tbody>
        <?php 
          $group_setting_game_array = array();
          $group_and_setting_array = array();

          while($use = mysqli_fetch_assoc($use_set)) { 

            // Hide online setting uses
            if ($hide_online_setting === 'yes') {
              if (h($use['note']) === 'online') {
                continue;
              }
            }

            $usersResultObject = find_users_by_use_id($use['useID']);

            $i = 0;
            $situation = '';
            if ($usersResultObject->num_rows < 10) {
              $situation .= '0';
            }
            
            $situation .= $usersResultObject->num_rows . ': ';
            
            $usersArray = [];
            foreach ($usersResultObject as $user) {
              $usersArray[$user['id']] = $user['FirstName'] . ' ' . $user['LastName'];
            }

            // sort by the key ascending
            ksort($usersArray);

            $i = 0;
            foreach ($usersArray as $user) {
              $i++;
              $situation .= $user;
              if ($i != $usersResultObject->num_rows) {
                $situation .= ', ';
              }
            }

            if ($use['note'] != 'online') {
              $situation .= ' at';
            }

            $situation .= ' ' . $use['note'];

            $group_and_setting = $situation;
            if (!in_array($group_and_setting, $group_and_setting_array)) {
              $group_and_setting_array[] = $group_and_setting;
            }

            $situation .= ' (';
            $situation .= h($use['Title']);

            $group_setting_game = $situation;

            if (!in_array($group_setting_game, $group_setting_game_array)) {
              $group_setting_game_array[] = $group_setting_game;
            } elseif ($hide_duplicate_group_settings === 'yes') {
              continue;
            }

            $group_setting_game_escaped = db_escape($db, $group_setting_game);

            $query = "SELECT title 
              FROM games WHERE candidate LIKE '$group_setting_game_escaped%'
            ";
      
            $candidate_artifact = singleValueQuery($query);

            $situation .= ' on ' . h(substr($use['use_date'],0,10));
            $situation .= ')';
          
            ?>
            <tr>
              <td class="date">
                <?php if (!is_guest()) { ?>
                <a
                  class="action"
                  href="<?php echo url_for('/uses/1-n-edit.php?id=' . h(u($use['useID']))); ?>"
                  >
                  <?php echo h(substr($use['use_date'],0,10)); ?>
                </a>
                <?php } else { echo h(substr($use['use_date'],0,10)); } ?>
              </td>

              <td class="title">
                <a
                  class="action"
                  href="<?php echo url_for('/artifacts/' . (is_guest() ? 'show' : 'edit') . '.php?id=' . h(u($use['gameID']))); ?>"
                  >
                  <?php echo h($use['Title']); ?>
                </a>
              </td>

              <td class="group_setting">
                <?php echo $situation; ?>
              </td>
                          
              <td class="type">
                <?php echo h($use['type']); ?>
              </td>

              <td class="setting">
                <?php echo h($use['note']); ?>
              </td>

              <?php
                if ($showAttributes === 'yes') {
                  ?>
                  <td class="candidate">
                    <?php 
                      if (h($use['Candidate']) != '' && h($use['Candidate']) != 0) {
                        echo 'Yes';
                      } 
                    ?>
                  </td>
    
                  <td class="sweet_spot">
                    <?php echo h($use['SwS']); ?>
                  </td>
    
                  <td class="user_count">
                    <?php echo $usersResultObject->num_rows; ?>
                  </td>
    
                  <td class="canidate_spot_used"><?php if ($candidate_artifact != 'No results') {echo "$candidate_artifact";} ?></td>
                  <?php
                }
              ?>
              
            </tr>
            <?php 
          } 
        ?>
      </tbody>
  	</table>

    <p>Group, setting, and game combinations: <?php echo count($group_setting_game_array); ?></p>
    <p>Group and setting combinations: <?php echo count($group_and_setting_array); ?></p>

    <?php mysqli_free_result($use_set); ?>

    <script>


      <?php
      if ($hide_duplicate_group_settings === 'yes' || $hide_online_setting === 'yes') {
        ?>
        document.querySelector('h1').innerText += ' (<?php echo count($group_setting_game_array); ?> group settings)';
        document.querySelector('.group_setting').innerText += ' (<?php echo count($group_setting_game_array); ?>)';
        let table = new DataTable('#uses', {
          // options
          order: [
            [ 5, 'desc'], // User group descending
            [ 0, 'desc'] // Most recently used first
          ]
        });
        <?php
      } else {
        ?>
        let table = new DataTable('#uses', {
          // options
          order: [
            [ 0, 'desc'], // Most recently used ascending
            [ 2, 'asc'] // smaller user groups first
          ]
        });
        <?php
      }
      ?>
    </script>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
