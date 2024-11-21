<?php
namespace BuildingMaterials\BulkRedirects\Block\System\Config;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'BuildingMaterials_BulkRedirects::system/config/button.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

     public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
       protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
    public function getAjaxUrl()
    {
        return $this->getUrl('buildingmaterials/customAjax/index');
    }
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'validate-download-btn',
                'label' => __('Validate / Download CSV File'),
            ]
        );

        return $button->toHtml();
    }
}