<?php



use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
\defined('ABSPATH') or 'Plugin file cannot be accessed directly.';
add_action('admin_menu', function () {
    if (!is_plugin_active('wp-security-hardening/wp-security-hardening.php')) {
        add_menu_page('Quick links', 'Astra Security', 'manage_options', 'astra-premium', 'astraAdminPage', plugin_dir_url(__DIR__) . 'icon.png', 4);
    }
});
function astraAdminPage()
{
    global $astraContainer;
    $oauthService = $astraContainer->get('oauth');
    $tokenObject = $oauthService->getTokenObject();
    $astraSiteId = $astraContainer->get('options')->get('siteId');
    $astraSiteSettings = $astraContainer->get('options')->get('siteSettings');
    if (!\is_null($tokenObject) && !empty($astraSiteId) && !empty($astraSiteSettings)) {
        include_once \GK_DIR_PATH . 'bridge/wordpress/pages/page-connected.php';
    } else {
        include_once \GK_DIR_PATH . 'bridge/wordpress/pages/page-not-connected.php';
    }
}
function astraLogout()
{
    global $astraContainer;
    $astraContainer->get('options')->deleteMultiple(['accessToken', 'oauthClientId', 'oauthClientSecret', 'wafClientId', 'siteId', 'wafClientPassword', 'siteSettings', 'redirectUri', 'clientApiToken']);
    return 'true';
}
add_action('wp_ajax_astra_logout_api', 'astra_logout_api');
function astra_logout_api()
{
    global $wpdb;
    // this is how you get access to the database
    if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
        wp_send_json(['success' => 'false'], 403);
    }
    if (!is_admin()) {
        wp_send_json(['success' => 'false'], 403);
    }
    //json response
    if (astraLogout()) {
        wp_send_json(['success' => 'true'], 200);
    } else {
        wp_send_json(['success' => 'false'], 400);
    }
}
//wp-admin will call this wordpress apis
//so that only authenticated users can login/logout
//Front-end
//is admin check and jquery call to api
//wordpress verify nons token check
//confirm the action - "are you sure you want to logout ?"
