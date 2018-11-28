<?php

namespace arenaApiWrapper\requests;

/**
 * Class GetMessagesRequest
 * @property int arena_game_id
 * @package arenaApiWrapper\requests
 */
class GetMessagesRequest extends Request
{

    /**
     * @inheritdoc
     */
    protected static function getMethod()
    {
        return 'getMessages';
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