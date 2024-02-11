# Revious Microdata

[![Build Status](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/badges/build.png?b=master)](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/?branch=master)
[![static analysis](https://github.com/yiisoft/html/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/html/actions?query=workflow%3A%22static+analysis%22)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

[![WordPress rating](https://img.shields.io/wordpress/plugin/r/gik25-quotes.svg?maxAge=3600&label=wordpress%20rating)](https://wordpress.org/support/view/plugin-reviews/gik25-quotes)
[![WordPress](https://img.shields.io/wordpress/plugin/dt/gik25-quotes.svg?maxAge=3600)](https://downloads.wordpress.org/plugin/gik25-quotes.latest-stable.zip)
[![WordPress](https://img.shields.io/wordpress/v/gik25-quotes.svg?maxAge=3600)](https://wordpress.org/plugins/gik25-quotes/)
[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/gik25-quotes.svg?maxAge=3600)](https://wordpress.org/plugins/gik25-quotes/)
[![license](https://img.shields.io/github/license/adamdehaven/gik25-quotes.svg?maxAge=3600)](https://raw.githubusercontent.com/adamdehaven/gik25-quotes/master/LICENSE)

A Wordpress plugin to add beautifully styled left-aligned, right-aligned, or full-width pullquotes. 

## INSTALLATION
Download the plugin and install it 
<pre>Plugins > Add New > Upload Plugin</pre>

php composer.phar update --no-dev --lock

Aggiornare anche la versione del plugin su revious-microdata.php



# Changelog

##### 1.8.0-minimal version _(2024-02-11)_
In our ongoing commitment to performance and efficiency, version 1.8.0 brings significant enhancements and optimizations to superinformati.com, emphasizing a streamlined and efficient codebase:

_Enhancements and Optimizations for **List of Posts**:_

- **Thumbnail Creation:** Introduced the capability to generate and use 50x50 thumbnails, further enriching media handling.
- **Refactoring Initiatives:** Simplified the codebase with the introduction of utility classes `ImageDetails` and `ImageHelper`. Additionally, extraneous namespace declarations were removed from `superinformati_specific.php` for a more streamlined code structure.
- **Performance Optimization Techniques:** A new bulk function was implemented to enhance performance in data and database operations, coupled with the implementation of caching for database access, specifically targeting the `get_post` function to decrease database load and improve response times.
- **CSS and HTML Improvements:** Implemented inline CSS printing, allowing critical path CSS to be directly included in HTML, thus speeding up page load times.
- **Content Management and Functionality:** Enhanced the management of list of links with new saving to db capabilities and the concept of composable blocks, facilitating content management or page building activities.
- **Compatibility and Maintenance:** Updated `composer.json` to address compatibility issues with PHP 7.4, ensuring a smooth operation. Streamlined the plugin by removing non-essential features and files, aiming for a minimalistic yet functional user experience.

##### 1.7.1-minimal version _(2023-08-25)_
Following a detailed review, we adopted a minimalist approach focused on superinformati.com, leading to several key changes:

_Non-Essential Features Removed or Modified:_

Removed: TagHelper, various shortcode files like youtube.php and flexlist.php.
Modified: Classes like OptimizationHelper, ServerHelper, and Pages404Helper.
Specific Changes: WPPostsHelper.php, HtmlHelper.class.php, and OptimizationHelper.php were modified, with methods removed or altered.
Code was cleaned up in Pages404Helper.php.
Comments and methods were removed in ServerHelper.class.php.
Functions were removed in superinformati_specific.php.
Lastly, the plugin information was updated in revious-microdata.php.

##### 1.7.0 _(2023-03-19)_
* Fixed shortcode callback issue by properly referencing the namespaced function link_vitamine_handler in add_shortcode: add_shortcode('link_vitamine', __NAMESPACE__ . '\\link_vitamine_handler');
* Fixed composer autoload.php and versions
* Fixed missing css load bug (see OptimizazionHelper)
* Fixing huge bug on lists on nonsolodiete

##### 1.6.1 _(2023-03-15)_
* Automatically load domain-specific PHP files based on the current domain, removing the need for manual updates
* Refactor vitamine list in nonsolodiete to use Collection and LinkBase classes

##### 1.6.0 _(2023-03-14)_
* Resolved the blocking issue where the server's PHP version (8.0) was incompatible with the packages specified in composer.json (8.1 required for the --dev)
* Update Composer Version to 2.4
* Explained in the readme how to install --no-dev

##### 1.5.0 _(2023-01-22)_
* Refactored ShortcodeBase.php to convert everything to classes and added namespaces to the classes (not tested)
* Performed code cleaning: Removed unnecessary files: GenericShortcode.php, OttimizzazioneNewspaper.php, LowLevelShortcode.class.php, and shortcode-wpautop-control.php as they contained unused functionality.

##### 1.4.0 _(2022-10-27)_
* Major changes: using OOP and Composer
* Fixed and tested Lists of Posts in Superinformati
* Implemented PHPStan

##### 1.3.3 _(2022-10-9)_
* Forced caching of 404 pages

##### 1.3.2 _(2021-10-2)_
* Added function_exists('is_plugin_active') check, maybe unnecessary because it is related to another error

##### 1.3.1 _(2021-10-2)_

* TODO: removing all tags should be configurable
* Tags: removed the links to tags from every post
* Tags: put in 410 from htaccess (in sitemaps they seem absent)
* Added file RankMathOptimizer.php to noindex specific pages

##### 1.3.0 _(2021-09-22)_

* Implemented conditional loading in all shortcodes (for BE and FE)
* in OptimizationHelper.php changed the method to accept delegates from other classes too
* disabled a couple of unused shortcodes
* replaced PLUGIN_NAME_PREFIX with md_

##### 1.2.5 _(2021-09-15)_

* Completed the implementation of conditional css loading through OptimizationHelper.php
* Fixed huge bug which prevented the loading of CSS revious-microdata.css
* Avoided direct call to OptimizationHelper::ConditionalLoadCssJsOnPostsWhichContainEnabledShortcodes() from GenericShortcode.php (now done through the class constructor)
* TODO: found a bug in blinkingbutton.php all the conditional methods should call ExecuteAfterTemplateRedirect

##### 1.2.0 _(2021-09-13)_

* Progress bar: Fixed bug, introduced typescript 
* Renamed classes inside shortcodes to match the file name 

##### 1.1.9 _(2021-07-17)_

* Moved ListOfPostsHelper in folder \class
* Added to superinformati_specific.php the handler for  scripts in header and override author to "Redazione"

##### 1.1.8 _(2021-06-05)_

* Fixed breadcrumb on Psicocultura author pages [requires Yoast]
* Added elementor experiment files

##### 1.1.7 _(2021-05-11)_

* Fixed regressione (due to lack of template_redirect) conditional loading for FE Boxinformativo 

##### 1.1.6 _(2021-05-06)_

* Added conditional loading for FE / BE (only for Boxinformativo and blinkingbutton) 
* Fixed bug in OptimizationHelper::IsShortcodeUsedInCurrentPost('md_blinkingbutton');
* Initial improvement to OptimizationHelper

##### 1.1.5 _(2020-08-22)_
* Fixed issue with 5px margin in tag body (Progress bar + Elementor) 

##### 1.1.4 _(2020-08-21)_
* Renamed progress bar assets to a more speaking name
* Separated loading of css and js for FE/BE (should be continued)

#### 1.1.3 ###
* Fixed the path of faq.js

#### 1.1.2 ###
* Refactored TinyMCE js to a subfolder

#### 1.1.1 ###
* Renamed progress bar to a more speaking name
