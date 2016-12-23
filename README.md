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
Setup the url, username and pw within the git-tp.php file.
```
    var $_targetProcessUrl = '';
    var $_username = '';
    var $_password = '';
```

### How to use
* Go to git main directory
* run `git pull` to have update the local git repo
* run `php git-tp.php <git-commit-hash-from> <git-commit-hash-to>` to show the Stories and Bugs related to the commit messages
* run `php git-tp.php <git-commit-hash-from> <git-commit-hash-to> <tag-to-add>` to show the Stories and Bugs related to the commit messages and also add a tag to all of those stories
