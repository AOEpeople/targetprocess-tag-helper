<?php

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

if (isset($_POST["team"])) {

    ?>
                Team: <input type="text" name="team" value="<?php echo $_POST['team'] ?>"><br>
                Project: <input type="text" name="project" value="<?php echo $_POST['project'] ?>"><br>
                From: <input type="text" name="from" value="<?php echo $_POST['from'] ?>"><br>
                To: <input type="text" name="to" value="<?php echo $_POST['to'] ?>"><br>
    <?php
    if ($_POST['group'] == 'Release') {
    ?>
                Releases <input type="radio" name="group" value="Release" checked><br>
                Team Iterations <input type="radio" name="group" value="TeamIteration"><br>
        <?php
    } else {
        ?>
                Releases <input type="radio" name="group" value="Release"><br>
                Team Iterations <input type="radio" name="group" value="TeamIteration" checked><br>
        <?php
    }
    ?>
                <input type="submit">
            </form>
        </body>
    </html>
    <?php
} else { // DEFAULT
    ?>
    <html>
        <body>
            <form action="index.php" method="post">
                Team: <input type="text" name="team" value="<?php echo "Stockmann" ?>"><br>
                Project: <input type="text" name="project" value="<?php echo "Stockmann eCom #4" ?>"><br>
                From: <input type="text" name="from" value="<?php echo $thisMonth ?>"><br>
                To: <input type="text" name="to" value="<?php echo $nextMonth ?>"><br>
                Releases <input type="radio" name="group" value="Release" checked><br>
                Team Iterations <input type="radio" name="group" value="TeamIteration"><br>
                <input type="submit">
            </form>
        </body>
    </html>
    <?php
}

$team = $_POST['team'];
$project = $_POST['project'];
$from = $_POST['from'];
$to = $_POST['to'];

$filter = "?where=(Team.Name eq '" . $team . "') and (Project.Name eq '" . $project . "') and (" . $_POST['group'] . ".StartDate gte '" . $from . "') and (" . $_POST['group'] . ".EndDate lte '" . $to . "')";
$filter = str_replace('#', '%23', $filter);
$filter = str_replace(' ', '%20', $filter);

//gets Assignables
$targetProcessHelper = new TargetProcessHelper($configuration);
$assignables = $targetProcessHelper->getAssignables($filter);

$informationArray['Name'] = $from . ' to ' . $to;

foreach ($assignables as $key => $entity) {
    if ($entity['EntityType']['Name'] == 'UserStory')
        $informationArray['UserStories'][] = $entity;
    else
        $informationArray['Bugs'][] = $entity;
}

//formats the information into a list
$reviewOutput = new ReviewHelper($configuration);
$information = $reviewOutput->generateOutputForEntities($informationArray);

//direct output
$pagehelper = new ReviewPageHelper();
$pagehelper->printArray($information);