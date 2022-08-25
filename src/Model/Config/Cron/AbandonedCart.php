<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Config\Cron;

class AbandonedCart extends \ActiveCampaign\Integration\Model\Config\Cron\AbstractCron
{
    /**
     * @inheirtdoc
     * @var string
     */
    protected $cronExpressionPath = 'crontab/default/jobs/ac_abandoned_cart_sync/schedule/cron_expr';

    /**
     * @inheirtdoc
     * @var string
     */
    protected $cronModelPath = 'crontab/default/jobs/ac_abandoned_cart_sync/run/model';
}
