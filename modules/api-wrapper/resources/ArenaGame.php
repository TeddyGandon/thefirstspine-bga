<?php

namespace thefirstspine\apiwrapper\resources;

use thefirstspine\apiwrapper\core\Resource;

/**
 * Class ArenaGame
 * @package thefirstspine\apiwrapper\resources
 * @property int arena_game_id
 * @property int user_id_1
 * @property int destiny_deck_id_1
 * @property int origin_deck_id_1
 * @property int user_id_2
 * @property int destiny_deck_id_2
 * @property int origin_deck_id_2
 * @property int user_id_3
 * @property int destiny_deck_id_3
 * @property int origin_deck_id_3
 * @property int user_id_4
 * @property int destiny_deck_id_4
 * @property int origin_deck_id_4
 * @property int is_opened
 * @property string game_type
 * @property array options
 * @property array winners
 * @property string created_at
 * @property string updated_at
 */
class ArenaGame extends Resource
{

    protected $restResource = 'arena-games';
    protected $restIdField = 'arena_game_id';

}
