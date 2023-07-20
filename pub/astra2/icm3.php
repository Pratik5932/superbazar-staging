<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

class AstraWPIntegrity
{

    private $logs;
    private $cms;
    private $path;
    private $reports;
    private $exclude;

    function __construct()
    {
        $path = rtrim(substr(getcwd(), 0, strpos(getcwd(), '/astra')), "/");

        $path = "/var/www/html/";

        $path = substr(getcwd(), 0, strpos(getcwd(), '/pub/astra2'));

        $this->log("Path: " . $path);
        $this->exclude = array('astra', '360assets', '360assets', 'journal-cache', 'form', 'store-locator', 'superstorefinder', 'userTrack', 'cache');

        $this->cms = $this->detect_cms($path);

        if (!$this->cms) {
            $this->log("Unable to detect CMS");
            return false;
        }

        $this->reports = array();

        define('ABSPATH', $path . '/');
$this->path = $path . '/';
        $this->logs = "";

        echo '<a class="btn btn-danger btn-xs" href="?what=seppuku">Seppuku</a>';
    }

    protected function log($msg)
    {
        $this->logs .= "<p>$msg</p>\r\n";
    }

    function __destruct()
    {
        echo $this->logs;
    }

    function save($msg)
    {
        file_put_contents('scan_results.txt', $msg . PHP_EOL, FILE_APPEND);
    }

    function seppuku()
    {
        if (!defined('ABSPATH')) {
            $this->log("Not able to set ABSPATH");
            return false;
        }

        if (file_exists($this->cms)) {
            $this->log('CMS folder found');
        } else {
            $this->log('CMS folder missing. Bye.');
            return true;
        }

        $this->rrmdir($this->cms);

        if (!file_exists($this->cms)) {
            $this->log('CMS folder deleted');
        } else {
            $this->log('Unable to delete CMS folder');
        }
    }

    protected function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object))
                        $this->rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            rmdir($dir);
        }
    }


    function getChecksums($cms)
    {
        if (file_exists($cms) && is_dir($cms)) {
            $this->log("Found CMS folder for: " . $cms);
            return $this->getChecksumOfFolder($cms);
        }

        $method_name = "{$cms}Checksums";

        if (method_exists($this, $method_name)) {
            $this->log('Fetching checksums for ' . $method_name);
            return $this->$method_name();
        }

        $this->log('No defined method for getting checksums for ' . $method_name);
        return array();
    }


    protected function wordpressDownload($version)
    {
        $source = "https://downloads.wordpress.org/release/wordpress-{$version}.zip";
        $this->log('Downloading wordpress: ' . $source);

        // get WordPress version
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $source);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        // save as wordpress.zip
        $destination = "wordpress.zip"; // NEW FILE LOCATION
        $file = fopen($destination, "w+");
        fputs($file, $data);
        fclose($file);


        if (!file_exists($destination)) {
            $this->log('Unable to download WP zip');
            return false;
        }

        $zip = new ZipArchive;
        if ($zip->open(getcwd() . '/wordpress.zip') === TRUE) {
            $zip->extractTo(getcwd());
            $zip->close();
            $this->log('Unzipped WP');
            return true;
        }

        return false;

    }

    protected function get_data($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    function get_redirect_target($destination)
    {
        $headers = get_headers($destination, 1);
        return $headers['Location'];
    }

    function codeigniterChecksums()
    {

        $ci_path = ABSPATH . 'system/core/CodeIgniter.php';

        if (!file_exists($ci_path)) {
            $this->log("Could not find " . $ci_path);
            return false;
        }

        $contents = file_get_contents($ci_path);
        $version = $this->get_string_between($contents, "define('CI_VERSION', '", "');");

        $this->log("CodeIgniter version " . $version . " detected");

        if (!empty($version)) {

            if ($this->codeigniterDownload($version)) {
                if (file_exists($this->cms) && is_dir($this->cms)) {
                    $this->log("Found CMS folder for: " . $this->cms);
                    return $this->getChecksumOfFolder($this->cms);
                }
            } else {
                $this->log("Failed to download CodeIgniter " . $version);
            }
        }
    }

    function codeigniterDownload($version)
    {


        $source = "https://github.com/bcit-ci/CodeIgniter/archive/{$version}.zip";
        $destination = "ci.zip";

        if (!file_exists($destination)) {
            $this->log('Downloading CodeIgniter: ' . $source);

            // get WordPress version
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            $data = curl_exec($ch);
            curl_close($ch);
            $file = fopen($destination, "w+");
            fputs($file, $data);
            fclose($file);
        }

        if (!file_exists($destination)) {
            $this->log('Unable to download CI zip');
            return false;
        }

        $zip = new ZipArchive;
        if ($zip->open(getcwd() . '/ci.zip') === TRUE) {
            $dir_upload = getcwd() . "/codeigniter";

            @mkdir($dir_upload);
            $zip->extractTo($dir_upload);
            $this->log('Unzipped CI');


            $dir = trim($zip->getNameIndex(0), '/');
            $dir_upload = $dir . DIRECTORY_SEPARATOR . 'upload';

            echo $dir . "**";
            echo $dir_upload;
die('**');
            if (!empty($dir_upload) && file_exists($dir_upload)) {
                rename($dir_upload, 'opencart');
            }


            $zip->close();

            return true;
        }

        return false;
    }



    function joomlaChecksums()
    {
        $version = "3.3.6";

        if (!empty($version)) {

            if ($this->joomlaDownload($version)) {
                if (file_exists($this->cms) && is_dir($this->cms)) {
                    $this->log("Found CMS folder for: " . $this->cms);
                    return $this->getChecksumOfFolder($this->cms);
                }
            } else {
                $this->log("Failed to download Joomla " . $version);
            }
        }
    }

    function joomlaDownload($version)
    {
        $source = "https://github.com/joomla/joomla-cms/releases/download/{$version}/Joomla_{$version}-Stable-Full_Package.zip";
        $destination = "joomla.zip";

        if (!file_exists($destination)) {
            $this->log('Downloading Joomla: ' . $source);

            // get WordPress version
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            $data = curl_exec($ch);
            curl_close($ch);
            $file = fopen($destination, "w+");
            fputs($file, $data);
            fclose($file);
        }

        if (!file_exists($destination)) {
            $this->log('Unable to download Joomla zip');
            return false;
        }

        $zip = new ZipArchive;
        if ($zip->open(getcwd() . '/joomla.zip') === TRUE) {
            $dir_upload = getcwd() . "/joomla";

            @mkdir($dir_upload);
            $zip->extractTo($dir_upload);
            $this->log('Unzipped Joomla');
            $zip->close();

            return true;
        }

        return false;
    }


    function drupalChecksums()
    {

        $drupal_path = ABSPATH . 'includes/bootstrap.inc';

        if (!file_exists($drupal_path)) {
            $this->log("Could not find " . $drupal_path);
            return false;
        }


        include_once($drupal_path);

        define('DRUPAL_ROOT', dirname(getcwd()));
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

        $version = VERSION;


        if (!empty($version)) {

            if ($this->drupalDownload($version)) {
                if (file_exists($this->cms) && is_dir($this->cms)) {
                    $this->log("Found CMS folder for: " . $this->cms);
                    return $this->getChecksumOfFolder($this->cms);
                }
            } else {
                $this->log("Failed to download Drupal " . $version);
            }
        }


    }

    protected function drupalDownload($version)
    {
        $source = "https://ftp.drupal.org/files/projects/drupal-{$version}.zip";
        $this->log('Downloading Drupal: ' . $source);

        // get WordPress version
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $source);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        // save as wordpress.zip
        $destination = "drupal.zip"; // NEW FILE LOCATION
        $file = fopen($destination, "w+");
        fputs($file, $data);
        fclose($file);


        if (!file_exists($destination)) {
            $this->log('Unable to download Drupal zip');
            return false;
        }

        $zip = new ZipArchive;
        if ($zip->open(getcwd() . '/drupal.zip') === TRUE) {
            $zip->extractTo(getcwd());
            $zip->close();
            $this->log('Unzipped Drupal');

            $dir_upload = "drupal-{$version}";
            if (!empty($dir_upload) && file_exists($dir_upload)) {
                rename($dir_upload, 'drupal');
            }

            return true;
        }

        return false;

    }


    function opencartChecksums()
    {

        $opencart_path = ABSPATH . 'index.php';

        if (!file_exists($opencart_path)) {
            $this->log("Could not find " . $opencart_path);
            return false;
        }

        $contents = file_get_contents($opencart_path);

        $version = $this->get_string_between($contents, "define('VERSION', '", "');");

        if (!empty($version)) {

            if ($this->opencartDownload($version)) {
                if (file_exists($this->cms) && is_dir($this->cms)) {
                    $this->log("Found CMS folder for: " . $this->cms);
                    return $this->getChecksumOfFolder($this->cms);
                }
            } else {
                $this->log("Failed to download Magento");
            }
        }

    }

    function opencartDownload($version)
    {
        $source = "https://codeload.github.com/opencart/opencart/zip/{$version}";
        $destination = "opencart.zip";


        if (!file_exists($destination)) {
            $this->log('Downloading OpenCart: ' . $source);

            // get WordPress version
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            // save as wordpress.zip
            $file = fopen($destination, "w+");
            fputs($file, $data);
            fclose($file);
        }

        if (!file_exists($destination)) {
            $this->log('Unable to download OpenCart zip');
            return false;
        }

        $zip = new ZipArchive;
        if ($zip->open(getcwd() . '/opencart.zip') === TRUE) {
            $zip->extractTo(getcwd());
            $this->log('Unzipped OpenCart');

            $dir = trim($zip->getNameIndex(0), '/');
            $dir_upload = $dir . DIRECTORY_SEPARATOR . 'upload';

            if (!empty($dir_upload) && file_exists($dir_upload)) {
                rename($dir_upload, 'opencart');
            }

            $zip->close();

            return true;
        }

        return false;
    }

    /* PrestaShop */

    function prestashopChecksums()
    {

        $ps_path = ABSPATH . 'config/settings.inc.php';

        if (!file_exists($ps_path)) {
            $this->log("Could not find " . $ps_path);
            return false;
        }

        $contents = file_get_contents($ps_path);

        $version = $this->get_string_between($contents, "_PS_VERSION_', '", "');");

        if (!empty($version)) {
            $this->log("Found PS versions:" . $version);
            if ($this->prestashopDownload($version)) {
                if (file_exists($this->cms) && is_dir($this->cms)) {
                    $this->log("Found CMS folder for: " . $this->cms);
                    return $this->getChecksumOfFolder($this->cms);
                }
            } else {
                $this->log("Failed to download PS");
            }
        } else {
            $this->log("Unable to determine PS version");
        }

    }

    function prestashopDownload($version)
    {
        $source = "https://download.prestashop.com/download/releases/prestashop_{$version}.zip";
        $destination = "prestashop.zip";


        if (!file_exists($destination)) {
            $this->log('Downloading OpenCart: ' . $source);

            // get WordPress version
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            // save as wordpress.zip
            $file = fopen($destination, "w+");
            fputs($file, $data);
            fclose($file);
        }

        if (!file_exists($destination)) {
            $this->log('Unable to download OpenCart zip');
            return false;
        }

        $zip = new ZipArchive;
        if ($zip->open(getcwd() . '/' . $destination) === TRUE) {
            $zip->extractTo(getcwd());
            $this->log('Unzipped PrestaShop');

            $dir = trim($zip->getNameIndex(0), '/');
            $dir_upload = $dir . DIRECTORY_SEPARATOR . 'prestashop';

            if (!empty($dir_upload) && file_exists($dir_upload)) {
                rename($dir_upload, 'prestashop');
            }

            $zip->close();

            return true;
        }

        return false;
    }

    /* */

    function magentoChecksums()
    {

        $magento_path = ABSPATH . 'app/Mage.php';

        if (!file_exists($magento_path)) {
            $this->log("Could not find " . $magento_path);
            return false;
        }

        include($magento_path);

        $version = Mage::getVersion();

        if (!empty($version)) {

            if ($this->magentoDownload($version)) {
                if (file_exists($this->cms) && is_dir($this->cms)) {
                    $this->log("Found CMS folder for: " . $this->cms);
                    return $this->getChecksumOfFolder($this->cms);
                }
            } else {
                $this->log("Failed to download Magento");
            }
        }

    }

    function magentoDownload($version)
    {
        $source = "http://pubfiles.nexcess.net/magento/ce-packages/magento-{$version}.tar.gz";
        $this->log('Downloading Magento: ' . $source);

        // get WordPress version
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $source);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        // save as wordpress.zip
        $destination = "magento.tar.gz"; // NEW FILE LOCATION
        $file = fopen($destination, "w+");
        fputs($file, $data);
        fclose($file);


        if (!file_exists($destination)) {
            $this->log('Unable to download Magento zip');
            return false;
        }

        $p = new PharData(getcwd() . '/magento.tar.gz');
        $p->decompress();
        $phar = new PharData(getcwd() . '/magento.tar');
        $phar->extractTo(getcwd());

        if (file_exists('magento')) {
            $this->log('Unzipped Magento ' . $version);
            return true;
        }

        return false;
    }


    function magento2Checksums()
    {

 $mg_path = ABSPATH . '../composer.json';

        if (!file_exists($mg_path)) {
            $this->log("Could not find " . $mg_path);
            return false;
        }

        $contents = file_get_contents($mg_path);
        $version = $this->get_string_between($contents, '"magento/product-community-edition": "', '",');

        $this->log("magento2 version " . $version . " detected");

        if (!empty($version)) {

            if ($this->magento2Download($version)) {
                if (file_exists($this->cms) && is_dir($this->cms)) {
                    $this->log("Found CMS folder for: " . $this->cms);
                    return $this->getChecksumOfFolder($this->cms);
                }
            } else {
                $this->log("Failed to download Magento2");
            }
        }
    }

    function magento2Download($version)
    {
        $source = "https://codeload.github.com/magento/magento2/zip/{$version}";
        $destination = "magento2.zip";


        if (!file_exists($destination)) {
            $this->log('Downloading magento2: ' . $source);

            // get WordPress version
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            // save as wordpress.zip
            $file = fopen($destination, "w+");
            fputs($file, $data);
            fclose($file);
        }

        if (!file_exists($destination)) {
            $this->log('Unable to download Magento2 zip');
            return false;
        }

        $zip = new ZipArchive;
        if ($zip->open(getcwd() . '/magento2.zip') === TRUE) {
            $zip->extractTo(getcwd());
            $this->log('Unzipped magento2');

            $dir = trim($zip->getNameIndex(0), '/');
$this->log('zip folder: ' . $dir);

            if (!empty($dir) && file_exists($dir)) {
                rename($dir, 'magento2');
            }

      
            $zip->close();

            return true;
        }

        return false;
    }

    function wordpressChecksums()
    {

        include(ABSPATH . 'wp-includes/version.php');
        $wp_locale = isset($wp_local_package) ? $wp_local_package : 'en_US';
        $this->log("WP_locale: " . $wp_locale);
        $this->log("WP_Version: " . $wp_version);
        $apiurl = 'http://api.wordpress.org/core/checksums/1.0/?version=' . $wp_version . '&locale=' . $wp_locale;
        $this->log("API URL: " . $apiurl);

        if (!empty($wp_version)) {

            if ($this->wordpressDownload($wp_version)) {
                if (file_exists($this->cms) && is_dir($this->cms)) {
                    $this->log("Found CMS folder for: " . $this->cms);
                    return $this->getChecksumOfFolder($this->cms);
                }
            } else {
                $this->log("Failed to download WordPress");
            }
        }

    }


    function wordpressChecksums2()
    {
        include(ABSPATH . 'wp-includes/version.php');
        $wp_locale = isset($wp_local_package) ? $wp_local_package : 'en_US';
        $this->log("WP_locale: " . $wp_locale);
        $this->log("WP_Version: " . $wp_version);
        $apiurl = 'http://api.wordpress.org/core/checksums/1.0/?version=' . $wp_version . '&locale=' . $wp_locale;
        $this->log("API URL: " . $apiurl);

        //$resp = file_get_contents($apiurl);
        $resp = $this->get_data($apiurl);

        if (empty($resp)) {
            return array();
        }

        $json = json_decode($resp);

        if (!is_object($json)) {
            return array();
        }

        $checksums = $json->checksums;
        $num = count((array)$checksums);


        $this->log("Received checksum of " . $num . " files.");
        if ($num < 5) {
            $this->log("Too few checksums downloaded");
            return false;
        }

        if (!file_exists('wordpress')) {
            $this->wordpressDownload($wp_version);
        }

        return $checksums;
    }

    function getChecksumOfFolder($cms)
    {
        $checksums = array();

        $source = realpath($cms);
        $filter_dir = $this->exclude;
        $files = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator(
                    $source,
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                function ($files, $key, $iterator) use ($filter_dir) {
                    return ($files->isFile()) || !in_array($files->getBaseName(), $filter_dir);
                }
            )
        );

        foreach ($files as $file) {
            $base = dirname(__FILE__) . DIRECTORY_SEPARATOR . $cms;
            $file = realpath($file);

            if (is_file($file)) {
                $fp = $this->getRelativePath($base, $file);
                $checksums[$fp] = md5_file($file);
            }
        }

        return $checksums;
    }


    protected function detect_cms($path)
    {

        $path .= "/";

        $mapping = array(
            "wordpress" => array('wp-load.php', 'wp-config.php', 'wp-includes/plugin.php'),
            "joomla" => array('configuration.php', 'components/com_wrapper', 'libraries/joomla'),
            "drupal" => array('modules', 'profiles', 'includes', 'sites', 'includes/cache.inc'),
            //"magento" => array('skin', 'app', 'lib'),
            "magento2" => array('vendor/magento/framework', 'app/autoload.php'),
            "opencart" => array('config.php', 'system/startup.php', 'catalog/controller'),
            "rvssetup" => array('rvsincludefile', 'compoDBConnect.ini.php'),
            "prestashop" => array('config/smartyfront.config.inc.php', 'config/settings.inc.php'),
            "codeigniter" => array('system/core/CodeIgniter.php', 'system/libraries/Zip.php', 'application/config/config.php'),
            //"default" => array('font-awesome-4.2.0', 'README.md'),
        );

        foreach ($mapping as $cms_name => $files) {
            $not_found = false;

            foreach ($files as $file) {
                if (!file_exists($path . $file)) {
                    $not_found = true;
                }
            }

            if ($not_found === false) {
                return $cms_name;
            }
        }

        return FALSE;

    }

    function getRelativePath($base, $path)
    {
        // Detect directory separator
        $separator = substr($base, 0, 1);
        $base = array_slice(explode($separator, rtrim($base, $separator)), 1);
        $path = array_slice(explode($separator, rtrim($path, $separator)), 1);

        return $separator . implode($separator, array_slice($path, count($base)));
    }

    protected function isAbsolutePath($path)
    {
        if (!is_string($path)) {
            $mess = sprintf('String expected but was given %s', gettype($path));
            throw new \InvalidArgumentException($mess);
        }
        if (!ctype_print($path)) {
            $mess = 'Path can NOT have non-printable characters or be empty';
            throw new \DomainException($mess);
        }
        // Optional wrapper(s).
        $regExp = '%^(?<wrappers>(?:[[:print:]]{2,}://)*)';
        // Optional root prefix.
        $regExp .= '(?<root>(?:[[:alpha:]]:/|/)?)';
        // Actual path.
        $regExp .= '(?<path>(?:[[:print:]]*))$%';
        $parts = [];
        if (!preg_match($regExp, $path, $parts)) {
            $mess = sprintf('Path is NOT valid, was given %s', $path);
            throw new \DomainException($mess);
        }
        if ('' !== $parts['root']) {
            return true;
        }
        return false;
    }

    protected function getFolderContents($path)
    {

        if ($this->isAbsolutePath($path)) {
            $base = $path;
        } else {
            $base = dirname(__FILE__) . DIRECTORY_SEPARATOR . $path;
        }

        $data = array();

        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file) {

            if (is_file($base . $file)) {
                $filetypes = array("jpg", "png", "txt", "css", "md", "jpeg", "less", "scss", "pdf", "gif");
                $filetype = pathinfo($file, PATHINFO_EXTENSION);
                if (!in_array(strtolower($filetype), $filetypes)) {
                    $data[$file] = $base . $file;
                }
            }
        }

        return $data;
    }


    protected function getFilesInFolder($path, $prepend = '')
    {
        if (!file_exists($path) || !is_dir($path)) {
            return array();
        }

        $data = array();
        $source = realpath($path);
        $filter_dir = $this->exclude;
        $files = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator(
                    $source,
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                function ($files, $key, $iterator) use ($filter_dir) {

                    return $files->isFile() || !in_array($files->getBaseName(), $filter_dir);
                }
            )
        );


        if ($this->isAbsolutePath($path)) {
            $base = $path;
        } else {
            $base = dirname(__FILE__) . DIRECTORY_SEPARATOR . $path;
        }

        foreach ($files as $file) {

            $file = realpath($file);

            if (is_file($file)) {

                $filetypes = array("jpg", "png", "txt", "css", "md", "jpeg", "less", "scss", "pdf");
                $filetype = pathinfo($file, PATHINFO_EXTENSION);


                $fp = $this->getRelativePath($base, $file);
                //$data[] = array('file' => $fp, 'path' => $file);

                if (!empty($prepend)) {
                    $fp = DIRECTORY_SEPARATOR . $prepend . $fp;
                }

                if (!in_array(strtolower($filetype), $filetypes)) {
                    $data[$fp] = $file;
                }
            }
        }

        return $data;
    }

    protected function getFoldersInFolder($path)
    {
        $directories = glob($path . '/*', GLOB_ONLYDIR);
        foreach ($directories as $k => $v) {
            $directories[$k] = str_replace($path . DIRECTORY_SEPARATOR, "", $v);
        }

        return $directories;
    }

    protected function getFilesInActualSite($cms)
    {
        $include = $this->getFoldersInFolder($cms);

        $data = array();

        foreach ($include as $f) {
            $temp = $this->getFilesInFolder(ABSPATH . $f, $f);
            $data = $data + $temp;
        }

        $root = $this->getFolderContents(ABSPATH);

        return $root + $data;
    }

    protected function scanExtraFiles($cms)
    {
        $original = $this->getFilesInFolder($cms);
        $actual = $this->getFilesInActualSite($cms);

        foreach ($actual as $file => $path) {
            if (!isset($original[$file])) {
if( $this->cms !== 'magento2'){
                $this->addReport($path, 'Unknown file in live site');
}
            }
        }
    }

    function addReport($file_path, $reason)
    {
        $this->reports[] = array('path' => $file_path, 'reason' => $reason);
    }

    function getReportCount()
    {
        return count($this->reports);
    }

    function reportToTable()
    {
        $html = '<table class="table">';
        foreach ($this->reports as $fn) {
            $rp = $this->getRelativePath(ABSPATH, $fn['path']);
            $html .= "<tr><td>" . $fn['reason'] . "</td><td>" . $this->anchor('?what=diff&f2=' . base64_encode($rp), $fn['path'], 'target="_new"') . "</td></tr>";
        }
        $html .= "</table>";

        return $html;
    }

    function scan()
    {
        if (!defined('ABSPATH')) {
            $this->log("Not able to set ABSPATH");
            return false;

        }
        $this->log(ABSPATH);

        $checksums = $this->getChecksums($this->cms);

        $changed_files = array();

        if (empty($checksums)) {
            $this->log("Unable to calculate checksums for " . $this->cms);
            return false;
        }

//print_r($checksums); die();
        foreach ($checksums as $file => $checksum) {
            $file_path = ABSPATH . $file;
$file_path = str_replace("pub/", "",(ABSPATH)) . $file;

//echo "#" . $file_path . "#"; die('yo');
            if (file_exists($file_path)) {
                if (md5_file($file_path) !== $checksum) {
                    $f2 = $file_path;
                    $f1 = $this->cms . $this->getRelativePath(ABSPATH , ABSPATH . $file);
//echo "((" . $file_path . "))";
//echo "-->" .  $f1; die('^');
///echo "f2 = $f2<br/>";
//echo "f1 = $f1<br/>";
                    $c1 = file_get_contents($f1);
                    $c2 = file_get_contents($f2);

                    if ($this->verify_diff($c1, $c2)) {
                        $this->addReport($file_path, 'modified');
                    }

                }
            } else {

if( $this->cms !== 'magento2'){
                $this->addReport($file_path, 'missing');
}
                //$this->log("Does not exist: " . $file_path);
            }
        }


        //$this->scanExtraFiles($this->cms);

        echo $this->reportToTable();
    }

    function anchor($url, $title, $options = '')
    {
        return "<a href='{$url}' {$options}>{$title}</a>";
    }

    function index()
    {
        echo "Scan";
    }

    function restore()
    {
        $f2 = base64_decode($_GET['f2']);
        $f1 = $this->cms . DIRECTORY_SEPARATOR . $f2;
        $f2 = ABSPATH . $f2;

        if (!file_exists($f1)) {
            $this->log('Unable to find original file:' . $f1);
            return false;
        }

        if (!file_exists($f2)) {
            $this->log('Unable to find actual file:' . $f2);
            return false;
        }

        $content1 = md5_file($f1);
        $content2 = md5_file($f2);

        if (!is_writable($f2)) {
            if (is_file($f2)) {


                chmod($f2, 0644);
                if (!is_writable($f2)) {
                    $this->log("Does not have permissions to write");
                    return false;
                }
            }

        }
        copy($f1, $f2);

        $content2_2 = md5_file($f2);

        if ($content2_2 === $content1) {
            echo "File restored";
        } else {
            echo "Unable to replace";
        }

        echo "<hr/>";

        $this->diff();
    }

    function recreate()
    {
        $f2 = base64_decode($_GET['f2']);
        $f1 = $this->cms . DIRECTORY_SEPARATOR . $f2;
        $f2 = ABSPATH . $f2;

        if (!file_exists($f1)) {
            $this->log('Unable to find original file:' . $f1);
            return false;
        }

        if (file_exists($f2)) {
            $this->log('This file already exists, no need to recreate:' . $f2);
            return false;
        }

        $content1 = md5_file($f1);
        //$content2 = md5_file($f2);

        if (!is_writable(dirname($f2))) {
            $this->log('Folder is not writable: ' . dirname($f2));
            return false;
        }

        copy($f1, $f2);

        if(!file_exists($f1)){
            echo "Unable to create file: " . $f1;
        }

        $content2_2 = md5_file($f2);

        if ($content2_2 === $content1) {
            echo "File restored";
            return true;
        } else {
            echo "Unable to replace";
        }

        echo "<hr/>";

        $this->diff();
    }

    function erase()
    {
        $f2 = base64_decode($_GET['f2']);
        $f1 = $this->cms . DIRECTORY_SEPARATOR . $f2;
        $f2 = ABSPATH . $f2;

        if (file_exists($f1)) {
            $this->log('Operation denied: Try restoring file as this is a core cms file (' . $f1 . ')');
            return false;
        }

        if (!file_exists($f2)) {
            $this->log('Unable to find actual file:' . $f2);
            return false;
        }

        unlink($f2);

        if (file_exists($f2)) {
            $this->log('Unable to delete ' . $f2);
        } else {
            $this->save($f2);
            $this->log('Successfully deleted ' . $f2);
        }
        //$this->diff();
    }

    protected function remove_spaces($string)
    {

        $pattern = '/\s*/m';
        $replace = '';
        $removedLinebaksAndWhitespace = preg_replace($pattern, $replace, $string);
        return $removedLinebaksAndWhitespace;

    }

    protected function verify_diff($c1, $c2)
    {
        $c1 = $this->remove_spaces($c1);
        $c2 = $this->remove_spaces($c2);

        if (md5($c1) === md5($c2)) {
            return false;
        }

        return true;
    }

    function diff()
    {
        $f2 = base64_decode($_GET['f2']);
        $f1 = $this->cms . DIRECTORY_SEPARATOR . $f2;
        $f2 = ABSPATH . $f2;

        echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">';
        $rp = $this->getRelativePath(ABSPATH, $f2);

//p=wp-content%2Fplugins%2Fastra_wp%2Fastra%2Fdebug&edit=ic.php
        echo '<div class="row"><div class="col-12">';
        echo '<a class="btn btn-warning btn-xs" href="?what=restore&f2=' . base64_encode($rp) . '">Restore File to Original</a>';
        echo '<a class="btn btn-info btn-xs" href="?what=recreate&f2=' . base64_encode($rp) . '">Copy file to Live site</a>';
        echo '<a class="btn btn-danger btn-xs" href="?what=erase&f2=' . base64_encode($rp) . '">Delete Original file</a>';
        echo '<a class="btn btn-danger btn-xs" href="file-manager-astra.php?p=' . dirname($rp) . '&dl=' . basename($rp) . '">Download file</a>';
        echo '<a class="btn btn-danger btn-xs" href="file-manager-astra.php?p=' . dirname($rp) . '&edit=' . basename($rp) . '">Edit file</a>';
        echo '<a class="btn btn-danger btn-xs" href="?what=erase&f2=' . base64_encode($rp) . '">Seppuku</a>';
        echo '</div></div><hr/>';

        echo "<style>.diff td {
    padding :0 0.667em;
    vertical-align: top;
    white-space: pre;
    white-space: pre-wrap;
    font-family: Consolas,'Courier New',Courier,monospace;
    font-size: 0.75em;
    line-height: 1.333;
}

.diff span {
    display: block;
    min-height: 1.333em;
    margin-top: -1px;
    padding: 0 3px;
}

* html .diff span {
    height: 1.333em;
}

.diff span:first-child {
    margin-top: 0;
}

.diffDeleted span {
    border: 1px solid rgb(255,192,192);
    background: rgb(255,224,224);
}

.diffInserted span {
    border: 1px solid rgb(192,255,192);
    background: rgb(224,255,224);
}</style>";


        if (!file_exists($f1)) {
            $this->log('Unable to find original file:' . $f1);
            $f1_contents = "";
            //return false;
        } else {
            $f1_contents = file_get_contents($f1);
        }

        if (!file_exists($f2)) {
            $this->log('Unable to find actual file:' . $f2);

            if(empty($f1_contents)){
                return false;
            }

            $f2_contents = "";
        } else {
            $f2_contents = file_get_contents($f2);
        }

        echo "<ul>";
        echo "<li><strong>File path:</strong> " . $f2 . "</li>";
        echo "<li><strong>Checksum Original:</strong> " . (file_exists($f1) ? md5_file($f1) : "404") . "</li>";
        echo "<li><strong>Checksum Live:</strong> " . (file_exists($f2) ? md5_file($f2) : "404") . "</li>";
        echo "</ul>";

        if (!$this->verify_diff($f1_contents, $f2_contents)) {
            $this->log("Files are same");
            return false;
        }

        // compare two files line by line
        $diff = Diff::compare($f1_contents, $f2_contents);

        echo Diff::toTable($diff);


        /*
        echo '<div class="row">';
        echo '<div class="col-md-6 well">';
        echo '<div class="well2">';
        echo "<code>" . highlight_file($f1) . "</code></pre>";
        echo '</div>';
        echo '</div>';
        echo '<div class="col-md-6 well">';
        echo '<div class="well2">';
        echo "<code>" . highlight_file($f2) . "</code></pre>";
        echo '</div>';
        echo '</div>';
        echo '</div>';
        */
    }
}

$ga = new AstraWPIntegrity();

$action = !empty($_GET['what']) ? $_GET['what'] : 'scan';

if (method_exists($ga, $action)) {
    $ga->$action();
}
//$ga->scan();

?>


<?php

/*

class.Diff.php

A class containing a diff implementation

Created by Kate Morley - http://iamkate.com/ - and released under the terms of
the CC0 1.0 Universal legal code:

http://creativecommons.org/publicdomain/zero/1.0/legalcode

*/

// A class containing functions for computing diffs and formatting the output.
class Diff
{

    // define the constants
    const UNMODIFIED = 0;
    const DELETED = 1;
    const INSERTED = 2;

    /* Returns the diff for two strings. The return value is an array, each of
     * whose values is an array containing two values: a line (or character, if
     * $compareCharacters is true), and one of the constants DIFF::UNMODIFIED (the
     * line or character is in both strings), DIFF::DELETED (the line or character
     * is only in the first string), and DIFF::INSERTED (the line or character is
     * only in the second string). The parameters are:
     *
     * $string1           - the first string
     * $string2           - the second string
     * $compareCharacters - true to compare characters, and false to compare
     *                      lines; this optional parameter defaults to false
     */
    public static function compare(
        $string1, $string2, $compareCharacters = false)
    {

        // initialise the sequences and comparison start and end positions
        $start = 0;
        if ($compareCharacters) {
            $sequence1 = $string1;
            $sequence2 = $string2;
            $end1 = strlen($string1) - 1;
            $end2 = strlen($string2) - 1;
        } else {
            $sequence1 = preg_split('/\R/', $string1);
            $sequence2 = preg_split('/\R/', $string2);
            $end1 = count($sequence1) - 1;
            $end2 = count($sequence2) - 1;
        }

        // skip any common prefix
        while ($start <= $end1 && $start <= $end2
            && $sequence1[$start] == $sequence2[$start]) {
            $start++;
        }

        // skip any common suffix
        while ($end1 >= $start && $end2 >= $start
            && $sequence1[$end1] == $sequence2[$end2]) {
            $end1--;
            $end2--;
        }

        // compute the table of longest common subsequence lengths
        $table = self::computeTable($sequence1, $sequence2, $start, $end1, $end2);

        // generate the partial diff
        $partialDiff =
            self::generatePartialDiff($table, $sequence1, $sequence2, $start);

        // generate the full diff
        $diff = array();
        for ($index = 0; $index < $start; $index++) {
            $diff[] = array($sequence1[$index], self::UNMODIFIED);
        }
        while (count($partialDiff) > 0) $diff[] = array_pop($partialDiff);
        for ($index = $end1 + 1;
             $index < ($compareCharacters ? strlen($sequence1) : count($sequence1));
             $index++) {
            $diff[] = array($sequence1[$index], self::UNMODIFIED);
        }

        // return the diff
        return $diff;

    }

    /* Returns the diff for two files. The parameters are:
     *
     * $file1             - the path to the first file
     * $file2             - the path to the second file
     * $compareCharacters - true to compare characters, and false to compare
     *                      lines; this optional parameter defaults to false
     */
    public static function compareFiles(
        $file1, $file2, $compareCharacters = false)
    {

        // return the diff of the files
        return self::compare(
            file_get_contents($file1),
            file_get_contents($file2),
            $compareCharacters);

    }

    /* Returns the table of longest common subsequence lengths for the specified
     * sequences. The parameters are:
     *
     * $sequence1 - the first sequence
     * $sequence2 - the second sequence
     * $start     - the starting index
     * $end1      - the ending index for the first sequence
     * $end2      - the ending index for the second sequence
     */
    private static function computeTable(
        $sequence1, $sequence2, $start, $end1, $end2)
    {

        // determine the lengths to be compared
        $length1 = $end1 - $start + 1;
        $length2 = $end2 - $start + 1;

        // initialise the table
        $table = array(array_fill(0, $length2 + 1, 0));

        // loop over the rows
        for ($index1 = 1; $index1 <= $length1; $index1++) {

            // create the new row
            $table[$index1] = array(0);

            // loop over the columns
            for ($index2 = 1; $index2 <= $length2; $index2++) {

                // store the longest common subsequence length
                if ($sequence1[$index1 + $start - 1]
                    == $sequence2[$index2 + $start - 1]) {
                    $table[$index1][$index2] = $table[$index1 - 1][$index2 - 1] + 1;
                } else {
                    $table[$index1][$index2] =
                        max($table[$index1 - 1][$index2], $table[$index1][$index2 - 1]);
                }

            }
        }

        // return the table
        return $table;

    }

    /* Returns the partial diff for the specificed sequences, in reverse order.
     * The parameters are:
     *
     * $table     - the table returned by the computeTable function
     * $sequence1 - the first sequence
     * $sequence2 - the second sequence
     * $start     - the starting index
     */
    private static function generatePartialDiff(
        $table, $sequence1, $sequence2, $start)
    {

        //  initialise the diff
        $diff = array();

        // initialise the indices
        $index1 = count($table) - 1;
        $index2 = count($table[0]) - 1;

        // loop until there are no items remaining in either sequence
        while ($index1 > 0 || $index2 > 0) {

            // check what has happened to the items at these indices
            if ($index1 > 0 && $index2 > 0
                && $sequence1[$index1 + $start - 1]
                == $sequence2[$index2 + $start - 1]) {

                // update the diff and the indices
                $diff[] = array($sequence1[$index1 + $start - 1], self::UNMODIFIED);
                $index1--;
                $index2--;

            } elseif ($index2 > 0
                && $table[$index1][$index2] == $table[$index1][$index2 - 1]) {

                // update the diff and the indices
                $diff[] = array($sequence2[$index2 + $start - 1], self::INSERTED);
                $index2--;

            } else {

                // update the diff and the indices
                $diff[] = array($sequence1[$index1 + $start - 1], self::DELETED);
                $index1--;

            }

        }

        // return the diff
        return $diff;

    }

    /* Returns a diff as a string, where unmodified lines are prefixed by '  ',
     * deletions are prefixed by '- ', and insertions are prefixed by '+ '. The
     * parameters are:
     *
     * $diff      - the diff array
     * $separator - the separator between lines; this optional parameter defaults
     *              to "\n"
     */
    public static function toString($diff, $separator = "\n")
    {

        // initialise the string
        $string = '';

        // loop over the lines in the diff
        foreach ($diff as $line) {

            // extend the string with the line
            switch ($line[1]) {
                case self::UNMODIFIED :
                    $string .= '  ' . $line[0];
                    break;
                case self::DELETED    :
                    $string .= '- ' . $line[0];
                    break;
                case self::INSERTED   :
                    $string .= '+ ' . $line[0];
                    break;
            }

            // extend the string with the separator
            $string .= $separator;

        }

        // return the string
        return $string;

    }

    /* Returns a diff as an HTML string, where unmodified lines are contained
     * within 'span' elements, deletions are contained within 'del' elements, and
     * insertions are contained within 'ins' elements. The parameters are:
     *
     * $diff      - the diff array
     * $separator - the separator between lines; this optional parameter defaults
     *              to '<br>'
     */
    public static function toHTML($diff, $separator = '<br>')
    {

        // initialise the HTML
        $html = '';

        // loop over the lines in the diff
        foreach ($diff as $line) {

            // extend the HTML with the line
            switch ($line[1]) {
                case self::UNMODIFIED :
                    $element = 'span';
                    break;
                case self::DELETED    :
                    $element = 'del';
                    break;
                case self::INSERTED   :
                    $element = 'ins';
                    break;
            }
            $html .=
                '<' . $element . '>'
                . htmlspecialchars($line[0])
                . '</' . $element . '>';

            // extend the HTML with the separator
            $html .= $separator;

        }

        // return the HTML
        return $html;

    }

    /* Returns a diff as an HTML table. The parameters are:
     *
     * $diff        - the diff array
     * $indentation - indentation to add to every line of the generated HTML; this
     *                optional parameter defaults to ''
     * $separator   - the separator between lines; this optional parameter
     *                defaults to '<br>'
     */
    public static function toTable($diff, $indentation = '', $separator = '<br>')
    {

        // initialise the HTML
        $html = $indentation . "<table class=\"diff\">\n";

        // loop over the lines in the diff
        $index = 0;
        while ($index < count($diff)) {

            // determine the line type
            switch ($diff[$index][1]) {

                // display the content on the left and right
                case self::UNMODIFIED:
                    $leftCell =
                        self::getCellContent(
                            $diff, $indentation, $separator, $index, self::UNMODIFIED);
                    $rightCell = $leftCell;
                    break;

                // display the deleted on the left and inserted content on the right
                case self::DELETED:
                    $leftCell =
                        self::getCellContent(
                            $diff, $indentation, $separator, $index, self::DELETED);
                    $rightCell =
                        self::getCellContent(
                            $diff, $indentation, $separator, $index, self::INSERTED);
                    break;

                // display the inserted content on the right
                case self::INSERTED:
                    $leftCell = '';
                    $rightCell =
                        self::getCellContent(
                            $diff, $indentation, $separator, $index, self::INSERTED);
                    break;

            }

            // extend the HTML with the new row
            $html .=
                $indentation
                . "  <tr>\n"
                . $indentation
                . '    <td class="diff'
                . ($leftCell == $rightCell
                    ? 'Unmodified'
                    : ($leftCell == '' ? 'Blank' : 'Deleted'))
                . '">'
                . $leftCell
                . "</td>\n"
                . $indentation
                . '    <td class="diff'
                . ($leftCell == $rightCell
                    ? 'Unmodified'
                    : ($rightCell == '' ? 'Blank' : 'Inserted'))
                . '">'
                . $rightCell
                . "</td>\n"
                . $indentation
                . "  </tr>\n";

        }

        // return the HTML
        return $html . $indentation . "</table>\n";

    }

    /* Returns the content of the cell, for use in the toTable function. The
     * parameters are:
     *
     * $diff        - the diff array
     * $indentation - indentation to add to every line of the generated HTML
     * $separator   - the separator between lines
     * $index       - the current index, passes by reference
     * $type        - the type of line
     */
    private static function getCellContent(
        $diff, $indentation, $separator, &$index, $type)
    {

        // initialise the HTML
        $html = '';

        // loop over the matching lines, adding them to the HTML
        while ($index < count($diff) && $diff[$index][1] == $type) {
            $html .=
                '<span>'
                . htmlspecialchars($diff[$index][0])
                . '</span>'
                . $separator;
            $index++;
        }

        // return the HTML
        return $html;

    }

}

?>

