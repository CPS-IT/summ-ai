<?php

defined('TYPO3') or die();

call_user_func(function() {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'summ-ai-translate',
        'Configuration/TypoScript',
        'Backend Dashboard Widgets Configuration'
    );
});

