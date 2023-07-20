<?php
namespace Mexbs\AdditionalPromotions\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    protected $apHelper;


    public function __construct(
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper
    ) {
        $this->apHelper = $apHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'isApShowBreakdown' => $this->apHelper->getIsDiscountBreakdownEnabled(),
            'isApBreakdownCollapsedByDefault' => $this->apHelper->getIsDiscountBreakdownCollapsed()
        ];
    }
}
