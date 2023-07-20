<?php

namespace AstraPrefixed\Http\Client\Exception;

use AstraPrefixed\Psr\Http\Message\RequestInterface;
trait RequestAwareTrait
{
    /**
     * @var RequestInterface
     */
    private $request;
    private function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }
    /**
     * {@inheritdoc}
     */
    public function getRequest() : RequestInterface
    {
        return $this->request;
    }
}
