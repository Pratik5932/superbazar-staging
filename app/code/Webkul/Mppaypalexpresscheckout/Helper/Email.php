<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mppaypalexpresscheckout\Helper;

use Magento\Framework\Exception\MailException;

/**
 * Webkul Mppaypalexpresscheckout Helper Email.
 */
class Email extends \Webkul\Marketplace\Helper\Email
{
    const XML_PATH_EXPRESS_STATUS_CHANGE = 'marketplace/email/expresscheckout_status_template';

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Model\MppaypalexpresscheckoutFactory
     */
    protected $mpExpresscheckoutModel;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Logger\Logger
     */
    private $logger;

    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Mppaypalexpresscheckout\Model\MppaypalexpresscheckoutFactory $mpExpresscheckoutModel
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Webkul\Mppaypalexpresscheckout\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Mppaypalexpresscheckout\Model\MppaypalexpresscheckoutFactory $mpExpresscheckoutModel,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Customer\Model\Customer $customer,
        \Webkul\Mppaypalexpresscheckout\Logger\Logger $logger
    ) {
        parent::__construct(
            $context,
            $inlineTranslation,
            $transportBuilder,
            $messageManager,
            $storeManager,
            $customerSession
        );
        $this->mpExpresscheckoutModel = $mpExpresscheckoutModel;
        $this->mpHelper = $mpHelper;
        $this->customer = $customer;
        $this->logger = $logger;
    }

    /**
     * [sendDetailsStatusMailToSeller description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendDetailsStatusMailToSeller($id)
    {
        $emailTemplateVariables = [];
        try {
            $data = $this->mpExpresscheckoutModel->create()->load($id);
            $enabledStatus = \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout::STATUS_ENABLED;
            $seller = $this->customer->load($data->getSellerId());

            $emailTemplateVariables['sellername'] = $seller->getName();
            if ($data->getStatus() == $enabledStatus) {
                $emailTemplateVariables['status'] = __("Approved");
            } else {
                $emailTemplateVariables['status'] = __("Disapproved");
            }
            $emailTemplateVariables['edit_profile'] = $this->_storeManager->getStore()
            ->getUrl(
                'customer/account/login'
            );
            $adminStoremail = $this->mpHelper->getAdminEmailId();
            $adminEmail = $adminStoremail? $adminStoremail : $this->mpHelper->getDefaultTransEmailId();
            $adminUsername = 'Admin';

            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $this->_template = $this->getTemplateId(self::XML_PATH_EXPRESS_STATUS_CHANGE);
            $this->_inlineTranslation->suspend();
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Email sendDetailsStatusMailToSeller : ".$e->getMessage());
            $this->_messageManager->addError($e->getMessage());
        }
    }

    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }
}
