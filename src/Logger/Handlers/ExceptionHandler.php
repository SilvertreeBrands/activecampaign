<?php
declare(strict_types=1);

namespace ActiveCampaign\Logger;

class ExceptionHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var int
     */
    protected $loggerType = \Monolog\Logger::ERROR;

    /**
     * @var string
     */
    protected $fileName = '/var/log/activecampaign-exception.log';
}
