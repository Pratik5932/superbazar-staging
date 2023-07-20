<?php



use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
\defined('ABSPATH') or 'Plugin file cannot be accessed directly.';
?>
<script>
    function confirmLogout(){
        //console.log(url);
        //return;
        let result = confirm("Are you sure you want to disconnect your site from Astra ?");
        if(result == true){
            jQuery(document).ready(function($) {
                jQuery.ajax({
                    url : '<?php 
echo admin_url("admin-ajax.php");
?>',
                    type : 'post',
                    data : {
                        action : 'astra_logout_api',
                        nonce : '<?php 
echo wp_create_nonce("ajax-nonce");
?>'
                    },
                    success : function( response ) {
                        //alert('Got this from the server: ' + response);
                        //location.reload(true);
                        //location.reload(true); 
                        window.location.href = '<?php 
echo admin_url('admin.php?page=astra-premium');
?>';
                    }
                });
            }); 
        }   
    }    
</script>
<style>
    @font-face {
        font-family: AvertaStd-Regular;
        src: url("<?php 
echo plugin_dir_url(\ASTRAROOT) . "gk/assets/fonts/AvertaStd-Regular.otf";
?>") format("opentype");
    }

    * {
        font-family: 'AvertaStd-Regular','Arial';
    }
    
    #disconnect{
        display: block;
        text-align: center;
        margin: 25px 0 0 auto;
        background-color: red;
        padding: 15px;
        color: white;
        font-weight: bold;
        border-radius: 10px;
        font-size: 15px;
    }

    .astra-header {
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
        gap: 40px;
    }

    .astra-logo-container {
        padding: 5px;
    }

    .astra-main {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-auto-flow: row;
        height: 85%;
        width: 100%;
        gap: 20px;
        margin-top: 16px;
    }

    /*.top-wrapper, .bottom-wrapper {*/
    /*    display: flex;*/
    /*    justify-content: space-evenly;*/
    /*    align-items: center;*/
    /*    width: 100%;*/
    /*}*/

    .gk-button {
        height: 125px;
        padding: 5px 10px;
        border-width: 0;
        background: #fff;
        color: #000;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 1px 1px 6px 0px #00000026;
        text-decoration: none;
    }

    .gk-button img {
        height: auto;
    }

    .btn-text-content {
        width: 70%;
        text-align: justify;
        margin-left: 20px;
    }

    .btn-heading {
        font-weight: 600;
        font-size: 20px;
        margin-bottom: 6px;
    }

    .btn-description {
        font-size: 15px;
    }

    @media (max-width: 800px) {
        .main {
            grid-template-columns: 1fr;
        }
    }
</style>
<?php 
//echo 'SiteId : '.$GLOBALS['vr'];
?>
<div class="wrap main-col-inner">
    <div class="astra-header">
        <img src="<?php 
echo plugin_dir_url(__DIR__) . "../../assets/images/astra-logo.svg";
?>" alt="astra logo" classname="astra-logo" width="160px">
    </div>

    <div class="astra-main">
        <a href="<?php 
echo UrlHelper::getDashboardUri("waf/{$astraSiteId}/dashboard");
?>" class="gk-button" target="_blank">
            <img src="<?php 
echo plugin_dir_url(__DIR__) . "../../assets/images/logos/dashboard.png";
?>" alt="dashboard">
            <div class="btn-text-content">
                <div class="btn-heading">Dashboard</div>
                <div class="btn-description">Quickly view the latest information for your website</div>
            </div>
        </a>
        <a href="<?php 
echo UrlHelper::getDashboardUri("waf/{$astraSiteId}/threats");
?>" class="gk-button" target="_blank">
            <img src="<?php 
echo plugin_dir_url(__DIR__) . "../../assets/images/logos/threats.png";
?>" alt="threats">
            <div class="btn-text-content">
                <div class="btn-heading">Threats</div>
                <div class="btn-description">Get details about the threats we stopped automatically</div>
            </div>
        </a>
        <a href="<?php 
echo UrlHelper::getDashboardUri("waf/{$astraSiteId}/boosters");
?>" class="gk-button" target="_blank">
            <img src="<?php 
echo plugin_dir_url(__DIR__) . "../../assets/images/logos/boosters.png";
?>" alt="boosters">
            <div class="btn-text-content">
                <div class="btn-heading">Boosters</div>
                <div class="btn-description">Create your own boosters to strengthen your site’s security</div>
            </div>
        </a>
        <a href="<?php 
echo UrlHelper::getDashboardUri("waf/{$astraSiteId}/malware-scan");
?>" class="gk-button" target="_blank">
            <img src="<?php 
echo plugin_dir_url(__DIR__) . "../../assets/images/logos/malware-scan.png";
?>" alt="malware-scan">
            <div class="btn-text-content">
                <div class="btn-heading">Malware Scan</div>
                <div class="btn-description">Get your site cleaned of every possible malware out there</div>
            </div>
        </a>
        <a href="<?php 
echo UrlHelper::getDashboardUri("waf/{$astraSiteId}/login-protection");
?>" class="gk-button" target="_blank">
            <img src="<?php 
echo plugin_dir_url(__DIR__) . "../../assets/images/logos/login-protection.png";
?>" alt="login-protection">
            <div class="btn-text-content">
                <div class="btn-heading">Login Protection</div>
                <div class="btn-description">Have a look at the login activity of your website’s admin area</div>
            </div>
        </a>
        <a href="<?php 
echo UrlHelper::getDashboardUri("waf/{$astraSiteId}/settings");
?>" class="gk-button" target="_blank">
            <img src="<?php 
echo plugin_dir_url(__DIR__) . "../../assets/images/logos/settings.png";
?>" alt="settings">
            <div class="btn-text-content">
                <div class="btn-heading">Settings</div>
                <div class="btn-description">Modify settings of your firewall as required</div>
            </div>
        </a>
        <a href="<?php 
echo UrlHelper::getDashboardUri("my-account/information");
?>" class="gk-button" target="_blank">
            <img src="<?php 
echo plugin_dir_url(__DIR__) . "../../assets/images/logos/my-account.png";
?>" alt="my-account">
            <div class="btn-text-content">
                <div class="btn-heading">My Account</div>
                <div class="btn-description">Modify your account info, subscriptions & communication preferences</div>
            </div>
        </a>
    </div>

    <?php 
if (is_admin() === \true) {
    ?>
        <button onclick='confirmLogout();' id="disconnect" type="button">
            Disconnect
        </button>
    <?php 
}
?>
    
</div>
<?php 
