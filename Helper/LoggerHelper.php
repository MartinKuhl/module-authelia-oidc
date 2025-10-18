<?php

namespace Martinkuhl\AutheliaOidc\Helper;

use Psr\Log\LoggerInterface;

class LoggerHelper
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Debug-Log schreiben
     *
     * @param string|array $message
     * @param array $context
     */
    public function debug($message, array $context = []): void
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $this->logger->debug($message, $context);
    }

    /**
     * Info-Log schreiben
     *
     * @param string|array $message
     * @param array $context
     */
    public function info($message, array $context = []): void
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $this->logger->info($message, $context);
    }

    /**
     * Warning-Log schreiben
     *
     * @param string|array $message
     * @param array $context
     */
    public function warning($message, array $context = []): void
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $this->logger->warning($message, $context);
    }

    /**
     * Error-Log schreiben
     *
     * @param string|array $message
     * @param array $context
     */
    public function error($message, array $context = []): void
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $this->logger->error($message, $context);
    }

    /**
     * Critical-Log schreiben
     *
     * @param string|array $message
     * @param array $context
     */
    public function critical($message, array $context = []): void
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $this->logger->critical($message, $context);
    }
    
    /**
     * Notice-Log schreiben
     *
     * @param string|array $message
     * @param array $context
     */
    public function notice($message, array $context = []): void
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $this->logger->notice($message, $context);
    }
    
    /**
     * Alert-Log schreiben
     *
     * @param string|array $message
     * @param array $context
     */
    public function alert($message, array $context = []): void
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $this->logger->alert($message, $context);
    }
    
    /**
     * Emergency-Log schreiben
     *
     * @param string|array $message
     * @param array $context
     */
    public function emergency($message, array $context = []): void
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        $this->logger->emergency($message, $context);
    }
}