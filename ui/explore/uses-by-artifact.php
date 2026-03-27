<?php 

  require_once('../../private/initialize.php');

  require_login_or_guest();

  $page_title = 'Uses By Artifact';

  include(SHARED_PATH . '/header.php');

  if ($_SESSION['player_id'] == '') {
    echo 'Go to users, choose yourself, ensure "This user is me" is checked, submit the form, then log out and log back in.';
  }

  $player_id = (int) $_SESSION['player_id'];
  $stmt = mysqli_prepare($db, "SELECT
    COUNT('responses.PlayDate') AS CountOfUses,
    games.Title AS ArtifactTitle,
    responses.Title AS ArtifactID
    FROM responses
    JOIN games ON games.id = responses.Title
    WHERE responses.Player = ?
    GROUP BY responses.Title
    ORDER BY CountOfUses DESC");
  mysqli_stmt_bind_param($stmt, "i", $player_id);
  mysqli_stmt_execute($stmt);
  $usesByPlayerResultObject = mysqli_stmt_get_result($stmt);

  // find the last letter of the name
  // and set fitting punctuation
  if (substr($_SESSION['username'], -1, 1) == 's') {
    $possessivePunctuation = "' ";
  } else {
    $possessivePunctuation = "'s ";
  }

?>

<main>
  
  <h1>
    <?php echo $_SESSION['username'] . $possessivePunctuation . $page_title; ?>
  </h1>

  <a href="uses-by-artifact-last-year.php">
    <p>Uses over last 365 days</p>
  </a>

  <table>

    <tr>
      <th>Uses</th>
      <th>Artifact</th>
    </tr>

    <?php foreach ($usesByPlayerResultObject as $usesByPlayerArray) { ?>
      <tr>
        <td>
          <?php echo $usesByPlayerArray['CountOfUses']; ?>
        </td>
        <td>
          <a href="/artifacts/edit.php?id=<?php echo $usesByPlayerArray['ArtifactID']; ?>">
            <?php echo $usesByPlayerArray['ArtifactTitle']; ?>
          </a>
        </td>
      </tr>
    <?php } ?>

  </table>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>