<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helper/targetProcessHelper.php';
require_once __DIR__ . '/helper/reviewHelper.php';
require_once __DIR__ . '/helper/reviewPageHelper.php';

$teamId = isset($_GET['teamId']) ? $_GET['teamId'] : $configuration['teamId'];
$sprintId = isset($_GET['sprintId']) ? $_GET['sprintId'] : "";
$sprintName = isset($_GET['sprintName']) ? $_GET['sprintName'] : "";


$targetProcessHelper = new TargetProcessHelper($configuration);

if ($sprintId) {
    $teamIterations = [['Id' => $sprintId, 'Name' => $sprintName]];
} else {
    $teamIterations = $targetProcessHelper->getTeamIterationCollectionByTeamId($teamId);
}

$informationArray = $targetProcessHelper->getInformationForTeamIterationId($teamIterations);

//formats the information into a list
$information = [];
$reviewOutput = new ReviewHelper($configuration);
foreach ($informationArray as $sprint => $info) {
    $information = $reviewOutput->generateOutputForEntities($info);

    //direct output
    $pagehelper = new ReviewPageHelper($configuration);
    $pagehelper->printArray($information);
}