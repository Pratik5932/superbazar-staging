<?php

namespace Cminds\AdvancedPermissions\Plugin\Block\User\Edit\Tab;

use Closure;
use Magento\Backend\Block\System\Account\Edit\Form;
use Magento\Backend\Model\Auth\Session;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class Main
 *
 * @package Cminds\AdvancedPermissions\Plugin\Block\User\Edit\Tab
 */
class Main
{
    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * @var UserFactory
     */
    protected $_userFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Main constructor.
     * @param UserFactory $userFactory
     * @param Session $authSession
     * @param RequestInterface $request
     */
    public function __construct(
        UserFactory $userFactory,
        Session $authSession,
        RequestInterface $request
    ) {
        $this->_userFactory = $userFactory;
        $this->_authSession = $authSession;
        $this->request = $request;
    }

    /**
     * Get form HTML
     *
     * @param $subject
     * @param Closure $proceed
     * @return string
     */
    public function aroundGetFormHtml(
        $subject,
        Closure $proceed
    ) {
        if (!empty($this->request->getParam('user_id'))) {
            $userId = $this->request->getParam('user_id');
        } else {
            /** @var $model User */
            $userId = $this->_authSession->getUser()->getId();
        }
        $user = $this->_userFactory->create()->load($userId);
        $postCodesData = [
            'post_code' => 'Store location',
            'post_codes' => 'Post Codes'
        ];
        $form = $subject->getForm();
        if (is_object($form)) {
            $fieldset = $form->addFieldset('admin_post_code', ['legend' => __('Post Code')]);
            foreach ($postCodesData as $code => $label) {
                $value = $user->getData($code) ?? "";
                $fieldset->addField(
                    $code,
                    'text',
                    [
                        'name' => $code,
                        'label' => __($label),
                        'id' => $code,
                        'title' => __($label),
                        'required' => false,
                        'value' => $value
                    ]
                );
            }
            $subject->setForm($form);
        }

        return $proceed();
    }
}
