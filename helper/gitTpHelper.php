<?php

class GitTpHelper
{
    /**
     * @var string[] $_configuration
     */
    protected $_configuration = [];

    public function __construct($configuration = [])
    {
        if ($configuration) {
            $this->_configuration = $configuration;
        }
    }

    /**
     * @param string[] $argv
     */
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
                case 'saveToFile':
                    $filename = isset($argv[2]) ? $argv[2] : null;
                    $teamId = isset($argv[3]) ? $argv[3] : null;

                    $filter = "?where=(Team.Id eq '" . $teamId . "')";
                    $filter = str_replace('#', '%23', $filter);
                    $filter = str_replace(' ', '%20', $filter);

                    $targetProcessHelper = new TargetProcessHelper($this->_configuration);
                    $assignables = $targetProcessHelper->getAssignables($filter);

                    $informationArray['Name'] = $teamId;

                    foreach ($assignables as $key => $entity) {
                        if ($entity['EntityType']['Name'] == 'UserStory')
                            $informationArray['UserStories'][] = $entity;
                        else
                            $informationArray['Bugs'][] = $entity;
                    }

                    $reviewOutput = new ReviewHelper($this->_configuration);
                    $information = $reviewOutput->generateOutputForEntities($informationArray);

                    $fileHelper = new FileHelper();
                    $text = $fileHelper->printArray($information);
                    $fileHelper->writeFile($text, $filename);
                    break;
                case 'saveToPdf':
                    $filename = isset($argv[2]) ? $argv[2] : null;
                    $teamId = isset($argv[3]) ? $argv[3] : null;
                    $sprintId = isset($argv[4]) ? $argv[4] : null;

                    $filter = "?where=(Team.Id eq '" . $teamId . "') and (TeamIteration.Id eq '" . $sprintId . "')";
                    $filter = str_replace('#', '%23', $filter);
                    $filter = str_replace(' ', '%20', $filter);

                    $targetProcessHelper = new TargetProcessHelper($this->_configuration);
                    $assignables = $targetProcessHelper->getAssignables($filter);

                    $informationArray['Name'] = $sprintId;

                    foreach ($assignables as $key => $entity) {
                        if ($entity['EntityType']['Name'] == 'UserStory')
                            $informationArray['UserStories'][] = $entity;
                        else
                            $informationArray['Bugs'][] = $entity;
                    }

                    $reviewOutput = new ReviewHelper($this->_configuration);
                    $information = $reviewOutput->generateOutputForEntities($informationArray);

                    $pdfHelper = new \PDFLib\Test\pdfHelper($this->_configuration);
                    $pdfHelper->printArray($information);

                    $fileHelper = new FileHelper();
                    $fileHelper->writeFile($pdfHelper->Output(null, 'S'), $filename.'.pdf');

            }
        }
    }
}
