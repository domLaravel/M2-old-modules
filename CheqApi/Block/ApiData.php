<?php
namespace BuildingMaterials\CheqApi\Block;

use BuildingMaterials\CheqApi\Helper\getApiData AS dataHelper;
use Magento\Backend\Block\Template\Context;

class ApiData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var dataHelper
     */
    protected dataHelper $dataHelper;

    /**
     * @var Context
     */
    protected Context $context;

    public function __construct(
        Context $context,
        dataHelper $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return string
     */
    public function getApiResponse(): string
    {
        $postQuery = $this->buildQuery();

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://rti-eu-west-1.cheqzone.com/v1/realtime-interception',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postQuery,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $responseRTI = curl_exec($curl);
        curl_close($curl);

        return($responseRTI);

    }

    public function getCacheLifetime()
    {
        return null;
    }

    /**
     *
     * @return string
     */
    public function getApiQuery(): string
    {
        return $this->buildQuery();
    }

    /**
     * @return string
     */
    protected function buildQuery()
    {
        if(isset($_COOKIE["_cheq_rti"])) {
            $valueArray = [
                "ApiKey" => $this->getApiKeyHelper()['api_key_input'],
                "TagHash" => $this->getTagHashHelper()['tag_hash_input'],
                "ClientIP" => $this->getUserIpAddr(),
                "RequestURL" => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                "ResourceType" => "application/json",
                "Method" => "GET",
                "Host" => $_SERVER['HTTP_HOST'],
                "UserAgent" => $_SERVER['HTTP_USER_AGENT'],
                "Accept" => "*/*",
                "CheqCookie" => $_COOKIE["_cheq_rti"],
                "AcceptLanguage" => "en-US,en;q=0.9",
                "AcceptEncoding" => "gzip, deflate, br",
                "HeaderNames" => "Host,Connection,Accept,Cache-Control,Cookie,User-Agent",
                "EventType" => "page_load"
            ];
        } else {
            $valueArray = [
                "ApiKey" => $this->getApiKeyHelper()['api_key_input'],
                "TagHash" => $this->getTagHashHelper()['tag_hash_input'],
                "ClientIP" => $this->getUserIpAddr(),
                "RequestURL" => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                "ResourceType" => "application/json",
                "Method" => "GET",
                "Host" => $_SERVER['HTTP_HOST'],
                "UserAgent" => $_SERVER['HTTP_USER_AGENT'],
                "Accept" => "*/*",
                "AcceptLanguage" => "en-US,en;q=0.9",
                "AcceptEncoding" => "gzip, deflate, br",
                "HeaderNames" => "Host,Connection,Accept,Cache-Control,Cookie,User-Agent",
                "EventType" => "page_load"
            ];
        }


        $string = '';
        foreach($valueArray as $key => $value) {
            if($value != end($valueArray)) {
                $string .= $key . '=' . urlencode($value) . '&';
            } else {
                $string .= $key . '=' . urlencode($value);
            }
        }

        return($string);
    }

    protected function getUserIpAddr(){
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * @return string
     */
    protected function getApiKeyHelper(): string
    {
        return($this->dataHelper->getApiKey());
    }

    /**
     * @return string
     */
    protected function getTagHashHelper(): string
    {
        return($this->dataHelper->getTagHash());
    }

    /**
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl('CheqApi/customAjax/index');
    }

    /**
     * @return string
     */
    public function getElementConfig(): string
    {
        return($this->dataHelper->getElementArray());
    }

    /**
     * @return array
     */
    public function getExplodedResults($results): array
    {
        $parsedResults = json_decode($results);
        $resultArray = ['Name' => '','Value' => '','Duration' => '','Domain' => '','Path' => ''];
        $explodedResults = explode(';', $parsedResults->setCookie);
        foreach(array_keys($resultArray) as $key => $item) {
            if($key == 0) { // cheq_rti cookie needs to be handled separately
                $items = explode('=', $explodedResults[$key])[0];
                $resultArray['Value'] = str_replace('_cheq_rti=', '', $explodedResults[$key]);
            } else {
                $items = explode('=', $explodedResults[$key-1])[1];
            }

            if($key != 1){
                $resultArray[$item] = $items;
            }

        }

        return $resultArray;
    }

}