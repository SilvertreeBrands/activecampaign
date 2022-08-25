<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Config\Cron;

class Customer extends \ActiveCampaign\Integration\Model\Config\Cron\AbstractCron
{
    /**
     * @inheirtdoc
     * @var string
     */
    protected $cronExpressionPath = 'crontab/default/jobs/ac_customer_sync/schedule/cron_expr';

    /**
     * @inheirtdoc
     * @var string
     */
    protected $cronModelPath = 'crontab/default/jobs/ac_customer_sync/run/model';
}
