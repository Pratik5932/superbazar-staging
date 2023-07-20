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
namespace Webkul\MpStripe\Controller\Seller\Connect;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Account extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    private $helper;

    private $marketplaceHelper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param RemoteAddress $remoteAddress
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem\Driver\File $driver,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        RemoteAddress $remoteAddress,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->helper = $helper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->dateTime = $dateTime;
        $this->driver = $driver;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->remoteAddress = $remoteAddress;
        $this->customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl =
        $this->customerUrl
        ->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Connect to stripe.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->marketplaceHelper->isSeller();
        if ($isPartner == 1) {
            $this->helper->setUpDefaultDetails();
            // process data
            $postRequest = $this->getRequest()->getParam('stripe_user');
            
            //get general data
            $wholdData = $this->getGeneralData($postRequest);
            
            if ($postRequest['business_type'] == 'individual') {

                $wholdData['individual'] = $this->getIndividualData($postRequest);
                
            } else {
                $postRequest['company']['address']['country'] = $postRequest['country'];
                $wholdData['company'] = [
                    "address" => $postRequest['company']['address'],
                    "name" => $postRequest['company']['name'],
                    "phone" => $postRequest['company']['phone'],
                    "tax_id" => $postRequest['company']['tax_id'],
                    "owners_provided" => true
                ];
            }
            try {
                $response = \Stripe\Account::create($wholdData);
                if ($postRequest['business_type'] == 'company') {
                    $this->createPerson($response, $postRequest);
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
                    $this->messageManager->addSuccess(__('You are successfully connected to stripe'));
                }
            } catch (\Exception $e) {
                $this
                ->messageManager
                ->addError(
                    $e->getMessage()
                );
            }
            return $this->resultRedirectFactory->create()->setPath(
                'mpstripe/seller/connect',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * manage general data for seller
     *
     * @param array $postRequest
     * @return array
     */
    public function getGeneralData($postRequest)
    {
        return [
            "type" => "custom",
            "country" => $postRequest['country'],
            "email" => $postRequest['email'],
            "requested_capabilities" => ["card_payments", "transfers"],
            "business_type" => $postRequest['business_type'],
            "tos_acceptance" => [
                "date" => strtotime($this->dateTime->gmtDate()),
                "ip" => $this->remoteAddress->getRemoteAddress()
            ],
            "business_profile" => [
                "mcc" => $postRequest["business_profile"]["mcc"],
                "url" => $postRequest["business_profile"]["url"]
            ],
            "external_account" => [
                "object" => $postRequest['external_accounts']['object'],
                "country" => $postRequest['external_accounts']['country'],
                "currency" => $postRequest['external_accounts']['currency'],
                "account_holder_name" => $postRequest['external_accounts']['account_holder_name'],
                "account_holder_type" => $postRequest['external_accounts']['account_holder_type'],
                "routing_number" => $postRequest['external_accounts']['routing_number'],
                "account_number" => $postRequest['external_accounts']['account_number']
            ]
        ];
    }

    /**
     * get individual data
     *
     * @param array $postRequest
     * @return array
     */
    public function getIndividualData($postRequest)
    {
        // upload files front document
        $target = $this->mediaDirectory->getAbsolutePath('idproof/'.$postRequest['user_id'].'/');
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->fileUploaderFactory->create(
                ['fileId' => 'document_front']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target);
            $fp = $this->driver->fileOpen($result['path'].$result['file'], 'r');
            $documentFront = \Stripe\File::create([
              'purpose' => 'identity_document',
              'file' => $fp
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() != 'The file was not uploaded.') {
                $this->messageManager->addError($e->getMessage());
            }
        }

        //back side
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->fileUploaderFactory->create(
                ['fileId' => 'document_back']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target);
            $fp = $this->driver->fileOpen($result['path'].$result['file'], 'r');
            $documentBack = \Stripe\File::create([
              'purpose' => 'identity_document',
              'file' => $fp
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() != 'The file was not uploaded.') {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $postRequest['individual']['address']['country'] = $postRequest['country'];
        $dob = explode('/', $postRequest['individual']['dob']);
        return [
            "address" => $postRequest['individual']['address'],
            "email" => $postRequest['email'],
            "first_name" => $postRequest['individual']['first_name'],
            "last_name" => $postRequest['individual']['last_name'],
            "phone" => $postRequest['individual']['phone'],
            "id_number" => $postRequest['individual']['id_number'],
            "dob" => [
                "day" => $dob[0],
                "month" => $dob[1],
                "year" => $dob[2]
            ],
            "verification" => [
                "document" => [
                    "back" => $documentBack['id'],
                    "front" => $documentFront['id']
                ]
            ]
        ];
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
        // upload files front document
        $target = $this->mediaDirectory->getAbsolutePath('idproof/'.$postRequest['user_id'].'/');
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->fileUploaderFactory->create(
                ['fileId' => 'owner_document_front']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target);
            $fp = $this->driver->fileOpen($result['path'].$result['name'], 'r');
            $ownerDocumentFront = \Stripe\File::create([
              'purpose' => 'identity_document',
              'file' => $fp
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() != 'The file was not uploaded.') {
                $this->messageManager->addError($e->getMessage());
            }
        }

        //back side
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->fileUploaderFactory->create(
                ['fileId' => 'owner_document_back']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target);
            $fp = $this->driver->fileOpen($result['path'].$result['name'], 'r');
            $ownerDocumentBack = \Stripe\File::create([
              'purpose' => 'identity_document',
              'file' => $fp
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() != 'The file was not uploaded.') {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $ownerDob = explode('/', $postRequest['owner']['dob']);
        $person = \Stripe\Account::createPerson(
            $response['id'],
            [
                "address" => $postRequest['owner']['address'],
                "email" => $postRequest['email'],
                "first_name" => $postRequest['owner']['first_name'],
                "last_name" => $postRequest['owner']['last_name'],
                "phone" => $postRequest['owner']['phone'],
                "id_number" => $postRequest['owner']['id_number'],
                "dob" => [
                    "day" => $ownerDob[0],
                    "month" => $ownerDob[1],
                    "year" => $ownerDob[2]
                ],
                "verification" => [
                    "document" => [
                        "back" => $ownerDocumentBack['id'],
                        "front" => $ownerDocumentFront['id']
                    ]
                ],
                "relationship" => [
                    "account_opener" => true,
                    "owner" => true,
                    "title" => $postRequest['owner']['job_title']
                ]
            ]
        );
    }
}
