<?php

/**
 * This file is part of the Astra Security Suite.
 *
 *  Copyright (c) 2019 (https://www.getastra.com/)
 *
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
/**
 * @author HumansofAstra-WZ <help@getastra.com>
 * @date   2019-04-05
 */
namespace AstraPrefixed\GetAstra\Client\Helper\Cms;

class AbstractCmsHelper
{
    private $path;
    private $cms;
    private $instance;
    public function __construct($path = '')
    {
        if (!empty($path)) {
            $this->path = $path;
        } else {
            $this->path = ASTRA_DOC_ROOT;
        }
        $this->cms = $this->detect_cms($this->path);
        $this->instance = $this->getCmsInstance($this->cms);
    }
    public function getCmsName()
    {
        return $this->cms;
    }
    public function getCms()
    {
        return $this->instance;
    }
    protected function getCmsInstance($cms, $recursion = \false)
    {
        $className = 'AstraPrefixed\\GetAstra\\Client\\Helper\\Cms\\' . \ucwords($cms) . 'Helper';
        if (\class_exists($className)) {
            return new $className($this->path);
        } elseif (!$recursion) {
            return $this->getCmsInstance('unknown');
        }
    }
    protected function detect_cms($path)
    {
        $mapping = [
            'wordpress' => ['wp-load.php', 'wp-config.php', 'wp-includes/plugin.php'],
            'joomla2' => ['libraries/cms/version/version.php', 'components/com_wrapper', 'libraries/joomla'],
            'joomla3' => ['libraries/fof', 'libraries/src/Version.php', 'modules/mod_menu'],
            //"drupal" => array('modules', 'profiles', 'includes', 'sites', 'includes/cache.inc'),
            'magento19' => ['skin', 'app', 'lib'],
            'magento2' => ['app/design/adminhtml/Magento', 'lib/web'],
            'drupal7' => ['includes/bootstrap.inc', 'sites/all'],
            'opencart' => ['config.php', 'system/startup.php', 'catalog/controller'],
            'prestashop16' => ['config/smartyfront.config.inc.php', 'config/settings.inc.php', 'footer.php'],
            'prestashop17' => ['app/AppKernel.php', 'app/AppCache.php'],
        ];
        foreach ($mapping as $cms_name => $files) {
            $not_found = \false;
            foreach ($files as $file) {
                if (!\file_exists($path . $file)) {
                    $not_found = \true;
                }
            }
            if (\false === $not_found) {
                return $cms_name;
            }
        }
        return 'unknown';
    }
}
