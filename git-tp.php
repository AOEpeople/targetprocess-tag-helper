<?php

if (!class_exists('SesClient')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/config.php';

require_once __DIR__ . '/helper/targetProcessHelper.php';
require_once __DIR__ . '/helper/emailHelper.php';
require_once __DIR__ . '/helper/hashHelper.php';
require_once __DIR__ . '/helper/gitTpHelper.php';


if (isset($argv[1]) && $argv[1] == 'help') {
    echo <<<USAGE
Usage:  php git-tp.php getStartHash <version.txt url>
        php git-tp.php getEndHash <version.txt url>
        php git-tp.php addTpTag <tag>
        php git-tp.php sendChangelog <tag> <email-address-separated-by-komma>
        
  help                          Prints this help

USAGE;
    die;
}

$tpHelper= new GitTpHelper($configuration);
$tpHelper->run($argv);
