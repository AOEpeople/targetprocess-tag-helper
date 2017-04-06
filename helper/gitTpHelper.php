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

                    $targetProcessHelper = new TargetProcessHelper($this->_configuration);
                    $teamIterations = $targetProcessHelper->getTeamIterationCollectionByTeamId($teamId);

                    $userStories = $targetProcessHelper->getInformationForTeamIterationId($teamIterations, true);
                    $bugs = $targetProcessHelper->getInformationForTeamIterationId($teamIterations, false);

                    $reviewOutput = new ReviewHelper($this->_configuration);
                    $userStories = $reviewOutput->generateOutputForEntities($userStories);
                    $bugs = $reviewOutput->generateOutputForEntities($bugs);

                    $fileHelper = new FileHelper();
                    $fileHelper->writeFile($userStories . $bugs, $filename);
                    break;
                case 'saveToPdf':
                    $filename = isset($argv[2]) ? $argv[2] : null;
                    $teamId = isset($argv[3]) ? $argv[3] : null;
                    $sprintId = isset($argv[4]) ? $argv[4] : null;

                    $targetProcessHelper = new TargetProcessHelper($this->_configuration);
                    $teamIterations = $targetProcessHelper->getTeamIterationCollectionByTeamId($teamId);
                    $teamIterationID[0] = $targetProcessHelper->extractSprintById($teamIterations, $sprintId);

                    $userStories = $targetProcessHelper->getInformationForTeamIterationId($teamIterationID, true);
                    $bugs = $targetProcessHelper->getInformationForTeamIterationId($teamIterationID, false);

                    $pdfHelper = new \PDFLib\Test\pdfHelper();
                    $pdfHelper->init($teamIterationID);

                    $reviewOutput = new ReviewHelper($this->_configuration);
                    $reviewOutput->generateOutputForEntities($userStories, $bugs, $pdfHelper);

                    $fileHelper = new FileHelper();
                    $fileHelper->writeFile($pdfHelper->Output(null, 'S'), $filename.'.pdf');

            }
        }
    }
}
