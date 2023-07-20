<?php

declare (strict_types=1);
namespace AstraPrefixed\Http\Client\Common\Exception;

use AstraPrefixed\Http\Client\Exception\TransferException;
/**
 * Thrown when a http client cannot be chosen in a pool.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class HttpClientNotFoundException extends TransferException
{
}
