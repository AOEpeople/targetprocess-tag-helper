<?php
class GitTpHelper
{
    protected $_configuration = [];

    public function __construct($configuration = [])
    {
        if ($configuration) {
            $this->_configuration = $configuration;
        }
    }

    public function run($argv)
    {
        if (isset($argv[1])) {
            $hashHelper = new HashHelper();
            $tp = new TargetProcessHelper($this->_configuration);
            $emailHelper = new EmailHelper($this->_configuration);

            switch ($argv[1]) {
                case 'getStartHash':
                    $url = isset($this->_configuration['server_url']) ? $this->_configuration['server_url'] : $argv[2];
                    $hashHelper->getStartHash($url);
                    break;
                case 'getEndHash':
                    $url = isset($this->_configuration['server_url']) ? $this->_configuration['server_url'] : $argv[2];
                    $hashHelper->getEndHash($url);
                    break;
                case 'createGitLog':
                    shell_exec('git -C ' . $argv[2] . ' log --pretty=oneline ' . file_get_contents('startHash.txt') . '...' . file_get_contents('endHash.txt') . ' > git.log');
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

                    $tickets = $tp->addTagsToTargetProcessTickets($addTicket, $tag);
                    print_r($tickets);

                    if (is_string($sendMail)) {
                        $emailHelper->sendMail($sendMail, $tickets, $tag);
                    }
                    break;
            }
        }
    }
}
