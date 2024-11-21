<?php

namespace BuildingMaterials\MenuCustomization\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Rixxo\Menu\Helper\MenuData AS MenuDataHelper;

class ImageSelector extends Select
{
    private $searchCriteriaBuilder;
    private $categoryList;
	/**
	 * @var \Rixxo\Menu\Helper\MenuData
	 */
	public $menuDataHelper;

    public function __construct(
        Context $context,
        array $data = [],
        CategoryListInterface $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MenuDataHelper $menuDataHelper
    )
    {
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->menuDataHelper = $menuDataHelper;
        parent::__construct($context, $data);
    }

    /**
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/pub/media/wysiwyg/Banners/MenuBanners/default";
        $images = scandir($dir);
        $catArray = [];
        if(count($images) > 0) {
            foreach($images as $image) {
                if(strlen($image) > 3) {
                    $catArray[] = ['value' => $image, 'label' => $image];
                }
            }
        }

        return $catArray;
    }

    /**
     * Fetch all Category list
     *
     * @return CategorySearchResultsInterface
     */
    public function getAllSystemCategory(): CategorySearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->categoryList->getList($searchCriteria);
    }
}