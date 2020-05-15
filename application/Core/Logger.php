<?php
namespace Core;

class Logger
{
    protected $application;
    protected $publisher;
    protected $levels =[
                'emergency' => 'EMERGENCY',
                'alert' => 'ALERT',
                'critical' => 'CRITICAL',
                'error' => 'ERROR',
                'warning' => 'WARNING',
                'notice' => 'NOTICE',
                'info' => 'INFO',
                'debug' => 'DEBUG'
            ];
    
    const FACILITY = "payments.synergy.ru";

    public function __construct()
    {        
        $transport = new \Gelf\Transport\UdpTransport(
                $_SERVER['LOG_HOST'], 
                $_SERVER['LOG_PORT'], 
                \Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN
            );

        $this->publisher = new \Gelf\Publisher();
        $this->publisher->addTransport($transport);
    }
    
/**
 * Loggers section
 * GELF logger severity levels:
 * 0 - Emergency (emerg)
 * 1 - Alert (alert)
 * 2 - Critical (crit)
 * 3 - Error (error)
 * 4 - Warning (warning)
 * 5 - Notice (notice)
 * 6 - Informational (info)
 * 7 - Debug (debug)
 */
    public function log($level = 'alert', $shortMessage = '', $fullMessage = '')
    {
        $gelf_level = $this->getLevel($level);
        
        $message = new \Gelf\Message();
        $message->setShortMessage($shortMessage)
                ->setFullMessage($fullMessage)
                ->setLevel($gelf_level)
                ->setHost($_SERVER['HTTP_HOST'])
                ->setFacility(self::FACILITY)
        ;

        $this->publisher->publish($message);
    }
    
    public function getLevel($level)
    {
        if ($this->levels[$level]) {
            return constant('\Psr\Log\LogLevel::'.$this->levels[$level]);
        } else {
            return constant('\Psr\Log\LogLevel::'.$this->levels['alert']);
        }
    }
    
}

