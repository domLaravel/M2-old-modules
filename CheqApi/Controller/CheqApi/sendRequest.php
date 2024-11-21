<?php
namespace BuildingMaterials\CheqApi\Controller\CheqApi;

use BuildingMaterials\CheqApi\Helper\getApiData AS dataHelper;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Context;

class sendRequest extends \Magento\Framework\App\Action\Action
{
    protected Context $context;
    protected dataHelper $dataHelper;
    protected ResponseInterface $response;

    public function __construct(
        Context $context,
        dataHelper $dataHelper,
        ResponseInterface $response
    ) {
        $this->dataHelper = $dataHelper;
        $this->response = $response;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        echo($this->getCheqApiResponse());
    }

    /**
     * @return string
     */
    public function getCheqApiResponse(): string
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

        $threatType = explode(':',explode(',',$responseRTI)[2])[1];
        $exploded = $this->getExplodedResults($responseRTI);

        $threatType404 = explode(',', $this->dataHelper->get404Types());
        $threatTypeCaptcha = explode(',', $this->dataHelper->getCaptchaTypes());

        return(json_encode(in_array($threatType, $threatType404) ? json_encode(['redirect','404-not-found']) : (in_array($threatType, $threatTypeCaptcha) ? json_encode(['redirect','captchacheck']) : json_encode($exploded))));
    }

    /**
     * @return string
     */
    private function buildQuery(): string
    {
        $eventType = $_POST['eventType'] ?? 'page_load';
        $defaultEventTypes = ["page_load", "add_payment", "add_to_cart", "add_to_wishlist", "registration", "purchase", "search", "start_trial", "subscribe", "form_submission"];
        $customEventType = "";
        if (!in_array($eventType, $defaultEventTypes)) {
            $customEventType = $eventType;
            $eventType = 'custom';
        }

        $host = $_SERVER['HTTP_HOST'];
        $currentUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $currentUrl .= "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $eventUrl = $_POST['eventUrl'] ?? $currentUrl;

        $valueArray = array(
            'ApiKey' => $this->getApiKeyHelper()['api_key_input'],
            'TagHash' => $this->getTagHashHelper()['tag_hash_input'],
            'ClientIP' => $this->getUserIpAddr(),
            'RequestURL' => "$eventUrl",
            'ResourceType' => 'application/json',
            'Method' => 'GET',
            'Host' => $host,
            'UserAgent' => $userAgent,
            'Accept' => '*/*',
            'AcceptLanguage' => 'en-US,en;q=0.9',
            'AcceptEncoding' => 'gzip, deflate, br',
            'HeaderNames' => 'Host,Connection,Accept,Cache-Control,Cookie,User-Agent',
            'EventType' => $eventType,
        );

        if (isset($_COOKIE["_cheq_rti"])) {
            $valueArray['CheqCookie'] = $_COOKIE["_cheq_rti"];
        }

        if ($customEventType) {
            $valueArray['Channel'] = $customEventType;
        }

        return http_build_query($valueArray);
    }

    /**
     * @return string
     */
    protected function getUserIpAddr(): string
    {
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $ip = filter_var($ip, FILTER_VALIDATE_IP);

        return ($ip !== false) ? $ip : 'Invalid IP';
    }

    /**
     * @return array
     */
    protected function getApiKeyHelper(): array
    {
        return($this->dataHelper->getApiKey());
    }

    /**
     * @return array
     */
    protected function getTagHashHelper(): array
    {
        return($this->dataHelper->getTagHash());
    }

    /**
     * @param $results
     * @return array
     */
    public function getExplodedResults($results): array
    {
        $parsedResults = json_decode($results);
        $resultArray = ['Name' => '', 'Value' => '', 'Duration' => '', 'Domain' => '', 'Path' => ''];
        $explodedResults = explode(';', $parsedResults->setCookie);

        foreach (array_keys($resultArray) as $key => $item) {
            // Handle cheq_rti cookie separately
            if ($key == 0) {
                $resultArray['Name'] = "_cheq_rti";
                $resultArray['Value'] = str_replace('_cheq_rti=', '', $explodedResults[$key]);
            } else {
                // Split key-value pairs and assign values to the result array
                $items = explode('=', $explodedResults[$key - 1]);
                $resultArray[$item] = $items[1] ?? ''; // Use the second part of the exploded value, or an empty string if not available
            }
        }

        return $resultArray;
    }
}