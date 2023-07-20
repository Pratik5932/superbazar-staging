<?php

namespace Superbazaar\CustomWork\Plugin\Wishlist;

use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Wishlist\Controller\Index\Add;

class AddPlugin
{
    /**
     * @var RedirectInterface
     */
    private $redirectIntrface;

    /**
     * AddPlugin constructor.
     * @param RedirectInterface $redirectIntrface
     */
    public function __construct(
        RedirectInterface $redirectIntrface
    ) {
        $this->redirectIntrface = $redirectIntrface;
    }

    /**
     * @param Add $subject
     * @param Redirect $resultRedirect
     * @return Redirect
     */
    public function afterExecute(
        Add $subject,
        Redirect $resultRedirect
    ): Redirect
    {
        $resultRedirect->setUrl($this->redirectIntrface->getRefererUrl());

        return $resultRedirect;
    }
}