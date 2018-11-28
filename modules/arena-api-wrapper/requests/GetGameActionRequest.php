<?php

namespace arenaApiWrapper\requests;

/**
 * Class GetGameActionRequest
 * @property int arena_game_action_id
 * @package arenaApiWrapper\requests
 */
class GetGameActionRequest extends Request
{

    /**
     * @inheritdoc
     */
    protected static function getMethod()
    {
        return 'getGameAction';
    }

    /**
     * @inheritdoc
     */
    protected static function getAttributes()
    {
        return array(
            'arena_game_action_id',
        );
    }

}