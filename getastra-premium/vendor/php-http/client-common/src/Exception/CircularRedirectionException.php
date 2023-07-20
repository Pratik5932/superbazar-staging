<?php

declare (strict_types=1);
namespace AstraPrefixed\Http\Client\Common\Exception;

use AstraPrefixed\Http\Client\Exception\HttpException;
/**
 * Thrown when circular redirection is detected.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class CircularRedirectionException extends HttpException
{
}
