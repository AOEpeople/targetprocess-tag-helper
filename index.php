<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helper/reviewHelper.php';
require_once __DIR__ . '/helper/targetProcessHelper.php';
require_once __DIR__ . '/helper/fileHelper.php';

$teamId = $_GET['teamId'];
if (!$teamId)
    $teamId = $configuration['teamId'];

//gets the team iteration IDs
$targetProcessHelper = new TargetProcessHelper($configuration);
$teamIterations = $targetProcessHelper->getTeamIterationCollectionByTeamId($teamId);

//reads userStories and bugs
$userStories = $targetProcessHelper->getInformationForTeamIterationId($teamIterations, true);
$bugs = $targetProcessHelper->getInformationForTeamIterationId($teamIterations, false);

//formats the information into a list
$reviewOutput = new ReviewHelper($configuration);

//direct output
$information = $reviewOutput->generateOutputForEntities($userStories, $bugs);

$information = str_replace("|https:/", "replaceme1", $information);
$information = str_replace("|title=", "replaceme2", $information);
$information = str_replace("|subtle=", "replaceme3", $information);

$information = str_replace("||<br><br>", "</tr><tr>", $information);
$information = str_replace("||<br>", "</th></tr><tr>", $information);
$information = str_replace("||", "</th><th>", $information);

$information = str_replace("|<br>", "</td></tr><tr>", $information);
$information = str_replace("|", "</td><th>", $information);

$information = str_replace("<br><br>", "</table><br><br><table border=\"1\">", $information);

$information = str_replace("replaceme1","|https:/", $information);
$information = str_replace("replaceme2","|title=", $information);
$information = str_replace("replaceme3","|subtle=",  $information);

print("<table border=\"1\">");
print($information);