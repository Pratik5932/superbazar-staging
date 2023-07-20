<?php

namespace Cminds\AdvancedPermissions\Rewrite\Adminhtml\User;

use Magento\Backend\Model\Locale\Manager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Validator\Exception;
use Magento\Framework\Validator\Locale;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\SecurityCookie;
use Magento\User\Block\User\Edit\Tab\Main;
use Magento\User\Model\Spi\NotificationExceptionInterface;

/**
 * Class Save
 *
 * @package Cminds\AdvancedPermissions\Rewrite\Adminhtml\User
 */
class Save extends \Magento\User\Controller\Adminhtml\User\Save
{
    /**
     * @var SecurityCookie
     */
    private $securityCookie;

    /**
     * @var array
     */
    private $postCodeFields = [
        'post_code',
        'post_codes'
    ];

    /**
     * Get security cookie
     *
     * @return SecurityCookie
     * @deprecated 100.1.0
     */
    private function getSecurityCookie()
    {
        if (!($this->securityCookie instanceof SecurityCookie)) {
            return ObjectManager::getInstance()->get(SecurityCookie::class);
        } else {
            return $this->securityCookie;
        }
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPostValue();
        if (array_key_exists('form_key', $data)) {
            unset($data['form_key']);
        }
        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }

        /** @var $model \Magento\User\Model\User */
        $model = $this->_userFactory->create()->load($userId);
        if ($userId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This user no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }
        $model->setData($this->_getAdminUserData($data));

        foreach ($this->postCodeFields as $postCodeField) {
            $value = $this->_request->getParam($postCodeField, false);
            if (null !== $value) {
                $model->setData(
                    $postCodeField,
                    $value
                );
            }
        }

        $userRoles = $this->getRequest()->getParam('roles', []);
        if (count($userRoles)) {
            $model->setRoleId($userRoles[0]);
        }

        /** @var $currentUser \Magento\User\Model\User */
        $currentUser = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->getUser();
        if ($userId == $currentUser->getId()
            && $this->_objectManager->get(Locale::class)
                ->isValid($data['interface_locale'])
        ) {
            $this->_objectManager->get(
                Manager::class
            )->switchBackendInterfaceLocale(
                $data['interface_locale']
            );
        }

        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        $currentUserPasswordField = Main::CURRENT_USER_PASSWORD_FIELD;
        $isCurrentUserPasswordValid = isset($data[$currentUserPasswordField])
            && !empty($data[$currentUserPasswordField]) && is_string($data[$currentUserPasswordField]);
        try {
            if (!($isCurrentUserPasswordValid)) {
                throw new AuthenticationException(
                    __('The password entered for the current user is invalid. Verify the password and try again.')
                );
            }
            //var_dump($model->getData());die;
            $currentUser->performIdentityCheck($data[$currentUserPasswordField]);
            $model->save();

            $this->messageManager->addSuccess(__('You saved the user.'));
            $this->_getSession()->setUserData(false);
            $this->_redirect('adminhtml/*/');

            $model->sendNotificationEmailsIfRequired();
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
                AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            $this->_redirect('adminhtml/*/');
        } catch (NotificationExceptionInterface $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addError(
                __('The password entered for the current user is invalid. Verify the password and try again.')
            );
            $this->redirectToEdit($model, $data);
        } catch (Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($model, $data);
        }
    }
}
