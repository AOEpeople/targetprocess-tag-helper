# TargetProcess Tag Helper
A tool to collect the git commit messages from a git repo and add based on the git messages tags to targetprocess

## What the tool does
* create a git message log
* split every message by ":" and get the task id / bug id
* scan for that task id / bug id if they are part of a story
  * For Tasks always add the story to the list
  * For Bugs if they are standalone add the bug and if not add the story to the list
* provide that list
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
    'logo_url' => ''
];
```

### How to install / prepare
* Go to git main directory
* run `git pull` to have update the local git repo
* run `./composer.phar install` to install the aws sdk

### How to use
All those should be run in the folder in which the git is located based on which you want to create a changelog

Full Command: `php git-tp.php <git-commit-hash-from> <git-commit-hash-to> <tag-to-add> <do-tag-adding> <email-address>` 

* Just output:
    * run `php git-tp.php <git-commit-hash-from> <git-commit-hash-to>`
* Output and add Tags to Targetprocess: 
    * run `php git-tp.php <git-commit-hash-from> <git-commit-hash-to> <tag-to-add> <do-tag-adding>`
* Output and send mail via AWS-SES (without adding tags to targetprocess)
    * run `php git-tp.php <git-commit-hash-from> <git-commit-hash-to> <tag-to-add> 0 <email-address>` 
* Output, add Tags to Targetprocess and send mail via AWS-SES
    * run `php git-tp.php <git-commit-hash-from> <git-commit-hash-to> <tag-to-add> 1 <email-address>` 

