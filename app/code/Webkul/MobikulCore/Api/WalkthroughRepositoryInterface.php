<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Webkul\MobikulCore\Api\Data\WalkthroughInterface;

/**
 * Interface WalkthroughRepositoryInterface
 */
interface WalkthroughRepositoryInterface
{
    /**
     * Function getById
     *
     * @param integer $walkThroughId walkThroughId
     */
    public function getById($walkThroughId);

    /**
     * Function deleteById
     *
     * @param integer $walkThroughId walkThroughId
     */
    public function deleteById($walkThroughId);

    /**
     * Function save
     *
     * @param WalkthroughInterface $walkThrough walkThrough
     */
    public function save(WalkthroughInterface $walkThrough);

    /**
     * Function delete
     *
     * @param WalkthroughInterface $walkThrough walkThrough
     */
    public function delete(WalkthroughInterface $walkThrough);

    /**
     * Function getList
     *
     * @param SearchCriteriaInterface $searchCriteria searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
