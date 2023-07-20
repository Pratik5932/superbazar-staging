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


namespace Mirasvit\RewardsAdminUi\Ui\Earning\Form\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Mirasvit\RewardsAdminUi\Model\System\Source\Productearningstyle;

class EarningProductStyle extends EarningStyle implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;
    protected $systemSourceProductearningstyle;

    public function __construct(
        Productearningstyle $systemSourceProductearningstyle
    ) {
        $this->systemSourceProductearningstyle = $systemSourceProductearningstyle;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $sourceArray = $this->systemSourceProductearningstyle->toArray();
        $this->options = $this->toEarningOptionArray($sourceArray);

        return $this->options;
    }
}
