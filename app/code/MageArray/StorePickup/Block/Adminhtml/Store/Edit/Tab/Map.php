<?php
namespace MageArray\StorePickup\Block\Adminhtml\Store\Edit\Tab;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Map
 * @package MageArray\StorePickup\Block\Adminhtml\Store\Edit\Tab
 */
class Map extends \Magento\Backend\Block\Widget implements RendererInterface
{

    protected $_template = 'MageArray_StorePickup::store/map.phtml';

    /**
     * Map constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageArray\StorePickup\Model\StoreFactory $storeFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeFactory = $storeFactory;
    }

    public function getStoreDetail()
    {
        $storeId = $this->getRequest()->getParam('storepickup_id');
        if ($storeId) {
            return $this->_storeFactory->create()->load($storeId);
        }
    }

    public function render(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $this->setElement($element);
        return $this->toHtml();
    }
}