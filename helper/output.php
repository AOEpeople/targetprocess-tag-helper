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
            case 'Done by AOE':
                $color = 'GREEN';
                break;
            case 'ON QA':
                $color = 'YELLOW';
                break;
            case 'ON PRD':
                $color = 'RED';
                break;
            case 'In Progress';
                $color = 'BLUE';
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