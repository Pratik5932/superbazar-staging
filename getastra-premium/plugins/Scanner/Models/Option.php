<?php

/**
 * This file is part of the Astra Security Suite.
 *
 *  Copyright (c) 2019 (https://www.getastra.com/)
 *
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
namespace AstraPrefixed\GetAstra\Plugins\Scanner\Models;

use AstraPrefixed\Illuminate\Database\Eloquent\Model;
class Option extends Model
{
    public $timestamps = \false;
    protected $fillable = ['name', 'val', 'group', 'autoload'];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = ['name' => 'string', 'val' => 'array'];
    protected $primaryKey = 'name';
    public function getOption($name)
    {
        return Option::find($name)->toArray();
        //return Option::find($name)->toArray();
    }
}
