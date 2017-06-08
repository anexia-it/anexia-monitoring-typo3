# Anexia Monitoring

A TYPO3 extension used to monitor updates for TYPO3 and all installed extensions. It can be also used to check if the 
website is alive and working correctly.


## Installation and configuration

Install the extension by copying the extension code in the ``typo3conf/ext/anexia_monitoring`` folder, or by using the 
Extension Manager.


## Usage

The extensions registers REST endpoints which can be used for monitoring. Make sure that the **accessToken** is configured
within the configuration form of the extension. The endpoints will return a 401 HTTP status code if the token is not
defined or invalid.

### Version monitoring of core and extensions

**URL:** http://your.tld/?eID=anxapi/v1/modules&access_token=YOUR_ACCESS_TOKEN

**Response headers:**
```
Status Code: 200 OK
Access-Control-Allow-Origin: *
Access-Control-Allow-Credentials: true
Allow: GET, OPTIONS
Content-Type: application/json
```

**Response body:**
```json
{
   "runtime":{
      "platform":"php",
      "platform_version":"5.6.30",
      "framework":"typo3",
      "framework_version":"6.2.31",
      "framework_newest_version":"8.7.1"
   },
   "modules":[
      {
         "name":"sitemap_generator",
         "installed_version":"1.0.0",
         "newest_version":"1.1.0"
      },
      {
         "name":"extension_builder",
         "installed_version":"6.2.15",
         "newest_version":"6.2.15"
      },
      {
         "name":"realurl",
         "installed_version":"1.12.8",
         "newest_version":"2.2.1"
      },
      {
         "name":"anexia_monitoring",
         "installed_version":"1.0.0",
         "newest_version":"1.0.0"
      },
      {
         "name":"themes_distribution",
         "installed_version":"0.1.3",
         "newest_version":"0.1.3"
      }
   ]
}
```

### Live monitoring

This endpoint can be used to verify if the application is alive and working correctly. It checks if the database 
connection is working and makes a query for users. It allows to register custom check by using hooks.

**URL:** http://your.tld/?eID=anxapi/v1/up&access_token=YOUR_ACCESS_TOKEN

**Response headers:**
```
Status Code: 200 OK
Access-Control-Allow-Origin: *
Access-Control-Allow-Credentials: true
Allow: GET, OPTIONS
Content-Type: text/pain
```

**Response body:**
```
OK
```

**Custom live monitoring hooks:**

Additional checks can be defined by registering hook functions as follows. Return ``false`` if a check failed.

```php
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['anexia_monitoring-UpCheck'][] =
    'EXT:your_extension/Resources/Private/Hooks/yourHook.php:yourHookFunction';
```

## List of developers

* Andreas Stocker <AStocker@anexia-it.com>, Lead developer

## Project related external resources

* [TYPO3 documentation](https://docs.typo3.org)
