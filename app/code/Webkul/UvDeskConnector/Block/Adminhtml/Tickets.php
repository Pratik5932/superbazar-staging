<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_UvDeskConnector
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\UvDeskConnector\Block\Adminhtml;

class Tickets extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
     */
    private $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Webkul\UvDeskConnector\Helper\Data
     */
    private $helper;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context     $context
     * @param \Webkul\UvDeskConnector\Model\TicketManager $ticketManager
     * @param \Webkul\UvDeskConnector\Helper\Data         $helper
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\UvDeskConnector\Model\TicketManager $ticketManager,
        \Webkul\UvDeskConnector\Helper\Data $helper,
        array $data = []
    ) {
        $this->_ticketManager = $ticketManager;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * getWysiwygConfig function
     *
     * @return json
     */
    public function getWysiwygConfig()
    {
        $config = json_encode([
            "width"=>"99%", 
            "height"=>"200px", 
            "plugins"=>[["name"=>"image"]],
            "tinymce4"=>[
                "toolbar"=>"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap",
                "plugins"=>"advlist autolink lists link charmap media noneditable table contextmenu paste code help table"]
            ]);
        return $config;
    }

    /**
     * getAllTicketsAccToLabel function get all ticket acc to label
     *
     * @return array
     */
    public function getAllTicketsAccToLabel()
    {
        $page = 1;
        $isLabelId = false;
        $flag = $this->getRequest()->getParam('labels');
        $flag1 = $this->getRequest()->getParam('labelsId');
        if (isset($flag)) {
            $page = $this->getRequest()->getParam('labels');
        } else {
            if (isset($flag1)) {
                $page = $this->getRequest()->getParam('labelsId');
                $isLabelId = true;
            }
        }
        $response = null;
        $response = $this->_ticketManager->getAllTicketsAccToLabel($page, $isLabelId);

        /*to change the blank agent name in to 'unassigned'*/
        array_walk($response['tickets'], [$this, 'changeAgentNameToUnassigned']);
        return $response;
    }

    /**
     * changeAgentNameToUnassigned function to change the blank agent name in to 'unassigned'
     *
     * @param array $value
     * @param array $key
     * @return void
     */
    public function changeAgentNameToUnassigned(&$value, $key)
    {
        if ($value['agent'] == '') {
            $value['agent']['name'] = 'unassigned';
        }
    }

    /**
     * getFilterDataFor function get the ticket acc to the selected filters
     *
     * @param string $filterType
     * @return array
     */
    public function getFilterDataFor($filterType)
    {
        return $this->_ticketManager->getFilterDataFor($filterType);
    }

    /**
     * labelParamater function get the selected labels
     *
     * @return strings
     */
    public function labelParamater()
    {
        $label = $this->getRequest()->getParam('labels');
        $labelId = $this->getRequest()->getParam('labelsId');
        if (isset($label)) {
            return $label;
        } else {
            if (isset($labelId)) {
                return $labelId;
            }
        }
        return null;
    }

    /**
     * getTicketThread function return the all thread of particular ticket id
     *
     * @return array
     */
    public function getTicketThread()
    {
        $ticketId = $this->getRequest()->getParam('id');
        $threads = $this->_ticketManager->getTicketThread(null, $ticketId);
        return $threads;
    }

    /**
     * getErrorMessage function
     *
     * @param array $response
     * @return string
     */
    public function getErrorMessage($response = [])
    {
        return $this->helper->getErrorMessage($response);
    }

    /**
     * getSingleTicketData function get all data related to a single ticket using ticket increment id
     *
     * @return array
     */
    public function getSingleTicketData()
    {
        $ticketIncrementId = $this->getRequest()->getParam('increment_id');
        $ticketData = $this->_ticketManager->getSingleTicketData($ticketIncrementId);
        return $ticketData ;
    }

    /**
     * selectedLabelClass function return the active selected label
     *
     * @param string $key
     * @return string
     */
    public function selectedLabelClass($key = "")
    {
        $label = $this->labelParamater();
        if ($label == $key) {
            return 'active';
        }
        return '';
    }
}
