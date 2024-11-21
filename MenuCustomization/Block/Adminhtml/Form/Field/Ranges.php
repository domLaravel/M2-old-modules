<?php
namespace BuildingMaterials\MenuCustomization\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use BuildingMaterials\MenuCustomization\Block\Adminhtml\Form\Field\Enable;
use BuildingMaterials\MenuCustomization\Block\Adminhtml\Form\Field\CategorySelector;
use BuildingMaterials\MenuCustomization\Block\Adminhtml\Form\Field\ImageSelector;


/**
 * Class Ranges
 */
class Ranges extends AbstractFieldArray
{
    /**
     * @var Enable
     */
    private $enableRenderer;

    /**
     * @var CategorySelector
     */
    private $categoryRenderer;

    /**
     * @var ImageSelector
     */
    private $imageRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('categories', [
            'label' => __('Categories'),
            'renderer' => $this->getCategoryRenderer()
        ]);
        $this->addColumn('Image', [
            'label' => __('Image to use'),
            'renderer' => $this->getImageRenderer()
        ]);
        $this->addColumn('html', ['label' => __('Link')]);
        $this->addColumn('enable', [
            'label' => __('Enable'),
            'renderer' => $this->getEnableRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $optionsEnable = [];
        $optionsCategories = [];
        $optionsImages = [];

        $enable = $row->getEnable();
        if ($enable !== null) {
            $optionsEnable['option_' . $this->getEnableRenderer()->calcOptionHash($enable)] = 'selected="selected"';
        }

        $categories = $row->getCategories();
        if ($categories !== null) {
            $optionsCategories['option_' . $this->getCategoryRenderer()->calcOptionHash($categories)] = 'selected="selected"';
        }

        $images = $row->getImages();
        if ($images !== null) {
            $optionsImages['option_' . $this->getImageRenderer()->calcOptionHash($images)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $optionsEnable);
        $row->setData('option_extra_attrs', $optionsCategories);
        $row->setData('option_extra_attrs', $optionsImages);

    }

    /**
     * @return Enable
     */
    private function getEnableRenderer()
    {
        if (!$this->enableRenderer) {
            $this->enableRenderer = $this->getLayout()->createBlock(
                Enable::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->enableRenderer;
    }

    /**
     * @return CategorySelector
     */
    private function getCategoryRenderer()
    {
        if (!$this->categoryRenderer) {
            $this->categoryRenderer = $this->getLayout()->createBlock(
                CategorySelector::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->categoryRenderer;
    }

    /**
     * @return ImageSelector
     */
    private function getImageRenderer()
    {
        if (!$this->imageRenderer) {
            $this->imageRenderer = $this->getLayout()->createBlock(
                ImageSelector::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->imageRenderer;
    }
}
