<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AstraPrefixed\GetAstra\Plugins\Scanner\Helpers;

use AstraPrefixed\Illuminate\Database\Capsule\Manager as DB;
class DBHelper
{
    public static function vacuumDB()
    {
        if (\defined('ASTRA_DB_DRIVER') && ASTRA_DB_DRIVER == 'sqlite') {
            DB::select(DB::raw('VACUUM;'));
        }
    }
}
