<?php
namespace THB\SummAiTranslate\Widgets\Provider;

use THB\SummAiTranslate\Utility\RecordHelper;
use THB\SummAiTranslate\Utility\TranslationHelper;
use \TYPO3\CMS\Dashboard\Widgets\ListDataProviderInterface;

class UnpublishedDataProvider implements ListDataProviderInterface
{
    /**
     * Retrieves an array of unpublished records as formatted strings.
     *
     * This function retrieves the language ID for the plain DE language from the TranslationHelper class.
     * If there are no such language IDs, it returns an array with the message "No translations pending publishing.".
     * Otherwise, it retrieves the logger instance from the TYPO3\CMS\Core\Log\LogManager class and the translated
     * hidden records from the RecordHelper class using the first language ID from the plain DE language IDs.
     * It then logs a warning message with the retrieved records.
     * The retrieved records are formatted into an array of strings using the formatJson method.
     * If the array of formatted strings is not empty, it is returned.
     * Otherwise, an array with the message "No translations pending publishing." is returned.
     *
     * @return array An array of unpublished records as formatted strings, or an array with the message
     *               "No translations pending publishing." if there are no such records.
     */
    public function getItems(): array
    {
        $langIdDePlain = TranslationHelper::getAllDePlainLanguageIds();
        if (empty($langIdDePlain)) {
            return ['No translations pending publishing.'];
        }
        $records = [];
        foreach ($langIdDePlain as $langId) {
            $recordsForLangId = RecordHelper::getTranslatedHiddenRecords((int) $langId);
            $records = array_merge($records, $recordsForLangId);
        }
        $records = RecordHelper::getTranslatedHiddenRecords((int) $langIdDePlain[0]);
        $recordsAsText = self::formatJson($records);
        if (count($recordsAsText) > 0) {
            return $recordsAsText;
        }
        return ['No translations pending publishing.'];
    }

    /**
     * Formats an array of records into an array of formatted strings.
     *
     * @param array $records The array of records to be formatted.
     * @return array The array of formatted strings.
     */
    private function formatJson(array $records): array
    {
        $recordsAsText = [];
        foreach ($records as $record) {
            $formattedRecord = "Erstellt am " . date('d.m.y H:i', $record['crdate'])
                . " mit der Überschrift: " . $record['header'] . " für Website " . $record['title'];
            $recordsAsText[] = $formattedRecord;
        }
        return $recordsAsText;
    }
}
