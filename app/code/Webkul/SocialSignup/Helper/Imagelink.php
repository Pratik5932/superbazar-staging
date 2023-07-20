<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Helper;

use Magento\Framework\View\Element\AbstractBlock;

class Imagelink extends \Magento\Framework\View\Element\AbstractBlock
{

    public function __construct(
        \Magento\Framework\View\Element\Context $context
    ) {
    
        parent::__construct($context);
    }

    public function getWebImageLink($imagename)
    {
        $image = parent::getViewFileUrl($imagename);
        return $image;
    }
}
