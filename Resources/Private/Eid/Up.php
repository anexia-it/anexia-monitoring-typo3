<?php
namespace Anexia\Monitoring\Eid;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Frontend\Utility\EidUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


class Up {

    /**
     * Constructor of the controller class. Initializes the TCA, TSFE and
     * cObj objects, and reads the extension configuration.
     */
    public function __construct() {
        EidUtility::initLanguage();
        EidUtility::initTCA();

        $this->tsfe = $GLOBALS['TSFE'] = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);

        // Get FE User Information
        $this->tsfe->initFEuser();
        $this->tsfe->initUserGroups();

        // No Cache for Ajax stuff
        $this->tsfe->set_no_cache();

        $this->tsfe->checkAlternativeIdMethods();
        $this->tsfe->determineId();

        $this->tsfe->cObj = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');

        $this->configuration = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['anexia_monitoring']) or array();
    }

    /**
     * Main entry point of the controller class. Checks the access token, sends the HTTP headers and
     * sends "OK" as payload to the client.
     */
    public function main() {
        $this->enforceAccessToken();
        $this->sendHttpHeaders();
        $this->enforceCheckHooks();

        echo "OK";
    }

    /**
     * Checks if the given access token (GET parameter `access_token`) is set and equals the configured
     * access token on the extension. The configured access token must be configured.
     */
    private function enforceAccessToken() {
        $token = array_key_exists("access_token", $_GET) ? $_GET["access_token"] : null;

        if(!$token || $token != @$this->configuration['accessToken']){
            http_response_code(401);
            exit;
        }
    }

    private function enforceCheckHooks() {
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['anexia_monitoring-UpCheck'])) {
            $_params = array('pObj' => &$this);
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['anexia_monitoring-UpCheck'] as $funcRef) {
                if (GeneralUtility::callUserFunction($funcRef, $_params, $this) === false) {
                    echo "ERROR";
                    http_response_code(500);
                    exit;
                }
            }
        }
    }

    /**
     * Sends the HTTP access control and content-type headers to the client.
     */
    private function sendHttpHeaders() {
        header('Access-Control-Allow-Origin', '*');
        header('Access-Control-Allow-Credentials', 'true');
        header('Allow', 'GET, OPTIONS');
        header('Content-Type', 'text/plain');

        http_response_code(200);
    }

}

$instance = new Up();
$instance->main();
