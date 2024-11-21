<?php
namespace BuildingMaterials\BulkRedirects\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;

class ProductUrlKey extends AbstractHelper
{
    protected $resourceConnection;
    protected $tableName = "bmm2_url_rewrite";
    public function __construct(Context $context, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }
    /**
    * @param $tableName
    *
    */
    public function runSqlQuery($productId, $currentUrl, $sku)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName($this->tableName);
            $query = "SELECT url_rewrite_id FROM " . $table . " WHERE entity_id = " . '"' . $productId . '" AND entity_type = ' . '"product" AND store_id = ' . '"1" AND request_path = ' . '"' . $currentUrl . '" ORDER BY url_rewrite_id desc';
            $result = $connection->fetchAll($query);
            if(isset($result[0])) {
                return($result[0]);
            } else {
                $query = "SELECT url_rewrite_id, request_path FROM " . $table . " WHERE entity_id = " . '"' . $productId . '" AND entity_type = ' . '"product" AND store_id = ' . '"1" ORDER BY url_rewrite_id desc';
                $result = $connection->fetchAll($query);

                if(isset($result[0])) {
                    return [0, "request_path does not exist for this product But results are found for entity_id (URL given could be incorrect)", $sku, $productId, $currentUrl, $result];
                } else {
                    return [1, "request_path does not exist for this product", $sku, $productId, $currentUrl];
                }
            }
        } catch(\Exception $e) {
            return([$e]);
        }
    }
}