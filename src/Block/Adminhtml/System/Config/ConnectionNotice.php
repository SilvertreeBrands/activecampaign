<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Block\Adminhtml\System\Config;

class ConnectionNotice extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Escaper
     */
    public $escaper;

    /**
     * @var \ActiveCampaign\Integration\Helper\Api
     */
    public $apiHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Escaper $escaper
     * @param \ActiveCampaign\Integration\Helper\Api $apiHelper
     * @param array $data
     * @param \Magento\Framework\View\Helper\SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Escaper $escaper,
        \ActiveCampaign\Integration\Helper\Api $apiHelper,
        array $data = [],
        ?\Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->escaper = $escaper;
        $this->apiHelper = $apiHelper;

        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * Get connected stores
     *
     * @return array
     */
    public function getConnectedStores(): array
    {
        $result = [];

        foreach ($this->_storeManager->getStores() as $store) {
            $result[$store->getId()]['name'] = $store->getName();
            $result[$store->getId()]['status'] = $this->apiHelper->getConnectionId($store);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('ActiveCampaign_Integration::system/config/connection-notice.phtml');
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
