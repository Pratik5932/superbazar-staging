<?php
namespace Mexbs\AdditionalPromotions\Model\Rewrite\PageCache;

class DepersonalizeChecker extends \Magento\PageCache\Model\DepersonalizeChecker
{
    public function checkIfDepersonalize(\Magento\Framework\View\LayoutInterface $subject)
    {
        return
            (!$subject->getBlock('content') || !$subject->getBlock('content')->getDoNotDepersonalize())
            && parent::checkIfDepersonalize($subject);
    }
}