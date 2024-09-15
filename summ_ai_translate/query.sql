SELECT 'recordOriginal.header', 'recordTranslated.header', 'recordTranslated.bodytext'
FROM `tt_content` AS 'recordTranslated'
JOIN 'tt_content' AS 'recordOriginal' ON 'recordOriginal.uid' = 'recordTranslated.l10_source'
WHERE (tt_content.sys_language_uid = '1') AND (`tt_content`.`deleted` = 0)

