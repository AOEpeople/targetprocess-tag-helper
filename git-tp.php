<?php

/**
 * Target process helper.
 */
class TargetProcess
{
    /** string */
    const BUG = 'bug';

    /** string */
    const STORY = 'story';

    /** @var string */
    var $_targetProcessUrl = '';

    /** @var string */
    var $_username = '';

    /** @var string */
    var $_password = '';

    /**
     * Curl request to targetprocess.
     *
     * @param string $entityUrl
     * @param null|string[] $data
     * @return string[]
     */
    protected function _curlRequest($entityUrl, $data = null)
    {
        $ch = curl_init();

        $entityUrl = $this->_targetProcessUrl . $entityUrl;


        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $entityUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERPWD, $this->_username . ':' . $this->_password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                "Content-type: application/json",
                "Accept: application/json"
            ]
        );

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Receive Bug information from targetprocess.
     *
     * @param int $bugId
     * @return string[]
     */
    protected function _getBugInfo($bugId)
    {
        return $this->_curlRequest("Bugs/{$bugId}?include=[Name,UserStory]");
    }

    /**
     * Receive Task information from targetprocess.
     *
     * @param int $taskId
     * @return string[]
     */
    protected function _getTaskInfo($taskId)
    {
        return $this->_curlRequest("Tasks/{$taskId}?include=[UserStory,Name]");
    }

    /**
     * Receive Story information from targetprocess.
     *
     * @param int $storyId
     * @return string[]
     */
    protected function _getStoryInfo($storyId)
    {
        return $this->_curlRequest("UserStories/{$storyId}?include=[Name]");
    }

    /**
     * Receive story information from targetprocess and adds the provided tag.
     *
     * @param int $storyId
     * @param string $tag
     */
    public function addTagToStory($storyId, $tag)
    {
        $story = $this->_curlRequest("UserStories/{$storyId}?include=[Name,Project,Tags]");
        $story['Tags'] = $story['Tags'] . "," . $tag;
        $this->_curlRequest("UserStories/{$storyId}?include=[Name,Project,Tags]", $story);
    }

    /**
     * Receive bug information from targetprocess and adds the provided tag.
     *
     * @param int $bugId
     * @param string $tag
     */
    public function addTagToBug($bugId, $tag)
    {
        $bug = $this->_curlRequest("Bugs/{$bugId}?include=[Name,Project,Tags]");
        $bug['Tags'] = $bug['Tags'] . ",". $tag;
        $this->_curlRequest("Bugs/{$bugId}?include=[Name,Project,Tags]", $bug);
    }

    /**
     * Logic to check if a story exists for the provided ticket number.
     * If story exists story is returned
     * If no story exists bug is returned.
     *
     * @param $ticketId
     * @return string[]
     */
    public function findUserStoryOrBugTitle($ticketId)
    {
        $bugInfo = $this->_getBugInfo($ticketId);
        if ($bugInfo && !isset($bugInfo['Status'])) {
            if (!isset($bugInfo['UserStory'])) {
                return [$ticketId, $bugInfo['Name'], self::BUG];
            } else {
                $userStoryInfo = $this->_getStoryInfo($bugInfo['UserStory']['Id']);
                return [$bugInfo['UserStory']['Id'], $userStoryInfo['Name'], self::STORY];
            }
        }

        $taskInfo = $this->_getTaskInfo($ticketId);
        if ($taskInfo) {
            if (isset($taskInfo['UserStory']['Id'])) {
                $userStoryInfo = $this->_getStoryInfo($taskInfo['UserStory']['Id']);

                if (isset($taskInfo['UserStory']['Id'])) {
                    return [$taskInfo['UserStory']['Id'], $userStoryInfo['Name'], self::STORY];
                }
            }
        }

        $userStory = $this->_getStoryInfo($ticketId);
        if (!isset($userStory['Id'])) {
            return [];
        }
        return [$userStory['Id'], $userStory['Name'], self::STORY];
    }
}


/**
 * @param string[][] $realTickets
 */
function _addTagsToTargetProcessTickets($realTickets)
{
    $targetProcess = new TargetProcess();

    foreach ($realTickets as $realTicket) {

        echo $realTicket[2] . ' - ' . $realTicket[0] . ' - ' . $realTicket[1] . "\n";

        if (!isset($argv[3]) || !is_string($argv[3])) {
            continue;
        }

        $tag = $argv[3];

        switch ($realTicket[2]) {
            case TargetProcess::STORY:
                $targetProcess->addTagToStory($realTicket[0], $tag);
                echo "Tag <" . $tag . "> added to UserStory " . $realTicket[0] . "\n";
                break;
            case TargetProcess::BUG:
                $targetProcess->addTagToBug($realTicket[0], $tag);
                echo "Tag <" . $tag . "> added to Bug " . $realTicket[0] . "\n";
                break;
        }
    }
}

/**
 * @param string $start
 * @param string $end
 *
 * @return array
 */
function _getGitCommitMessagesAndCheckForStories($start, $end)
{
    shell_exec('git log --pretty=oneline ' . $start . '...' . $end . ' > git.log');
    $myfile = fopen("git.log", "r");

    $realTickets = [];

    $targetProcess = new TargetProcess();

    while (!feof($myfile)) {
        $line = fgets($myfile);
        $lineArray = explode(' ', $line);
        if (count($lineArray) < 2) {
            continue;
        }

        $numbering = explode(':', $lineArray[1])[0];

        if (!is_numeric($numbering)) {
            continue;
        }

        $targetProcessTicket = $targetProcess->findUserStoryOrBugTitle($numbering);
        if (!count($targetProcessTicket) || array_key_exists($targetProcessTicket[0], $realTickets)) {
            continue;
        }

        $realTickets[$targetProcessTicket[0]] = $targetProcessTicket;
    }

    fclose($myfile);
    return $realTickets;
}


if (isset($argv[1]) && $argv[1] == 'help') {
    echo <<<USAGE
Usage:  php git-tp.php <git-commit-hash-from> <git-commit-hash-to>
        php git-tp.php <git-commit-hash-from> <git-commit-hash-to> <hash-which-should-be-added>

  help                          Prints this help

USAGE;
    die;
}



if (!isset($argv[1]) || !isset($argv[2])) {
    echo "Git commit hashes missing";
    die;
}

$realTickets = _getGitCommitMessagesAndCheckForStories($argv[1], $argv[2]);
_addTagsToTargetProcessTickets($realTickets);
