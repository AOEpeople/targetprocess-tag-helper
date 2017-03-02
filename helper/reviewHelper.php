<?php


class ReviewHelper
{
    var $teamId;
    var $_skipUsers;
    var $_effort = 0;
    var $configuration;
    var $tph;

    public function __construct($configuration)
    {
        $this->tph = new TargetProcessHelper($configuration);
        $this->configuration = $configuration;
        $this->_skipUsers = $configuration['skipUser'];
        $this->teamId = $_GET['teamid'];
        $this->printOut($this->teamId);
    }

    protected function getAssignedUsers($userStory, $skipUsers)
    {
        $assignedUsers = [];
        $assignedUsersFromUserStory = $userStory['AssignedUser']['Items'];
        foreach ($assignedUsersFromUserStory as $assignedUser) {
            $name = trim($assignedUser['FirstName'] . ' ' . $assignedUser['LastName']);
            if (in_array($name, $skipUsers)) {
                continue;
            }
            $assignedUsers[] = $name;
        }
        return implode('<br>', $assignedUsers);
    }

    protected function getStatusMarkup($entityName)
    {
        switch ($entityName) {
            case 'Done':
                $color = 'GREEN';
                break;
            case 'Approval':
                $color = 'GREEN';
                break;
            case 'In Testing':
                $color = 'BLUE';
                break;
            case 'Waiting for Feedback':
                $color = 'YELLOW';
                break;
            case 'Awaiting Deployment':
                $color = 'BLUE';
                break;
            case 'In Progress';
                $color = 'YELLOW';
                break;
            case 'Open';
                $color = 'RED';
                break;
            default:
                $color = 'WHITE';
                break;
        }

        $title = strtoupper($entityName);
        return "{status:colour={$color}|title={$title}|subtle=false}";
    }

    protected function getColor($entityName)
    {
        switch ($entityName) {
            case 'Done':
                return 'green';
            case 'In Testing';
                return 'yellow';
            case 'In Progress';
                return 'blue';
            case 'Open';
                return 'grey';
        }
    }

    protected function printTableRow($array, $topicRow = false)
    {
        if ($topicRow)
            return $tableRow = '|| {color:#CD6600} *' . implode('* {color} || {color:#CD6600} *', $array) . '* {color} ||<br>';
        else
            return $tableRow = '| ' . implode(' | ', $array) . ' |<br>';
    }

    protected function printOut($teamId)
    {
        $teamIterationCollection = $this->tph->getTeamIterationCollectionByTeamId($teamId);
        for ($i = 0; $i < count($teamIterationCollection['Items']); $i++) {
            $firstTeamIterationId = $teamIterationCollection['Items'][$i]['Id'];
            echo "<h1>{$teamIterationCollection['Items'][$i]['Name']}</h1>";

            $userStories = $this->tph->getUserStoriesForTeamIterationId($firstTeamIterationId);
            $bugs = $this->tph->getBugsForTeamIterationId($firstTeamIterationId);

            $sortByPriority = [];
            $sortBugByPriority = [];

            foreach ($userStories['Items'] as $userStory) {
                $sortByPriority[$userStory['NumericPriority']][] = $userStory;
            }

            foreach ($bugs['Items'] as $bug) {
                $sortBugByPriority[$bug['NumericPriority']][] = $bug;
            }

            ksort($sortByPriority);
            ksort($sortBugByPriority);

            $printArray = ["Link", "Title", "Status", "Effort", "Responsible", "Presentable", "Presentation Notes"];
            echo $this->printTableRow($printArray, true);
            $this->_effort = 0;
            foreach ($sortByPriority as $userStories) {
                foreach ($userStories as $userStory) {
                    $userStory = $this->tph->getStoryInfo($userStory['Id']);
                    $this->_generateOutputForEntity($userStory);
                }
            }
            $printArray = ["", "{color:#CD6600} *BUGS* {color}", "", "", "", "", ""];
            echo $this->printTableRow($printArray);

            foreach ($sortBugByPriority as $bugs) {
                foreach ($bugs as $bug) {
                    $bug = $this->tph->getBugInfo($bug['Id']);

                    if ($bug['UserStory'] != null) {
                        continue;
                    }
                    $this->_generateOutputForEntity($bug);
                }
            }
            $printArray = ["", "", "", "{$this->_effort}", "", "", ""];
            echo $this->printTableRow($printArray) . "<hr/>";
        }
    }

    protected function _generateOutputForEntity($entity)
    {
        $colorMarkUp = $this->getStatusMarkup($entity['EntityState']['Name']);
        $this->_effort += $entity['Effort'];
        $assignedUser = $this->getAssignedUsers($entity, $this->_skipUsers);


        $printArray = [
            "[#{$entity['Id']}|{$this->configuration['url']}{$entity['Id']}]",
            str_replace("|", ", ", "{$entity['Name']}"),
            "{$colorMarkUp}",
            "{$entity['Effort']}",
            "{$assignedUser}",
            "",
            ""
        ];
        echo $this->printTableRow($printArray);
        return array($colorMarkUp, $assignedUser, $printArray);
    }
}