<?php
namespace Anexia\Monitoring\Eid;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Frontend\Utility\EidUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


class Modules {

    /**
     * Constructor of the controller class. Initializes the TCA, TSFE and
     * cObj objects, reads the extension configuration and sets the ListUtility instance
     * of the extension manager on the object.
     */
    public function __construct() {
        EidUtility::initLanguage();
        EidUtility::initTCA();

        $this->tsfe = $GLOBALS['TSFE'] = GeneralUtility::makeInstance('\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);

        // Get FE User Information
        $this->tsfe->initFEuser();
        $this->tsfe->initUserGroups();

        // No Cache for Ajax stuff
        $this->tsfe->set_no_cache();

        $this->tsfe->checkAlternativeIdMethods();
        $this->tsfe->determineId();

        $this->tsfe->cObj = GeneralUtility::makeInstance('\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        $this->objectManager = GeneralUtility::makeInstance('\TYPO3\CMS\Extbase\Object\ObjectManager');
        $this->extensionListUtility = $this->objectManager->get('\TYPO3\CMS\Extensionmanager\Utility\ListUtility');

        $this->configuration = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['anexia_monitoring']) or array();
    }

    /**
     * Main entry point of the controller class. Checks the access token, sends the HTTP headers and
     * sends the version data as JSON to the client.
     */
    public function main() {
        $this->enforceAccessToken();
        $this->sendHttpHeaders();

        echo json_encode($this->getVersionData());
    }

    /**
     * Gets the version data as array. The array has a key `runtime` containing data about the
     * platform and framework, as well as a key `modules` where the installed extensions, including
     * their installed and newest version number, are listed.
     * @return array
     */
    private function getVersionData() {
        $result = [];
        $extensions = $this->extensionListUtility->getAvailableExtensions();
        $extensionTer = $this->extensionListUtility->enrichExtensionsWithEmConfAndTerInformation($extensions);
        $t3Versions = $this->getT3Versions();

        // Platform data
        $result['runtime'] = [
            'platform' => 'php',
            'platform_version' => phpversion(),
            'framework' => 'typo3',
            'framework_version' => $t3Versions[0],
            'framework_newest_version' => $t3Versions[1],
        ];

        // Extension data
        $result['modules'] = [];

        foreach ($extensionTer as $extensionKey => $extension) {
            if($extension['type'] === 'Local') {
                $result['modules'][] = [
                    'name' => $extension['key'],
                    'installed_version' => $extension['version'],
                    'newest_version' => isset($extension['updateToVersion']) ? $extension['updateToVersion']->getVersion() : $extension['version'],
                ];
            }
        }

        return $result;
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

    /**
     * Sends the HTTP access control and content-type headers to the client.
     */
    private function sendHttpHeaders() {
        header('Access-Control-Allow-Origin', '*');
        header('Access-Control-Allow-Credentials', 'true');
        header('Allow', 'GET, OPTIONS');
        header('Content-Type', 'application/json');

        http_response_code(200);
    }

    /**
     * Gets the currently installed and the newest available version of TYPO3. Returns an
     * array with these versions, where index 0 is the current version and index 1 is the
     * newest available version.
     * @return array<string>
     */
    private function getT3Versions() {
        $versionInformationUrl = 'https://get.typo3.org/json';
        $versionInformationResult = GeneralUtility::getUrl($versionInformationUrl);

        // if fetching the release data failed, just return the installed
        // version and version 0.0.0 as latest.
        if (!$versionInformationResult) {
            return [TYPO3_version, '0.0.0'];
        }

        $versionInformation = @json_decode($versionInformationResult, true);
        $latestStable = explode('.', $versionInformation['latest_stable']);
        $latestLts = explode('.', $versionInformation['latest_lts']);
        $latest = $versionInformation['latest_stable'];

        // for some wired reason the latest LTS version is greater than
        // the latest stable version. as we are interested in the most recent version
        // we check which of the twe, lts or stable, is the greatest version number, and return
        // this version.
        foreach ($latestStable as $key => $part) {
            if ($latestLts[$key] > $latestStable[$key]) {
                $latest = $versionInformation['latest_lts'];
                break;
            }
        }

        return [
            TYPO3_version,
            $latest,
        ];
    }

}

$instance = new Modules();
$instance->main();
