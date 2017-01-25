<?php

use Aws\Ses\SesClient;

class EmailHelper
{
    var $_sesMailer = null;

    /** @var string */
    var $_sender = '';

    /** @var string */
    var $_logo = '';

    /** @var string  */
    var $_textHeader = '';

    /**
     * EmailHelper constructor.
     * @param array $configuration
     */
    public function __construct($configuration = [])
    {
        $this->_sesMailer = SesClient::factory([
            'version'=> 'latest',
            'region' => 'eu-west-1'
        ]);
        $this->_sender = $configuration['aws_ses_sender_address'];
        $this->_logo = $configuration['logo_url'];
        $this->_textHeader = $configuration['mail_header'];
    }


    public function sendMail($receiver, $content, $tag)
    {
        $userStories = $content['stories'];
        $bugs = $content['bugs'];

        $userStoryContent = "";
        foreach ($userStories as $userStory) {
            $userStoryContent .= "<li>$userStory</li>";
        }

        $bugContent = "";
        foreach ($bugs as $bug) {
            $bugContent .= "<li>$bug</li>";
        }


        $mailText = $this->_textHeader. "
        
<h1>Changelog for {$tag}</h1>";

        $mailText = str_replace("\n", "<br>", $mailText);

        $logo = $this->_logo;

        $request = [];
        $request['Source'] = $this->_sender;
        $request['Destination']['ToAddresses'] = explode(',', $receiver);
        $request['Message']['Subject']['Data'] = 'Changelog for ' . $tag;
        $request['Message']['Body']['Html']['Data'] =
            $mailText .
            "<h2>Stories</h2>".
            "<ul>" . $userStoryContent . "</ul>".
            "<h2>Bugs</h2>".
            "<ul>" . $bugContent .  "</ul>".
        "<br><img src=\"{$logo}\" width=\"246\" height=\"55\">";

        try {
            $result = $this->_sesMailer->sendEmail($request);
            $messageId = $result->get('MessageId');
            echo("Email sent! Message ID: $messageId"."\n");

        } catch (Exception $e) {
            echo("The email was not sent. Error message: ");
            echo($e->getMessage()."\n");
        }
    }
}
