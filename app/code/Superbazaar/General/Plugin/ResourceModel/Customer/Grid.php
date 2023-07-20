<?php
namespace Superbazaar\General\Plugin\ResourceModel\Customer;

class Grid
{
    public static $table = 'customer_grid_flat';
    public static $leftJoinTable = 'newsletter_subscriber';

    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $request = $objectManager->get("Magento\Framework\App\RequestInterface")->getParams();



            # echo $collection->getSelect()->__toString();exit;

            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

            $collection
            ->getSelect()
            ->joinLeft(
                ['co'=>$leftJoinTableName],
                "co.customer_id = main_table.entity_id",
                [
                    'subscriber_status' => 'co.subscriber_status'
                ]
            );

            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where)->group('main_table.entity_id');

            $postcdeArrray ="";
            if(isset($request['filters']['billing_postcode']) && !$request['search']){
                $postcdeArrray = $request['filters']['billing_postcode'];


                # print_r($postcdeArrray);exit;
                $postCodes = [];
                $collection1 = $objectManager->get('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
                ->getCollection()
                ->addFieldToSelect('seller_id')
                ->addFieldToFilter('address_type', 'postcode') 
                ->addFieldToFilter('postcode', ['in' => $postcdeArrray]);
                $sellerId = $collection1->getColumnValues('seller_id');
                #print_r($sellerId);exit;
                #if(in_array("3024",$postcdeArrray)){
                if (($key = array_search('3024', $postcdeArrray)) !== FALSE) {
                    #unset($sellerId['219']);
                    $sellerId[] = 6637;


                    # $sellerId = array("6637");
                }

                #print_r($sellerId);exit;
                $collectionpostcode = $objectManager->get('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
                ->getCollection()
                ->addFieldToSelect('postcode')
                ->addFieldToFilter('address_type', 'postcode')          
                ->addFieldToFilter('seller_id', $sellerId);
                $postCodes = ($collectionpostcode->getColumnValues('postcode'));
                $postCodes1= array_map('trim', $postCodes);
                #print_R($postCodes1);exit;
                # $addressCollection = $objectManager->get('Magento\Customer\Model\ResourceModel\Address\Collection')->addFieldToFilter('postcode', ['in' =>$postCodes1]);
                # echo $addressCollection->getSize();exit;
                #echo $addressCollection->getSelect()->__toString();exit;

                # if ($addressCollection->getSize()) {
                #    $parentIds = array_unique($addressCollection->getColumnValues('parent_id'));
                #print_r($parentIds);exit;
                if(isset($request['filters']['postcode'])){
                    $collection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
                    $collection->addFieldToFilter('billing_postcode', ['in' => $postCodes1]);
                    $collection->addFieldToFilter('billing_postcode', ['eq' => $request['filters']['postcode']]);
                }else{
                    $collection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
                    $collection->addFieldToFilter('billing_postcode', ['in' => $postCodes1]);

                }
                #  $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where)->in('main_table.entity_id',$parentIds);

                #   }
                #echo $collection->getSelect()->__toString();exit;
            }

            /*if(isset($request['filters']['postcode'])){
               $collection->addFieldToFilter('billing_postcode', ['eq' => $request['filters']['postcode']]);
                #$collection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE); 

            }*/
            # echo $collection->getSelect()->__toString();exit;



        }
        return $collection;
    }     
}