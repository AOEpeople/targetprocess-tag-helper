<?php


class ReviewHelper
{
    /** @var string[]  */
    protected $_skipUsers;

    /** @var int  */
    protected $_effort;

    /** @var string[]  */
    protected $_configuration;

    /** @var int[]  */
    protected $_width;

    /**
     * ReviewHelper constructor.
     * @param string[] $configuration
     */
    public function __construct($configuration)
    {
        $this->_configuration = $configuration;
        $this->_skipUsers = $configuration['skipUser']?:[];
        $this->_width = $configuration['width']?:[3, 7, 3, 2, 5, 5, 5];
        $this->_effort = 0;
    }

    /**
     * @param $userStory
     * @param string[] $skipUsers
     * @return string
     */
    protected function getAssignedUsers($userStory, $skipUsers)
    {
        $assignedUsers = [];
        if(isset($userStory['AssignedUser']))
            $assignedUsersFromUserStory = $userStory['AssignedUser']['Items'];
        else
            return '';

        foreach ($assignedUsersFromUserStory as $assignedUser) {
            $name = trim($assignedUser['FirstName'] . ' ' . $assignedUser['LastName']);
            if (in_array($name, $skipUsers)) {
                continue;
            }
            $assignedUsers[] = $name;
        }
        return implode(', ', $assignedUsers);
    }

    /**
     * generates 1 array (row) each time
     * @param string[][] $entity
     * @return array[]
     */
    protected function _generateOutputForEntity($entity)
    {
        $this->_effort += $entity['Effort'];
        $assignedUser = $this->getAssignedUsers($entity, $this->_skipUsers);
        return [false, $entity['Id'], str_replace("|", ", ", "{$entity['Name']}"), $entity['EntityState']['Name'], $entity['Effort'], $assignedUser, "", ""];
    }

    /**
     * puts all rows together
     * @param string[] $informationArray
     * @return array[][]
     */
    public function generateOutputForEntities(array $informationArray)
    {
        $this->_effort = 0;
        $Name = $informationArray['Name'];

        $printArray[$Name][] = [true, "ID", "Title", "Status", "Effort", "Responsible", "Presentable", "Presentation Notes"];

        $userStories = $informationArray['UserStories'];

        foreach ($userStories as $userStory)
            $printArray[$Name][] = $this->_generateOutputForEntity($userStory);

        if ($informationArray['Bugs'] != null){

            $bugs = $informationArray['Bugs'];

            $printArray[$Name][] = [true, "", "BUGS", "", "", "", "", ""];

            foreach ($bugs as $bug)
                $printArray[$Name][] = $this->_generateOutputForEntity($bug);

        }
        $printArray[$Name][] = [false, "", "", "", "{$this->_effort}", "", "", ""];

        return $printArray;
    }
}
