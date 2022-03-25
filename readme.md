# WPBP Generator
[![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)
![Downloads](https://img.shields.io/packagist/dt/wpbp/generator.svg) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/7dea19a435514ccd9079f614dacfda46)](https://www.codacy.com/gh/WPBP/generator?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=WPBP/generator&amp;utm_campaign=Badge_Grade)

This generator (based on PHP) parse the [WordPress Plugin Boilerplate Powered](https://github.com/WPBP/WordPress-Plugin-Boilerplate-Powered) and remove the stuff that you don't need.

## Requirements

Let's have a look on what you need and how to install it:

### Debian/Ubuntu

`sudo apt install php php-zip php-mbstring php-yaml`

### Fedora/Centos

`sudo dnf install php php-zip php-mbstring php-yaml`

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

## wpbp.json

This [file](https://github.com/WPBP/generator/blob/master/generator/wpbp.json) contains all the default variables that will be used to scaffold the boilerplate.  

* *plugin*/*author* section includes [Plugin names for the WordPress plugin standard](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/) but also the Comments section in every file
* *public-assets* section includes code for frontend that [enqueue CSS and JS file](https://developer.wordpress.org/plugins/javascript/enqueuing/#enqueue-script)
* *act-deact* section includes the code on [activation/deactivation of the plugin itself](https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/) and the [uninstall.php file](https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/#method-2-uninstall-php)
* *admin-assets* section includes code for backend that [enqueue CSS and JS file](https://developer.wordpress.org/plugins/javascript/enqueuing/#enqueue-script), `settings` values are for the Plugin setting page and admin for the rest of backend, `admin-page` add a new setting plugin page
* *ajax* section add code for [WordPress Ajax](https://codex.wordpress.org/AJAX_in_Plugins) system for [logged/non-logged users](https://developer.wordpress.org/plugins/javascript/enqueuing/#ajax-action)
* *git-repo* execute automatically `git init` in the folder created
* *grunt* adds the support for [GruntJS](https://gruntjs.com/), *grumphp* adds the support for [GrumPHP](https://github.com/phpro/grumphp) and *phpstan* for [PHPStan](https://github.com/phpstan/phpstan)
* *phpcs/phpstan* enable predefined settings and rulesets for this tools
* *unit-test* adds the `tests` folder and `codeception.dist.yml` file with the various packages for composer about [Codeception](https://codeception.com/)
* *wpcli* adds the support in the plugin code for the [WP-CLI tool](https://wp-cli.org/)
* *language-files* adds the [po/mo files and the code to load custom languages files](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/)
* *libraries* includes all the composer packages that will be downloaded with the related example code in the boilerplate itself, removing them will not add it
* *snippet* in the various subsections add specific code snippet integrated for the various needs in WordPress, removing them will not add it
