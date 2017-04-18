<?php

abstract class Output
{
    abstract function printArray($contentArray);
    abstract function getPrintableArray($contentArray);

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
            case '';
                return "";
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
            default:
                return '';
        }
    }

}