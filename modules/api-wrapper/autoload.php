<?php

$psr4Classes = array(
    '/core/Model',
    '/core/QueryBuilder',
    '/core/Resource',
    '/resources/ArenaGame',
    '/resources/ArenaCard',
    '/resources/ArenaGameAction',
    '/resources/ArenaMessage',
    '/resources/Code',
);

foreach ($psr4Classes as $psr4Class)
{
    include __DIR__ . $psr4Class . '.php';
}
