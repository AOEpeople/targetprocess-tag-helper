<?php

class HashHelper
{

    public function __construct()
    {
    }

    /**
     * @param string $url
     */
    public function getStartHash($url)
    {
        $currentHash = $this->getCurrentHash($url);
        $this->saveHash('startHash.txt', $currentHash);
    }

    /**
     * @param string $url
     */
    public function getEndHash($url)
    {
        $currentHash = $this->getCurrentHash($url);
        $this->saveHash('endHash.txt', $currentHash);
    }

    /**
     * @param string $url
     * @return string[]
     */
    public function getCurrentHash($url)
    {
        $timeHash = time();
        $url .= '?randomizer=' . $timeHash;
        $curlResponse = $this->_curlRequest($url);
        return $curlResponse;
    }

    /**
     * Curl request.
     *
     * @param string $entityUrl
     * @return string[]
     */
    protected function _curlRequest($entityUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $entityUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * @param $fileName
     * @param $currentHash
     */
    protected function saveHash($fileName, $currentHash)
    {
        $arraySplit = explode("\n", $currentHash);
        $hash = str_replace('Revision: ', '', $arraySplit[3]);
        file_put_contents($fileName, $hash);
    }
}
