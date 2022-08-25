<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Block\Adminhtml\System\Config;

class Connection extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Escaper
     */
    public $escaper;

    /**
     * @var \ActiveCampaign\Integration\Helper\Api
     */
    private $apiHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    public $jsonSerializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Escaper $escaper
     * @param \ActiveCampaign\Integration\Helper\Api $apiHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param array $data
     * @param \Magento\Framework\View\Helper\SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Escaper $escaper,
        \ActiveCampaign\Integration\Helper\Api $apiHelper,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        array $data = [],
        ?\Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->escaper = $escaper;
        $this->apiHelper = $apiHelper;
        $this->jsonSerializer = $jsonSerializer;

        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * Returns configuration fields required to perform the ping request
     *
     * @return array
     */
    protected function getFieldMappingArray()
    {
        return [
            'active'            => 'activecampaign_integration_general_active',
            'debug'             => 'activecampaign_integration_general_debug',
            'api_url'           => 'activecampaign_integration_general_api_url',
            'api_key'           => 'activecampaign_integration_general_api_key',
            'connection_name'   => 'activecampaign_integration_general_connection_name',
            'store'             => 'store_switcher'
        ];
    }

    /**
     * Get cache URL
     *
     * @return string
     */
    public function getCacheUrl(): string
    {
        return $this->_urlBuilder->getUrl('adminhtml/cache');
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
        $this->setTemplate('ActiveCampaign_Integration::system/config/connection.phtml');
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $currentButton = $this->escaper->escapeHtmlAttr(__('Connect'));
        $successButton = $this->escaper->escapeHtmlAttr(__('Disconnect'));
        $ajaxUrl = $this->_urlBuilder->getUrl('activecampaign/system_config/connect');
        $connection = true;

        $storeId = $this->getRequest()->getParam('store');
        $isConnected = (bool)$this->apiHelper->getConnectionId($storeId);

        if ($isConnected) {
            $currentButton = $this->escaper->escapeHtmlAttr(__('Disconnect'));
            $successButton = $this->escaper->escapeHtmlAttr(__('Connect'));
            $ajaxUrl = $this->_urlBuilder->getUrl('activecampaign/system_config/disconnect');
            $connection = false;
        }

        $this->addData(
            [
                'button_label'  => __($currentButton),
                'html_id'       => $element->getHtmlId(),
                'ajax_url'      => $ajaxUrl,
                'connection'    => $connection,
                'success_text'  => $successButton,
                'field_mapping' => str_replace(
                    '"',
                    '\\"',
                    $this->jsonSerializer->serialize($this->getFieldMappingArray())
                )
            ]
        );

        return $this->_toHtml();
    }
}
