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

* tbu.
* Dashboard widget showing amount of available characters to translate.
  * Source file located under `Classes\Widgets\UsageWidget.php`
  * ![image](https://github.com/user-attachments/assets/5cf1c78b-eaea-44ea-a703-df66da52dbdc)
  * ![image](https://github.com/user-attachments/assets/5efc2503-e1b0-4aa0-80e0-5a096257a65b)

## License

* GPL-2.0 or later
* SVG-files located under `Resources\Public\Icons` are property of SUMM AI GmbH and used exclusively for educational purposes / this university work.
