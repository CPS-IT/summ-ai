<?php
declare(strict_types=1);

namespace THB\SummAiTranslate\Utility;

use \TYPO3\CMS\Core\Exception\SiteNotFoundException;
use \TYPO3\CMS\Core\Site\SiteFinder;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

class TranslationHelper
{
    /**
     * Retrieves the SUMM AI API key based on the page ID.
     * If no page ID provided looks for the API key in all sites.
     *
     * @param int|null $pageId The ID of the page.
     * @return string|null The API key if found, otherwise null.
     * @throws SiteNotFoundException If the site is not found.
     */
    public static function getApiKey(?int $pageId = null): ?string
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        if (empty($pageId)) {
            $sites = $siteFinder->getAllSites();
            foreach ($sites as $site) {
                $apiKey = $site->getConfiguration()['summAiApiKey'] ?? null;
                if ($apiKey !== null) {
                    return $apiKey;
                }
            }
            return null;
        }

        try {
            $site = $siteFinder->getSiteByPageId($pageId);
            return $site->getConfiguration()['summAiApiKey'] ?? null;
        } catch (SiteNotFoundException $exception) {
            return null;
        }
    }

    /**
     * Retrieves the site languages from the given site configuration.
     *
     * @param array $siteConfig The site configuration containing the languages.
     * @return array An array of language objects, each containing 'label' and 'value' properties.
     */
    public static function getSiteLanguages(array $siteConfig): array
    {
        $siteLanguages = $siteConfig['languages'];
        $langArr = [];
        foreach ($siteLanguages as $lang) {
            $langArr[] = ['label' => $lang['title'], 'value' => $lang['languageId']];
        }
        return $langArr;
    }

    public static function getSiteConfigurationByPageId(int $pageId, string $configKey = null)
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId($pageId);
        $siteConfig = $site->getConfiguration();
        return $configKey ? $siteConfig[$configKey] : $siteConfig;
    }

    public static function getSiteConfigurationByIdentifier(string $page): array
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByIdentifier($page);
        return $site->getConfiguration();
    }

    public static function getAllDePlainLanguageIds(): array
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sites = $siteFinder->getAllSites();
        $langIds = [];
        foreach ($sites as $site) {
            $config = $site->getConfiguration();
            if (array_key_exists('summAiLangIdDePlain', $config)) {
                $langIds[] = $config['summAiLangIdDePlain'];
            }
        }
        return $langIds;
    }
}

