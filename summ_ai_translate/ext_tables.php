<?php
defined('TYPO3') || die();

(function () {
    $iconProviderConfiguration = [
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class => [
            'actions-localize-summ-ai' => ['source' => 'summ-ai-logo.svg'],
        ],
    ];

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($iconProviderConfiguration as $provider => $iconConfiguration) {
        foreach ($iconConfiguration as $identifier => $option) {
            $iconRegistry->registerIcon($identifier, $provider, $option);
        }
    }

    #\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_wvdeepltranslate_domain_model_glossaries');
    #\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_wvdeepltranslate_domain_model_glossariessync');
})();
