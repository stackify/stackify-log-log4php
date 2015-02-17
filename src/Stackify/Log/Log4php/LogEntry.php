<?php

namespace Stackify\Log\Log4php;

use Stackify\Utils\TypeConverter;
use Stackify\Log\Entities\LogEntryInterface;

final class LogEntry implements LogEntryInterface
{

    /**
     * @var \LoggerLoggingEvent
     */
    private $logEvent;

    public function __construct(\LoggerLoggingEvent $logEvent)
    {
        $this->logEvent = $logEvent;
    }

    public function getContext()
    {
        $context = array();
        foreach (\LoggerMDC::getMap() as $mdcKey => $mdcValue) {
            $context[$mdcKey] = TypeConverter::stringify($mdcValue);
        }
        $ndc = \LoggerNDC::get();
        if (!empty($ndc)) {
            $context['NDC'] = $ndc;
        }
        return empty($context) ? null : $context;
    }

    public function getException()
    {
        $throwable = $this->logEvent->getThrowableInformation();
        if (null !== $throwable) {
            return $throwable->getThrowable();
        }
    }

    public function getLevel()
    {
        return (string) $this->logEvent->getLevel();
    }

    public function getMessage()
    {
        return $this->logEvent->getMessage();
    }

    public function getMilliseconds()
    {
        return round($this->logEvent->getTimeStamp() * 1000);
    }

    public function getNativeError()
    {
        // log4php logger does not support native errors
        return null;
    }

    public function isErrorLevel()
    {
        $errorLevel = \LoggerLevel::getLevelError();
        return $this->logEvent->getLevel()->isGreaterOrEqual($errorLevel);
    }

}
