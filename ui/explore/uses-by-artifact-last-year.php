<?php 

require_once('../../private/initialize.php');

require_login_or_guest();

$page_title = 'Uses By Artifact Over Last 365 Days';

include(SHARED_PATH . '/header.php');
include(SHARED_PATH . '/dataTable.html'); 

$player_id = (int) $_SESSION['player_id'];
$stmt1 = mysqli_prepare($db, "SELECT
  COUNT('responses.PlayDate') AS CountOfUses,
  games.Title AS ArtifactTitle,
  responses.Title AS ArtifactID,
  games.type AS ArtifactType
  FROM responses
  JOIN games ON games.id = responses.Title
  WHERE responses.Player = ?
  AND responses.PlayDate > DATE_SUB(NOW(), INTERVAL 365 DAY)
  GROUP BY responses.Title
  ORDER BY CountOfUses DESC");
mysqli_stmt_bind_param($stmt1, "i", $player_id);
mysqli_stmt_execute($stmt1);
$usesSingleByPlayerResultObject = mysqli_stmt_get_result($stmt1);

// get counts from multi use table
$usesMultiByPlayerResultObjec = mysqli_query($db, "SELECT title,
    COUNT(artifact_id) AS uses, 
    artifact_id,
    type,
    MIN(use_date) AS mostRecentUse,
    MAX(use_date) AS mostDistantUse
  FROM uses
  JOIN games ON games.id = uses.artifact_id
  WHERE use_date >= DATE_SUB(NOW(), INTERVAL 365 DAY)
  GROUP BY artifact_id
  ORDER BY uses DESC,
    mostRecentUse DESC,
    mostDistantUse DESC
");

// find the last letter of the name
// and set fitting punctuation
if (substr($_SESSION['FullName'], -1, 1) == 's') {
  $possessivePunctuation = "' ";
} else {
  $possessivePunctuation = "'s ";
}

$singleAndMultiUsesArray = [];

$i = 0;
foreach ($usesSingleByPlayerResultObject as $usesSingleByPlayerArray) { 

  $singleAndMultiUsesArray[$i]['artifact_title'] = $usesSingleByPlayerArray['ArtifactTitle'];
  $singleAndMultiUsesArray[$i]['artifact_id'] = $usesSingleByPlayerArray['ArtifactID'];
  $singleAndMultiUsesArray[$i]['artifact_uses'] = $usesSingleByPlayerArray['CountOfUses'];
  $singleAndMultiUsesArray[$i]['artifact_type'] = $usesSingleByPlayerArray['ArtifactType'];
  
  foreach ($usesMultiByPlayerResultObjec as $usesMultiByPlayerArray) {

    if ($usesMultiByPlayerArray['artifact_id'] == $usesSingleByPlayerArray['ArtifactID']) {
      $singleAndMultiUsesArray[$i]['artifact_uses'] += $usesMultiByPlayerArray['uses'];
    }
    
  }
  
  $i++;
}

function recursive_in_array($needle, $haystack) {
  foreach ($haystack as $item) {
      if (is_array($item)) {
          if (recursive_in_array($needle, $item)) {
              return true;
          }
      } else {
          if ($item === $needle) {
              return true;
          }
      }
  }
  return false;
}

foreach ($usesMultiByPlayerResultObjec as $usesMultiByPlayerArray) {

  if (!recursive_in_array($usesMultiByPlayerArray['artifact_id'], $singleAndMultiUsesArray)) {
    $newIndex = count($singleAndMultiUsesArray) + 1;

    $singleAndMultiUsesArray[$newIndex]['artifact_title'] = $usesMultiByPlayerArray['title'];
    $singleAndMultiUsesArray[$newIndex]['artifact_id'] = $usesMultiByPlayerArray['artifact_id'];
    $singleAndMultiUsesArray[$newIndex]['artifact_uses'] = $usesMultiByPlayerArray['uses'];
    $singleAndMultiUsesArray[$newIndex]['artifact_type'] = $usesMultiByPlayerArray['type'];
  
  }

}

?>


<script defer src="uses-by-artifact-last-year.js"></script>

<main>
  
  <h1>
    <?php echo $_SESSION['FullName'] . $possessivePunctuation . $page_title; ?>
  </h1>

  <a href="uses-by-artifact.php">
    <p>All Uses By Artifact</p>
  </a>

  <table id='usesByArtifact'>

    <thead>
      <tr>
        <th>Uses</th>
        <th>Artifact</th>
        <th>Type</th>
      </tr>
    </thead>
    
    <tbody>
      <?php 
        $i = 1;
        foreach ($singleAndMultiUsesArray as $usesByPlayerArray) { 
          ?>
          <tr>
            <td>
              <?php echo $usesByPlayerArray['artifact_uses']; ?>
            </td>
            <td>
              <a href="/artifacts/edit.php?id=<?php echo $usesByPlayerArray['artifact_id']; ?>">
                <?php echo $usesByPlayerArray['artifact_title']; ?>
              </a>
            </td>
            <td>
              <?php echo $usesByPlayerArray['artifact_type']; ?>
            </td>
          </tr>
          <?php 
          $i++;
        } 
      ?>
    </tbody>

  </table>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>