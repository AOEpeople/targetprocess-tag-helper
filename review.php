<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helper/reviewHelper.php';
require_once __DIR__ . '/helper/targetProcessHelper.php';
require_once __DIR__ . '/helper/fileHelper.php';


$teamId = $_GET['teamid'];

//gets the team iteration IDs
$targetProcessHelper = new TargetProcessHelper($configuration);
$teamIterations = $targetProcessHelper->getTeamIterationCollectionByTeamId($teamId);

//reads userStories and bugs
$userStories = $targetProcessHelper->getInformationForTeamIterationId($teamIterations, true);
$bugs = $targetProcessHelper->getInformationForTeamIterationId($teamIterations, false);

//formats the information into a list
$reviewOutput = new ReviewHelper($configuration);

//direct output
$both = $reviewOutput->generateOutputForEntities($userStories, $bugs);
echo "{$both}";