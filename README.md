[![PHP version](https://badge.fury.io/ph/stackify%2Flog4php.svg)](http://badge.fury.io/ph/stackify%2Flog4php)

# Stackify log4php Appender

Apache log4php appender for sending log messages and exceptions to Stackify.
Apache log4php >= 2.2.0 is supported.

* **Errors and Logs Overview:** http://support.stackify.com/errors-and-logs-overview/
* **Sign Up for a Trial:** http://www.stackify.com/sign-up/

## Installation

Install the latest version with `composer require stackify/log4php`

### Installation with Linux Agent

This is the suggested installation option, offering the best logging performance.

```xml
<root>
    ...
    <appender_ref ref="stackifyAppender" />
</root>

<appender name="stackifyAppender" class="\Stackify\Log\Log4php\Appender">
    <param name="appName" value="application_name" />
    <param name="environmentName" value="environment_name" />
</appender>
```

#### Optional Configuration

**Log Server Environment Variables**
- Server environment variables can be added to error log message metadata. **Note:** This will log all 
system environment variables; do not enable if sensitive information such as passwords or keys are stored this way.

 ```xml
<param name="logServerVariables" value="true" />
```

### Installation without Linux Agent

This option does not require a Stackify Agent to be installed because it sends data directly to Stackify services. It collects log entries in batches, calls curl using the ```exec``` function, and sends data to the background immediately [```exec('curl ... &')```]. This will affect the performance of your application minimally, but it requires permissions to call ```exec``` inside the PHP script and it may cause silent data loss in the event of any network issues. This transport method does not work on Windows. To configure ExecTransport you need to pass the environment name and API key (license key):

```xml
<root>
    ...
    <appender_ref ref="stackifyAppender" />
</root>

<appender name="stackifyAppender" class="\Stackify\Log\Log4php\Appender">
    <param name="appName" value="application_name" />
    <param name="environmentName" value="environment_name" />
    <param name="mode" value="exec" />
    <param name="apiKey" value="api_key" />
</appender>
```

#### Optional Configuration

**Proxy**
- ExecTransport supports data delivery through proxy. Specify proxy using [libcurl format](http://curl.haxx.se/libcurl/c/CURLOPT_PROXY.html): <[protocol://][user:password@]proxyhost[:port]>
```xml
<param name="proxy" value="https://55.88.22.11:3128" />
```

**Curl path**
- It can be useful to specify ```curl``` destination path for ExecTransport. This option is set to 'curl' by default.
```xml
<param name="curlPath" value="/usr/bin/curl" />
```
 
## Notes

To get more error details pass Exception objects to the logger if available:
```php
try {
    $db->connect();
catch (DbException $ex) {
    $logger->error('DB is not available', $ex);
}
```

All data added to the [MDC](https://logging.apache.org/log4php/apidocs/class-LoggerMDC.html) or [NDC](https://logging.apache.org/log4php/apidocs/class-LoggerNDC.html) will automatically be captured and attached to your log message. This information will be available as JSON data and will be searchable within Stackify.

## Troubleshooting
If transport does not work, try looking into ```vendor\stackify\logger\src\Stackify\debug\log.log``` file (if it is available for writing). Errors are also written to global PHP [error_log](http://php.net/manual/en/errorfunc.configuration.php#ini.error-log).
Note that ExecTransport does not produce any errors at all, but you can switch it to debug mode:
```php
<param name="debug" value="1" />
```

## Additional Configuration
For additional configurations, you can set on the XML or the PHP File Configuration. Reference for the additional options are located on the stackify logger repository [Stackify PHP Logger - Configuration Settings](stackify/stackify-api-php#configuration-settings)

### XML Configuration
- The string should be a JSON String and HTML Encoded (will improve this eventually.)
```php
// Sample PHP Config
$config = array(
    'Debug' => true,
    'DebugLogPath' => '/path/to/log.log'
);
// 1. Converting PHP array to JSON String
// - String: {"Debug":true,"DebugLogPath":"/path/to/log.log"}
$jsonString = json_encode($config);
// 2. XML Encode the JSON String
//   - ' is replaced with &apos;
//   - " is replaced with &quot;
//   - & is replaced with &amp;
//   - < is replaced with &lt;
//   - > is replaced with &gt;
// - Encoded String: {&quot;Debug&quot;:true,&quot;DebugLogPath&quot;:&quot;/path/to/log.log&quot;}
// - Note: In case the string has new line ("\n") or a carriage return ("\r") character, you still need to escape it
//   - \n should be replaced with &#10; or &#xA;
//   - \r should be replaced with &#13; or &#xD;
// - Reference: https://stackoverflow.com/a/29924176/14542233
$xmlEncode = htmlspecialchars($jsonString, ENT_XML1 | ENT_QUOTES, 'UTF-8');

// 3. Add it as a value on XML Configuration
<param name="config" value="{&quot;Debug&quot;:true,&quot;DebugLogPath&quot;:&quot;/path/to/log.log&quot;}" />
```

### PHP Configuration
- If using the PHP configuration, basically add the `config` attribute on the `params` under the Stackify Appender settings
```php
<?php
return array(
    'rootLogger' => array(
        'appenders' => array('stackifyAppender'),
    ),
    'appenders' => array(
        'stackifyAppender' => array(
            'class' => '\Stackify\Log\Log4php\Appender',
            'params' => array(
                'appName' => 'application-name',
                'environmentName' => 'environment-name',
                // Additional configuration
                'config' => array(
                    'Debug' => true,
                    'DebugLogPath' => 'logConfig.log'
                )
            )
        )
```

## License

Copyright 2019 Stackify, LLC.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
