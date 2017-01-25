<?php

require_once __DIR__ . '/vendor/autoload.php';

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
    switch ($argv[1]) {
        case 'getStartHash':
            $hashHelper->getStartHash($argv[2]);
            break;
        case'getEndHash':
            $hashHelper->getEndHash($argv[2]);
            break;
        case 'addTpTag':
            $startHash = file_get_contents('startHash.txt');
            $endHash = file_get_contents('endHash.txt');
            if (!$startHash || !$endHash) {
                echo "Git commit hashes missing";
                die;
            }

            $tag = isset($argv[2]) ? $argv[2] : null;
            $addTicket = true;

            $tp = new TargetProcessHelper($configuration);
            $tickets = $tp->_addTagsToTargetProcessTickets($startHash, $endHash, $addTicket, $tag);
            print_r($tickets);
            break;
        case 'sendChangelog':
            $startHash = file_get_contents('startHash.txt');
            $endHash = file_get_contents('endHash.txt');
            if (!$startHash || !$endHash) {
                echo "Git commit hashes missing";
                die;
            }

            $tag = isset($argv[2]) ? $argv[2] : null;
            $addTicket = false;
            $sendMail = isset($argv[3]) ? $argv[3] : false;

            $tp = new TargetProcessHelper($configuration);
            $tickets = $tp->_addTagsToTargetProcessTickets($startHash, $endHash, $addTicket, $tag);
            print_r($tickets);

            if (is_string($sendMail)) {
                $emailHelper = new EmailHelper($configuration);
                $emailHelper->sendMail($sendMail, $tickets, $tag);
            }
            break;
    }
}



