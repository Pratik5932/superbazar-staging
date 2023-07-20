<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Model\Config\Source;

class Status
{
    const ENABLE    = '1';
    const DISABLE   = '0';
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_manager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Module\Manager $manager
     */
    public function __construct(
        \Magento\Framework\Module\Manager $manager
    ) {
    
        $this->_manager = $manager;
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data =  [
                    ['value'=>self::DISABLE, 'label'=>__('Disable')],
                    ['value'=>self::ENABLE, 'label'=>__('Enable')]
                    
        ];
        return $data;
    }
}
