<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Api;

interface SyncRepositoryInterface
{
    /**
     * Save
     *
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterface $model
     *
     * @return \ActiveCampaign\Integration\Api\Data\SyncInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(\ActiveCampaign\Integration\Api\Data\SyncInterface $model);

    /**
     * Get by ID
     *
     * @param int $id
     *
     * @return \ActiveCampaign\Integration\Api\Data\SyncInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id);

    /**
     * Get by Magento entity
     *
     * @param int $mageEntityId
     * @param string $mageEntityType
     * @param string $acEntityType
     * @param int $storeId
     *
     * @return \ActiveCampaign\Integration\Api\Data\SyncInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByMageEntity(int $mageEntityId, string $mageEntityType, string $acEntityType, int $storeId);

    /**
     * Get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     *
     * @return \ActiveCampaign\Integration\Api\Data\SyncSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);

    /**
     * Delete
     *
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterface $model
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\ActiveCampaign\Integration\Api\Data\SyncInterface $model);
}
