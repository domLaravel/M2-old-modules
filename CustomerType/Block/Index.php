<?php
namespace BuildingMaterials\CustomerType\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Index extends Template
{
    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param CookieManagerInterface $cookieManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        array $data = []
    ) {
        $this->cookieManager = $cookieManager;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getCookieValue()
    {
        return $this->cookieManager->getCookie('BMN_Type');
    }
}
