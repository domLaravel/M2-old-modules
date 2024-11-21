<?php
namespace BuildingMaterials\CheqApi\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class getApiData {
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }


    public function getApiKey()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
        return $this->scopeConfig->getValue("cheq_api/api_key", $storeScope);
    }

    public function getTagHash()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
        return $this->scopeConfig->getValue("cheq_api/tag_hash", $storeScope);
    }

    public function getElementArray()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
        return $this->scopeConfig->getValue("cheq_api/element_array/element_array_input", $storeScope);
    }

    public function getCustomElementArray()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
        return $this->scopeConfig->getValue("cheq_api/custom_element_array/custom_element_array_input", $storeScope);
    }

    public function getSecretKey()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
        return $this->scopeConfig->getValue("googlerecaptcha/general/invisible/api_secret", $storeScope);
    }

    public function get404Types()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
        return $this->scopeConfig->getValue("cheq_api/404_types/404_types_input", $storeScope);
    }

    public function getCaptchaTypes()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
        return $this->scopeConfig->getValue("cheq_api/captcha_types/captcha_types_input", $storeScope);
    }

}