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

namespace Webkul\UvDeskConnector\Block;

class TicketView extends \Magento\Framework\View\Element\Template
{
    /**
     * __construct function
     *
     * @param \Magento\Framework\View\Element\Template\Context      $context
     * @param \Webkul\UvDeskConnector\Helper\Tickets                $ticketHelper
     * @param \Webkul\UvDeskConnector\Model\TicketManagerCustomer   $ticketManagerCustomer
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketHelper,
        \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ticketHelper = $ticketHelper;
        $this->_ticketManagerCustomer = $ticketManagerCustomer;
    }

    /**
     * getWysiwygConfig function get the configuration of wyswyg editor
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
     * getErrorMessage function get the error message acc to theresponse comes from Uvdesk  end
     *
     * @param array $tickets
     * @return string
     */
    public function getErrorMessage($tickets = [])
    {
        if (isset($tickets['error_description'])) {
            return $tickets['error_description']." Please contact administration.";
        }
        if (isset($tickets['error']) && $tickets['error']!==1 && $tickets['error']!==0) {
            return $tickets['error']." Please contact administration.";
        }
        return __("Some thing went wrong in the Uvdesk configuration. Please contact the administration");
    }

    /**
     * getLoggedInUserDetail function get the logged in user details
     *
     * @return array
     */
    public function getLoggedInUserDetail()
    {
        $customerDetal = $this->_ticketHelper->getLoggedInUserDetail();
        return $customerDetal;
    }

    /**
     * getTicketThread function get the ticket thread depend upon ticket id
     *
     * @return array
     */
    public function getTicketThread()
    {
        $ticketId = $this->getRequest()->getParam('id');
        $threads = $this->_ticketManagerCustomer->getTicketThread(null, $ticketId);
        return $threads;
    }

    /**
     * isCustomer function check the value is equal to customer or not?
     *
     * @param string $name
     * @return string
     */
    public function isCustomer($name)
    {
        if ($name == 'customer') {
            return 'customer';
        }
        return '';
    }

    /**
     * getSingleTicketData function get detail of single ticke based on ticket's increment id
     *
     * @return array
     */
    public function getSingleTicketData()
    {
        $ticketIncrementId = $this->getRequest()->getParam('increment_id');
        $ticketData = $this->_ticketManagerCustomer->getSingleTicketData($ticketIncrementId);
        return $ticketData ;
    }

    /**
     * getCollaboratorImage function get the added collaborator image
     *
     * @param string $smallThumbnail
     * @return string
     */
    public function getCollaboratorImage($smallThumbnail)
    {
        if (empty($smallThumbnail)) {
            return "https://cdn.uvdesk.com/uvdesk/images/d94332c.png";
        }
        return $smallThumbnail;
    }
}
