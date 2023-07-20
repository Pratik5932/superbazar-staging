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



namespace Mirasvit\Rewards\Api\Data\Earning;

/**
 * Interface for earning rule search results.
 */
interface RuleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get earning rules list.
     *
     * @return \Mirasvit\Rewards\Api\Data\Earning\RuleInterface[]
     */
    public function getItems();

    /**
     * Set earning rules list.
     *
     * @param \Mirasvit\Rewards\Api\Data\Earning\RuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
