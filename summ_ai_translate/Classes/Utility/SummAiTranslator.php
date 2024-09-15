<?php

namespace THB\SummAiTranslate\Utility;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SummAiTranslator
{
    private static string $TEXT_FIELD_SEPARATOR = ' / ';

    /**
     * Constructor for SummAiTranslator class.
     *
     * @param int $pageId The ID of the page.
     * @param array $siteConfig The site configuration array.
     */
    function __construct(int $pageId, array $siteConfig)
    {
        $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->pageId = $pageId;
        $this->apiKey = $siteConfig['summAiApiKey'];
        $this->langIdDeStandard = $siteConfig['summAiLangIdDeStandard'];
        $this->langIdDePlain = $siteConfig['summAiLangIdDePlain'];
        $this->pageTextFields = $siteConfig['summAiLangTextFields'];
        $this->pageHeader = $siteConfig['summAiHeader'];
        $this->pageSubheader = $siteConfig['summAiSubheader'];
    }

    /**
     * Translates the content of a record and creates a new entry in tt_content
     *
     * @param string $table The name of the table.
     * @param int $recordUid The unique identifier of the record.
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject The parent object.
     * @return void
     */
    public function translateContent(string $table, int $recordUid, \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject): void
    {
        $startTime = microtime(true);

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

        $endTime = microtime(true);

        $this->logger->warning('Translation in Easy German {localizedUid} for record {recordUid} from page {pageId} created in {time} s.',
            ['pageId' => $this->pageId, 'recordUid' => $recordUid, 'localizedUid' => $localizedUid, 'time' => $endTime - $startTime]);
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
        $startTime = microtime(true);

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

        $endTime = microtime(true);

        $this->logger->warning('Translation in Easy German {uid} for record {recordUid} from page {pageId} updated in {time} s.',
            ['pageId' => $this->pageId, 'recordUid' => $recordUid,  'uid'=> $recordTranslation['uid'], 'time' => $endTime - $startTime]);
    }

    /**
     * Translates the text fields of a record using the provided API key and returns the localized columns.
     *
     * @param array $record The record to be translated.
     * @return array The localized columns of the record.
     */
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
            if ($key === $this->pageSubheader) {
                $localizedCols[$key] = '';
            } else if ($key === $this->pageHeader) {
                $localizedCols[$key] = RequestHelper::callEndpoint('translate', $this->apiKey, $value, true);
            } else {
                $localizedCols[$key] = RequestHelper::callEndpoint('translate', $this->apiKey, $value);
            }
            /*
            if ($key === $this->pageHeader) {
                $localizedCols[$key] = 'Leichte Sprache: ' . $value;
            } else if ($key === $this->pageSubheader) {
                $localizedCols[$key] = '';
            } else {
                $localizedCols[$key] = RequestHelper::callEndpoint('translate', $this->apiKey, $value);
            }
            */
        }
        //$this->logger->warning('Translation: {localizedCols}', ['localizedCols' => $localizedCols]);
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
