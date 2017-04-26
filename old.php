<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helper/targetProcessHelper.php';
require_once __DIR__ . '/helper/reviewHelper.php';
require_once __DIR__ . '/helper/reviewPageHelper.php';

$teamId = $_GET['teamId']?: $configuration['teamId'];

$targetProcessHelper = new TargetProcessHelper($configuration);
$teamIterations = $targetProcessHelper->getTeamIterationCollectionByTeamId($teamId);
$informationArray = $targetProcessHelper->getInformationForTeamIterationId($teamIterations);

//formats the information into a list
$information = [];
$reviewOutput = new ReviewHelper($configuration);
foreach ($informationArray as $sprint => $info) {
    $information = $reviewOutput->generateOutputForEntities($info);

//direct output
    $pagehelper = new ReviewPageHelper();
    $pagehelper->printArrayAsHtml($information);
}