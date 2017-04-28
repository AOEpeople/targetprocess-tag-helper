<meta charset="UTF-8">

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helper/targetProcessHelper.php';
require_once __DIR__ . '/helper/reviewHelper.php';
require_once __DIR__ . '/helper/reviewPageHelper.php';

$date = getdate();
$thisMonth = $date['year'] . '-' .  $date['mon'] . '-' . '1';
$nextMonth = $date['year'] . '-' .  (string)($date['mon'] %12 + 1) . '-' . '1';

?>
    <html>
        <body>
            <form action="old.php" method="post">
                Don't like the new page? <input type="submit" value="Go back to the old one">
            </form>

            <form action="index.php" method="post">
<?php

$team = isset($_POST['team']) ? $_POST['team'] : isset($configuration['defaultTeamName']) ? $configuration['defaultTeamName'] : "";
$project = isset($_POST['project']) ? $_POST['project'] : isset($configuration['defaultProjectName']) ? $configuration['defaultProjectName'] : "";
$from = isset($_POST['from']) ? $_POST['from'] : $thisMonth;
$to = isset($_POST['to']) ? $_POST['to'] : $nextMonth;
$release = !isset($_POST['group']) || $_POST['group'] == 'Release' ? true : false;
$forFilter = $release ? 'Release' : 'TeamIteration';

?>
        Team: <input type="text" name="team" value="<?php echo $team ?>"><br>
        Project: <input type="text" name="project" value="<?php echo $project ?>"><br>
        From: <input type="text" name="from" value="<?php echo $from ?>"><br>
        To: <input type="text" name="to" value="<?php echo $to ?>"><br>
        Releases <input type="radio" name="group" value="Release" <?= $release ? "checked" : "" ?>><br>
        Team Iterations <input type="radio" name="group" value="TeamIteration" <?= !$release ? "checked" : "" ?>><br>
    <input type="submit">
</form>
    <?php

$filter = "";
$assignables = [];

if (isset($_POST['group'])) {
    $filter = "?where=(Team.Name eq '" . $team . "') and (Project.Name eq '" . $project . "') and (" . $forFilter . ".StartDate gte '" . $from . "') and (" . $forFilter . ".EndDate lte '" . $to . "')";

    $filter = str_replace('#', '%23', $filter);
    $filter = str_replace(' ', '%20', $filter);
    //gets Assignables
    $targetProcessHelper = new TargetProcessHelper($configuration);
    $assignables = $targetProcessHelper->getAssignables($filter);

    $informationArray['Name'] = $from . ' to ' . $to;

    foreach ($assignables as $key => $entity) {
        if ($configuration['stateFilter'] == [] || in_array($entity['EntityState']['Name'], $configuration['stateFilter'])) {
            if ($entity['EntityType']['Name'] == 'UserStory' || $entity['EntityType']['Name'] == 'Request') {
                $informationArray['UserStories'][] = $entity;
            }
            if ($entity['EntityType']['Name'] == 'Bug') {
                $informationArray['Bugs'][] = $entity;
            }
        }
    }

    //formats the information into a list
    $reviewOutput = new ReviewHelper($configuration);
    $information = $reviewOutput->generateOutputForEntities($informationArray);

    //direct output
    $pagehelper = new ReviewPageHelper($configuration);
    $pagehelper->printArray($information);

}




