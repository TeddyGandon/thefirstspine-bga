<?php

namespace arenaApiWrapper\requests;

/**
 * Class RespondToGameActionRequest
 * @property int arena_game_id
 * @property int arena_game_action_id
 * @property array response
 * @package arenaApiWrapper\requests
 */
class RespondToGameActionRequest extends Request
{

    /**
     * @inheritdoc
     */
    protected static function getMethod()
    {
        return 'respondToGameAction';
    }

    /**
     * @inheritdoc
     */
    protected static function getAttributes()
    {
        return [
            'arena_game_id',
            'arena_game_action_id',
            'response',
        ];
    }

}