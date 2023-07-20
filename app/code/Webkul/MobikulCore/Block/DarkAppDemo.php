<?php
namespace Webkul\MobikulCore\Block;

class DarkAppDemo extends \Magento\Config\Block\System\Config\Form\Field {


    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MobikulCore\Helper\Data $helper,
        array $data = []
    ) {
        $this->_assetRepo = $assetRepo;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $mobileurl = $this->_assetRepo->getUrl("Webkul_MobikulCore::images/general_Settings_Mobile.png");
        $defaultLogo = $this->_assetRepo->getUrl("Webkul_MobikulCore::images/webkul.png");
        $searchicon = $this->_assetRepo->getUrl("Webkul_MobikulCore::images/search.png");
        $baricon = $this->_assetRepo->getUrl("Webkul_MobikulCore::images/menu.png");
        $carticon = $this->_assetRepo->getUrl("Webkul_MobikulCore::images/cart.png");
        $productImage = $this->_assetRepo->getUrl("Webkul_MobikulCore::images/product.jpg");
        $defaultThemeColor = $this->helper->getConfigData('mobikul/dark_mode_config/dark_app_theme_color') ?? '#ff8a80';
        $defaultButtonColor = $this->helper->getConfigData(
            'mobikul/dark_mode_config/dark_app_button_color'
        ) ?? '#00000';
        $defaultButtonTextColor = $this->helper->getConfigData(
            'mobikul/dark_mode_config/dark_button_text_color'
        ) ?? '#ffffff';
        $defaultThemeTextColor = $this->helper->getConfigData('mobikul/dark_mode_config/dark_app_theme_text_color') ?? '#ffffff';
        $html = "<div class='dark_front_preview' style='padding: 46px 0 55px;
        width: 261px;
        min-height: 355px;
        margin: auto;
        overflow: hidden;
        clear: both;
        background: url($mobileurl);'>
            <div class='dark_layout_gallery' style='min-height: 355px;
            position: relative;
            overflow: hidden;
            max-height: 202px;
            max-width: 249px;
            margin: 0 auto;
            background-color:#fff;'>
                <div class='dark_topHeader' style='background-color: $defaultThemeColor;
                padding: 1px 10px;
                color: #fff;
                font-size: 18px;
                padding: 5px 10px;
                float: left;
                width: 100%;
                width: 100%;
                box-sizing: border-box;
                margin-top: 0px;'>
                    <div class='dark_leftmenu' style='width: 20px;float: left;margin-top: 6px;'>
                        <span class='dark_toggleMenu'><img src='$baricon' style='max-width: 18px;
                        display: inline-block;
                        max-height: 18px;
                        object-fit: contain;'></span>
                    </div>
                    <div class='dark_logo' style='display: inline-block;vertical-align: middle;margin-right: 20px;margin-left:20px;'>
                        <img src='$defaultLogo' alt='Logo' style='max-width: 90px;
                        display: inline-block;
                        max-height: 25px;
                        object-fit: contain;'>
                    </div>
                    <div class='dark_cartSection' style='padding: 6px 0 0 0px; float: right;'>
                        <span class='dark_cartIcon'><img src='$carticon' style='max-width: 20px;
                        display: inline-block;
                        max-height: 20px;
                        object-fit: contain;'></span>
                    </div>
                    <div class='dark_searchBar' style='float: right; padding: 6px 10px 0;'>
                        <span class='dark_searchicon'><img src='$searchicon' style='max-width: 18px;
                        display: inline-block;
                        max-height: 18px;
                        object-fit: contain;'></span>
                    </div>	
                </div>
                <div class='dark_app_body' style='background-color:#fff;width:100%;'>
                    <img src='$productImage'>
                    <p class='dark_body_text' style='font-size:10px;padding:5px 10px;color:$defaultThemeTextColor'>
                    Antonia Racer Tank &nbsp;<strong>$34.00</strong></p>
                    <div class='dark_btn-left' style='float: left;
                    padding: 5px 20px;
                    width: 35%;
                    height:20px;
                    font-weight: 600;
                    line-height:20px;
                    font-size: 12px;
                    background-color: #dfdfdf;
                    text-align:center;'>ADD TO CART</div>
                    <div class='dark_btn-right' style='float: left;
                    padding: 5px 20px;
                    width: 32%;
                    font-weight: 600;
                    line-height:20px;
                    height:20px;
                    font-size: 12px;
                    background-color:$defaultButtonColor;
                    color:$defaultButtonTextColor;
                    text-align:center;'>BUY NOW</div>
                </div>
            </div>
        </div>
        <script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function(){
                let buttonTextColor = document.querySelector('#mobikul_dark_mode_config_dark_button_text_color');
                let buttonColor = document.querySelector('#mobikul_dark_mode_config_dark_app_button_color');
                let themeColor = document.querySelector('#mobikul_dark_mode_config_dark_app_theme_color');
                let themeTextColor = document.querySelector('#mobikul_dark_mode_config_dark_app_theme_text_color');
                let buttonColorEle = document.querySelector('.dark_btn-right');
                let topHeader = document.querySelector('.dark_topHeader');
                let bodyText = document.querySelector('.dark_body_text');
                buttonTextColor.addEventListener('change', function () {
                    buttonColorEle.style.color = this.value;
                });
                buttonColor.addEventListener('change', function () {
                    buttonColorEle.style.backgroundColor = this.value;
                });
                themeColor.addEventListener('change', function () {
                    topHeader.style.backgroundColor = this.value;
                });
                themeTextColor.addEventListener('change', function () {
                    bodyText.style.color = this.value;
                });
            });
        </script>
        ";
        return $html;
    }

    // protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
    //     $html = $element->getElementHtml();
    //     $value = $element->getData('value');

    //     $html = '<div id="app_view">App Wiew</div>';
    //     return $html;
    // }

}