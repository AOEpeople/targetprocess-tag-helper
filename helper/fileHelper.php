<?php


class fileHelper
{
    /**
     * @param string $userStories
     * @param string $bugs
     */
    public function writeFile($userStories, $bugs)
    {
        file_put_contents("User Stories", $userStories);
        file_put_contents("Bugs", $bugs);
    }
}
