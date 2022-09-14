<?php

class CustomerOld
{
    /**
     * Sync contact from order object
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function syncContactFromOrder(
        \Magento\Sales\Api\Data\OrderInterface $order
    ): int {
        $contact = $this->contactFactory->create();

        $contact
            ->setEmail($order->getBillingAddress()->getEmail())
            ->setFirstName($order->getBillingAddress()->getFirstname())
            ->setLastName($order->getBillingAddress()->getLastname())
            ->setPhone($order->getBillingAddress()->getTelephone())
        ;

        // Sync contact
        $contactResponse = $this->contactsApi->setConfig(
            $this->apiHelper->getApiKey($order->getStoreId()),
            $this->apiHelper->getApiUrl($order->getStoreId()),
            $this->apiHelper->isDebugActive($order->getStoreId())
        )->sync($contact);

        if (empty($contactResponse->result['contact']['id'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve contact ID from result')
            );
        }

        return (int)$contactResponse->result['contact']['id'];
    }
}
