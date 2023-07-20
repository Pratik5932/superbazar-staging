<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /** @var LayoutInterface  */
    protected $_layout;
    
    /** @var \Webkul\SocialSignup\Helper\Data  */
    private $helper;

    public function __construct(
        LayoutInterface $layout,
        \Webkul\SocialSignup\Helper\Data $helper
    ) {
    
        $this->_layout = $layout;
        $this->helper = $helper;
    }

    public function getConfig()
    {
        $helper = $this->helper;
        return [
            'webkul_socialsignup' => $helper->getSocialSignupConfiguration()
        ];
    }
}
