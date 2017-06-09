<?php

/****************************************************
 * Extension config file for ext: "anexia_monitoring"
 ****************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Anexia Monitoring',
    'description' => 'A TYPO3 extension used to monitor updates for TYPO3 and all installed extensions. It can be also used to check if the website is alive and working correctly.',
    'category' => 'plugin',
    'author' => 'Andreas Stocker',
    'author_email' => 'AStocker@anexia-it.com',
    'author_company' => 'ANEXIA Internetdienstleistungs GmbH',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.1.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0-8.7.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);