<?php

use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use THB\SummAiTranslate\Utility\TranslationHelper;

// Get site configuration for selected site in Sites-module
//$siteConfiguration = isset($_REQUEST['site']) ? GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($_REQUEST['site'])->getConfiguration() : null;
$siteConfiguration = isset($_REQUEST['site']) ? TranslationHelper::getSiteConfigurationByIdentifier($_REQUEST['site']) : null;

// Get languages for selected site
if(isset($_REQUEST['site'])) {
    $siteLanguages = TranslationHelper::getSiteLanguages($siteConfiguration);
    $defaultLangSelection = [['label' => '', 'value' => null]];
    $siteLanguagesWithDefault = array_merge($defaultLangSelection, $siteLanguages);
}

// Input field for SUMM AI API key
$GLOBALS['SiteConfiguration']['site']['columns']['summAiApiKey'] = [
    'label' => 'SUMM AI API Schlüssel',
    'description' => 'Geben Sie Ihren generierten API-Schlüssel ein oder generieren Sie einen neuen unter https://app.summ-ai.com/account/main.',
    'config' => [
        'type' => 'input',
        'size' => 30,
        'eval' => 'trim',
        'required' => false
    ],
];

// Checkbox to allow auto-translation of text elements
$GLOBALS['SiteConfiguration']['site']['columns']['summAiAutotranslate'] = [
    'label' => 'Automatische Übersetzung',
    'description' => 'Automatische Übersetzung von Textinhalten mit SUMM AI aktivieren.',
    'config' => [
        'type' => 'check',
        'required' => false
    ],
];

// Input field for lang UID of German (int) for translation FROM
$GLOBALS['SiteConfiguration']['site']['columns']['summAiLangIdDeStandard'] = [
    'label' => 'Sprach-UID: Deutsch',
    'description' => 'Wählen Sie die Sprachkofiguration "Deutsch" aus der Liste aus.',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => $siteLanguagesWithDefault,
        'required' => false
    ],
];

// Input field for lang UID of German (int) for translation TO
$GLOBALS['SiteConfiguration']['site']['columns']['summAiLangIdDePlain'] = [
    'label' => 'Sprach-UID: Leichte Sprache',
    'description' => 'Wählen Sie die Sprachkofiguration "Leichte Sprache" aus der Liste aus.',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => $siteLanguagesWithDefault,
        'required' => false
    ],
];

// Define the palette for fields without lines to build a group of fields
$GLOBALS['SiteConfiguration']['site']['palettes']['summAiPalette'] = [
    'showitem' => 'summAiAutotranslate, --linebreak--, summAiLangIdDeStandard, --linebreak--, summAiLangIdDePlain'
];

// Define input for text fields, even custom ones
$GLOBALS['SiteConfiguration']['site']['columns']['summAiLangTextFields'] = [
    'label' => 'Textfelder',
    'description' => 'Geben Sie hier alle Textfelder durch " / " getrennt ein.',
    'config' => [
        'type' => 'text',
        'cols' => 30,
        'rows' => 1,
        'eval' => 'trim',
        'required' => false,
        'default' => 'header / subheader / bodytext'
    ],
];

// Define input for header and subheader fields, required for proper text processing by SUMM AI
$GLOBALS['SiteConfiguration']['site']['columns']['summAiHeader'] = [
    'label' => 'Textfeld für Überschrift',
    'description' => 'Geben Sie den Textfeld ein, welcher in Ihrer Website für Überschrift verwendet wird.',
    'config' => [
        'type' => 'input',
        'size' => 30,
        'eval' => 'trim',
        'default' => 'header',
        'required' => false
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['summAiSubheader'] = [
    'label' => 'Textfeld für Untertitel',
    'description' => 'Geben Sie den Textfeld ein, welcher in Ihrer Website für Untertitel verwendet wird. SUMM AI wird Untertitel in Übersetzung LÖSCHEN.',
    'config' => [
        'type' => 'input',
        'size' => 30,
        'eval' => 'trim',
        'default' => 'subheader',
        'required' => false
    ],
];

// Define the palette for fields without lines to build a group of fields
$GLOBALS['SiteConfiguration']['site']['palettes']['summAiPaletteFields'] = [
    'showitem' => 'summAiLangTextFields, --linebreak--, summAiHeader, --linebreak--, summAiSubheader'
];

// Add fields to the custom tab with the API key field having a break
$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ', --div--;SUMM AI Translate, summAiApiKey, --palette--;;summAiPalette, --palette--;;summAiPaletteFields';

