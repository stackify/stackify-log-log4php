stackify-log-log4php
================

Apache log4php appender for sending log messages and exceptions to Stackify.
Apache log4php >= 2.2.0 is supported.

Errors and Logs Overview:

http://docs.stackify.com/m/7787/l/189767

Smarter Errors & Logs for PHP:

http://stackify.com/smarter-errors-logs-php/

Sign Up for a Trial:

http://www.stackify.com/sign-up/

## Installation

Install the latest version with `composer require stackify/log4php`

Or add dependency to `composer.json` file:
```json
"stackify/log4php": "~1.0",
```

There are three different transport options that can be configured to send data to Stackify.  Below will show how to implement the different transport options.

Once the appender is configured, add it to your root logger definition.

```xml
<root>
    ...
    <appender_ref ref="stackifyAppender" />
</root>
```

### ExecTransport

ExecTransport does not require a Stackify agent to be installed because it sends data directly to Stackify services. It collects log entries in batches, calls curl using the ```exec``` function, and sends data to the background immediately [```exec('curl ... &')```]. This will affect the performance of your application minimally, but it requires permissions to call ```exec``` inside the PHP script and it may cause silent data loss in the event of any network issues. This transport method does not work on Windows. To configure ExecTransport you need to pass the environment name and API key (license key):

```xml
<appender name="stackifyAppender" class="\Stackify\Log\Log4php\Appender">
    <param name="appName" value="application_name" />
    <param name="environmentName" value="environment_name" />
    <param name="mode" value="exec" />
    <param name="apiKey" value="api_key" />
</appender>
```

#### Optional Configuration

<b>Proxy</b>
- ExecTransport supports data delivery through proxy. Specify proxy using [libcurl format](http://curl.haxx.se/libcurl/c/CURLOPT_PROXY.html): <[protocol://][user:password@]proxyhost[:port]>
```xml
<param name="proxy" value="https://55.88.22.11:3128" />
```

<b>Curl path</b>
- It can be useful to specify ```curl``` destination path for ExecTransport. This option is set to 'curl' by default.
```xml
<param name="curlPath" value="/usr/bin/curl" />
```

### CurlTransport

CurlTransport does not require a Stackify agent to be installed and it also sends data directly to Stackify services. It collects log entries in a single batch and sends data using native [PHP cURL](http://php.net/manual/en/book.curl.php) functions. This way is a blocking one, so it should not be used on production environments. To configure CurlTransport you need to pass environment name and API key (license key):

```xml
<appender name="stackifyAppender" class="\Stackify\Log\Log4php\Appender">
    <param name="appName" value="application_name" />
    <param name="environmentName" value="environment_name" />
    <param name="mode" value="curl" />
    <param name="apiKey" value="api_key" />
</appender>
```

#### Optional Configuration

<b>Proxy</b>
- CurlTransport supports data delivery through proxy. Specify proxy using [libcurl format](http://curl.haxx.se/libcurl/c/CURLOPT_PROXY.html): <[protocol://][user:password@]proxyhost[:port]>
```xml
<param name="proxy" value="https://55.88.22.11:3128" />
```

### AgentTransport

AgentTransport does not require additional configuration in your PHP code because all data is passed to the [Stackify agent](https://stackify.screenstepslive.com/s/3095/m/7787/l/119709-installation-for-linux). The agent must be installed on the same machine. Local TCP socket on port 10515 is used, so performance of your application is affected minimally.

```xml
<appender name="stackifyAppender" class="\Stackify\Log\Log4php\Appender">
    <param name="appName" value="application_name" />
</appender>
```

You will need to enable the TCP listener by checking the "PHP App Logs (Agent Log Collector)" in the server settings page in Stackify. See [Log Collectors Page](http://docs.stackify.com/m/7787/l/302705-log-collectors) for more details.

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

## License

Copyright 2015 Stackify, LLC.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
