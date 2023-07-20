<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Status implements OptionSourceInterface
{
    /**
     * @var \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout
     */
    protected $mppaypalexpresscheckout;

    /**
     * Constructor
     *
     * @param \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout $mppaypalexpresscheckout
     */
    public function __construct(\Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout $mppaypalexpresscheckout)
    {
        $this->mppaypalexpresscheckout = $mppaypalexpresscheckout;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->mppaypalexpresscheckout->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
