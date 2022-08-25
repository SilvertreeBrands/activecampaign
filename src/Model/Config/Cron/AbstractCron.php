<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Config\Cron;

abstract class AbstractCron extends \Magento\Framework\App\Config\Value
{
    private const CRON_REGEX = '/^((((\d+,)+\d+|(\d+(\/|-|#)\d+)|\d+L?|\*(\/\d+)?|L(-\d+)?|\?|[A-Z]{3}(-[A-Z]{3})?) ?)'
        . '{5,7})$|(@(annually|yearly|monthly|weekly|daily|hourly|reboot))|(@every (\d+(ns|us|µs|ms|s|m|h))+)$/';

    /**
     * @var string
     */
    protected $cronExpressionPath = '';

    /**
     * @var string
     */
    protected $cronModelPath = '';

    /**
     * @var \ActiveCampaign\Integration\Helper\Data
     */
    private $helper;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \ActiveCampaign\Integration\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \ActiveCampaign\Integration\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @inheirtdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        $cronExpression = $this->getValue();
        if (!preg_match(self::CRON_REGEX, $cronExpression)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The cron expression does not match regex "%1"', self::CRON_REGEX)
            );
        }

        $this->helper->setStoreCrontabConfig(
            $cronExpression,
            $this->cronExpressionPath,
            $this->cronModelPath
        );

        return parent::afterSave();
    }
}
