<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\RewardsCatalog\Controller;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;

abstract class Notification extends Action
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        ProductRepository $productRepository,
        Registry $registry,
        JsonFactory $resultJsonFactory,
        Context $context
    ) {
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultFactory = $context->getResultFactory();

        parent::__construct($context);
    }
}
