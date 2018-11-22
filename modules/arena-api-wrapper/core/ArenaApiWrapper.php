<?php

namespace arenaApiWrapper\core;

use arenaApiWrapper\requests\CreateGameRequest;
use arenaApiWrapper\requests\GetGameActionsRequest;
use arenaApiWrapper\requests\GetGameRequest;
use arenaApiWrapper\requests\Request;

abstract class ArenaApiWrapper
{

    protected static $config = null;

    /**
     * @param Request $request
     * @return array|null
     */
    protected static function callAPI($request)
    {
        // Build the HTTP request
        $ch = curl_init(self::getBaseURL());
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request->toArray()));
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'X-API-Credentials: ' . self::getCredentials(),
                'Content-Type: application/json',
            )
        );

        // Send the request
        $return = curl_exec($ch);

        // Treat the returned data
        $returnJSON = json_decode($return, true);
        if (isset($returnJSON['status']))
        {
            if ($returnJSON['status'] === true)
            {
                return $returnJSON['data'];
            }

            throw new \Exception($returnJSON['error']);
        }

        return $returnJSON;
    }

    /**
     * Get the credentials in the config
     * @return mixed
     */
    protected static function getCredentials()
    {
        return self::getConfig('credentials');
    }

    /**
     * Get the base URL in the config
     * @return mixed
     */
    protected static function getBaseURL()
    {
        return self::getConfig('baseURL');
    }

    /**
     * Store the config to reduce I/O & return a config item.
     * @param $key
     * @return mixed
     */
    protected static function getConfig($key)
    {
        self::$config = is_null(self::$config) ? require(__DIR__ . '/../config.php') : self::$config;
        return self::$config[$key];
    }

    /**
     * @param CreateGameRequest $request
     * @throws \Exception
     * @return array|null
     */
    public static function createGame($request)
    {
        if (!$request instanceof CreateGameRequest)
        {
            throw new \Exception("Request should be a CreateGameRequest");
        }

        return self::callAPI($request);
    }

    /**
     * @param GetGameActionsRequest $request
     * @throws \Exception
     * @return array|null
     */
    public static function getGameActions($request)
    {
        if (!$request instanceof GetGameActionsRequest)
        {
            throw new \Exception("Request should be a GetGameActionsRequest");
        }

        return self::callAPI($request);
    }

    /**
     * @param GetGameRequest $request
     * @throws \Exception
     * @return array|null
     */
    public static function getGame($request)
    {
        if (!$request instanceof GetGameRequest)
        {
            throw new \Exception("Request should be a GetGameRequest");
        }

        return self::callAPI($request);
    }

    /**
     * @param RespondToGameActionRequest $request
     * @throws \Exception
     * @return array|null
     */
    public static function respondToGameAction($request)
    {
        if (!$request instanceof RespondToGameActionRequest)
        {
            throw new \Exception("Request should be a RespondToGameActionRequest");
        }

        return self::callAPI($request);
    }

}