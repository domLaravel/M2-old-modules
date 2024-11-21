<?php
namespace BuildingMaterials\ProductDeliveryText\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DeliveryTextHelper {
	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;

	public function __construct(
		ScopeConfigInterface $scopeConfig
	) {
		$this->scopeConfig = $scopeConfig;
	}


    public function getConfigData()
	{
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
		return $this->scopeConfig->getValue("delivery_text/delivery_text_input/delivery_text_field", $storeScope);
	}

}