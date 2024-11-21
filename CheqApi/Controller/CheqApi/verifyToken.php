<?php
namespace BuildingMaterials\CheqApi\Controller\CheqApi;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use BuildingMaterials\CheqApi\Helper\getApiData AS dataHelper;
use Magento\Framework\App\Action\Context;

class verifyToken extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected dataHelper $dataHelper;
    protected Context $context;
    protected $resultFactory;
    protected ResponseInterface $response;

    public function __construct(
        Context $context,
        dataHelper $dataHelper,
        \Magento\Framework\View\Result\PageFactory $resultFactory,
        ResponseInterface $response
    ) {
        $this->resultFactory = $resultFactory;
        $this->dataHelper = $dataHelper;
        $this->response = $response;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $rawData = file_get_contents('php://input');
        $dataObject = json_decode($rawData);
        $captchaResponse = $dataObject->captcha;
        echo($this->getApiResponse($captchaResponse));
    }

    /**
     * @param $token
     * @return string|null
     */
    public function getApiResponse($token): ?string
    {
        if(isset($token)){
            return $this->sendApi($token);
        } else {
            return null;
        }
    }

    /**
     * @param $token
     * @return string
     */
    private function sendApi($token): string
    {
        $secret = $this->getSecretKey();
        $valueArray = [
            "secret" => $secret,
            "response" => $token,
            "remoteip" => $this->getUserIpAddr()
        ];

        $string = '';
        foreach($valueArray as $key => $value) {
            if($value != end($valueArray)) {
                $string .= $key . '=' . urlencode($value) . '&';
            } else {
                $string .= $key . '=' . urlencode($value);
            }
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $string,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));
        $responseRTI = curl_exec($curl);
        curl_close($curl);
        return $responseRTI;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function validateForCsrf(RequestInterface $request): bool
    {
        return true;
    }

    /**
     * @return string
     */
    private function getSecretKey(): string
    {
        return $this->dataHelper->getSecretKey();
    }

    /**
     * @return string
     */
    private  function getUserIpAddr(): string
    {
        return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    }

}