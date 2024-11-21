<?php

namespace BuildingMaterials\CheqApi\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use BuildingMaterials\CheqApi\Helper\getApiData AS dataHelper;

class ElementConfig extends Template
{
    /**
     * @var dataHelper
     */
    protected dataHelper $dataHelper;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context                $context,
        dataHelper             $dataHelper,
        array                  $data = []
    )
    {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }
    public function getElementConfig()
    {
        return($this->dataHelper->getElementArray());
    }
    public function getCustomElementConfig()
    {
        return($this->dataHelper->getCustomElementArray());
    }
}
