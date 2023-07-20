<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpStripe\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Save extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     * @param RemoteAddress $remoteAddress
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Filesystem\Driver\File $driver,
        RemoteAddress $remoteAddress,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->_coreSession = $coreSession;
        $this->driver = $driver;
        $this->dateTime = $dateTime;
        $this->remoteAddress = $remoteAddress;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Seller list page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->helper->setUpDefaultDetails();
        // process data
        $postRequest = $this->getRequest()->getParam('stripe_user');
        $request = $this->getRequest()->getParams();
        //get general data
        $wholdData = $this->getGeneralData($postRequest);
        
        if ($postRequest['account_id']) {
            $response = \Stripe\Account::update(
                $postRequest['account_id'],
                $wholdData
            );
            if ($response) {

                $this->messageManager->addSuccess(__('You have successfully updated connected account on stripe'));
                
            } else {

                $this
                    ->messageManager
                    ->addError(
                        __('There some error, not able connect you with stripe, please contact admin')
                    );
            }

            return $this->resultRedirectFactory->create()->setPath(
                'mpstripe/manage/account',
                ['seller_id' => $postRequest['user_id'], '_secure' => $this->getRequest()->isSecure()]
            );
        }
        if ($postRequest['business_type'] == 'individual') {

            $wholdData['individual'] = $this->getIndividualData($postRequest, $request);

        } else {
            $postRequest['company']['address']['country'] = $postRequest['country'];
            $wholdData['company'] = [
                "address" => $postRequest['company']['address'],
                "name" => $postRequest['company']['name'],
                "phone" => $postRequest['company']['phone'],
                "tax_id" => $postRequest['company']['tax_id'],
                "owners_provided" => true
            ];
            $wholdData['company'] = $this->getCompanyDocumentData($wholdData['company'], $request);
        }
        try {
            $response = \Stripe\Account::create($wholdData);

            if ($postRequest['business_type'] == 'company') {
                $this->createPerson($response, $request);
            }
            $response['user_id'] = $postRequest['user_id'];
            $res = $this->helper->saveCustomStripeSeller($response);
            if ($res) {
                $this
                    ->messageManager
                    ->addError(
                        __('There some error, not able connect you with stripe, please contact admin')
                    );
            } else {

                $this->_coreSession->setSellerCustomAccountData(null);
                $this->messageManager->addSuccess(__('You are successfully connected to stripe'));
            }
        } catch (\Exception $e) {
            $this->_coreSession->setSellerCustomAccountData($wholdData);
            $this
            ->messageManager
            ->addError(
                $e->getMessage()
            );
        }
        return $this->resultRedirectFactory->create()->setPath(
            'mpstripe/manage/account',
            ['seller_id' => $postRequest['user_id'], '_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * generate general data
     *
     * @param array $postRequest
     * @return array
     */
    public function getGeneralData($postRequest)
    {
        if (empty($postRequest['account_id'])) {
            $defaultData["type"] = 'custom';
            $defaultData["country"] = $postRequest['country'];
            $defaultData["email"] = $postRequest['email'];
            $defaultData["external_account"] = [
                "object" => 'bank_account',
                "country" => $postRequest['external_accounts']['country'],
                "currency" => $postRequest['external_accounts']['currency'],
                "account_holder_name" => $postRequest['external_accounts']['account_holder_name'],
                "account_holder_type" => $postRequest['external_accounts']['account_holder_type'],
                "routing_number" => $postRequest['external_accounts']['routing_number'],
                "account_number" => $postRequest['external_accounts']['account_number']
            ];
        }
        
        $defaultData["requested_capabilities"] = ["card_payments", "transfers"];
        $defaultData["business_type"] = $postRequest['business_type'];

        $defaultData["tos_acceptance"] = [
            "date" => strtotime($this->dateTime->gmtDate()),
            "ip" => $this->remoteAddress->getRemoteAddress()
        ];
        $defaultData["business_profile"] = [
            "mcc" => $postRequest["business_profile"]["mcc"],
            "url" => $postRequest["business_profile"]["url"]
        ];
        return $defaultData;
    }

    /**
     *  manage company document data
     *
     * @param array $postRequest
     * @param array $wholeRequest
     * @return array
     */
    public function getCompanyDocumentData($postRequest, $wholeRequest)
    {
        if (!empty($wholeRequest['company_document_front'])) {
            $result = $wholeRequest['company_document_front'][0];
            $fp = $this->driver->fileOpen($result['path'].'/'.$result['name'], 'r');
            $documentFront = \Stripe\File::create([
                'purpose' => 'identity_document',
                'file' => $fp
            ]);
            $postRequest['verification']['document']['front'] = $documentFront['id'];
        }
        if (!empty($wholeRequest['company_document_back'])) {
            $result = $wholeRequest['company_document_back'][0];
            $fp = $this->driver->fileOpen($result['path'].'/'.$result['name'], 'r');
            $documentBack = \Stripe\File::create([
                'purpose' => 'identity_document',
                'file' => $fp
            ]);
            $postRequest['verification']['document']['back'] = $documentBack['id'];
        }
        
        return $postRequest;
    }

    /**
     * get individual data
     *
     * @param array $postRequest
     * @param array $wholeRequest
     * @return array
     */
    public function getIndividualData($postRequest, $wholeRequest)
    {
        $postRequest['individual']['address']['country'] = $postRequest['country'];
        $dob = explode('/', $postRequest['individual']['dob']);
        unset($postRequest['individual']['address']['first_name']);
        unset($postRequest['individual']['address']['last_name']);
        $returnData = [
            "address" => $postRequest['individual']['address'],
            "email" => $postRequest['email'],
            "first_name" => $postRequest['individual']['first_name'],
            "last_name" => $postRequest['individual']['last_name'],
            "phone" => $postRequest['individual']['phone'],
            "id_number" => $postRequest['individual']['id_number']
        ];
        if (is_array($dob) && !empty($dob[1])) {
            $returnData["dob"] = [
                "day" => $dob[1],
                "month" => $dob[0],
                "year" => $dob[2]
            ];
        }
        
        if (!empty($wholeRequest['individual_document_front'])) {
            $result = $wholeRequest['individual_document_front'][0];
            $fp = $this->driver->fileOpen($result['path'].'/'.$result['name'], 'r');
            $documentFront = \Stripe\File::create([
                'purpose' => 'identity_document',
                'file' => $fp
            ]);
            $returnData['verification']['document']['front'] = $documentFront['id'];
        }
        if (!empty($wholeRequest['individual_document_back'])) {
            $result = $wholeRequest['individual_document_back'][0];
            $fp = $this->driver->fileOpen($result['path'].'/'.$result['name'], 'r');
            $documentBack = \Stripe\File::create([
                'purpose' => 'identity_document',
                'file' => $fp
            ]);
            $returnData['verification']['document']['back'] = $documentBack['id'];
        }
        
        return $returnData;
    }

    /**
     * create person account on stripe
     *
     * @param array $response
     * @param array $postRequest
     * @return array
     */
    public function createPerson($response, $postRequest)
    {
        $personData = [
            "address" => $postRequest['stripe_user']['owner']['address'],
            "email" => $postRequest['stripe_user']['email'],
            "first_name" => $postRequest['stripe_user']['owner']['first_name'],
            "last_name" => $postRequest['stripe_user']['owner']['last_name'],
            "phone" => $postRequest['stripe_user']['owner']['phone'],
            "id_number" => $postRequest['stripe_user']['owner']['id_number'],
            "relationship" => [
                "account_opener" => true,
                "owner" => true,
                "title" => $postRequest['stripe_user']['owner']['job_title']
            ]
        ];
        if (!empty($postRequest['owner_document_front'])) {
            $result = $postRequest['owner_document_front'][0];
            $fp = $this->driver->fileOpen($result['path'].'/'.$result['name'], 'r');
            $documentFront = \Stripe\File::create([
                'purpose' => 'identity_document',
                'file' => $fp
            ]);
            $personData['verification']['document']['front'] = $documentFront['id'];
        }
        if (!empty($postRequest['owner_document_back'])) {
            $result = $postRequest['owner_document_back'][0];
            $fp = $this->driver->fileOpen($result['path'].'/'.$result['name'], 'r');
            $documentBack = \Stripe\File::create([
                'purpose' => 'identity_document',
                'file' => $fp
            ]);
            $personData['verification']['document']['back'] = $documentBack['id'];
        }

        $ownerDob = explode('/', $postRequest['stripe_user']['owner']['dob']);

        if (is_array($ownerDob) && !empty($ownerDob[1])) {
            $personData["dob"] = [
                "day" => $ownerDob[1],
                "month" => $ownerDob[0],
                "year" => $ownerDob[2]
            ];
        }

        $person = \Stripe\Account::createPerson(
            $response['id'],
            $personData
        );
    }

    /**
     * Check for is allowed.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpSeller::seller');
    }
}
