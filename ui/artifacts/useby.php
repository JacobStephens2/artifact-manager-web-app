<?php  // require login
  require_once('../../private/initialize.php');
  require_login_or_guest();
?>

<?php // load header
  $page_title = 'Interact By';
  include(SHARED_PATH . '/header.php');
  include(SHARED_PATH . '/dataTable.html'); 
?>
<script defer src="/shared/filter_button.js"></script>
<script defer src="useby.js?v=3"></script>

<?php // process form submission and initialize variables
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

  $user_id = $_SESSION['user_id'];
  $_SESSION['type'] = $type;
  $sweetSpot = $_POST['sweetSpot'] ?? '';
  $minimumAge = $_POST['minimumAge'] ?? 0;
  $shelfSort = $_POST['shelfSort'] ?? 'no';
  $showAttributes = $_POST['showAttributes'] ?? 'no';
  $typeArray = $_SESSION['type'] ?? [];
  $default_use_interval = singleValueQuery("SELECT default_use_interval
    FROM users
    WHERE id = '$user_id'
  ");
  $interval = $_POST['interval'] ?? $default_use_interval;
  $artifact_set = use_by($type, $interval, $sweetSpot, $minimumAge, $shelfSort);
  $total_overdue = 0;
?>

<main>

  <meta id="apiOrigin" content="<?php echo API_ORIGIN; ?>">

  <div style="display: flex;
    justify-content: space-between;"
    >
    <h1>
      <a class="hideOnPrint" target="_blank"
        href="<?php echo url_for('/objects/about-useby.php'); ?>"
        >
        Interact with by date
      </a>
    </h1>
  
    <?php if (!is_guest()) { ?><button id="send_use_email" data-userid="<?php echo $user_id; ?>">Send Interact Email</button><?php } ?>
    <button id="display_filters" style="display: block">Show filters</button>
  </div>

  <form action="<?php echo url_for('/artifacts/useby.php'); ?>"
    method="post"
    style="display: none"
    >
    <?php echo csrf_input(); ?>
    <div class="hideOnPrint">

      <label for="artifactType">Artifact type</label>
      <section id="artifactType" style="display: flex; flex-wrap: wrap">
        <?php require_once SHARED_PATH . '/artifact_type_checkboxes.php'; ?>
      </section>

      <label for="sweetSpot">Sweet Spot</label>
      <input type="number" name="sweetSpot" id="sweetSpot" value="<?php echo $sweetSpot; ?>">

      <label for="minimumAge">Minimum Age</label>
      <input type="number" name="minimumAge" id="minimumAge" value="<?php echo $minimumAge; ?>">
      
      <label for="shelfSort">Shelf Sort (Instead of Interact By Sort)</label>
      <input type="hidden" name="shelfSort" value="no">
      <input type="checkbox" name="shelfSort" id="shelfSort" value="yes"
        <?php 
          if ($shelfSort === 'yes') {
            echo ' checked ';
          }
        ?>
      >
      
      <label for="showAttributes">Show artifact attributes</label>
      <input type="hidden" name="showAttributes" value="no">
      <input type="checkbox" name="showAttributes" id="showAttributes" value="yes"
        <?php 
          if ($showAttributes === 'yes') {
            echo ' checked ';
          }
        ?>
      >

    </div>

    <div class="displayOnPrint">
      <label for="interval">Interval in days from most recent or to upcoming use</label>
      <input type="number" step="0.1" name="interval" id="interval" value="<?php echo $interval ?>">
    </div>
    
    <input type="submit" value="Submit" class="hideOnPrint"/>
  
    <section id="legend">
      <p>U stands for used at recommended user count or used fully through at non-recommended count</p>
    </section>
  </form>

  <p class="copied_message" style="display: none"></p>

  <table id="useBy" class="list" data-page-length='100'>
    <thead>
      <tr id="headerRow">
        <th>Name (<?php echo $artifact_set->num_rows; ?>)</th>
        <th>Interact By</th>
        <th>Type</th>
        <?php
          if ($showAttributes === 'yes') {
            ?>
            <?php if (!is_guest()) { ?><th>Record</th><?php } ?>
            <th>SwS</th>
            <th>AvgT</th>
            <th>Age</th>
            <th>SwS's</th>
            <th>MnP</th>
            <th>MxP</th>
            <th>C</th>
            <?php
          } else {
            ?>
            <?php if (!is_guest()) { ?><th>Record Interaction</th><?php } ?>
            <?php
          }
        ?>
        <?php if (!is_guest()) { ?><th class="hideOnPrint">Get Rid Of</th><?php } ?>
        <th>Overdue (<span id="totalOverdue"></span>)</th>
        <th class="hideOnPrint">Recent Interaction</th>
        <th>Tracking Start</th>
        <th>Interval</th>
      </tr>
    </thead>

    <tbody>
      <?php while($artifact = mysqli_fetch_assoc($artifact_set)) { 
        $id = h(u($artifact['id']));
        if ($artifact['interaction_frequency_days'] !== null) {
          $this_interval = $artifact['interaction_frequency_days'];
        } else {
          $this_interval = $interval;
        }
        ?>
        <tr>
          <td class="name artifact edit">
            <div>
              <a id="artifact_id_<?php echo $id; ?>"
                class="action edit"
                href="<?php echo url_for('/artifacts/' . (is_guest() ? 'show' : 'edit') . '.php?id=' . $id); ?>"
                ><?php echo h($artifact['Title']);
              ?></a>
              </a>
              <img class="clipboard"
                id="artifact_id_copy_<?php echo $id; ?>"
                src="/assets/copy.png"
                alt="A clipboard icon for copying"
              >

              <script>
                document
                  .querySelector('img#artifact_id_copy_<?php echo $id; ?>')
                  .addEventListener('click', function() {
                    let text = document.querySelector('a#artifact_id_<?php echo $id; ?>').innerHTML;
                    navigator.clipboard.writeText(text);
                    var copied_message = document.querySelector('p.copied_message');
                    copied_message.innerText = text + ' copied';
                    copied_message.style.display = 'block';
                    setTimeout(() => {
                      copied_message.innerText = '';
                      copied_message.style.display = 'none';
                    }, 1500);
                  }
                );

              </script>
            </div>
          </td>

          <?php
              date_default_timezone_set('America/New_York');
              $DateTimeNow = new DateTime(date('Y-m-d'));
              $DateTimeMostRecentUse = new DateTime(substr($artifact['MostRecentUseOrResponse'],0,10));
              $DateTimeAcquisition = new DateTime(substr($artifact['Acq'],0,10));

              $intervalInHours = $this_interval * 24;

              if ($DateTimeMostRecentUse < $DateTimeAcquisition || $artifact['MostRecentUseOrResponse'] === NULL) {
                $DateInterval = DateInterval::createFromDateString("$intervalInHours hour");
                $useByDate = date_add($DateTimeAcquisition, $DateInterval);
              } else {
                $doubledInterval = $intervalInHours * 2;
                $DateInterval = DateInterval::createFromDateString("$doubledInterval hour");
                $useByDate = date_add($DateTimeMostRecentUse, $DateInterval);
              }
          ?>

          <td class="useByDate date"><?php print_r($useByDate->format('Y-m-d')); ?></td>

          <td class="type"><?php echo h($artifact['type']); ?></td>


            <?php if (!is_guest()) { ?>
            <td class="record">
              <a href="/uses/1-n-new?artifact_id=<?php echo $id; ?>"
                target="_blank"
                >
                Record
              </a>
            </td>
            <?php } ?>

          <?php
          if ($showAttributes === 'yes') {
            ?>
            <td class="SwS">
              <?php
                // find the first number without leading zeros
                preg_match(
                  '/([1-9][0-9])|[1-9]/',
                  $artifact['ss'],
                  $match
                );
                echo h($match[0]);
              ?>
            </td>

            <td class="AvgT"><?php echo (h($artifact['mnt']) + h($artifact['mxt'])) / 2; ?></td>
            <td class="Age"><?php echo h($artifact['age']); ?></td>
            <td class="SwSs"><?php echo h($artifact['ss']); ?></td>
            <td class="MnP" ><?php echo h($artifact['mnp']); ?></td>
            <td class="MxP"><?php echo h($artifact['mxp']); ?></td>

            <td class="candidate">
              <?php
              if ( strlen($artifact['Candidate']) > 0 ) {
                echo 'Yes';
              }
              ?>
            </td>
            <?php
          }
          ?>

          <?php if (!is_guest()) { ?>
          <td class="get-rid-of hideOnPrint">
            <form method="post" action="<?php echo url_for('/artifacts/mark-get-rid-of.php'); ?>" style="display:inline; margin:0;">
              <?php echo csrf_input(); ?>
              <input type="hidden" name="artifact_id" value="<?php echo $id; ?>">
              <input type="hidden" name="artifact_name" value="<?php echo h($artifact['Title']); ?>">
              <input type="hidden" name="return_to" value="useby">
              <button type="submit" class="get-rid-of-btn">Get Rid Of</button>
            </form>
          </td>
          <?php } ?>

          <td class="overdue"
            <?php
                if ($useByDate < $DateTimeNow) {
                  echo 'style="color: red;"';
                }
            ?>
            >
            <?php
                if ($useByDate < $DateTimeNow) {
                  $total_overdue++;
                  echo 'Yes';
                } else {
                  echo 'No';
                }
              ?>
          </td>

          <td class="mostRecentUse date hideOnPrint">
            <?php echo h(substr($artifact['MostRecentUseOrResponse'],0,10)); ?>
          </td>

          <td class="acquisitionDate"><?php echo h($artifact['Acq']); ?></td>
          <td class="interval"><?php echo $this_interval; ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <?php mysqli_free_result($artifact_set); ?>
  <script>
    document.querySelector('span#totalOverdue').innerText = '<?php echo $total_overdue; ?>';
    let table = new DataTable('#useBy', {
      // options
      <?php
        if ($shelfSort === 'yes') {
          ?>
          order: [
            [ 2, 'asc'], // Type
            [ 4, 'asc'], // SwS
            [ 5, 'asc'], // AvgT
            [ 6, 'asc'], // Age
            [ 7, 'asc'], // SwS's
            [ 8, 'asc'], // MnP
            [ 9, 'asc'], // MxP
            [ 13, 'desc'], // recent use
            [ 10, 'desc'], // C
          ]
          <?php
        } elseif ($showAttributes === 'yes') {
          ?>
          order: [
            [ 1, 'asc'],  // interact by date
            [ 5, 'asc'],  // AvgT
            [ 6, 'asc'],  // Age
          ]
          <?php
        } else {
          ?>
          order: [
            [ 1, 'asc'],  // interact by date
            [ 6, 'asc'],  // recent use
            [ 7, 'asc'],  // acquisition date
          ]
          <?php
        }
      ?>
    });

    document.addEventListener('keypress', function(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        document.querySelector('form').submit();
      }
    })
  </script>

  <a href="https://www.flaticon.com/free-icons/copy" title="copy icons">Copy icons created by Anggara - Flaticon</a>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
