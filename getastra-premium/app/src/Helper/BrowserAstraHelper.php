<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Client\Helper;

class BrowserAstraHelper
{
    private $_agent = '';
    private $_browser_name = '';
    private $_version = '';
    private $_platform = '';
    private $_os = '';
    private $_is_aol = \false;
    private $_is_mobile = \false;
    private $_is_tablet = \false;
    private $_is_robot = \false;
    private $_is_facebook = \false;
    private $_aol_version = '';
    const BROWSER_UNKNOWN = 'unknown';
    const VERSION_UNKNOWN = 'unknown';
    const BROWSER_OPERA = 'Opera';
    // http://www.opera.com/
    const BROWSER_OPERA_MINI = 'Opera Mini';
    // http://www.opera.com/mini/
    const BROWSER_WEBTV = 'WebTV';
    // http://www.webtv.net/pc/
    const BROWSER_EDGE = 'Edge';
    // https://www.microsoft.com/edge
    const BROWSER_IE = 'Internet Explorer';
    // http://www.microsoft.com/ie/
    const BROWSER_POCKET_IE = 'Pocket Internet Explorer';
    // http://en.wikipedia.org/wiki/Internet_Explorer_Mobile
    const BROWSER_KONQUEROR = 'Konqueror';
    // http://www.konqueror.org/
    const BROWSER_ICAB = 'iCab';
    // http://www.icab.de/
    const BROWSER_OMNIWEB = 'OmniWeb';
    // http://www.omnigroup.com/applications/omniweb/
    const BROWSER_FIREBIRD = 'Firebird';
    // http://www.ibphoenix.com/
    const BROWSER_FIREFOX = 'Firefox';
    // http://www.mozilla.com/en-US/firefox/firefox.html
    const BROWSER_ICEWEASEL = 'Iceweasel';
    // http://www.geticeweasel.org/
    const BROWSER_SHIRETOKO = 'Shiretoko';
    // http://wiki.mozilla.org/Projects/shiretoko
    const BROWSER_MOZILLA = 'Mozilla';
    // http://www.mozilla.com/en-US/
    const BROWSER_AMAYA = 'Amaya';
    // http://www.w3.org/Amaya/
    const BROWSER_LYNX = 'Lynx';
    // http://en.wikipedia.org/wiki/Lynx
    const BROWSER_SAFARI = 'Safari';
    // http://apple.com
    const BROWSER_IPHONE = 'iPhone';
    // http://apple.com
    const BROWSER_IPOD = 'iPod';
    // http://apple.com
    const BROWSER_IPAD = 'iPad';
    // http://apple.com
    const BROWSER_CHROME = 'Chrome';
    // http://www.google.com/chrome
    const BROWSER_ANDROID = 'Android';
    // http://www.android.com/
    const BROWSER_GOOGLEBOT = 'GoogleBot';
    // http://en.wikipedia.org/wiki/Googlebot
    const BROWSER_YANDEXBOT = 'YandexBot';
    // http://yandex.com/bots
    const BROWSER_YANDEXIMAGERESIZER_BOT = 'YandexImageResizer';
    // http://yandex.com/bots
    const BROWSER_YANDEXIMAGES_BOT = 'YandexImages';
    // http://yandex.com/bots
    const BROWSER_YANDEXVIDEO_BOT = 'YandexVideo';
    // http://yandex.com/bots
    const BROWSER_YANDEXMEDIA_BOT = 'YandexMedia';
    // http://yandex.com/bots
    const BROWSER_YANDEXBLOGS_BOT = 'YandexBlogs';
    // http://yandex.com/bots
    const BROWSER_YANDEXFAVICONS_BOT = 'YandexFavicons';
    // http://yandex.com/bots
    const BROWSER_YANDEXWEBMASTER_BOT = 'YandexWebmaster';
    // http://yandex.com/bots
    const BROWSER_YANDEXDIRECT_BOT = 'YandexDirect';
    // http://yandex.com/bots
    const BROWSER_YANDEXMETRIKA_BOT = 'YandexMetrika';
    // http://yandex.com/bots
    const BROWSER_YANDEXNEWS_BOT = 'YandexNews';
    // http://yandex.com/bots
    const BROWSER_YANDEXCATALOG_BOT = 'YandexCatalog';
    // http://yandex.com/bots
    const BROWSER_SLURP = 'Yahoo! Slurp';
    // http://en.wikipedia.org/wiki/Yahoo!_Slurp
    const BROWSER_W3CVALIDATOR = 'W3C Validator';
    // http://validator.w3.org/
    const BROWSER_BLACKBERRY = 'BlackBerry';
    // http://www.blackberry.com/
    const BROWSER_ICECAT = 'IceCat';
    // http://en.wikipedia.org/wiki/GNU_IceCat
    const BROWSER_NOKIA_S60 = 'Nokia S60 OSS Browser';
    // http://en.wikipedia.org/wiki/Web_Browser_for_S60
    const BROWSER_NOKIA = 'Nokia Browser';
    // * all other WAP-based browsers on the Nokia Platform
    const BROWSER_MSN = 'MSN Browser';
    // http://explorer.msn.com/
    const BROWSER_MSNBOT = 'MSN Bot';
    // http://search.msn.com/msnbot.htm
    const BROWSER_BINGBOT = 'Bing Bot';
    // http://en.wikipedia.org/wiki/Bingbot
    const BROWSER_VIVALDI = 'Vivalidi';
    // https://vivaldi.com/
    const BROWSER_YANDEX = 'Yandex';
    // https://browser.yandex.ua/
    const BROWSER_NETSCAPE_NAVIGATOR = 'Netscape Navigator';
    // http://browser.netscape.com/ (DEPRECATED)
    const BROWSER_GALEON = 'Galeon';
    // http://galeon.sourceforge.net/ (DEPRECATED)
    const BROWSER_NETPOSITIVE = 'NetPositive';
    // http://en.wikipedia.org/wiki/NetPositive (DEPRECATED)
    const BROWSER_PHOENIX = 'Phoenix';
    // http://en.wikipedia.org/wiki/History_of_Mozilla_Firefox (DEPRECATED)
    const BROWSER_PLAYSTATION = 'PlayStation';
    const BROWSER_SAMSUNG = 'SamsungBrowser';
    const BROWSER_SILK = 'Silk';
    const BROWSER_I_FRAME = 'Iframely';
    const BROWSER_COCOA = 'CocoaRestClient';
    const PLATFORM_UNKNOWN = 'unknown';
    const PLATFORM_WINDOWS = 'Windows';
    const PLATFORM_WINDOWS_CE = 'Windows CE';
    const PLATFORM_APPLE = 'Apple';
    const PLATFORM_LINUX = 'Linux';
    const PLATFORM_OS2 = 'OS/2';
    const PLATFORM_BEOS = 'BeOS';
    const PLATFORM_IPHONE = 'iPhone';
    const PLATFORM_IPOD = 'iPod';
    const PLATFORM_IPAD = 'iPad';
    const PLATFORM_BLACKBERRY = 'BlackBerry';
    const PLATFORM_NOKIA = 'Nokia';
    const PLATFORM_FREEBSD = 'FreeBSD';
    const PLATFORM_OPENBSD = 'OpenBSD';
    const PLATFORM_NETBSD = 'NetBSD';
    const PLATFORM_SUNOS = 'SunOS';
    const PLATFORM_OPENSOLARIS = 'OpenSolaris';
    const PLATFORM_ANDROID = 'Android';
    const PLATFORM_PLAYSTATION = 'Sony PlayStation';
    const PLATFORM_ROKU = 'Roku';
    const PLATFORM_APPLE_TV = 'Apple TV';
    const PLATFORM_TERMINAL = 'Terminal';
    const PLATFORM_FIRE_OS = 'Fire OS';
    const PLATFORM_SMART_TV = 'SMART-TV';
    const PLATFORM_CHROME_OS = 'Chrome OS';
    const PLATFORM_JAVA_ANDROID = 'Java/Android';
    const PLATFORM_POSTMAN = 'Postman';
    const PLATFORM_I_FRAME = 'Iframely';
    const OPERATING_SYSTEM_UNKNOWN = 'unknown';
    /**
     * Class constructor.
     */
    public function __construct($userAgent = '')
    {
        $this->reset();
        if ('' != $userAgent) {
            $this->setUserAgent($userAgent);
        } else {
            $this->determine();
        }
    }
    /**
     * Reset all properties.
     */
    public function reset()
    {
        $this->_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $this->_browser_name = self::BROWSER_UNKNOWN;
        $this->_version = self::VERSION_UNKNOWN;
        $this->_platform = self::PLATFORM_UNKNOWN;
        $this->_os = self::OPERATING_SYSTEM_UNKNOWN;
        $this->_is_aol = \false;
        $this->_is_mobile = \false;
        $this->_is_tablet = \false;
        $this->_is_robot = \false;
        $this->_is_facebook = \false;
        $this->_aol_version = self::VERSION_UNKNOWN;
    }
    /**
     * Check to see if the specific browser is valid.
     *
     * @param string $browserName
     *
     * @return bool True if the browser is the specified browser
     */
    public function isBrowser($browserName)
    {
        return 0 == \strcasecmp($this->_browser_name, \trim($browserName));
    }
    /**
     * The name of the browser.  All return types are from the class contants.
     *
     * @return string Name of the browser
     */
    public function getBrowser()
    {
        return $this->_browser_name;
    }
    /**
     * Set the name of the browser.
     *
     * @param $browser string The name of the Browser
     */
    public function setBrowser($browser)
    {
        $this->_browser_name = $browser;
    }
    /**
     * The name of the platform.  All return types are from the class contants.
     *
     * @return string Name of the browser
     */
    public function getPlatform()
    {
        return $this->_platform;
    }
    /**
     * Set the name of the platform.
     *
     * @param string $platform The name of the Platform
     */
    public function setPlatform($platform)
    {
        $this->_platform = $platform;
    }
    /**
     * The version of the browser.
     *
     * @return string Version of the browser (will only contain alpha-numeric characters and a period)
     */
    public function getVersion()
    {
        return $this->_version;
    }
    /**
     * Set the version of the browser.
     *
     * @param string $version The version of the Browser
     */
    public function setVersion($version)
    {
        $this->_version = \preg_replace('/[^0-9,.,a-z,A-Z-]/', '', $version);
    }
    /**
     * The version of AOL.
     *
     * @return string Version of AOL (will only contain alpha-numeric characters and a period)
     */
    public function getAolVersion()
    {
        return $this->_aol_version;
    }
    /**
     * Set the version of AOL.
     *
     * @param string $version The version of AOL
     */
    public function setAolVersion($version)
    {
        $this->_aol_version = \preg_replace('/[^0-9,.,a-z,A-Z]/', '', $version);
    }
    /**
     * Is the browser from AOL?
     *
     * @return bool True if the browser is from AOL otherwise false
     */
    public function isAol()
    {
        return $this->_is_aol;
    }
    /**
     * Is the browser from a mobile device?
     *
     * @return bool True if the browser is from a mobile device otherwise false
     */
    public function isMobile()
    {
        return $this->_is_mobile;
    }
    /**
     * Is the browser from a tablet device?
     *
     * @return bool True if the browser is from a tablet device otherwise false
     */
    public function isTablet()
    {
        return $this->_is_tablet;
    }
    /**
     * Is the browser from a robot (ex Slurp,GoogleBot)?
     *
     * @return bool True if the browser is from a robot otherwise false
     */
    public function isRobot()
    {
        return $this->_is_robot;
    }
    /**
     * Is the browser from facebook?
     *
     * @return bool True if the browser is from facebook otherwise false
     */
    public function isFacebook()
    {
        return $this->_is_facebook;
    }
    /**
     * Set the browser to be from AOL.
     *
     * @param $isAol
     */
    public function setAol($isAol)
    {
        $this->_is_aol = $isAol;
    }
    /**
     * Set the Browser to be mobile.
     *
     * @param bool $value is the browser a mobile browser or not
     */
    protected function setMobile($value = \true)
    {
        $this->_is_mobile = $value;
    }
    /**
     * Set the Browser to be tablet.
     *
     * @param bool $value is the browser a tablet browser or not
     */
    protected function setTablet($value = \true)
    {
        $this->_is_tablet = $value;
    }
    /**
     * Set the Browser to be a robot.
     *
     * @param bool $value is the browser a robot or not
     */
    protected function setRobot($value = \true)
    {
        $this->_is_robot = $value;
    }
    /**
     * Set the Browser to be a Facebook request.
     *
     * @param bool $value is the browser a robot or not
     */
    protected function setFacebook($value = \true)
    {
        $this->_is_facebook = $value;
    }
    /**
     * Get the user agent value in use to determine the browser.
     *
     * @return string The user agent from the HTTP header
     */
    public function getUserAgent()
    {
        return $this->_agent;
    }
    /**
     * Set the user agent value (the construction will use the HTTP header value - this will overwrite it).
     *
     * @param string $agent_string The value for the User Agent
     */
    public function setUserAgent($agent_string)
    {
        $this->reset();
        $this->_agent = $agent_string;
        $this->determine();
    }
    /**
     * Used to determine if the browser is actually "chromeframe".
     *
     * @since 1.7
     *
     * @return bool True if the browser is using chromeframe
     */
    public function isChromeFrame()
    {
        return \false !== \strpos($this->_agent, 'chromeframe');
    }
    /**
     * Returns a formatted string with a summary of the details of the browser.
     *
     * @return string formatted string with a summary of the browser
     */
    public function __toString()
    {
        return "<strong>Browser Name:</strong> {$this->getBrowser()}<br/>\n" . "<strong>Browser Version:</strong> {$this->getVersion()}<br/>\n" . "<strong>Browser User Agent String:</strong> {$this->getUserAgent()}<br/>\n" . "<strong>Platform:</strong> {$this->getPlatform()}<br/>";
    }
    /**
     * Protected routine to calculate and determine what the browser is in use (including platform).
     */
    protected function determine()
    {
        $this->checkPlatform();
        $this->checkBrowsers();
        $this->checkForAol();
    }
    /**
     * Protected routine to determine the browser type.
     *
     * @return bool True if the browser was detected otherwise false
     */
    protected function checkBrowsers()
    {
        return $this->checkBrowserWebTv() || $this->checkBrowserEdge() || $this->checkBrowserInternetExplorer() || $this->checkBrowserOpera() || $this->checkBrowserGaleon() || $this->checkBrowserNetscapeNavigator9Plus() || $this->checkBrowserVivaldi() || $this->checkBrowserYandex() || $this->checkBrowserFirefox() || $this->checkBrowserChrome() || $this->checkBrowserOmniWeb() || $this->checkBrowserAndroid() || $this->checkBrowseriPad() || $this->checkBrowseriPod() || $this->checkBrowseriPhone() || $this->checkBrowserBlackBerry() || $this->checkBrowserNokia() || $this->checkBrowserGoogleBot() || $this->checkBrowserMSNBot() || $this->checkBrowserBingBot() || $this->checkBrowserSlurp() || $this->checkBrowserYandexBot() || $this->checkBrowserYandexImageResizerBot() || $this->checkBrowserYandexBlogsBot() || $this->checkBrowserYandexCatalogBot() || $this->checkBrowserYandexDirectBot() || $this->checkBrowserYandexFaviconsBot() || $this->checkBrowserYandexImagesBot() || $this->checkBrowserYandexMediaBot() || $this->checkBrowserYandexMetrikaBot() || $this->checkBrowserYandexNewsBot() || $this->checkBrowserYandexVideoBot() || $this->checkBrowserYandexWebmasterBot() || $this->checkFacebookExternalHit() || $this->checkBrowserSamsung() || $this->checkBrowserSilk() || $this->checkBrowserSafari() || $this->checkBrowserNetPositive() || $this->checkBrowserFirebird() || $this->checkBrowserKonqueror() || $this->checkBrowserIcab() || $this->checkBrowserPhoenix() || $this->checkBrowserAmaya() || $this->checkBrowserLynx() || $this->checkBrowserShiretoko() || $this->checkBrowserIceCat() || $this->checkBrowserIceweasel() || $this->checkBrowserW3CValidator() || $this->checkBrowserPlayStation() || $this->checkBrowserIframely() || $this->checkBrowserCocoa() || $this->checkBrowserMozilla();
    }
    /**
     * Determine if the user is using a BlackBerry (last updated 1.7).
     *
     * @return bool True if the browser is the BlackBerry browser otherwise false
     */
    protected function checkBrowserBlackBerry()
    {
        if (\false !== \stripos($this->_agent, 'blackberry')) {
            $aresult = \explode('/', \stristr($this->_agent, 'BlackBerry'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->_browser_name = self::BROWSER_BLACKBERRY;
                $this->setMobile(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the user is using an AOL User Agent (last updated 1.7).
     *
     * @return bool True if the browser is from AOL otherwise false
     */
    protected function checkForAol()
    {
        $this->setAol(\false);
        $this->setAolVersion(self::VERSION_UNKNOWN);
        if (\false !== \stripos($this->_agent, 'aol')) {
            $aversion = \explode(' ', \stristr($this->_agent, 'AOL'));
            if (isset($aversion[1])) {
                $this->setAol(\true);
                $this->setAolVersion(\preg_replace('/[^0-9\\.a-z]/i', '', $aversion[1]));
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the GoogleBot or not (last updated 1.7).
     *
     * @return bool True if the browser is the GoogletBot otherwise false
     */
    protected function checkBrowserGoogleBot()
    {
        if (\false !== \stripos($this->_agent, 'googlebot')) {
            $aresult = \explode('/', \stristr($this->_agent, 'googlebot'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_GOOGLEBOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexBot or not.
     *
     * @return bool True if the browser is the YandexBot otherwise false
     */
    protected function checkBrowserYandexBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexBot')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexBot'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXBOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexImageResizer or not.
     *
     * @return bool True if the browser is the YandexImageResizer otherwise false
     */
    protected function checkBrowserYandexImageResizerBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexImageResizer')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexImageResizer'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXIMAGERESIZER_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexCatalog or not.
     *
     * @return bool True if the browser is the YandexCatalog otherwise false
     */
    protected function checkBrowserYandexCatalogBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexCatalog')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexCatalog'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXCATALOG_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexNews or not.
     *
     * @return bool True if the browser is the YandexNews otherwise false
     */
    protected function checkBrowserYandexNewsBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexNews')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexNews'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXNEWS_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexMetrika or not.
     *
     * @return bool True if the browser is the YandexMetrika otherwise false
     */
    protected function checkBrowserYandexMetrikaBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexMetrika')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexMetrika'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXMETRIKA_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexDirect or not.
     *
     * @return bool True if the browser is the YandexDirect otherwise false
     */
    protected function checkBrowserYandexDirectBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexDirect')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexDirect'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXDIRECT_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexWebmaster or not.
     *
     * @return bool True if the browser is the YandexWebmaster otherwise false
     */
    protected function checkBrowserYandexWebmasterBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexWebmaster')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexWebmaster'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXWEBMASTER_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexFavicons or not.
     *
     * @return bool True if the browser is the YandexFavicons otherwise false
     */
    protected function checkBrowserYandexFaviconsBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexFavicons')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexFavicons'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXFAVICONS_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexBlogs or not.
     *
     * @return bool True if the browser is the YandexBlogs otherwise false
     */
    protected function checkBrowserYandexBlogsBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexBlogs')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexBlogs'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXBLOGS_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexMedia or not.
     *
     * @return bool True if the browser is the YandexMedia otherwise false
     */
    protected function checkBrowserYandexMediaBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexMedia')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexMedia'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXMEDIA_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexVideo or not.
     *
     * @return bool True if the browser is the YandexVideo otherwise false
     */
    protected function checkBrowserYandexVideoBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexVideo')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexVideo'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXVIDEO_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the YandexImages or not.
     *
     * @return bool True if the browser is the YandexImages otherwise false
     */
    protected function checkBrowserYandexImagesBot()
    {
        if (\false !== \stripos($this->_agent, 'YandexImages')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YandexImages'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_YANDEXIMAGES_BOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the MSNBot or not (last updated 1.9).
     *
     * @return bool True if the browser is the MSNBot otherwise false
     */
    protected function checkBrowserMSNBot()
    {
        if (\false !== \stripos($this->_agent, 'msnbot')) {
            $aresult = \explode('/', \stristr($this->_agent, 'msnbot'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_MSNBOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the BingBot or not (last updated 1.9).
     *
     * @return bool True if the browser is the BingBot otherwise false
     */
    protected function checkBrowserBingBot()
    {
        if (\false !== \stripos($this->_agent, 'bingbot')) {
            $aresult = \explode('/', \stristr($this->_agent, 'bingbot'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(';', '', $aversion[0]));
                $this->_browser_name = self::BROWSER_BINGBOT;
                $this->setRobot(\true);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is the W3C Validator or not (last updated 1.7).
     *
     * @return bool True if the browser is the W3C Validator otherwise false
     */
    protected function checkBrowserW3CValidator()
    {
        if (\false !== \stripos($this->_agent, 'W3C-checklink')) {
            $aresult = \explode('/', \stristr($this->_agent, 'W3C-checklink'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->_browser_name = self::BROWSER_W3CVALIDATOR;
                return \true;
            }
        } elseif (\false !== \stripos($this->_agent, 'W3C_Validator')) {
            // Some of the Validator versions do not delineate w/ a slash - add it back in
            $ua = \str_replace('W3C_Validator ', 'W3C_Validator/', $this->_agent);
            $aresult = \explode('/', \stristr($ua, 'W3C_Validator'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->_browser_name = self::BROWSER_W3CVALIDATOR;
                return \true;
            }
        } elseif (\false !== \stripos($this->_agent, 'W3C-mobileOK')) {
            $this->_browser_name = self::BROWSER_W3CVALIDATOR;
            $this->setMobile(\true);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is the Yahoo! Slurp Robot or not (last updated 1.7).
     *
     * @return bool True if the browser is the Yahoo! Slurp Robot otherwise false
     */
    protected function checkBrowserSlurp()
    {
        if (\false !== \stripos($this->_agent, 'slurp')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Slurp'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->_browser_name = self::BROWSER_SLURP;
                $this->setRobot(\true);
                $this->setMobile(\false);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Edge or not.
     *
     * @return bool True if the browser is Edge otherwise false
     */
    protected function checkBrowserEdge()
    {
        if (\false !== \stripos($this->_agent, 'Edge/')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Edge'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_EDGE);
                if (\false !== \stripos($this->_agent, 'Windows Phone') || \false !== \stripos($this->_agent, 'Android')) {
                    $this->setMobile(\true);
                }
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Internet Explorer or not (last updated 1.7).
     *
     * @return bool True if the browser is Internet Explorer otherwise false
     */
    protected function checkBrowserInternetExplorer()
    {
        //  Test for IE11
        if (\false !== \stripos($this->_agent, 'Trident/7.0; rv:11.0')) {
            $this->setBrowser(self::BROWSER_IE);
            $this->setVersion('11.0');
            return \true;
        } elseif (\false !== \stripos($this->_agent, 'microsoft internet explorer')) {
            $this->setBrowser(self::BROWSER_IE);
            $this->setVersion('1.0');
            $aresult = \stristr($this->_agent, '/');
            if (\preg_match('/308|425|426|474|0b1/i', $aresult)) {
                $this->setVersion('1.5');
            }
            return \true;
        } elseif (\false !== \stripos($this->_agent, 'msie') && \false === \stripos($this->_agent, 'opera')) {
            // See if the browser is the odd MSN Explorer
            if (\false !== \stripos($this->_agent, 'msnb')) {
                $aresult = \explode(' ', \stristr(\str_replace(';', '; ', $this->_agent), 'MSN'));
                if (isset($aresult[1])) {
                    $this->setBrowser(self::BROWSER_MSN);
                    $this->setVersion(\str_replace(['(', ')', ';'], '', $aresult[1]));
                    return \true;
                }
            }
            $aresult = \explode(' ', \stristr(\str_replace(';', '; ', $this->_agent), 'msie'));
            if (isset($aresult[1])) {
                $this->setBrowser(self::BROWSER_IE);
                $this->setVersion(\str_replace(['(', ')', ';'], '', $aresult[1]));
                if (\preg_match('#trident/([0-9\\.]+);#i', $this->_agent, $aresult)) {
                    if ('3.1' == $aresult[1]) {
                        $this->setVersion('7.0');
                    } elseif ('4.0' == $aresult[1]) {
                        $this->setVersion('8.0');
                    } elseif ('5.0' == $aresult[1]) {
                        $this->setVersion('9.0');
                    } elseif ('6.0' == $aresult[1]) {
                        $this->setVersion('10.0');
                    } elseif ('7.0' == $aresult[1]) {
                        $this->setVersion('11.0');
                    } elseif ('8.0' == $aresult[1]) {
                        $this->setVersion('11.0');
                    }
                }
                if (\false !== \stripos($this->_agent, 'IEMobile')) {
                    $this->setBrowser(self::BROWSER_POCKET_IE);
                    $this->setMobile(\true);
                }
                return \true;
            }
        } elseif (\false !== \stripos($this->_agent, 'trident')) {
            $this->setBrowser(self::BROWSER_IE);
            $result = \explode('rv:', $this->_agent);
            if (isset($result[1])) {
                $this->setVersion(\preg_replace('/[^0-9.]+/', '', $result[1]));
                $this->_agent = \str_replace(['Mozilla', 'Gecko'], 'MSIE', $this->_agent);
            }
        } elseif (\false !== \stripos($this->_agent, 'mspie') || \false !== \stripos($this->_agent, 'pocket')) {
            $aresult = \explode(' ', \stristr($this->_agent, 'mspie'));
            if (isset($aresult[1])) {
                $this->setPlatform(self::PLATFORM_WINDOWS_CE);
                $this->setBrowser(self::BROWSER_POCKET_IE);
                $this->setMobile(\true);
                if (\false !== \stripos($this->_agent, 'mspie')) {
                    $this->setVersion($aresult[1]);
                } else {
                    $aversion = \explode('/', $this->_agent);
                    if (isset($aversion[1])) {
                        $this->setVersion($aversion[1]);
                    }
                }
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Opera or not (last updated 1.7).
     *
     * @return bool True if the browser is Opera otherwise false
     */
    protected function checkBrowserOpera()
    {
        if (\false !== \stripos($this->_agent, 'opera mini')) {
            $resultant = \stristr($this->_agent, 'opera mini');
            if (\preg_match('/\\//', $resultant)) {
                $aresult = \explode('/', $resultant);
                if (isset($aresult[1])) {
                    $aversion = \explode(' ', $aresult[1]);
                    $this->setVersion($aversion[0]);
                }
            } else {
                $aversion = \explode(' ', \stristr($resultant, 'opera mini'));
                if (isset($aversion[1])) {
                    $this->setVersion($aversion[1]);
                }
            }
            $this->_browser_name = self::BROWSER_OPERA_MINI;
            $this->setMobile(\true);
            return \true;
        } elseif (\false !== \stripos($this->_agent, 'opera')) {
            $resultant = \stristr($this->_agent, 'opera');
            if (\preg_match('/Version\\/(1*.*)$/', $resultant, $matches)) {
                $this->setVersion($matches[1]);
            } elseif (\preg_match('/\\//', $resultant)) {
                $aresult = \explode('/', \str_replace('(', ' ', $resultant));
                if (isset($aresult[1])) {
                    $aversion = \explode(' ', $aresult[1]);
                    $this->setVersion($aversion[0]);
                }
            } else {
                $aversion = \explode(' ', \stristr($resultant, 'opera'));
                $this->setVersion(isset($aversion[1]) ? $aversion[1] : '');
            }
            if (\false !== \stripos($this->_agent, 'Opera Mobi')) {
                $this->setMobile(\true);
            }
            $this->_browser_name = self::BROWSER_OPERA;
            return \true;
        } elseif (\false !== \stripos($this->_agent, 'OPR')) {
            $resultant = \stristr($this->_agent, 'OPR');
            if (\preg_match('/\\//', $resultant)) {
                $aresult = \explode('/', \str_replace('(', ' ', $resultant));
                if (isset($aresult[1])) {
                    $aversion = \explode(' ', $aresult[1]);
                    $this->setVersion($aversion[0]);
                }
            }
            if (\false !== \stripos($this->_agent, 'Mobile')) {
                $this->setMobile(\true);
            }
            $this->_browser_name = self::BROWSER_OPERA;
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Chrome or not (last updated 1.7).
     *
     * @return bool True if the browser is Chrome otherwise false
     */
    protected function checkBrowserChrome()
    {
        if (\false !== \stripos($this->_agent, 'Chrome')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Chrome'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_CHROME);
                //Chrome on Android
                if (\false !== \stripos($this->_agent, 'Android')) {
                    if (\false !== \stripos($this->_agent, 'Mobile')) {
                        $this->setMobile(\true);
                    } else {
                        $this->setTablet(\true);
                    }
                }
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is WebTv or not (last updated 1.7).
     *
     * @return bool True if the browser is WebTv otherwise false
     */
    protected function checkBrowserWebTv()
    {
        if (\false !== \stripos($this->_agent, 'webtv')) {
            $aresult = \explode('/', \stristr($this->_agent, 'webtv'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_WEBTV);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is NetPositive or not (last updated 1.7).
     *
     * @return bool True if the browser is NetPositive otherwise false
     */
    protected function checkBrowserNetPositive()
    {
        if (\false !== \stripos($this->_agent, 'NetPositive')) {
            $aresult = \explode('/', \stristr($this->_agent, 'NetPositive'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion(\str_replace(['(', ')', ';'], '', $aversion[0]));
                $this->setBrowser(self::BROWSER_NETPOSITIVE);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Galeon or not (last updated 1.7).
     *
     * @return bool True if the browser is Galeon otherwise false
     */
    protected function checkBrowserGaleon()
    {
        if (\false !== \stripos($this->_agent, 'galeon')) {
            $aresult = \explode(' ', \stristr($this->_agent, 'galeon'));
            $aversion = \explode('/', $aresult[0]);
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_GALEON);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Konqueror or not (last updated 1.7).
     *
     * @return bool True if the browser is Konqueror otherwise false
     */
    protected function checkBrowserKonqueror()
    {
        if (\false !== \stripos($this->_agent, 'Konqueror')) {
            $aresult = \explode(' ', \stristr($this->_agent, 'Konqueror'));
            $aversion = \explode('/', $aresult[0]);
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_KONQUEROR);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is iCab or not (last updated 1.7).
     *
     * @return bool True if the browser is iCab otherwise false
     */
    protected function checkBrowserIcab()
    {
        if (\false !== \stripos($this->_agent, 'icab')) {
            $aversion = \explode(' ', \stristr(\str_replace('/', ' ', $this->_agent), 'icab'));
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_ICAB);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is OmniWeb or not (last updated 1.7).
     *
     * @return bool True if the browser is OmniWeb otherwise false
     */
    protected function checkBrowserOmniWeb()
    {
        if (\false !== \stripos($this->_agent, 'omniweb')) {
            $aresult = \explode('/', \stristr($this->_agent, 'omniweb'));
            $aversion = \explode(' ', isset($aresult[1]) ? $aresult[1] : '');
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_OMNIWEB);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Phoenix or not (last updated 1.7).
     *
     * @return bool True if the browser is Phoenix otherwise false
     */
    protected function checkBrowserPhoenix()
    {
        if (\false !== \stripos($this->_agent, 'Phoenix')) {
            $aversion = \explode('/', \stristr($this->_agent, 'Phoenix'));
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_PHOENIX);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Firebird or not (last updated 1.7).
     *
     * @return bool True if the browser is Firebird otherwise false
     */
    protected function checkBrowserFirebird()
    {
        if (\false !== \stripos($this->_agent, 'Firebird')) {
            $aversion = \explode('/', \stristr($this->_agent, 'Firebird'));
            if (isset($aversion[1])) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_FIREBIRD);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Netscape Navigator 9+ or not (last updated 1.7)
     * NOTE: (http://browser.netscape.com/ - Official support ended on March 1st, 2008).
     *
     * @return bool True if the browser is Netscape Navigator 9+ otherwise false
     */
    protected function checkBrowserNetscapeNavigator9Plus()
    {
        if (\false !== \stripos($this->_agent, 'Firefox') && \preg_match('/Navigator\\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
            return \true;
        } elseif (\false === \stripos($this->_agent, 'Firefox') && \preg_match('/Netscape6?\\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Shiretoko or not (https://wiki.mozilla.org/Projects/shiretoko) (last updated 1.7).
     *
     * @return bool True if the browser is Shiretoko otherwise false
     */
    protected function checkBrowserShiretoko()
    {
        if (\false !== \stripos($this->_agent, 'Mozilla') && \preg_match('/Shiretoko\\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_SHIRETOKO);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Ice Cat or not (http://en.wikipedia.org/wiki/GNU_IceCat) (last updated 1.7).
     *
     * @return bool True if the browser is Ice Cat otherwise false
     */
    protected function checkBrowserIceCat()
    {
        if (\false !== \stripos($this->_agent, 'Mozilla') && \preg_match('/IceCat\\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_ICECAT);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Nokia or not (last updated 1.7).
     *
     * @return bool True if the browser is Nokia otherwise false
     */
    protected function checkBrowserNokia()
    {
        if (\preg_match("/Nokia([^\\/]+)\\/([^ SP]+)/i", $this->_agent, $matches)) {
            $this->setVersion($matches[2]);
            if (\false !== \stripos($this->_agent, 'Series60') || \false !== \strpos($this->_agent, 'S60')) {
                $this->setBrowser(self::BROWSER_NOKIA_S60);
            } else {
                $this->setBrowser(self::BROWSER_NOKIA);
            }
            $this->setMobile(\true);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Firefox or not (last updated 1.7).
     *
     * @return bool True if the browser is Firefox otherwise false
     */
    protected function checkBrowserFirefox()
    {
        if (\false === \stripos($this->_agent, 'safari')) {
            if (\preg_match("/Firefox[\\/ \\(]([^ ;\\)]+)/i", $this->_agent, $matches)) {
                $this->setVersion($matches[1]);
                $this->setBrowser(self::BROWSER_FIREFOX);
                //Firefox on Android
                if (\false !== \stripos($this->_agent, 'Android')) {
                    if (\false !== \stripos($this->_agent, 'Mobile')) {
                        $this->setMobile(\true);
                    } else {
                        $this->setTablet(\true);
                    }
                }
                return \true;
            } elseif (\preg_match('/Firefox$/i', $this->_agent, $matches)) {
                $this->setVersion('');
                $this->setBrowser(self::BROWSER_FIREFOX);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Firefox or not (last updated 1.7).
     *
     * @return bool True if the browser is Firefox otherwise false
     */
    protected function checkBrowserIceweasel()
    {
        if (\false !== \stripos($this->_agent, 'Iceweasel')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Iceweasel'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_ICEWEASEL);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Mozilla or not (last updated 1.7).
     *
     * @return bool True if the browser is Mozilla otherwise false
     */
    protected function checkBrowserMozilla()
    {
        if (\false !== \stripos($this->_agent, 'mozilla') && \preg_match('/rv:[0-9].[0-9][a-b]?/i', $this->_agent) && \false === \stripos($this->_agent, 'netscape')) {
            $aversion = \explode(' ', \stristr($this->_agent, 'rv:'));
            \preg_match('/rv:[0-9].[0-9][a-b]?/i', $this->_agent, $aversion);
            $this->setVersion(\str_replace('rv:', '', $aversion[0]));
            $this->setBrowser(self::BROWSER_MOZILLA);
            return \true;
        } elseif (\false !== \stripos($this->_agent, 'mozilla') && \preg_match('/rv:[0-9]\\.[0-9]/i', $this->_agent) && \false === \stripos($this->_agent, 'netscape')) {
            $aversion = \explode('', \stristr($this->_agent, 'rv:'));
            $this->setVersion(\str_replace('rv:', '', $aversion[0]));
            $this->setBrowser(self::BROWSER_MOZILLA);
            return \true;
        } elseif (\false !== \stripos($this->_agent, 'mozilla') && \preg_match('/mozilla\\/([^ ]*)/i', $this->_agent, $matches) && \false === \stripos($this->_agent, 'netscape')) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_MOZILLA);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Lynx or not (last updated 1.7).
     *
     * @return bool True if the browser is Lynx otherwise false
     */
    protected function checkBrowserLynx()
    {
        if (\false !== \stripos($this->_agent, 'lynx')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Lynx'));
            $aversion = \explode(' ', isset($aresult[1]) ? $aresult[1] : '');
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_LYNX);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Amaya or not (last updated 1.7).
     *
     * @return bool True if the browser is Amaya otherwise false
     */
    protected function checkBrowserAmaya()
    {
        if (\false !== \stripos($this->_agent, 'amaya')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Amaya'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_AMAYA);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Safari or not (last updated 1.7).
     *
     * @return bool True if the browser is Safari otherwise false
     */
    protected function checkBrowserSafari()
    {
        if (\false !== \stripos($this->_agent, 'Safari') && \false === \stripos($this->_agent, 'iPhone') && \false === \stripos($this->_agent, 'iPod')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Version'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_SAFARI);
            return \true;
        }
        return \false;
    }
    protected function checkBrowserSamsung()
    {
        if (\false !== \stripos($this->_agent, 'SamsungBrowser')) {
            $aresult = \explode('/', \stristr($this->_agent, 'SamsungBrowser'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_SAMSUNG);
            return \true;
        }
        return \false;
    }
    protected function checkBrowserSilk()
    {
        if (\false !== \stripos($this->_agent, 'Silk')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Silk'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_SILK);
            return \true;
        }
        return \false;
    }
    protected function checkBrowserIframely()
    {
        if (\false !== \stripos($this->_agent, 'Iframely')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Iframely'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_I_FRAME);
            return \true;
        }
        return \false;
    }
    protected function checkBrowserCocoa()
    {
        if (\false !== \stripos($this->_agent, 'CocoaRestClient')) {
            $aresult = \explode('/', \stristr($this->_agent, 'CocoaRestClient'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            $this->setBrowser(self::BROWSER_COCOA);
            return \true;
        }
        return \false;
    }
    /**
     * Detect if URL is loaded from FacebookExternalHit.
     *
     * @return bool True if it detects FacebookExternalHit otherwise false
     */
    protected function checkFacebookExternalHit()
    {
        if (\stristr($this->_agent, 'FacebookExternalHit')) {
            $this->setRobot(\true);
            $this->setFacebook(\true);
            return \true;
        }
        return \false;
    }
    /**
     * Detect if URL is being loaded from internal Facebook browser.
     *
     * @return bool True if it detects internal Facebook browser otherwise false
     */
    protected function checkForFacebookIos()
    {
        if (\stristr($this->_agent, 'FBIOS')) {
            $this->setFacebook(\true);
            return \true;
        }
        return \false;
    }
    /**
     * Detect Version for the Safari browser on iOS devices.
     *
     * @return bool True if it detects the version correctly otherwise false
     */
    protected function getSafariVersionOnIos()
    {
        $aresult = \explode('/', \stristr($this->_agent, 'Version'));
        if (isset($aresult[1])) {
            $aversion = \explode(' ', $aresult[1]);
            $this->setVersion($aversion[0]);
            return \true;
        }
        return \false;
    }
    /**
     * Detect Version for the Chrome browser on iOS devices.
     *
     * @return bool True if it detects the version correctly otherwise false
     */
    protected function getChromeVersionOnIos()
    {
        $aresult = \explode('/', \stristr($this->_agent, 'CriOS'));
        if (isset($aresult[1])) {
            $aversion = \explode(' ', $aresult[1]);
            $this->setVersion($aversion[0]);
            $this->setBrowser(self::BROWSER_CHROME);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is iPhone or not (last updated 1.7).
     *
     * @return bool True if the browser is iPhone otherwise false
     */
    protected function checkBrowseriPhone()
    {
        if (\false !== \stripos($this->_agent, 'iPhone')) {
            $this->setVersion(self::VERSION_UNKNOWN);
            $this->setBrowser(self::BROWSER_IPHONE);
            $this->getSafariVersionOnIos();
            $this->getChromeVersionOnIos();
            $this->checkForFacebookIos();
            $this->setMobile(\true);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is iPad or not (last updated 1.7).
     *
     * @return bool True if the browser is iPad otherwise false
     */
    protected function checkBrowseriPad()
    {
        if (\false !== \stripos($this->_agent, 'iPad')) {
            $this->setVersion(self::VERSION_UNKNOWN);
            $this->setBrowser(self::BROWSER_IPAD);
            $this->getSafariVersionOnIos();
            $this->getChromeVersionOnIos();
            $this->checkForFacebookIos();
            $this->setTablet(\true);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is iPod or not (last updated 1.7).
     *
     * @return bool True if the browser is iPod otherwise false
     */
    protected function checkBrowseriPod()
    {
        if (\false !== \stripos($this->_agent, 'iPod')) {
            $this->setVersion(self::VERSION_UNKNOWN);
            $this->setBrowser(self::BROWSER_IPOD);
            $this->getSafariVersionOnIos();
            $this->getChromeVersionOnIos();
            $this->checkForFacebookIos();
            $this->setMobile(\true);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Android or not (last updated 1.7).
     *
     * @return bool True if the browser is Android otherwise false
     */
    protected function checkBrowserAndroid()
    {
        if (\false !== \stripos($this->_agent, 'Android')) {
            $aresult = \explode(' ', \stristr($this->_agent, 'Android'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
            } else {
                $this->setVersion(self::VERSION_UNKNOWN);
            }
            if (\false !== \stripos($this->_agent, 'Mobile')) {
                $this->setMobile(\true);
            } else {
                $this->setTablet(\true);
            }
            $this->setBrowser(self::BROWSER_ANDROID);
            return \true;
        }
        return \false;
    }
    /**
     * Determine if the browser is Vivaldi.
     *
     * @return bool True if the browser is Vivaldi otherwise false
     */
    protected function checkBrowserVivaldi()
    {
        if (\false !== \stripos($this->_agent, 'Vivaldi')) {
            $aresult = \explode('/', \stristr($this->_agent, 'Vivaldi'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_VIVALDI);
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is Yandex.
     *
     * @return bool True if the browser is Yandex otherwise false
     */
    protected function checkBrowserYandex()
    {
        if (\false !== \stripos($this->_agent, 'YaBrowser')) {
            $aresult = \explode('/', \stristr($this->_agent, 'YaBrowser'));
            if (isset($aresult[1])) {
                $aversion = \explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
                $this->setBrowser(self::BROWSER_YANDEX);
                if (\false !== \stripos($this->_agent, 'iPad')) {
                    $this->setTablet(\true);
                } elseif (\false !== \stripos($this->_agent, 'Mobile')) {
                    $this->setMobile(\true);
                } elseif (\false !== \stripos($this->_agent, 'Android')) {
                    $this->setTablet(\true);
                }
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine if the browser is a PlayStation.
     *
     * @return bool True if the browser is PlayStation otherwise false
     */
    protected function checkBrowserPlayStation()
    {
        if (\false !== \stripos($this->_agent, 'PlayStation ')) {
            $aresult = \explode(' ', \stristr($this->_agent, 'PlayStation '));
            $this->setBrowser(self::BROWSER_PLAYSTATION);
            if (isset($aresult[0])) {
                $aversion = \explode(')', $aresult[2]);
                $this->setVersion($aversion[0]);
                if (\false !== \stripos($this->_agent, 'Portable)') || \false !== \stripos($this->_agent, 'Vita')) {
                    $this->setMobile(\true);
                }
                return \true;
            }
        }
        return \false;
    }
    /**
     * Determine the user's platform (last updated 2.0).
     */
    protected function checkPlatform()
    {
        if (\false !== \stripos($this->_agent, 'windows')) {
            $this->_platform = self::PLATFORM_WINDOWS;
        } elseif (\false !== \stripos($this->_agent, 'iPad')) {
            $this->_platform = self::PLATFORM_IPAD;
        } elseif (\false !== \stripos($this->_agent, 'iPod')) {
            $this->_platform = self::PLATFORM_IPOD;
        } elseif (\false !== \stripos($this->_agent, 'iPhone')) {
            $this->_platform = self::PLATFORM_IPHONE;
        } elseif (\false !== \stripos($this->_agent, 'mac')) {
            $this->_platform = self::PLATFORM_APPLE;
        } elseif (\false !== \stripos($this->_agent, 'android')) {
            $this->_platform = self::PLATFORM_ANDROID;
        } elseif (\false !== \stripos($this->_agent, 'Silk')) {
            $this->_platform = self::PLATFORM_FIRE_OS;
        } elseif (\false !== \stripos($this->_agent, 'linux') && \false !== \stripos($this->_agent, 'SMART-TV')) {
            $this->_platform = self::PLATFORM_LINUX . '/' . self::PLATFORM_SMART_TV;
        } elseif (\false !== \stripos($this->_agent, 'linux')) {
            $this->_platform = self::PLATFORM_LINUX;
        } elseif (\false !== \stripos($this->_agent, 'Nokia')) {
            $this->_platform = self::PLATFORM_NOKIA;
        } elseif (\false !== \stripos($this->_agent, 'BlackBerry')) {
            $this->_platform = self::PLATFORM_BLACKBERRY;
        } elseif (\false !== \stripos($this->_agent, 'FreeBSD')) {
            $this->_platform = self::PLATFORM_FREEBSD;
        } elseif (\false !== \stripos($this->_agent, 'OpenBSD')) {
            $this->_platform = self::PLATFORM_OPENBSD;
        } elseif (\false !== \stripos($this->_agent, 'NetBSD')) {
            $this->_platform = self::PLATFORM_NETBSD;
        } elseif (\false !== \stripos($this->_agent, 'OpenSolaris')) {
            $this->_platform = self::PLATFORM_OPENSOLARIS;
        } elseif (\false !== \stripos($this->_agent, 'SunOS')) {
            $this->_platform = self::PLATFORM_SUNOS;
        } elseif (\false !== \stripos($this->_agent, 'OS\\/2')) {
            $this->_platform = self::PLATFORM_OS2;
        } elseif (\false !== \stripos($this->_agent, 'BeOS')) {
            $this->_platform = self::PLATFORM_BEOS;
        } elseif (\false !== \stripos($this->_agent, 'win')) {
            $this->_platform = self::PLATFORM_WINDOWS;
        } elseif (\false !== \stripos($this->_agent, 'Playstation')) {
            $this->_platform = self::PLATFORM_PLAYSTATION;
        } elseif (\false !== \stripos($this->_agent, 'Roku')) {
            $this->_platform = self::PLATFORM_ROKU;
        } elseif (\false !== \stripos($this->_agent, 'iOS')) {
            $this->_platform = self::PLATFORM_IPHONE . '/' . self::PLATFORM_IPAD;
        } elseif (\false !== \stripos($this->_agent, 'tvOS')) {
            $this->_platform = self::PLATFORM_APPLE_TV;
        } elseif (\false !== \stripos($this->_agent, 'curl')) {
            $this->_platform = self::PLATFORM_TERMINAL;
        } elseif (\false !== \stripos($this->_agent, 'CrOS')) {
            $this->_platform = self::PLATFORM_CHROME_OS;
        } elseif (\false !== \stripos($this->_agent, 'okhttp')) {
            $this->_platform = self::PLATFORM_JAVA_ANDROID;
        } elseif (\false !== \stripos($this->_agent, 'PostmanRuntime')) {
            $this->_platform = self::PLATFORM_POSTMAN;
        } elseif (\false !== \stripos($this->_agent, 'Iframely')) {
            $this->_platform = self::PLATFORM_I_FRAME;
        }
    }
}
