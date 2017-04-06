# TargetProcess Tag Helper
A tool to collect the git commit messages from a git repo and add based on the git messages tags to targetprocess

## What the tool does
* create a git message log
* split every message by ":" and get the task id / bug id
* scan for that task id / bug id if they are part of a story
  * For Tasks always add the story to the list
  * For Bugs if they are standalone add the bug and if not add the story to the list
* provide that list (with confluence markup)
* Optional: Add tag to all of the collected stories / bugs

## How to run it
### Requirements
* Php with curl extension
* git console tool

### Setup
Setup the url, username, pw and sender address within the config.php file.
```
$configuration = [
    'targetprocess_url' => '',
    'targetprocess_username' => '',
    'targetprocess_password' => '',
    'aws_ses_sender_address' => '',
    'logo_url' => '',
    'mail_header' => 'Hello,
    Here the new changelog for the completed deployment.
    If you find issues please create a new Bug or ping us in channel.'
];
```

### How to install / prepare
* Go to git main directory
* run `git pull` to have update the local git repo
* run `./composer.phar install` to install the aws sdk

### How to use
All those should be run in the folder in which the git is located based on which you want to create a changelog

Full Command: `php git-tp.php <git-commit-hash-from> <git-commit-hash-to> <tag-to-add> <do-tag-adding> <email-address>` 

* Get start hash:
    * run `php git-tp.php getStartHash <version.txt url>`
* Get end hash:
    * run `php git-tp.php getEndHash <version.txt url>`
* To create a git log:
    * run `php git-tp.php createGitLog <git-folder>`
* Just output:
    * run `php git-tp.php addTpTag`
* Output and add Tag:
    * run `php git-tp.php addTpTag <add-tag>`
* Output and send mail via AWS-SES:
    * run `php git-tp.php sendChangelog <add-tag> <email-adresses>`
* Save Output to txt:
    * run `php git-tp.php saveToFile <filename> <teamId>`
* Save Sprint to Pdf:
    * run `php git-tp.php saveToPdf <filename> <teamId> <sprintId>`
