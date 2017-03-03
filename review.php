<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helper/reviewHelper.php';
require_once __DIR__ . '/helper/targetProcessHelper.php';
require_once __DIR__ . '/helper/fileHelper.php';


$targetProcessHelper = new TargetProcessHelper($configuration);
$teamId = $_GET['teamid'];

//gets the team iteration IDs for sprint#11
$teamIterations = $targetProcessHelper->getTeamIterationCollectionByTeamId($teamId);
$teamIteration = $teamIterations['Items'][11];
$teamIterationId = $teamIteration['Id'];

//reads userStories and bugs for that sprint
$userStories = $targetProcessHelper->getInformationForTeamIterationId($teamIterationId, true);
$bugs = $targetProcessHelper->getInformationForTeamIterationId($teamIterationId, false);

//formats the information into a list
$reviewOutput = new ReviewHelper($configuration);
$userStories = $reviewOutput->generateOutputForEntities($userStories);
$bugs = $reviewOutput->generateOutputForEntities($bugs);

//writes content into "User Stories" and "Bug" text file
$fileHelper = new FileHelper();
$fileHelper->writeFile($userStories, $bugs);

//optional direct input with seperating row between both
echo "{$userStories}";
echo " || ||{color:#CD6600} *BUGS* {color}|| || || || || ||<br>";
echo "{$bugs}";
