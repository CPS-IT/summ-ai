<?php

namespace THB\SummAiTranslate\Utility;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordHelper
{
    /**
     * Retrieves a record from the specified table based on its unique identifier.
     *
     * @param string $table The name of the table.
     * @param int $recordUid The unique identifier of the record.
     * @return mixed|null The record data, or null if no record is found.
     */
    public static function getRecord(string $table, int $recordUid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $query = $queryBuilder->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $recordUid)
            );
        return $query->execute()->fetch();
    }

    /**
     * Retrieves the translation of a record from the specified table.
     *
     * @param string $table The name of the table.
     * @param int $recordUid The unique identifier of the record.
     * @param int $languageIdDePlain The language ID for the DE plain language.
     * @return mixed|null The translated record, or null if no record is found.
     */
    public static function getRecordTranslation(string $table, int $recordUid, int $languageIdDePlain)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        //Check translation even if it's hidden
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $query = $queryBuilder->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('l10n_source', $recordUid)
            )
            ->andWhere(
                $queryBuilder->expr()->eq('sys_language_uid', $languageIdDePlain)
            );
        return $query->execute()->fetch();
    }

    /**
     * Updates a record in the specified table with the given columns.
     *
     * @param string $table The name of the table.
     * @param int $uid The unique identifier of the record.
     * @param array|null $cols An associative array of column names and their new values.
     * @return void
     * @throws \Exception If the update query fails to execute.
     */
    public static function updateRecord(string $table, int $uid, array $cols = null): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $query = $queryBuilder
            ->update($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $uid)
            );
        foreach ($cols as $col => $val) {
            $query->set($col, $val);
        }
        $query->execute();
    }

    /**
     * Deletes a record from the specified table.
     *
     * @param string $table The name of the table.
     * @param int $uid The unique identifier of the record.
     * @throws \Exception If the delete query fails to execute.
     * @return void
     */
    public static function deleteRecord(string $table, int $uid, string $languageIdDePlain): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        //Check translation even if it's hidden
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $query = $queryBuilder
            ->delete($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $uid)
            )
            ->andWhere(
                $queryBuilder->expr()->eq('sys_language_uid', $languageIdDePlain)
            );
        $query->execute();
    }

    /**
     * Retrieves an array of translated hidden records from the 'tt_content' table.
     *
     * @param int $languageIdDePlain The language ID for the DE plain language.
     * @return array An associative array containing the 'crdate', 'header', and 'title' fields of the translated hidden records.
     */
    public static function getTranslatedHiddenRecords(int $languageIdDePlain): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $query = $queryBuilder
            ->select('tt_content.crdate', 'tt_content.header', 'pages.title')
            ->from('tt_content')
            ->join(
                'tt_content',
                'pages',
                'pages',
                $queryBuilder->expr()->eq('pages.uid', 'tt_content.pid')
            )
            ->where(
                $queryBuilder->expr()->eq('tt_content.sys_language_uid', $languageIdDePlain)
            )
            ->andWhere(
                $queryBuilder->expr()->eq('tt_content.hidden', 1)
            )
            ->orderBy('tt_content.crdate', 'ASC');
        return $query->execute()->fetchAllAssociative();
    }
}
