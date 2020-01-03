# WPBP Generator
[![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)
![Downloads](https://img.shields.io/packagist/dt/wpbp/generator.svg) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/6df5d14213264ad196654bf9c611e410)](https://www.codacy.com/app/mte90net/generator?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=WPBP/generator&amp;utm_campaign=Badge_Grade)

This script parse [WPBP](https://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered) and remove the stuff that you don't need.

## Requirements

This generator is completely based on PHP. Let's have a look on what you need and how to install it:

### Debian/Ubuntu

`sudo apt-get install php php-zip php-mbstring`

### Fedora/Centos

`sudo dnf install php php-zip php-mbstring`

## Install

From [here](https://github.com/WPBP/generator/releases) you can download the phar version or you can chose to install it with composer:

`composer global require wpbp/generator:dev-master`

Add this directory to your PATH in your ~/.bash_profile (or ~/.bashrc) like this:

`export PATH=~/.composer/vendor/bin:$PATH`

## Execute

`wpbp-generator --help` to get a list of commands

```
--dark
     Use a dark theme for console output.

--dev
     Download from the master branch (the development version).

--help
     Show the help page for this command.

--json
     Generate a wpbp.json file in the current folder. Suggested to use the WordPress plugin folder.

--no-download
     Do you want to execute composer and npm manually? This is your flag!

--verbose
     Verbose output. Because this can be helpful for debugging!
```