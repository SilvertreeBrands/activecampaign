<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Logger\Handlers;

class DebugHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;

    /**
     * @var string
     */
    protected $fileName = '/var/log/activecampaign-debug.log';
}
