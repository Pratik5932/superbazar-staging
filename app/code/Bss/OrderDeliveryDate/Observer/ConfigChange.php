<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OrderDeliveryDate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class ConfigChange implements ObserverInterface
{
    const XML_PATH_FAQ_URL = 'orderdeliverydate/general/cut_off_time';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * ConfigChange constructor.
     * @param RequestInterface $request
     * @param WriterInterface $configWriter
     */
    public function __construct(
        RequestInterface $request,
        WriterInterface $configWriter
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;

    }

    public function execute(EventObserver $observer)
    {
        
        #echo "adasd";exit;
        $faqParams = $this->request->getParam('groups');
        $urlKey = $faqParams['general']['fields']['cut_off_time']['value']; //Current faq_url value, Here you can filter current value
        #print_r($urlKey);exit;
        \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($urlKey);
        $this->configWriter->save('faqtab/general/cut_off_time', $urlKey);
        return $this;
    }
}