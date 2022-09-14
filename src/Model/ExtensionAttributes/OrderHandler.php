<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\ExtensionAttributes;

class OrderHandler
{
    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var \ActiveCampaign\Integration\Api\SyncRepositoryInterface
     */
    private $repository;

    /**
     * @var \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory
     */
    private $modelFactory;

    /**
     * Construct
     *
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory
     * @param \ActiveCampaign\Integration\Api\SyncRepositoryInterface $repository
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory $modelFactory
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        \ActiveCampaign\Integration\Api\SyncRepositoryInterface $repository,
        \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory $modelFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->repository = $repository;
        $this->modelFactory = $modelFactory;
    }

    /**
     * Load extension attributes
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load(
        \Magento\Sales\Api\Data\OrderInterface $order
    ): \Magento\Sales\Api\Data\OrderInterface {
        $extension = $order->getExtensionAttributes();

        if ($extension === null) {
            $extension = $this->orderExtensionFactory->create();
        } elseif ($extension->getAcSync()) {
            return $order;
        }

        try {
            $acSync = $this->repository->getByMageEntity(
                \ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_ORDER,
                (int)$order->getId()
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException) {
            $acSync = $this->modelFactory->create();
        }

        $extension->setAcSync($acSync);
        $order->setExtensionAttributes($extension);

        return $order;
    }

    /**
     * Save extension attributes
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(
        \Magento\Sales\Api\Data\OrderInterface $order
    ): \Magento\Sales\Api\Data\OrderInterface {
        if (!$order->getExtensionAttributes()
            || !$order->getExtensionAttributes()->getAcSync()) {
            return $order;
        }

        $extension = $order->getExtensionAttributes();
        $acSync = $extension->getAcSync();

        $this->repository->save($acSync);

        $extension->setAcSync($acSync);
        $order->setExtensionAttributes($extension);

        return $order;
    }
}
