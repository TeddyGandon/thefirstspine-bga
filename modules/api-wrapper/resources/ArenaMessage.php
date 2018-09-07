<?php

namespace thefirstspine\apiwrapper\resources;

use thefirstspine\apiwrapper\core\Resource;

/**
 * Class ArenaMessage
 * @package thefirstspine\apiwrapper\resources
 * @property int arena_message_id
 * @property int arena_game_id
 * @property int user_id
 * @property string message
 * @property int is_log
 * @property string created_at
 * @property array user
 */
class ArenaMessage extends Resource
{

    protected $restResource = 'arena-messages';
    protected $restIdField = 'arena_message_id';

}
