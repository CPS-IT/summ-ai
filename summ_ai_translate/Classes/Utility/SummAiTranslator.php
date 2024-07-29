<?php

namespace THB\SummAiTranslate\Utility;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SummAiTranslator
{
    private static string $TEXT_FIELD_SEPARATOR = ' / ';

    function __construct($pageId)
    {
        $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->pageId = $pageId;
        $this->apiKey = TranslationHelper::getApiKey($pageId);
        $this->langIdDeStandard = TranslationHelper::getSiteConfigurationByPageId($pageId, 'summAiLangIdDeStandard');
        $this->langIdDePlain = TranslationHelper::getSiteConfigurationByPageId($pageId, 'summAiLangIdDePlain');
        $this->pageTextFields = TranslationHelper::getSiteConfigurationByPageId($pageId, 'summAiLangTextFields');
        $this->pageHeader = TranslationHelper::getSiteConfigurationByPageId($pageId, 'summAiHeader');
        $this->pageSubheader = TranslationHelper::getSiteConfigurationByPageId($pageId, 'summAiSubheader');
    }

    public function translateContent(string $table, int $recordUid, \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject): void
    {
        if ($this->apiKey === null) {
            return;
        }
        $record = RecordHelper::getRecord($table, $recordUid);
        //Disable further processing if record isn't in DE language or is hidden / disabled
        if ($record === false || $record['hidden'] === 1 || $record['sys_language_uid'] !== $this->langIdDeStandard) {
            return;
        }
        //check if translation already available for the record in DE language and skip if already translated
        $recordTranslation = RecordHelper::getRecordTranslation($table, $recordUid, $this->langIdDePlain);
        //$isTranslated = self::isTranslated($table, $recordUid);
        if ($recordTranslation) {
            return;
        }
        //Create localized record for parent
        $localizedUid = self::createLocalization($table, $recordUid);
        $localizedCols = self::translateRecordText($record);
        RecordHelper::updateRecord($table, $localizedUid, $localizedCols);
        $this->logger->warning('Translation in Easy German for record {recordUid} from page {pageId} created: {localizedUid}', ['pageId' => $this->pageId, 'recordUid' => $recordUid, 'localizedUid' => $localizedUid]);
    }

    /**
     * Updates the translation of a record in the given table.
     *
     * @param string $table The name of the table.
     * @param int $recordUid The unique identifier of the record.
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject The parent object.
     * @return void
     */
    public function updateTranslation(string $table, int $recordUid, \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject): void
    {
        if ($this->apiKey === null) {
            return;
        }

        $record = RecordHelper::getRecord($table, $recordUid);
        //Disable further processing if record isn't in DE language or is hidden / disabled
        if ($record === false || $record['hidden'] === 1 || $record['sys_language_uid'] !== $this->langIdDeStandard) {
            return;
        }

        $recordTranslation = RecordHelper::getRecordTranslation($table, $recordUid, $this->langIdDePlain);
        if (!$recordTranslation) {
            self::translateContent($table, $recordUid, $parentObject);
            return;
        }

        $localizedCols = self::translateRecordText($record);
        RecordHelper::updateRecord($table, $recordTranslation['uid'], $localizedCols);
    }

    private function translateRecordText(array $record): array
    {
        $textfields = explode(self::$TEXT_FIELD_SEPARATOR, $this->pageTextFields);
        $localizedCols = [];

        // Fill cols with text field values for translation
        foreach ($textfields as $textfield) {
            if (isset($record[$textfield])) {
                $localizedCols[$textfield] = $record[$textfield];
            }
        }
        //Translate each text field
        foreach ($localizedCols as $key => $value) {
            if ($key === $this->pageHeader) {
                $localizedCols[$key] = 'Leichte Sprache: ' . $value;
            } else if ($key === $this->pageSubheader) {
                $localizedCols[$key] = '';
            } else {
                $localizedCols[$key] = RequestHelper::callEndpoint('translate', $this->apiKey, $value);
            }
        }
        $this->logger->warning('Translation: {localizedCols}', ['localizedCols' => $localizedCols]);
        return $localizedCols;
    }

    /**
     * Creates a localization of a record in the given table.
     *
     * @param string $table The name of the table.
     * @param int $recordUid The unique identifier of the record.
     * @return int The unique identifier of the localized record.
     */
    private function createLocalization(string $table, int $recordUid): int
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], []);
        return $dataHandler->localize($table, $recordUid, $this->langIdDePlain);
    }
}
