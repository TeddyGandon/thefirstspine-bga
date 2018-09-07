<?php

namespace thefirstspine\apiwrapper\resources;

use thefirstspine\apiwrapper\core\Resource;

/**
 * Class ArenaCard
 * @package thefirstspine\apiwrapper\resources
 * @property int arena_game_id
 * @property int user_id
 * @property array card
 * @property array options
 * @property string square_type
 * @property string location
 * @property string updated_at
 * @property string created_at
 */
class ArenaCard extends Resource
{

    protected $restResource = 'arena-cards';
    protected $restIdField = 'arena_card_id';

}
