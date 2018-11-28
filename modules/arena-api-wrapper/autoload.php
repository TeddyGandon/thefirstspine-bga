<?php

$psr4Classes = array(
    '/core/ArenaApiWrapper',
    '/requests/Request',
    '/requests/CreateGameRequest',
    '/requests/GetGameRequest',
    '/requests/GetGameActionsRequest',
    '/requests/RespondToGameActionRequest',
    '/requests/GetCardsRequest',
    '/requests/GetMessagesRequest',
    '/requests/GetGameActionRequest',
);

foreach ($psr4Classes as $psr4Class)
{
    include __DIR__ . $psr4Class . '.php';
}
