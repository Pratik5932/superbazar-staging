<?php

namespace AstraPrefixed\Http\Client;

use AstraPrefixed\Psr\Http\Client\ClientExceptionInterface as PsrClientException;
/**
 * Every HTTP Client related Exception must implement this interface.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface Exception extends PsrClientException
{
}
