<?php

if (!class_exists('SesClient')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/config.php';

require_once __DIR__ . '/helper/targetProcessHelper.php';
require_once __DIR__ . '/helper/emailHelper.php';
require_once __DIR__ . '/helper/hashHelper.php';


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

if (isset($argv[1])) {
    $hashHelper = new HashHelper();
    $tp = new TargetProcessHelper($configuration);
    switch ($argv[1]) {
        case 'getStartHash':
            $hashHelper->getStartHash($argv[2]);
            break;
        case 'getEndHash':
            $hashHelper->getEndHash($argv[2]);
            break;
        case 'createGitLog':
            shell_exec('git -C ' . $argv[1] . ' log --pretty=oneline ' . $argv[2] . '...' . $argv[3] . ' > git.log');
            break;
        case 'addTpTag':
            $tag = isset($argv[2]) ? $argv[2] : null;
            $addTicket = true;

            $tickets = $tp->addTagsToTargetProcessTickets($addTicket, $tag);
            print_r($tickets);
            break;
        case 'sendChangelog':
            $tag = isset($argv[2]) ? $argv[2] : null;
            $addTicket = false;
            $sendMail = isset($argv[3]) ? $argv[3] : false;

            $tp = new TargetProcessHelper($configuration);
            $tickets = $tp->addTagsToTargetProcessTickets($addTicket, $tag);
            print_r($tickets);

            if (is_string($sendMail)) {
                $emailHelper = new EmailHelper($configuration);
                $emailHelper->sendMail($sendMail, $tickets, $tag);
            }
            break;
    }
}



