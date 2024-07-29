# TYPO3 SUMM AI Backend Extension

This is a SUMM AI backend module created as a part of bachelor thesis supervised by S. Kreideweiß.
API token provided by SUMM AI GmbH is used for HTTP requests.

## Prerequisites

* PHP 8.1
* [Composer](https://getcomposer.org/download/)
* TYPO3 Installation

## Quickstart

* `composer require thb/summ-ai-translate`
* As the extension isn't published you will have to update `composer.json` file of your TYPO3 installation.
  * `"repositories": [{"type": "path","url": "packages/*"}],"minimum-stability": "dev","prefer-stable": true`

## Contents

### Dashboard widgets
Configuration for widgets is located in `Services.yaml` file.
1.  Widget showing amount of available characters to translate as a text.
   Source file located under `Classes\Widgets\UsageWidget.php`
2. Widget showing amount of available characters to translate as a doughnut chart.
   Data provider for graph located under `Classes\Widgets\Provider\UsageDataProvider.php`
3. Widget showing a list of translations in Easy German in state 'hidden' (unpublished).
   Data provider for list located under `Classes\Widgets\Provider\UnpublishedDataProvider.php`
![image](https://github.com/user-attachments/assets/c24d64d1-c0cd-47ab-a2bd-30aceb4d1fb6)


### Translation Workflow
1. Extension configuration required in `Site Configuration`:
   1. Easy Language is set up manually
   2. All fields defined in `Configuration\SiteConfiguration\Overrides\sites.php` are filled out in backend.
![image](https://github.com/user-attachments/assets/6dd1a9dc-33f5-4c22-837a-0727c2a53a6c)

2. `DataHandler.php` hook is responsible for triggering translation upon action `new` and `update` on a tt_content with `sys_lanuage_id` of standard German setup in previous step.
   1. Utility class `SummAiTranslator.php` class is responsible for either creating new translation via POST request to Summ Ai API or updating existing one (depends on action on tt_content).
   2. Created / updated translation has a `sys_language_id` of Easy German defined in step 1.
   3. Utility class `RecordHelper.php` is responsible for database queries (creating and updating translation records for tt_content).
   4. Utility class `RequestHelper.php` is responsible for API request processing.
   5. Utility class `TranslationHelper.php` provides various support functions for `SummAiTranslator.php` class and widgets.
![image](https://github.com/user-attachments/assets/9ed1c766-8f5b-4926-b147-8aebcacff9c9)

3. As of now translation is done automatically on tt_content level, meaning that for translation of already existing entries it needs to be enabled and disabled to trigger the hook.

## License

* GPL-2.0 or later
* SVG-files located under `Resources\Public\Icons` are property of SUMM AI GmbH and used exclusively for educational purposes / this university work.
