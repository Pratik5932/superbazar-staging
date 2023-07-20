<?php

namespace AstraPrefixed;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use AstraPrefixed\GetAstra\Client\Helper\CommonHelper;
use AstraPrefixed\GetAstra\Client\Helper\IpBlockingHelper;
use AstraPrefixed\GetAstra\Client\Helper\UrlHelper;
use AstraPrefixed\GetAstra\Client\Helper\PluginInterface;
use AstraPrefixed\Psr\Container\ContainerInterface;
use AstraPrefixed\Psr\Http\Message\ResponseInterface;
use AstraPrefixed\Psr\Http\Message\ServerRequestInterface;
use AstraPrefixed\Psr\Log\LoggerInterface;
/**
 * Description of VirtualPatchPlugin.
 *
 * @author aditya
 */
class VirtualPatchPlugin implements PluginInterface
{
    private $container;
    private $urlHelper;
    private $ipBlockingHelper;
    private $commonHelper;
    protected $appliedPatches = [];
    protected $url;
    /**
     * @var LoggerInterface
     */
    private $logger;
    public function getMigrationDirPath() : string
    {
        return '';
    }
    public function getName() : string
    {
        return 'VirtualPatchPlugin';
    }
    public function getRoutes() : array
    {
        return '';
    }
    public function getVersion() : string
    {
        return 'v0.1';
    }
    public function isApiUser() : bool
    {
        return \true;
    }
    public function isRequestBlocker() : bool
    {
        return \true;
    }
    public function isMiddlewareBasedPlugin() : bool
    {
        return \true;
    }
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->urlHelper = new UrlHelper();
        $this->commonHelper = new CommonHelper();
        $this->ipBlockingHelper = new IpBlockingHelper($this->container);
        $this->logger = $this->container->get('logger');
    }
    /**
     * @param ServerRequestInterface $request  PSR7 request
     * @param ResponseInterface      $response PSR7 response
     * @param callable               $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        if (!$request->getAttribute('astraEnabled')) {
            $response = $next($request, $response);
            return $response;
        }
        if ($request->getAttribute('alreadyAllowed')) {
            $response = $next($request, $response);
            return $response;
        }
        $currentIp = $this->urlHelper->getClientIp();
        if (!$currentIp) {
            $this->logger->warning('Visitor IP detected as null in VirtualPatch plugin, exiting.');
            $response = $next($request, $response);
            return $response;
        }
        $this->applyPatch();
        if (\count($this->appliedPatches) > 0) {
            $blockRefId = $this->ipBlockingHelper->generateBlockReferenceId($this->getName());
            return $this->ipBlockingHelper->blockPageDataPrep($response, $currentIp, 'Virtual patch applied', $this->getName(), null, $blockRefId);
            //show block page
        }
        $response = $next($request, $response);
        return $response;
    }
    private function applyPatch()
    {
        $methods = \preg_grep('/^patch/', \get_class_methods($this));
        foreach ($methods as $method) {
            $isApplied = $this->{$method}();
            if (\true === $isApplied) {
                $this->appliedPatches[] = $method;
            }
        }
    }
    public function getAppliedPatches()
    {
        return $this->appliedPatches;
    }
    private function urlContains($slug = '')
    {
        return \false !== \strpos($this->url, $slug) ? \true : \false;
    }
    private function stringAfter($haystack, $needle)
    {
        return \substr($haystack, \strpos($haystack, $needle) + \strlen($needle));
    }
    private function patchMagentoSmartwaveQuickview()
    {
        if (!$this->urlContains('quickview/index/view/path')) {
            return \false;
        }
        $stringAfter = $this->stringAfter($this->url, 'quickview/index/view/path');
        if ($this->urlContains(';') || \strlen($stringAfter) > 3) {
            return \true;
        }
    }
}
/**
 * Description of VirtualPatchPlugin.
 *
 * @author aditya
 */
\class_alias('AstraPrefixed\\VirtualPatchPlugin', 'VirtualPatchPlugin', \false);
