<?php

namespace AstraPrefixed;

\defined('ABSPATH') or 'Plugin file cannot be accessed directly.';
/**
 *
 *
 * @author Ananda Krishna <ak@getastra.com>
 * @date   9/29/21
 */
class AstraLoginHook
{
    private $loginPluginClassPathInsideGk;
    private $container;
    private $astraLoginModule;
    public function __construct($container)
    {
        $this->container = $container;
        $this->loginPluginClassPathInsideGk = \GK_DIR_PATH . '/plugins/LoginProtectionPlugin/LoginProtectionPlugin.php';
        if (!\class_exists('AstraPrefixed\\LoginProtectionPlugin')) {
            include_once $this->loginPluginClassPathInsideGk;
        }
        $options = $this->container->get('options');
        $siteSettings = $options->get('siteSettings');
        if (!$siteSettings) {
            return;
        }
        $astraEnabled = $siteSettings['protectionEnabled'] ?? \false;
        $loginProtectionEnabled = $siteSettings['loginProtection'] ?? \false;
        if ($astraEnabled && $loginProtectionEnabled) {
            add_action('wp_login', [$this, 'adminLoginSuccessAction'], 10, 2);
            add_action('wp_login_failed', [$this, 'adminLoginFailedAction'], 10, 2);
            $this->astraLoginModule = new LoginProtectionPlugin($this->container);
        }
    }
    /**
     * User login successful.
     *
     * @param int $user_info User info
     * @param int $u         User object
     *
     * @return bool
     */
    public function adminLoginSuccessAction($user_info, $u)
    {
        $user = $u->data;
        unset($user->user_pass, $user->ID, $user->user_nicename, $user->user_url, $user->user_registered, $user->user_activation_key, $user->user_status);
        if (current_user_can('manage_options')) {
            $user->admin = 1;
        }
        $data['success'] = \true;
        $data['username'] = $u->user_login;
        $data['email'] = $u->user_email;
        $data['displayName'] = $u->display_name;
        $this->astraLoginModule->login($data);
    }
    /**
     * User login failed.
     *
     * @param string $username User name
     *
     * @return bool
     */
    public function adminLoginFailedAction($username)
    {
        $data['success'] = \false;
        $data['username'] = $username;
        $this->astraLoginModule->login($data);
    }
}
/**
 *
 *
 * @author Ananda Krishna <ak@getastra.com>
 * @date   9/29/21
 */
\class_alias('AstraPrefixed\\AstraLoginHook', 'AstraLoginHook', \false);
new AstraLoginHook($astraContainer);
