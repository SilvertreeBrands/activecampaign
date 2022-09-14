<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Api\Data;

interface SyncSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @inheritDoc
     * @return \ActiveCampaign\Integration\Api\Data\SyncInterface[]|\Magento\Framework\DataObject[]
     */
    public function getItems(): array;

    /**
     * @inheritDoc
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterface[]|\Magento\Framework\DataObject[] $items
     */
    public function setItems(array $items = null);
}
