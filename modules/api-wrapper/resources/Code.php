<?php

namespace thefirstspine\apiwrapper\resources;

use thefirstspine\apiwrapper\core\Resource;

/**
 * Class ArenaCard
 * @package thefirstspine\apiwrapper\resources
 * @property string loots
 * @property string code
 */
class Code extends Resource
{

    protected $restResource = 'codes';
    protected $restIdField = 'code_loot_id';

}
