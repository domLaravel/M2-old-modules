<?php
namespace BuildingMaterials\ProductDeliveryText\Block;

use Magento\Framework\View\Element\Template\Context;
use BuildingMaterials\ProductDeliveryText\Helper\DeliveryTextHelper AS dataHelper;

class DeliveryText extends \Magento\Framework\View\Element\Template {
    /**
	 * @var \BuildingMaterials\ProductDeliveryText\Helper\DeliveryTextHelper
	 */
	protected $dataHelper;

    public function __construct(
		Context $context,
		dataHelper $dataHelper,
		array $data = []
	) {
		$this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
	}

	public function getText() {
		return $this->dataHelper->getConfigData();
	}
}