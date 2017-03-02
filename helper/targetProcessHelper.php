<?php

/**
 * Target process helper.
 */
class TargetProcessHelper
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

    public function __construct($configuration = [])
    {
        $this->_targetProcessUrl  = $configuration['targetprocess_url'];
        $this->_username = $configuration['targetprocess_username'];
        $this->_password = $configuration['targetprocess_password'];
    }

    /**
     * Curl request to targetprocess.
     *
     * @param string $entityUrl
     * @param null|string[] $data
     * @return string[]
     */
    public function curlRequest($entityUrl, $data = null)
    {
        $ch = curl_init();

        $entityUrl = $this->_targetProcessUrl . "api/v1/". $entityUrl;


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

    public function getTeamIterationCollectionByTeamId($teamId)
    {
        return $this->curlRequest("Teams/{$teamId}/TeamIterations?include=[Name,Id,Velocity]");
    }

    public function getUserStoriesForTeamIterationId($teamIterationId)
    {
        return $this->curlRequest("TeamIterations/{$teamIterationId}/UserStories/?skip=0&take=50&include=[Id,Name,Effort,EntityState,AssignedUser,Tasks,NumericPriority]");
    }

    public function getBugsForTeamIterationId($teamIterationId)
    {
        return $this->curlRequest("TeamIterations/{$teamIterationId}/Bugs/?take=50");
    }

    public function getBugInfo($bugId)
    {
        return $this->curlRequest("Bugs/{$bugId}?include=[Id,AssignedUser,Name,EntityState,Effort,UserStory]");
        //return $this->curlRequest("Bugs/{$bugId}?include=[Name,UserStory]");
    }

    /**
     * Receive Task information from targetprocess.
     *
     * @param int $taskId
     * @return string[]
     */
    public function getTaskInfo($taskId)
    {
        return $this->curlRequest("Tasks/{$taskId}?include=[AssignedUser,Name,EntityState,Effort]");
        //return $this->curlRequest("Tasks/{$taskId}?include=[UserStory,Name]");
    }

    /**
     * Receive Story information from targetprocess.
     *
     * @param int $storyId
     * @return string[]
     */
    public function getStoryInfo($storyId)
    {
        return $this->curlRequest("UserStories/{$storyId}?include=[Id,AssignedUser,Name,EntityState,Effort]");
        //return $this->curlRequest("UserStories/{$storyId}?include=[Name]");
    }

    /**
     * Receive story information from targetprocess and adds the provided tag.
     *
     * @param int $storyId
     * @param string $tag
     */
    public function addTagToStory($storyId, $tag)
    {
        $story = $this->curlRequest("UserStories/{$storyId}?include=[Name,Project,Tags]");
        $story['Tags'] = $story['Tags'] . "," . $tag;
        $this->curlRequest("UserStories/{$storyId}?include=[Name,Project,Tags]", $story);
    }

    /**
     * Receive bug information from targetprocess and adds the provided tag.
     *
     * @param int $bugId
     * @param string $tag
     */
    public function addTagToBug($bugId, $tag)
    {
        $bug = $this->curlRequest("Bugs/{$bugId}?include=[Name,Project,Tags]");
        $bug['Tags'] = $bug['Tags'] . ",". $tag;
        $this->curlRequest("Bugs/{$bugId}?include=[Name,Project,Tags]", $bug);
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
        $bugInfo = $this->getBugInfo($ticketId);
        if ($bugInfo && !isset($bugInfo['Status'])) {
            if (!isset($bugInfo['UserStory'])) {
                return [$ticketId, $bugInfo['Name'], self::BUG];
            } else {
                $userStoryInfo = $this->getStoryInfo($bugInfo['UserStory']['Id']);
                return [$bugInfo['UserStory']['Id'], $userStoryInfo['Name'], self::STORY];
            }
        }

        $taskInfo = $this->getTaskInfo($ticketId);
        if ($taskInfo) {
            if (isset($taskInfo['UserStory']['Id'])) {
                $userStoryInfo = $this->getStoryInfo($taskInfo['UserStory']['Id']);

                if (isset($taskInfo['UserStory']['Id'])) {
                    return [$taskInfo['UserStory']['Id'], $userStoryInfo['Name'], self::STORY];
                }
            }
        }

        $userStory = $this->getStoryInfo($ticketId);
        if (!isset($userStory['Id'])) {
            return [];
        }
        return [$userStory['Id'], $userStory['Name'], self::STORY];
    }


    /**
     * @param boolean $addTicket
     * @param string $tag
     * @return string[][]
     */
    public function addTagsToTargetProcessTickets($addTicket, $tag = null)
    {
        $realTickets = $this->_checkForStoriesInGitLogFile();
        $stories = [];
        $bugs = [];
        $tags = [];
        foreach ($realTickets as $realTicket) {

            $targetProcessUrl = $this->_targetProcessUrl;
            $ticketId = $realTicket[0];

            switch ($realTicket[2]) {
                case self::STORY:
                    $stories[] = "<a href=\"{$targetProcessUrl}/entity/{$ticketId}\">" . $realTicket[0] . '</a> - ' . $realTicket[1] . "\n";
                    break;
                case self::BUG:
                    $bugs[] = "<a href=\"{$targetProcessUrl}/entity/{$ticketId}\">" . $realTicket[0] . '</a> - ' . $realTicket[1] . "\n";
                    break;
            }

            if (!$addTicket) {
                continue;
            }

            switch ($realTicket[2]) {
                case self::STORY:
                    $this->addTagToStory($realTicket[0], $tag);
                    $tags[] = "Tag <" . $tag . "> added to UserStory " . $realTicket[0] . "\n";
                    break;
                case self::BUG:
                    $this->addTagToBug($realTicket[0], $tag);
                    $tags[] = "Tag <" . $tag . "> added to Bug " . $realTicket[0] . "\n";
                    break;
            }
        }

        return [
            'tags' => $tags,
            'stories' => $stories,
            'bugs' => $bugs
        ];
    }

    /**
     * @return array
     */
    function _checkForStoriesInGitLogFile()
    {
        $myfile = fopen("git.log", "r");

        $realTickets = [];

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

            $targetProcessTicket = $this->findUserStoryOrBugTitle($numbering);
            if (!count($targetProcessTicket) || array_key_exists($targetProcessTicket[0], $realTickets)) {
                continue;
            }

            $realTickets[$targetProcessTicket[0]] = $targetProcessTicket;
        }

        fclose($myfile);
        return $realTickets;
    }
}
