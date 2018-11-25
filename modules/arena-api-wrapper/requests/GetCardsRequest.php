<?php

namespace arenaApiWrapper\requests;

/**
 * Class GetGameRequest
 * @property int arena_game_id
 * @package arenaApiWrapper\requests
 */
class GetCardsRequest extends Request
{

    /**
     * @inheritdoc
     */
    protected static function getMethod()
    {
        return 'getCards';
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