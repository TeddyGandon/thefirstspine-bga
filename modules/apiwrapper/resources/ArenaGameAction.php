<?php

namespace thefirstspine\apiwrapper\resources;

use thefirstspine\apiwrapper\core\Resource;

/**
 * Class ArenaGameAction
 * @package thefirstspine\apiwrapper\resources
 * @property int arena_game_action_id
 * @property int arena_game_id
 * @property int user_id
 * @property int is_active
 * @property string title
 * @property string reference
 * @property int priority
 * @property array script
 * @property array response
 * @property string created_at
 * @property string updated_at
 */
class ArenaGameAction extends Resource
{

    protected $restResource = 'arena-game-actions';
    protected $restIdField = 'arena_game_action_id';

}