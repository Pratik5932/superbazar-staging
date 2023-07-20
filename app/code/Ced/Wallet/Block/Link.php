<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Wallet\Block;

/**
 * Block representing link with two possible states.
 * "Current" state means link leads to URL equivalent to URL of currently displayed page.
 *
 * @api
 * @method string                          getLabel()
 * @method string                          getPath()
 * @method string                          getTitle()
 * @method null|array                      getAttributes()
 * @method null|bool                       getCurrent()
 * @method \Magento\Framework\View\Element\Html\Link\Current setCurrent(bool $value)
 */
class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * Default path
     *
     * @var \Magento\Framework\App\DefaultPathInterface
     */
    protected $_defaultPath;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
    	\Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
    	 parent::__construct($context,$defaultPath, $data);
    	 $this->_defaultPath = $defaultPath;
    	 $this->_objectManager = $objectManager;
      
    }

  /**
     * Get current mca
     *
     * @return string
     */
    private function getMca()
    {
        $routeParts = [
            'module' => $this->_request->getModuleName(),
            'controller' => $this->_request->getControllerName(),
            'action' => $this->_request->getActionName(),
        ];

        $parts = [];
        foreach ($routeParts as $key => $value) {
            if (!empty($value) && $value != $this->_defaultPath->getPart($key)) {
                $parts[] = $value;
            }
        }
        return implode('/', $parts);
    }

  

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
    	$html ='';
    	
    	$customerId = $this->_objectManager->get("Magento\Customer\Model\Session")->getCustomer()->getId();
    	if($customerId){
    	    $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($customerId);
    	    
    	    if(intval($customer->getEnableWalletSystem())){
        		if (false != $this->getTemplate()) {
        			return parent::_toHtml();
        		}
        		$highlights = '';
        		$title = $this->getTitle();
        		if ($this->getIsHighlighted()) {
        			$highlights = ' current';
        		}
        		if ($this->isCurrent()) {
        			$html = '<li class="nav item current">';
        			$html .= '<strong>'
        					. $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getLabel()))
        					. '</strong>';
        			$html .= '</li>';
        			
        		} else {
        			$html = '<li class="nav item' . $highlights . '"><a href="' . $this->escapeHtml($this->getHref()) . '"';
        			$html .= $title
        			? ' title="' . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getTitle())) . '"'
        					: '';
        			$html  .= $this->getAttributesHtml() . '>';
        		    if ($this->getIsHighlighted()) {
        				$html .= '<strong>';
        			}
        		    $html  .= $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getLabel()));
        		    if ($this->getIsHighlighted()) {
        				$html .= '</strong>';
        			}
        		    $html .= '</a></li>';
        		}
        	}
    	}
        return $html;
    }

  
    private function getAttributesHtml()
    {
        $attributesHtmls = '';
        $attributes = $this->getAttributes();
        if ($attributes) {
            foreach ($attributes as $attribute => $value) {
                $attributesHtmls .= ' ' . $attribute . '="' . $this->escapeHtml($value) . '"';
            }
        }

        return $attributesHtmls;
    }
}
