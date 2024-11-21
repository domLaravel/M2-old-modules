<?php

namespace BuildingMaterials\BulkRedirects\Controller\Adminhtml\customAjax;
use Magento\Catalog\Model\ProductRepository;
use BuildingMaterials\BulkRedirects\Helper\ProductUrlKey;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    protected $errorArray = [];
    protected $errors = [];
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ProductRepository $productRepository,
        ProductUrlKey $productUrlKey,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory

    )
    {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->productUrlKey = $productUrlKey;
        $this->resultFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $filePubPath = $_SERVER['DOCUMENT_ROOT'] . "/pub/media/";
        
        try {
            $fh = fopen($filePubPath . 'redirectCSVs/myCsv.csv', 'w' ); //clear csv on start
            fclose($fh);
            $fe = fopen($filePubPath . 'redirectCSVs/errorCSV.csv', 'w' ); //clear csv on start
            fclose($fe);
        } catch(\Exception $e) {
            array_push($this->errors, ["file does not exist on server."]);
        }

        $f = fopen($filePubPath . 'redirectCSVs/myCsv.csv', 'a');
        $ferror = fopen($filePubPath . 'redirectCSVs/errorCSV.csv', 'a');
        fputcsv($f, ["url_rewrite_id", "target_path", "redirect_type"]);
        fputcsv($ferror, ["errorMSG", "sku", "productId", "currentUrl"]);

        $baseFileURL = $filePubPath . "/csvUploads/default/";
        $params = $this->getRequest()->getParams();
        $fileName = $params['selections'];
        $fullUrl = $baseFileURL . $fileName;
        $file = $this->getCSVFile($fullUrl);

        try{
            if (!is_array($file)) {
                $CsvToArray = $this->csvstring_to_array($file);
            }
        } catch(\Exception $e) {
            array_push($this->errors, ["Something went wrong with uploading the file."]);
        }

        foreach($CsvToArray as $key => $items) {
            if($key != 0) {
                $product = $this->getProduct($items[0]);
                if($product != false) {
                    $entityId = $product->getEntityId();
                    $this->createCsv($entityId, $items[1], $items[2], $items[3], $f, $items[0]);
                }
            }
        }

        if($this->errorArray != null) {
            foreach($this->errorArray as $item) { //for debugging purposes
                fputcsv($ferror, [$item[1],$item[2],$item[3],$item[4]]);
            }
        }

        if(!empty($this->errors)) {
            echo(json_encode($this->errors));
        } else {
            echo(true);
        }
        fclose($f);
    }

    /**
     * @param $product
     *
     * @return Mixed
     */
    protected function createCsv($productId, $currentUrl, $url, $type, $f, $sku)
    {
        $productArray = [];
        
        try {
            $productUrlRewriteId = $this->productUrlKey->runSqlQuery($productId, $currentUrl, $sku);
            if(count($productUrlRewriteId) == 1) {
                $productArray[$productId] = $productUrlRewriteId;
                foreach($productUrlRewriteId as $item) {
                    fputcsv($f, [$item, $url, $type]);
                }
            } else if($productUrlRewriteId[0] === 0) { //no request path but has url rewrite attached to item
                array_push($this->errorArray,$productUrlRewriteId);
            } else if($productUrlRewriteId[0] === 1) { //no request path for all product
                array_push($this->errorArray,$productUrlRewriteId);
            }
        } catch(\Exception $e) {
            array_push($this->errors, ["Could not change URL KEY"]);
        }
    }

    /**
     * @param $fullUrl
     *
     * @return array string
     */
    protected function getCSVFile($fullUrl)
    {
        try {
            $file = file_get_contents($fullUrl);
            return $file;
        } catch(\Exception $e) {
            array_push($this->errors,["No such file exists on server. Please upload a file and try again."]);
        }
    }

    /**
     * @param $sku
     *
     * @return Mixed
     */
    protected function getProduct($sku) { //**loop through all products first to check if all available before changing url key
        try {
            $product = $this->productRepository->get($sku);
            return $product;
        } catch(\Exception $e) {
            array_push($this->errors,["Product with SKU: " . $sku . " Does not exist please check."]);
            return false;
        }
    }

    protected function csvstring_to_array($string, $separatorChar = ',', $enclosureChar = '"', $newlineChar = "\n") {
        $array = array();
        $size = strlen($string);
        $columnIndex = 0;
        $rowIndex = 0;
        $fieldValue="";
        $isEnclosured = false;
        for($i=0; $i<$size;$i++) {
    
            $char = $string[$i];
            $addChar = "";
    
            if($isEnclosured) {
                if($char==$enclosureChar) {
    
                    if($i+1<$size && $string[$i+1]==$enclosureChar){
                        // escaped char
                        $addChar=$char;
                        $i++; // dont check next char
                    }else{
                        $isEnclosured = false;
                    }
                }else {
                    $addChar=$char;
                }
            }else {
                if($char==$enclosureChar) {
                    $isEnclosured = true;
                }else {
    
                    if($char==$separatorChar) {
    
                        $array[$rowIndex][$columnIndex] = $fieldValue;
                        $fieldValue="";
    
                        $columnIndex++;
                    }elseif($char==$newlineChar) {
                        echo $char;
                        $array[$rowIndex][$columnIndex] = $fieldValue;
                        $fieldValue="";
                        $columnIndex=0;
                        $rowIndex++;
                    }else {
                        $addChar=$char;
                    }
                }
            }
            if($addChar!=""){
                $fieldValue.=$addChar;
            }
        }
    
        if($fieldValue) { // save last field
            $array[$rowIndex][$columnIndex] = $fieldValue;
        }
        return $array;
    }
}

