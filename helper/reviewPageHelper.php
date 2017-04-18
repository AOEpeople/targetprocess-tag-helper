<?php

require_once __DIR__ . '/output.php';

class ReviewPageHelper extends Output {

    /**
     * @param array[][] $contentArray
     */
    public function printArray($contentArray)
    {
        echo $this->getPrintableArray($contentArray);
    }

    /**
     * formats rows into a confluence table
     * @param array[][] $contentArray
     * @return string
     */
    function getPrintableArray($contentArray)
    {
        $table = '';
        foreach ($contentArray as $Name => $content) {
            $table = $table . 'h1.' . $Name . '<br>';
            foreach ($content as $row => $array) {
                if (array_shift($array)) {
                    $table = $table . '|| {color:#CD6600} *' . implode('* {color} || {color:#CD6600} *', $array) . '* {color} ||<br>';
                } else {
                    $array[2] = $this->getStatusMarkup($array[2]);
                    $table = $table . '| ' . implode(' | ', $array) . ' |<br>';
                }
            }
            $table = $table . '<br><br>';
        }
        return $table;
    }
}