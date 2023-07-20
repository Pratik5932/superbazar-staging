<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Block\Adminhtml\General\Edit;

use Magento\Search\Controller\RegistryConstants;

class GenericButton
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $stripeSeller
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\MpStripe\Model\StripeSellerFactory $stripeSeller,
        \Magento\Framework\Registry $registry
    ) {
        $this->context = $context;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
        $this->stripeSeller = $stripeSeller;
    }

    /**
     * Return the synonyms group Id.
     *
     * @return int|null
     */
    public function getId()
    {
        $contact = $this->registry->registry('contact');
        return $contact ? $contact->getId() : null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
