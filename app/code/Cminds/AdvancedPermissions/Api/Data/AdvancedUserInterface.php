<?php

namespace Cminds\AdvancedPermissions\Api\Data;

use Magento\User\Api\Data\UserInterface;

interface AdvancedUserInterface extends UserInterface
{
    /**
     * Get PostCode.
     *
     * @return int
     */
    public function getPostCode();

    /**
     * Set PostCode.
     *
     * @param string $postCode
     * @return $this
     */
    public function setPostCode($postCode);

    /**
     * Get PostCodes.
     *
     * @return int
     */
    public function getPostCodes();

    /**
     * Set PostCodes.
     *
     * @param string $postCodes
     * @return $this
     */
    public function setPostCodes($postCodes);
}
