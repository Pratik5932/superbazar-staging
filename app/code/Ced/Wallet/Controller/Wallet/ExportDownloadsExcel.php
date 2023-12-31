<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Wallet\Controller\Wallet;

use Magento\Framework\App\ResponseInterface;

class ExportDownloadsExcel extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Reports::report_products';

    /**
     * Export products downloads report to XLS format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'products_downloads.xml';
        $content = $this->_view->getLayout()->createBlock(
            \Ced\Wallet\Controller\Wallet\Grid::class
        )->setSaveParametersInSession(
            true
        )->getExcel(
            $fileName
        );

        return $this->_fileFactory->create($fileName, $content);
    }
}
