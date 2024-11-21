<?php
namespace BuildingMaterials\ProductBreadCrumbs\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ProductBreadCrumbsHelper {
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
		return $this->scopeConfig->getValue("product_breadcrumbs/product_breadcrumbs", $storeScope);
	}

}