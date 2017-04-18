<?php namespace PDFLib\Test;

require_once __DIR__ . '/output.php';

use PDFLib\PDFLib;

class pdfHelper extends PDFLib
{
    /**
     * pdfHelper constructor.
     * @param string $configuration
     */
    public function __construct($configuration)
    {
        parent::__construct();

        $this->_width  = $configuration['width'];

        $this->SetAutoPageBreak(true, $this->bMargin + $this->FontSizePt);
        $this->SetTopMargin($this->tMargin + $this->FontSizePt);
        $this->AddPage();
    }

    /**
     * @param array[][] $contentArray
     */
    function printArray($contentArray)
    {
        $this->getPrintableArray($contentArray);
    }

    /**
     * formats rows into a pdf table
     * @param array[][] $contentArray
     */
    function getPrintableArray($contentArray)
    {
        foreach ($contentArray as $Name => $content) {
            $this->FlowText("SprintID: ". $Name, array("size" => $this->FontSizePt * 2));
            $this->Ln();
            foreach ($content as $row => $array) {
                if (array_shift($array)) {
                    foreach ($array as $column => $value) {
                        $this->TableCell($value, $this->_width[$column] * $this->getMaxWidth() / array_sum($this->_width), array("background" => "#d3d3d3"), "C", 1);
                    }
                } else {
                    foreach ($array as $column => $value) {
                        $this->TableCell($value, $this->_width[$column] * $this->getMaxWidth() / array_sum($this->_width), array("size" => $this->FontSizePt * 4 / 5), "C", 1);
                    }
                }
                $this->Ln();
            }
            $this->Ln();
            $this->Ln();
        }
    }

    /**
     * @return int $mw
     */
    public function getMaxWidth()
    {
        return $this->GetPageWidth() - $this->lMargin - $this->rMargin;
    }
}