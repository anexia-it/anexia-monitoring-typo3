<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$TYPO3_CONF_VARS['FE']['eID_include']['anxapi/v1/up'] = 'EXT:anexia_monitoring/Resources/Private/Eid/Up.php';
$TYPO3_CONF_VARS['FE']['eID_include']['anxapi/v1/modules'] = 'EXT:anexia_monitoring/Resources/Private/Eid/Modules.php';
