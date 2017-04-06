<?php namespace PDFLib\Test;

use PDFLib\PDFLib;

class pdfHelper extends PDFLib
{

    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();

        $this->SetAutoPageBreak(true, $this->bMargin + $this->FontSizePt);
        $this->SetTopMargin($this->tMargin + $this->FontSizePt);
    }

    /**
     * @return int $mw
     */
    public function getMaxWidth()
    {
        return $this->GetPageWidth() - $this->lMargin - $this->rMargin;
    }

    /**
     * @param string[][] $teamIterationID
     */
    public function init($teamIterationID)
    {
        $this->AddPage();
        $this->FlowText("{$teamIterationID[0]['Name']}", array("size" => $this->FontSizePt * 2), 'C');
        $this->Ln();
        $this->Ln();
    }
}