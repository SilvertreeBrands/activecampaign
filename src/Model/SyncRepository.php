<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SyncRepository implements \ActiveCampaign\Integration\Api\SyncRepositoryInterface
{
    /**
     * @var \ActiveCampaign\Integration\Model\ResourceModel\Sync
     */
    private $modelResource;

    /**
     * @var \ActiveCampaign\Integration\Model\SyncFactory
     */
    private $modelFactory;

    /**
     * @var \ActiveCampaign\Integration\Model\ResourceModel\Sync\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \ActiveCampaign\Integration\Api\Data\SyncSearchResultInterfaceFactory
     */
    private $searchResultInterfaceFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Framework\EntityManager\HydratorInterface
     */
    private $hydrator;

    /**
     * Construct
     *
     * @param \ActiveCampaign\Integration\Model\ResourceModel\Sync $modelResource
     * @param \ActiveCampaign\Integration\Model\SyncFactory $modelFactory
     * @param \ActiveCampaign\Integration\Model\ResourceModel\Sync\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \ActiveCampaign\Integration\Api\Data\SyncSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\EntityManager\HydratorInterface|null $hydrator
     */
    public function __construct(
        \ActiveCampaign\Integration\Model\ResourceModel\Sync $modelResource,
        \ActiveCampaign\Integration\Model\SyncFactory $modelFactory,
        \ActiveCampaign\Integration\Model\ResourceModel\Sync\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        \ActiveCampaign\Integration\Api\Data\SyncSearchResultInterfaceFactory $searchResultInterfaceFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        ?\Magento\Framework\EntityManager\HydratorInterface $hydrator = null
    ) {
        $this->modelResource = $modelResource;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultInterfaceFactory = $searchResultInterfaceFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->hydrator = $hydrator ?? \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\HydratorInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function save(\ActiveCampaign\Integration\Api\Data\SyncInterface $model)
    {
        if ($model->getId()
            && $model instanceof \ActiveCampaign\Integration\Model\Sync
            && !$model->getOrigData()
            && !$model->isObjectNew()
        ) {
            $model = $this->hydrator->hydrate(
                $this->getById((int)$model->getId()),
                $this->hydrator->extract($model)
            );
        }

        try {
            $this->modelResource->save($model);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($exception->getMessage()));
        }

        if ($model->getMageEntityType() === \ActiveCampaign\Integration\Model\Source\MageEntityType::CUSTOMER) {
            $this->reindexMageEntity(
                \Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID,
                $model->getMageEntityId()
            );
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id)
    {
        $model = $this->modelFactory->create();

        $this->modelResource->load($model, $id);

        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('ActiveCampaign Sync ID "%1" does not exist.', $id)
            );
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function getByMageEntity(
        int $mageEntityId,
        string $mageEntityType,
        string $acEntityType,
        int $storeId
    ) {
        $model = $this->modelFactory->create();

        $this->modelResource->loadByMageEntity(
            $model,
            $mageEntityId,
            $mageEntityType,
            $acEntityType,
            $storeId
        );

        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__(
                'Sync entity "%1:%2:%3:%4" does not exist.',
                $mageEntityType,
                $acEntityType,
                $mageEntityId,
                $storeId
            ));
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {
        $collection = $this->collectionFactory->create();

        if ($searchCriteria === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        $searchResult = $this->searchResultInterfaceFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }

    /**
     * @inheritDoc
     */
    public function delete(\ActiveCampaign\Integration\Api\Data\SyncInterface $model): bool
    {
        try {
            $this->modelResource->delete($model);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * Reindex Magento entity
     *
     * This ensures grids are always updated.
     *
     * @param string $indexerId
     * @param int $entityId
     *
     * @return void
     */
    private function reindexMageEntity(string $indexerId, int $entityId): void
    {
        $indexer = $this->indexerRegistry->get($indexerId);

        if ($indexer->getState()->getStatus() == \Magento\Framework\Indexer\StateInterface::STATUS_VALID) {
            $indexer->reindexRow($entityId);
        }
    }
}
