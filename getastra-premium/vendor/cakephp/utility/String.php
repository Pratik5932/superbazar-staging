<?php

namespace AstraPrefixed;

// @deprecated 3.5.0 Backward compatibility with 2.x series
if (\PHP_VERSION_ID < 70000) {
    \class_alias('AstraPrefixed\\Cake\\Utility\\Text', 'AstraPrefixed\\Cake\\Utility\\String');
    deprecationWarning('Use Cake\\Utility\\Text instead of Cake\\Utility\\String.');
}
