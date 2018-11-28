<?php

namespace arenaApiWrapper\requests;

/**
 * Class CreateGameRequest
 * @property string gameType
 * @property array players
 * @package arenaApiWrapper\requests
 */
class CreateGameRequest extends Request
{

    /**
     * @inheritdoc
     */
    protected static function getMethod()
    {
        return 'createGame';
    }

    /**
     * @inheritdoc
     */
    protected static function getAttributes()
    {
        return array(
            'gameType',
            'players',
        );
    }

}