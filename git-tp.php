<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/config.php';

require_once __DIR__ . '/helper/targetProcessHelper.php';
require_once __DIR__ . '/helper/emailHelper.php';


if (isset($argv[1]) && $argv[1] == 'help') {
    echo <<<USAGE
Usage:  php git-tp.php <git-commit-hash-from> <git-commit-hash-to>
        php git-tp.php <git-commit-hash-from> <git-commit-hash-to>
        php git-tp.php <git-commit-hash-from> <git-commit-hash-to> <tag-which-should-be-added> <add-tag-flag> (To add the Tag)
        php git-tp.php <git-commit-hash-from> <git-commit-hash-to> <tag-which-should-be-added> <add-tag-flag> <email-address>
        
        e.g.  php git-tp.php <git-commit-hash-from> <git-commit-hash-to> Test 0 email@address.de
        will send an email with changelog but not add the tag to targetprocess

  help                          Prints this help

USAGE;
    die;
}



if (!isset($argv[1]) || !isset($argv[2])) {
    echo "Git commit hashes missing";
    die;
}

$tag = isset($argv[3]) ? $argv[3] : null;
$addTicket = isset($argv[4]) ? (bool) $argv[4] : false;
$sendMail = isset($argv[5]) ? $argv[5] : false;


$tp = new TargetProcessHelper($configuration);
$tickets = $tp->_addTagsToTargetProcessTickets($argv[1], $argv[2], $addTicket, $tag);
print_r($tickets);

if (is_string($sendMail)) {
    $emailHelper = new EmailHelper($configuration);
    $emailHelper->sendMail($sendMail, $tickets, $tag);
}
