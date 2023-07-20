<?php
namespace Mexbs\AdditionalPromotions\Model\Plugin\Quote\Cart;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;

class TotalsConverter
{
    protected $totalSegmentExtensionFactory;
    protected $detailsFactory;
    protected $serializer;

    public function __construct(
        TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        \Mexbs\AdditionalPromotions\Api\Data\DiscountDetailsInterfaceFactory $detailsFactory,
        \Mexbs\AdditionalPromotions\Api\Data\DescriptionLinesInterfaceFactory $descriptionLinesFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer
    ) {
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
        $this->detailsFactory = $detailsFactory;
        $this->descriptionLinesFactory = $descriptionLinesFactory;
        $this->serializer = $serializer;
    }

    public function aroundProcess(
        \Magento\Quote\Model\Cart\TotalsConverter $subject,
        \Closure $proceed,
        array $addressTotals = []
    ) {
        $totalSegments = $proceed($addressTotals);

        if (!array_key_exists('discount', $addressTotals)) {
            return $totalSegments;
        }

        $address = $addressTotals['discount']->getAddress();
        if(!$address){
            return $totalSegments;
        }

        $discountDetailsSerialized = $address->getDiscountDetails();
        $discountDetails = [];
        if($discountDetailsSerialized){
            $discountDetails = $this->serializer->unserialize($discountDetailsSerialized);
        }

        if(empty($discountDetails)){
            return $totalSegments;
        }

        $finalData = [];
        foreach($discountDetails as $discountDescriptionLinesRaw){
            $discountDescriptionLines = [];
            foreach($discountDescriptionLinesRaw as $discountDescriptionLineRaw){
                $discountDescriptionLine = $this->descriptionLinesFactory->create([])
                    ->setLine($discountDescriptionLineRaw);
                $discountDescriptionLines[] = $discountDescriptionLine;
            }

            $discountDetails = $this->detailsFactory->create([]);
            $discountDetails->setDescriptionLines($discountDescriptionLines);
            $finalData[] = $discountDetails;
        }


        $attributes = $totalSegments['discount']->getExtensionAttributes();
        if ($attributes === null) {
            $attributes = $this->totalSegmentExtensionFactory->create();
        }
        $attributes->setDiscountDetails($finalData);
        $totalSegments['discount']->setExtensionAttributes($attributes);
        return $totalSegments;
    }
}