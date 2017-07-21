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
     * @param array[][] $contentArray
     */
    public function printArrayAsHtml($contentArray)
    {
        $table = '';
        foreach ($contentArray as $Name => $content) {
            $table = $table . '<h1>' . $Name . '</h1><table border="1">';
            foreach ($content as $row => $array) {
                if (array_shift($array)) {
                    $table = $table . '<tr><th>' . implode('</th><th>', $array) . '</th></tr>';
                } else {
                    $array[0] = '<a href="' . $this->_configuration['targetprocess_url'] . 'entity/' . $array[0] . '">' . $array[0];
                    $array[2] = $this->getStatusMarkup($array[2]);
                    $table = $table . '<tr><td>' . implode('</td><td>', $array) . '</td></tr>';
                }
            }
            $table = $table . '</table><br><div class="clear" style="clear:both"></div><hr />';
        }
        echo $table;
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
                    $array[0] = $array[0] == "" ? "" : "[{$array[0]}|{$this->_configuration['targetprocess_url']}entity/{$array[0]}]";
                    $array[1] = $array[1] == "BUGS" ? "{color:#CD6600} *BUGS* {color}" : $array[1];
                    $array[2] = $array[2] != "" ? $this->getStatusMarkup($array[2]) : "";
                    $table = $table . '| ' . implode(' | ', $array) . ' |<br>';
                }
            }
            $table = $table . '<br><br>';
        }
        return $table;
    }
}