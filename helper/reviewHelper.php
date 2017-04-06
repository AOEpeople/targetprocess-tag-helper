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
        $assignedUsersFromUserStory = $userStory['AssignedUser']['Items'];

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
     * @param string $entityName
     * @return string
     */
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

    /**
     * @param string $entityName
     * @return string
     */
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


    //formats rows into a confluence table
    /**
     * @param $array
     * @param bool $topicRow
     * @param \PDFLib\Test\pdfHelper $pdfHelper
     * @return string
     */
    public function formatTableRow($array, $topicRow = false, $pdfHelper = null)
    {
        if(!$pdfHelper) {
            if ($topicRow)
                return $tableRow = '|| {color:#CD6600} *' . implode('* {color} || {color:#CD6600} *', $array) . '* {color} ||<br>
                ';
            else
                return $tableRow = '| ' . implode(' | ', $array) . ' |<br>
                ';
        } else {
            if ($topicRow) {
                foreach ($array as $key => $value) {
                    $pdfHelper->TableCell($value, $this->_width[$key] * $pdfHelper->getMaxWidth() / array_sum($this->_width), array("background" => "#d3d3d3"), "C", 1);
                }
                $pdfHelper->Ln();
            } else {
                foreach ($array as $key => $value) {
                    $pdfHelper->TableCell($value, $this->_width[$key] * $pdfHelper->getMaxWidth() / array_sum($this->_width), array("size" => $pdfHelper->FontSizePt * 4 / 5), "C", 1);
                }
                $pdfHelper->Ln();
            }
            return null;
        }
    }

    /**
     * generates 1 array (row) each time
     * @param string[][] $entity
     * @param \PDFLib\Test\pdfHelper $pdfHelper
     * @return string
     */
    protected function _generateOutputForEntity($entity, $pdfHelper)
    {
        $content = "";

        $colorMarkUp = $this->getStatusMarkup($entity['EntityState']['Name']);
        $this->_effort += $entity['Effort'];
        $assignedUser = $this->getAssignedUsers($entity, $this->_skipUsers);

        if (!$pdfHelper)
            $printArray = [
                "[#{$entity['Id']}|{$this->_configuration['targetprocess_url']}{$entity['Id']}]",
                str_replace("|", ", ", "{$entity['Name']}"),
                "{$colorMarkUp}",
                "{$entity['Effort']}",
                "{$assignedUser}",
                "",
                ""
            ];
        else
            $printArray = [
                "{$entity['Id']}",
                str_replace("|", ", ", "{$entity['Name']}"),
                "{$entity['EntityState']['Name']}",
                "{$entity['Effort']}",
                "{$assignedUser}",
                "",
                ""
            ];
        $content = $content . $this->formatTableRow($printArray, false, $pdfHelper);
        return $content;
    }

    //puts all rows together
    /**
     * @param string[] $informationArray
     * @param string[]|null $bugArray
     * @return string
     */
    public function generateOutputForEntities(array $informationArray, array $bugArray = null, $pdfHelper = null)
    {
        $content = "";
        $count = 0;

        foreach ($informationArray as $sprint) {

            $this->_effort = 0;

            $information = $sprint['Information'];

            $content = $content . "
            || " . $sprint['Name'] . "||<br><br>
            
            ";

            $printArray = ["ID", "Title", "Status", "Effort", "Responsible", "Presentable", "Presentation Notes"];
            $content = $content . $this->formatTableRow($printArray, true, $pdfHelper);

            foreach ($information as $entity)
                $content = $content . $this->_generateOutputForEntity($entity, $pdfHelper);

            if ($bugArray != null){
                $sprint = $bugArray[$count++];

                $information = $sprint['Information'];

                if(count($information) != 0) {

                    $printArray = ["", "BUGS", "", "", "", "", ""];
                    $content = $content . $this->formatTableRow($printArray, true, $pdfHelper);
                    $content = $content . "<br>
                        ";

                    foreach ($information as $entity)
                        $content = $content . $this->_generateOutputForEntity($entity, $pdfHelper);

                }
            }
            $printArray = ["", "", "", "{$this->_effort}", "", "", ""];
            $content = $content . $this->formatTableRow($printArray, false, $pdfHelper);
            $content = $content . "<br><br>
                ";
        }


        return $content;
    }
}
