<?php

namespace BoostMyShop\AdminLogger\Helper;

class Logger
{

    public function log($msg)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/adminlogger.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($msg);
    }

}