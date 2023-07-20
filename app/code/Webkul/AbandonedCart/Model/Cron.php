<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AbandonedCart
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AbandonedCart\Model;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;

class Cron
{
    /**
     * enabled webkul abandoned cart
     **/
    const WK_ABANDONED_CART_ENABLED = "webkul_abandoned_cart/abandoned_cart_settings/enable_disable_abandoned_cart";

    /**
     * cron schedule
     **/
    const WK_CRON_SCHEDULE = "webkul_abandoned_cart/abandoned_cart_cron/crone_schedule";

    /**
     * auto mail send enabled
     **/
    const WK_MAIL_STATUS = "webkul_abandoned_cart/abandoned_cart_mail_configuration/abandoned_cart_auto_mail_status";

    /**
     * abandoned cart days
     **/
    const WK_ABANDONED_CART_DAYS = 'webkul_abandoned_cart/abandoned_cart_settings/abanconed_cart_days';

    /**
     * abandoned cart hours
     **/
    const WK_ABANDONED_CART_TIME_HOURS = 'webkul_abandoned_cart/abandoned_cart_settings/abandoned_cart_time_hours';

    /**
     * admin name in email
     **/
    const WK_ADMIN_NAME_IN_MAIL = 'webkul_abandoned_cart/abandoned_cart_mail_configuration/admin_name_in_email';

    /**
     * admin email id
     **/
    const WK_ADMIN_EMAIL_ID = 'webkul_abandoned_cart/abandoned_cart_mail_configuration/admin_email_id';

    /**
     * abandoned cart mail content
     **/
    const WK_MAIL_BODY_ID = "webkul_abandoned_cart/abandoned_cart_mail_configuration/abandoned_cart_mail_content_";

    /**
     * follow up second mail
     **/
    const WK_FOLLOWUP_SECOND_MAIL = 'webkul_abandoned_cart/abandoned_cart_settings/follow_up_second_mail';

    /**
     * follow up third mail
     **/
    const WK_FOLLOWUP_THIRD_MAIL = 'webkul_abandoned_cart/abandoned_cart_settings/follow_up_third_mail';

    /**
     * first mail template
     **/
    const FIRST_MAIL_TEMP="webkul_abandoned_cart/abandoned_cart_mail_configuration/abandoned_cart_first_mail_template";

    /**
     * second mail template
     **/
    const SCND_MAIL_TEMP= "webkul_abandoned_cart/abandoned_cart_mail_configuration/abandoned_cart_second_mail_template";

    /**
     * third mail template
     **/
    const THIRD_TEMPLATE="webkul_abandoned_cart/abandoned_cart_mail_configuration/abandoned_cart_third_mail_template";

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     **/
    protected $_scopeConfig;

    /**
     * @var \Webkul\AbandonedCart\Logger\Logger
     **/
    protected $_logger;

    /**
     * @var \Magento\Quote\Model\Quote
     **/
    protected $_quoteModel;

    /**
     * @var \Webkul\AbandonedCart\Model\History
     **/
    protected $_abandonedCartMailHistory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     **/
    protected $_localeDate;

    /**
     * @var \Webkul\AbandonedCart\Helper\Email
     **/
    protected $_mailHelper;

    /**
     * @param \Magento\Quote\Model\Quote $quoteModel,
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
     * @param \Webkul\AbandonedCart\Logger\Logger $logger,
     * @param \Webkul\AbandonedCart\Model\History $abandonedCartMailHistory,
     * @param \Webkul\AbandonedCart\Helper\Email $abandonedCartMailHelper,
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     **/
    public function __construct(
        \Magento\Quote\Model\Quote $quoteModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Webkul\AbandonedCart\Logger\Logger $logger,
        \Webkul\AbandonedCart\Model\History $abandonedCartMailHistory,
        \Webkul\AbandonedCart\Helper\Email $abandonedCartMailHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_quoteModel = $quoteModel;
        $this->_abandonedCartMailHistory = $abandonedCartMailHistory;
        $this->_localeDate = $localeDate;
        $this->_mailHelper = $abandonedCartMailHelper;
    }

    /**
     * cron is managed and executed according to the admin configurations
     *
     * @return bool
     **/
    public function execute()
    {
        if (!$this->getConfiguration(self::WK_ABANDONED_CART_ENABLED)) {
            return false;
        }
        try {
            $mailRecord = $this->_abandonedCartMailHistory
                                ->getCollection()
                                ->setOrder('sent_on', 'DESC')
                                ->setPageSize(1);
            if ($mailRecord) {
                foreach ($mailRecord as $item) {
                    $time = $this->getConfiguration(self::WK_CRON_SCHEDULE);
                    $time = explode(",", $time);
                    $cronHours = $time['0'];
                    $cronMinutes = $time['1'];

                    $sentOn = $this->_localeDate->date($item->getCreatedAt());

                    $currentTime = $this->_localeDate->date();
                    $diff = date_diff($sentOn, $currentTime);
                    $hoursdiff = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i/60;
                    $minutesdiff = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h)
                    * 60 + $diff->i + $diff->s/60;
                    if ($diff->invert) {
                        $hoursdiff = -1 * $hoursdiff;
                        $minutesdiff = -1 * $minutesdiff;
                    }

                    if (($hoursdiff < $cronHours) || ($minutesdiff < $cronMinutes)) {
                        $this->_logger->info("Cron executed, no mails sent.");
                        return false;
                    }
                }
            }

            $autoMailEnabled = $this->getConfiguration(self::WK_MAIL_STATUS);
            $this->_logger->info('Abandoned Cart Cron Executed');
            if ($autoMailEnabled) {
                $quoteCollection = $this->getQuoteCollection();
                foreach ($quoteCollection as $cart) {
                    if ($this->isCartAbandoned($cart)) {
                        $messagesSent = $this->messageSent($cart);
                        $template = $this->getEmailTemplateAccordingToEmailCounter($messagesSent);
                        $sendMail = $this->sendFollowUpMail($messagesSent, $cart, $template);
                        $this->updateMailRecord($cart);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->info("Error while executing cron ".$e->getMessage());
        }
    }

    /**
     * get filtered quote collection
     *
     * @return \Magento\Quote\Model\Quote Object
     **/
    public function getQuoteCollection()
    {
        return $this->_quoteModel
                    ->getCollection()
                    ->addFieldToFilter('items_count', ['gt' => 0])
                    ->addFieldToFilter('customer_email', ['notnull' => true])
                    ->addFieldToFilter('converted_at', ['null' => true])
                    ->addFieldToFilter('reserved_order_id', ['null' => true])
                    ->addFieldToFilter('is_active', "1");
    }

    /**
     * check if the cart is abandoned
     *
     * @param \Magento\Quote\Model\Quote $cart
     * @return bool
     **/
    public function isCartAbandoned(\Magento\Quote\Model\Quote $cart)
    {
        $limitDaysAbandoned = $this->getConfiguration(self::WK_ABANDONED_CART_DAYS);

        $currDateTime = $this->_localeDate->date();
        $date = $this->_localeDate->date($cart->getUpdatedAt());
        $diff = date_diff($date, $currDateTime);
        $days = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h/24 + $diff->i / 60;
        if ($diff->invert) {
            $days = -1 * $days;
        }

        if ($days < $limitDaysAbandoned) {
            $hours = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i/60;
            if ($diff->invert) {
                $hours = -1 * $hours;
            }
            $hoursLimit = $this->getConfiguration(self::WK_ABANDONED_CART_TIME_HOURS);

            if ($hours >= $hoursLimit) {
                return $this->checkLastMail($cart);
            }
        }
        return false;
    }

    /**
     * get store configuration
     *
     * @param string admin config id
     **/
    public function getConfiguration($config)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue($config, $storeScope);
    }

    /**
     * get number of mails already sent to the customer
     *
     * @param \Magento\Quote\Model\Quote $cart
     * @return int
     */
    public function messageSent(\Magento\Quote\Model\Quote $cart)
    {
        $abandonedCartMailHistory = $this->_abandonedCartMailHistory->getCollection()
                                            ->addFieldToFilter('quote_id', $cart->getEntityId());
        if ($abandonedCartMailHistory->getSize()) {
            foreach ($abandonedCartMailHistory as $record) {
                return $record->getMailSent();
            }
        }
        return 0;
    }

    /**
     * send followup mails to customer
     *
     * @param int $messagesSent
     * @param \Magento\Quote\Model\Quote $cart
     * @param string $template
     * @return bool
     **/
    public function sendFollowUpMail($mesagesSent, $cart, $template)
    {
        $adminNameInMail = $this->getConfiguration(self::WK_ADMIN_NAME_IN_MAIL);
        $adminEmailAddress = $this->getConfiguration(self::WK_ADMIN_EMAIL_ID);
        $mailBodyId = self::WK_MAIL_BODY_ID.((int)$mesagesSent+1);
        $mailBody =  $this->getConfiguration($mailBodyId);
        $recieverEmail = $cart->getCustomerEmail();
        $this->_logger->info("Abandoned Cart Mail Sent To ".$cart->getCustomerEmail());
        $this->_mailHelper->sendFollowMail(
            $adminNameInMail,
            $adminEmailAddress,
            $mailBody,
            $recieverEmail,
            $template,
            $cart->getCustomerFirstname()
        );
        $mailLogData = [
            'quote_id' => $cart->getEntityId(),
            'sent_by' => $adminEmailAddress,
            'sent_on' => $this->_localeDate->date()
                                            ->format('Y-m-d h:i:sa'),
            'mail_content' => $mailBody,
            'mode' => 1
        ];
        $this->_mailHelper->logSentMail($mailLogData);
        return true;
    }

    /**
     * get number of mails already sent to the customer
     *
     * @param \Magento\Quote\Model\Quote
     * @return bool
     **/
    public function updateMailRecord($cart)
    {
        $abandonedCartMailHistory = $this->_abandonedCartMailHistory->getCollection()
                                                                    ->addFieldToFilter(
                                                                        'quote_id',
                                                                        $cart->getEntityId()
                                                                    );
        if ($abandonedCartMailHistory->getSize()) {
            foreach ($abandonedCartMailHistory as $history) {
                $counter = $history->getMailSent();
                $history->setMailSent($counter+1);
                $this->saveData($history);
            }
            $abandonedCartMailHistory->save();
        } else {
            $data = [
                'quote_id' => $cart->getEntityId(),
                'mail_sent' => 1,
                'sent_on' => $this->_localeDate->date()
                                                ->format('Y-m-d h:i:sa'),
                'created_at' => $this->_localeDate->date()
                                                    ->format('Y-m-d h:i:sa')
            ];
            $this->_abandonedCartMailHistory->setData($data)->save();
        }
        return true;
    }

    /**
     * Perform save operation on models
     */
    public function saveData($model)
    {
        $model->save();
    }

    /**
     * check for the last mail that was sent to the customer
     *
     * @param \Magento\Quote\Model\Quote $cart
     * @return bool
     **/
    public function checkLastMail($cart)
    {
        $messagesSent = $this->messageSent($cart);
        if ($messagesSent >=3) {
            return false;
        }
        if ($messagesSent==1) {
            $adminConfiguration = $this->getConfiguration(self::WK_FOLLOWUP_SECOND_MAIL);
        }
        if ($messagesSent==2) {
            $adminConfiguration = $this->getConfiguration(self::WK_FOLLOWUP_THIRD_MAIL);
        }

        $mailRecord = $this->_abandonedCartMailHistory
                            ->getCollection()
                            ->addFieldToFilter('quote_id', $cart->getEntityId());

        if (!$mailRecord->getSize()) {
            return true;
        }

        foreach ($mailRecord as $item) {
            $sentOn = $this->_localeDate->date($item->getCreatedAt());
            $currDateTime = $this->_localeDate->date();
            $diff = date_diff($sentOn, $currDateTime);
            $daysdiff = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h/24 + $diff->i / 60;
            if ($diff->invert) {
                $daysdiff = -1 * $daysdiff;
            }

            if ($daysdiff >= $adminConfiguration) {
                return true;
            }
            return false;
        }
    }

    /**
     * get email template id as set at admin configuration
     * @param int sent messages counter
     * @return string email template id
     **/
    public function getEmailTemplateAccordingToEmailCounter($messagesSent)
    {
        if ($messagesSent == 0) {
            return $this->getConfiguration(self::FIRST_MAIL_TEMP);
        }
        if ($messagesSent == 1) {
            return $this->getConfiguration(self::SCND_MAIL_TEMP);
        }
        if ($messagesSent == 2) {
            return $this->getConfiguration(self::THIRD_TEMPLATE);
        }
    }
}
