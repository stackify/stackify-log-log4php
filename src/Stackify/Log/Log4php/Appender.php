<?php

namespace Stackify\Log\Log4php;

use Stackify\Log\Builder\MessageBuilder;
use Stackify\Exceptions\InitializationException;

class Appender extends \LoggerAppender
{

    const MODE_AGENT = 'Agent';
    const MODE_AGENTSOCKET = 'AgentSocket';
    const MODE_CURL = 'Curl';
    const MODE_EXEC = 'Exec';

    /**
     * Transport to be used
     *
     * @var \Stackify\Log\Transport\TransportInterface
     */
    protected $transport;
    /**
     * Application Name
     *
     * @var string
     */
    protected $appName;
    /**
     * Environment Name
     *
     * @var string
     */
    protected $environmentName;
    /**
     * API Key
     *
     * @var string
     */
    protected $apiKey;
    /**
     * Transport mode
     *
     * @var string
     */
    protected $mode;
    /**
     * Agent Transport Port
     *
     * @var integer
     */
    protected $port;
    /**
     * Curl/Exec Transport Proxy
     *
     * @var string
     */
    protected $proxy;
    /**
     * Exec Transport Curl Path
     *
     * @var string
     */
    protected $curlPath;
    /**
     * Debug Mode
     *
     * @var boolean
     */
    protected $debug;
    /**
     * Log $_SERVER variables
     *
     * @var boolean
     */
    protected $logServerVariables = false;
    /**
     * Addition Custom Configuration
     *
     * @var array
     */
    protected $config = array();
    /**
     * Disable Layout from logging config
     *
     * @var boolean
     */
    protected $requiresLayout = false;

    /**
     * Constructor
     *
     * @param string $name
     * @param array  $config
     */
    public function __construct($name = '', $config = null)
    {
        $this->setConfig($config);
        parent::__construct($name);
    }

    /**
     * Set Application Name
     *
     * @param string $appName Application name
     *
     * @return void
     */
    public function setAppName($appName)
    {
        $this->appName = $this->validateNotEmpty('AppName', $appName);
    }

    /**
     * Set log $_SERVER variables
     *
     * @param boolean $logServerVariables Log server variables
     *
     * @return void
     */
    public function setLogServerVariables($logServerVariables)
    {
        $this->logServerVariables = $logServerVariables;
    }

    /**
     * Set Environtment Name
     *
     * @param string $environmentName Environment name
     *
     * @return void
     */
    public function setEnvironmentName($environmentName)
    {
        $this->environmentName = $environmentName;
    }

    /**
     * Set API Key
     *
     * @param string $apiKey API Key
     *
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $this->validateNotEmpty('ApiKey', $apiKey);
    }

    /**
     * Set Transport Mode
     *
     * @param string $mode Mode
     *
     * @return void
     */
    public function setMode($mode)
    {
        $this->mode = ucfirst(strtolower($mode));
    }

    /**
     * Set Agent Transport Port
     *
     * @param integer $port Port
     *
     * @return void
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Set Curl/Exec Transport Proxy
     *
     * @param string $proxy Proxy
     *
     * @return void
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    /**
     * Set Exec Transport Curl Path
     *
     * @param string $curlPath Curl path
     *
     * @return void
     */
    public function setCurlPath($curlPath)
    {
        $this->curlPath = $curlPath;
    }

    /**
     * Set Debug
     *
     * @param boolean $debug Debug
     *
     * @return void
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Set Additional Config
     *
     * @param string|array $config Config
     *
     * @return void
     */
    public function setConfig($config)
    {
        if (is_string($config)) {
            $config = @json_decode($config, true);
        }
        $this->config = $config;
    }

    /**
     * Append logging event
     *
     * @param \LoggerLoggingEvent $event Event
     *
     * @return void
     */
    protected function append(\LoggerLoggingEvent $event)
    {
        if (null === $this->transport) {
            $messageBuilder = new MessageBuilder('Stackify log4php v.1.1', $this->appName, $this->environmentName, $this->logServerVariables);
            $this->transport = $this->createTransport();
            $this->transport->setMessageBuilder($messageBuilder);
        }
        $logEntry = new LogEntry($event);
        $this->transport->addEntry($logEntry);
    }

    /**
     * Close logger
     *
     * @return void
     */
    public function close()
    {
        parent::close();
        if (null !== $this->transport) {
            $this->transport->finish();
        }
    }

    /**
     * Validate value
     *
     * @param string $name  Name
     * @param string $value Value
     *
     * @return void
     */
    protected function validateNotEmpty($name, $value)
    {
        $result = trim($value);
        if (empty($result)) {
            throw new InitializationException("$name cannot be empty");
        }
        return $result;
    }

    /**
     * Create Transport
     *
     * @return \Stackify\Log\Transport\TransportInterface
     */
    protected function createTransport()
    {
        $options = array(
            'proxy' => $this->proxy,
            'debug' => $this->debug,
            'port'  => $this->port,
            'curlPath' => $this->curlPath,
            'config' => $this->config
        );
        if (null === $this->mode) {
            $this->mode = self::MODE_AGENTSOCKET;
        }
        $allowed = array(
            self::MODE_AGENT,
            self::MODE_AGENTSOCKET,
            self::MODE_CURL,
            self::MODE_EXEC,
        );
        if (in_array($this->mode, $allowed)) {
            $className = '\Stackify\Log\Transport\\' . $this->mode . 'Transport';
            if (self::MODE_AGENT === $this->mode || self::MODE_AGENTSOCKET === $this->mode) {
                return new $className($options);
            }
            return new $className($this->apiKey, $options);
        }
        throw new InitializationException("Mode '$this->mode' is not supported");
    }

}
