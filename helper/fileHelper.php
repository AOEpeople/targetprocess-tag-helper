<?php


class fileHelper
{
    /**
     * @param string $content
     * @param string $filename
     */
    public function writeFile($content, $filename)
    {
        file_put_contents($filename, $content);
    }


}
