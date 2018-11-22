<?php

require_once __DIR__ . '/autoload.php';

$destinies = array(
    'conjurer',
    'summoner',
    'sorcerer',
    'hunter',
);

$origins = array(
    'healer',
    'surgeon',
    'ignorant',
    'architect',
);

$request = new \arenaApiWrapper\requests\CreateGameRequest();
$request->gameType = 'bga';
$request->players = array(
    array(
        'token' => 'FSSEe8DZnNRkUy2ntsln6bY7iNnmU9FGjayU5Ka552dcaNyv6HS38Ff9Im4WyO8w6uvjzInsR0PHS3YUPvh0kyWmxY3oehABYDA7',
        'destiny' => $destinies[rand(1, 3)],
        'origin' => $origins[rand(1, 3)],
    ),
    array(
        'token' => 'uVMFdNupqj8BrHWjf3v4Y6jonv5SOw4Skraso0DUXH4O9iBc907jp14hukBQ9ftS3bI3x6ccZmqLIyUwupfJqe89Zw3tziEUV3Ss',
        'destiny' => $destinies[rand(1, 3)],
        'origin' => $origins[rand(1, 3)],
    ),
);
$createdGame = \arenaApiWrapper\core\ArenaApiWrapper::createGame($request);
var_dump(is_array($createdGame) && !is_null($createdGame));

$arenaGameId = $createdGame['arena_game_id'];
$request = new \arenaApiWrapper\requests\GetGameRequest();
$request->arena_game_id = $arenaGameId;
$game = \arenaApiWrapper\core\ArenaApiWrapper::getGame($request);
var_dump(is_array($game) && !is_null($game));


$request = new \arenaApiWrapper\requests\GetGameActionsRequest();
$request->arena_game_id = $arenaGameId;
$actions = \arenaApiWrapper\core\ArenaApiWrapper::getGameActions($request);
var_dump(is_array($actions) && !is_null($actions) && count($actions) === 2);
