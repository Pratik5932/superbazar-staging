<?php

namespace AstraPrefixed\HttpSignatures;

use AstraPrefixed\Psr\Http\Message\RequestInterface;
class Signer
{
    /** @var Key */
    private $key;
    /** @var HmacAlgorithm */
    private $algorithm;
    /** @var HeaderList */
    private $headerList;
    /**
     * @param Key           $key
     * @param HmacAlgorithm $algorithm
     * @param HeaderList    $headerList
     */
    public function __construct($key, $algorithm, $headerList)
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
        $this->headerList = $headerList;
    }
    /**
     * @param RequestInterface $message
     *
     * @return RequestInterface
     */
    public function sign($message)
    {
        $signatureParameters = $this->signatureParameters($message);
        $message = $message->withAddedHeader('Signature', $signatureParameters->string());
        return $message;
    }
    /**
     * @param RequestInterface $message
     *
     * @return RequestInterface
     */
    public function authorize($message)
    {
        $signatureParameters = $this->signatureParameters($message);
        $message = $message->withAddedHeader('Authorization', 'Signature ' . $signatureParameters->string());
        return $message;
    }
    /**
     * @param RequestInterface $message
     *
     * @return RequestInterface
     */
    public function signWithDigest($message)
    {
        $bodyDigest = new BodyDigest();
        $this->headerList = $bodyDigest->putDigestInHeaderList($this->headerList);
        return $this->sign($bodyDigest->setDigestHeader($message));
    }
    /**
     * @param RequestInterface $message
     *
     * @return RequestInterface
     */
    public function authorizeWithDigest($message)
    {
        $bodyDigest = new BodyDigest();
        $this->headerList = $bodyDigest->putDigestInHeaderList($this->headerList);
        return $this->authorize($bodyDigest->setDigestHeader($message));
    }
    /**
     * @param RequestInterface $message
     *
     * @return SignatureParameters
     */
    private function signatureParameters($message)
    {
        return new SignatureParameters($this->key, $this->algorithm, $this->headerList, $this->signature($message));
    }
    /**
     * @param RequestInterface $message
     *
     * @return Signature
     */
    private function signature($message)
    {
        return new Signature($message, $this->key, $this->algorithm, $this->headerList);
    }
    public function getSigningString($message)
    {
        $singingString = new SigningString($this->headerList, $message);
        return $singingString->string();
    }
}