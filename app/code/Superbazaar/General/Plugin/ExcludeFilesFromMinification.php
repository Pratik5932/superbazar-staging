<?php

namespace Superbazaar\General\Plugin;
use Magento\Framework\View\Asset\Minification;

class ExcludeFilesFromMinification
{
    public function aroundGetExcludes(Minification $subject, callable $proceed, $contentType)
    {
        $result = $proceed($contentType);
        if ($contentType != 'js') {
            return $result;
        }
        $result[] = 'your file path'; // for e.g https://www.google.com/recaptcha/api.js'
        return $result;
    }
}