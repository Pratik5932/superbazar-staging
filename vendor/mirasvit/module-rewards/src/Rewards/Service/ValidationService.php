<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rewards\Service;

use Mirasvit\Core\Service\AbstractValidator;

class ValidationService extends AbstractValidator
{
    /**
     * @var array
     */
    private $socialBlocks = [
        'mirasvit_rewards_unsubscribe',
        'customer-account-navigation-rewards-link',
        'rewards.fbscript',
        'rewards-link',
        'rewards-notification',
        'rewards.social.buttons',
        'rewards-tooltip',
    ];

    /**
     * @return void
     */
    public function testFrontendBlocks()
    {
        $modifications = [];

        $blockNames = [];
        foreach ($this->socialBlocks as $blockName) {
            $blockNames[] = preg_quote($blockName);
        }
        $command = 'grep -r -lPR --include=*.xml "' . implode('|', $blockNames) . '" ' . BP;
        $result = [];
        @exec($command, $result);
        foreach ($result as $filename) {
            if (strpos(strtolower($filename), 'mirasvit') === false) {
                $modifications[] = $filename;
            }
        }

        if ($modifications) {
            $modifications = '<br>'.implode('<br>', $modifications);
            $this->addWarning('Please note this files overrides core blocks: {0}.', [$modifications]);
        }
    }
}