<?php
namespace BuildingMaterials\BulkRedirects\Block\System\Config;

class Select extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'BuildingMaterials_BulkRedirects::system/config/select.phtml';

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
    public function getSelectHtml()
    {
        $html = '<select id="SelectorRanges">';
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/pub/media/csvUploads/default";
        $files = scandir($dir);
        if(count($files) > 0) {
            foreach($files as $file) {
                if(strlen($file) > 3) {
                    $html .= '<option value="'.$file.'">' . $file . '</option>';
                }
            }
        }
        $html .= '</select>';
        return $html;
    }
}