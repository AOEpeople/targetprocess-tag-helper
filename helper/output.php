<?php

abstract class Output
{
    abstract function printArray($contentArray);
    abstract function getPrintableArray($contentArray);

    protected $_configuration;

    public function __construct ($configuration) {
        $this->_configuration = $configuration;
    }

    /**
     * @param string $entityName
     * @return string
     */
    protected function getStatusMarkup($entityName)
    {
        $stateFilter = $this->_configuration["stateFilter"];
        $color = isset($stateFilter[$entityName])? $stateFilter[$entityName] : "WHITE";
        $title = strtoupper($entityName);
        return "{status:colour={$color}|title={$title}|subtle=false}";
    }
}