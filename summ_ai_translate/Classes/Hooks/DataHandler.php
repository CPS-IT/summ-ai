<?php
declare(strict_types=1);

namespace THB\SummAiTranslate\Hooks;

use THB\SummAiTranslate\Utility\SummAiTranslator;
use THB\SummAiTranslate\Utility\TranslationHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\SingletonInterface;


class DataHandler implements SingletonInterface
{
    private bool $disabled = false;

    public function processDatamap_afterDatabaseOperations(
        $status,
        $table,
        $recordUid,
        array $fields,
        \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject
    )
    {
        // Only process record operations, skip page operations
        if ($table !== 'tt_content' || $this->disabled) {
            return;
        }
        // Skip auto translation if page created on root level.
        if ($table == 'pages' && $status == 'new' && $fields['pid'] === 0) {
            return;
        }
        // replace real record uid if is new record
        if (isset($parentObject->substNEWwithIDs[$recordUid])) {
            $recordUid = $parentObject->substNEWwithIDs[$recordUid];
        }
        $pid = $parentObject->getPID($table, $recordUid);
        $pageId = ($pid === 0 && $table === 'pages') ? $recordUid : $pid;
        $translationAllowed = TranslationHelper::getSiteConfigurationByPageId($pageId, 'summAiAutotranslate');
        if ($translationAllowed === false) {
            return;
        }
        $translator = GeneralUtility::makeInstance(SummAiTranslator::class, $pageId);
        // Create translation if record is new, otherwise update existing one
        if ($status === 'update') {
            $translator->updateTranslation($table, $recordUid, $parentObject);
        } else {
            $translator->translateContent($table, $recordUid, $parentObject);
        }
    }

    public function processCmdmap(string $command, $table, $id, $value, $commandIsProcessed, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler, $pasteUpdate): void
    {
        // Disable auto translation for copy or cut actions.
        if ($command === 'copy' || $command === 'cut') {
            $this->disabled = true;
        }
    }

    public function processCmdmap_postProcess(string $command, $table, $id, $value, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler, $pasteUpdate, $pasteDatamap)
    {
        // Reenable auto translation after copy command has finished.
        if ($command === 'copy' || $command === 'cut') {
            $this->disabled = false;
        }
    }
}
