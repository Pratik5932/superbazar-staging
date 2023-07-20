<?php

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace AstraPrefixed\Cake\Database\Type;

use AstraPrefixed\Cake\Database\Driver;
use AstraPrefixed\Cake\Utility\Text;
/**
 * Provides behavior for the UUID type
 */
class UuidType extends StringType
{
    /**
     * Casts given value from a PHP type to one acceptable by database
     *
     * @param mixed $value value to be converted to database equivalent
     * @param \Cake\Database\Driver $driver object from which database preferences and configuration will be extracted
     * @return string|null
     */
    public function toDatabase($value, Driver $driver)
    {
        if ($value === null || $value === '' || $value === \false) {
            return null;
        }
        return parent::toDatabase($value, $driver);
    }
    /**
     * Generate a new UUID
     *
     * @return string A new primary key value.
     */
    public function newId()
    {
        return Text::uuid();
    }
    /**
     * Marshals request data into a PHP string
     *
     * @param mixed $value The value to convert.
     * @return string|null Converted value.
     */
    public function marshal($value)
    {
        if ($value === null || $value === '' || \is_array($value)) {
            return null;
        }
        return (string) $value;
    }
}
