<?php

namespace AstraPrefixed;

// @deprecated 3.4.0 Load new class and alias
\class_exists('AstraPrefixed\\Cake\\Database\\Schema\\TableSchema');
deprecationWarning('Use Cake\\Database\\Schema\\TableSchema instead of Cake\\Database\\Schema\\Table.');
