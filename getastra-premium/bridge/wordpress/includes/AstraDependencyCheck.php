<?php



\defined('ABSPATH') or 'Plugin file cannot be accessed directly.';
/**
* Include the TGM_Plugin_Activation class.
*/
require_once \GK_WP_BRIDGE_PATH . 'tgm/class-tgm-plugin-activation.php';
add_action('tgmpa_register', 'astraRegisterRequiredPlugins');
/**
* Register the required plugins for this theme.
*
*  <snip />
*
* This function is hooked into tgmpa_init, which is fired within the
* TGM_Plugin_Activation class constructor.
*/
function astraRegisterRequiredPlugins()
{
    /*
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = [['name' => 'WP Hardening', 'slug' => 'wp-security-hardening', 'required' => \false]];
    /*
     * Array of configuration settings. Amend each line as needed.
     *
     * TGMPA will start providing localized text strings soon. If you already have translations of our standard
     * strings available, please help us make TGMPA even better by giving us access to these translations or by
     * sending in a pull-request with .po file(s) with the translations.
     *
     * Only uncomment the strings in the config array if you want to customize the strings.
     */
    $config = [
        'id' => 'getastra-tgmpa',
        // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',
        // Default absolute path to bundled plugins.
        'menu' => 'astra-tgmpa-install-plugins',
        // Menu slug.
        'parent_slug' => 'astra-premium',
        // Parent menu slug.
        'capability' => 'edit_theme_options',
        // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
        'has_notices' => \true,
        // Show admin notices or not.
        'dismissable' => \true,
        // If false, a user cannot dismiss the nag message.
        'dismiss_msg' => '',
        // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => \false,
        // Automatically activate plugins after installation or not.
        'message' => '',
    ];
    tgmpa($plugins, $config);
}
