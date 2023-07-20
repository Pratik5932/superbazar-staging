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

namespace Webkul\MobikulCore\Api\Data;

/**
 * Interface WalkthroughInterface
 */
interface WalkthroughInterface
{
    const ID = "id";
    const TITLE = "title";
    const DESCRIPTION = "description";
    const COLOR_CODE = "color_code";
    const CREATED_AT = "created_at";
    const UPDATED_AT = "updated_at";
    const IMAGE = "image";
    const SORT_ORDER = "sort_order";
    const STATUS = "status";

    /**
     * Function getId
     *
     * @return integer
     */
    public function getId();

    /**
     * Function setId
     *
     * @param integer $id id
     */
    public function setId($id);

    /**
     * Function getTitle
     *
     * @return string
     */
    public function getTitle();

    /**
     * Function setTitle
     *
     * @param string $title content
     */
    public function setTitle($title);

    /**
     * Function getDescription
     *
     * @return string
     */
    public function getDescription();

    /**
     * Function setDescription
     *
     * @param string $description content
     */
    public function setDescription($description);

    /**
     * Function getColorCode
     *
     * @return string
     */
    public function getColorCode();

    /**
     * Function setColorCode
     *
     * @param string $colorCode colorCode
     */
    public function setColorCode($colorCode);

    /**
     * Function getImage
     *
     * @return string
     */
    public function getImage();

    /**
     * Function setImage
     *
     * @param string $image image
     */
    public function setImage($image);

    /**
     * Function getStatus
     *
     * @return integer
     */
    public function getStatus();

    /**
     * Function setStatus
     *
     * @param integer $status status
     */
    public function setStatus($status);

    /**
     * Function getSortOrder
     *
     * @return integer
     */
    public function getSortOrder();

    /**
     * Function setSortOrder
     *
     * @param integer $sortOrder sortOrder
     */
    public function setSortOrder($sortOrder);
}
