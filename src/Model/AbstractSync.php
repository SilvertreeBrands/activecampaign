<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model;

abstract class AbstractSync extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Iterator callback
     *
     * Sync items traversed by the resource iterator.
     *
     * @param array $args
     *
     * @return void
     */
    abstract public function iteratorCallback(array $args): void;
}
