<?php

namespace arenaApiWrapper\requests;

/**
 * Class GetGameActionsRequest
 * @property int arena_game_id
 * @package arenaApiWrapper\requests
 */
class GetGameActionsRequest extends Request
{

    /**
     * @inheritdoc
     */
    protected static function getMethod()
    {
        return 'getGameActions';
    }

    /**
     * @inheritdoc
     */
    protected static function getAttributes()
    {
        return array(
            'arena_game_id',
        );
    }

}