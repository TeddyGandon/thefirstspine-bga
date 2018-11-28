<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * tfsbga implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * tfsbga.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */


require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');

// Add TFS API wrapper file
include(__DIR__ . '/modules/api-wrapper/autoload.php');

// Add TFS API wrapper file
include(__DIR__ . '/modules/arena-api-wrapper/autoload.php');


class tfsbga extends Table
{

    const ACTION_CATEGORY__UTILITY = 0;
    const ACTION_CATEGORY__USER_MOVE = 1;

    const STORAGE__ARENA_GAME = 'arenagame';
    const STORAGE__ACTIONS = 'actions';
    const STORAGE__CARDS = 'cards';
    const STORAGE__MESSAGES = 'messages';
    const STORAGE__MESSAGES_SENT = 'messages_sent';
    const STORAGE__JWT = 'jwt';
    const STORAGE__CODES = 'codes';

    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels(array(
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ));
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "tfsbga";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $arenaValidUsers = self::getArenaValidUserIds();
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, tfs_user_id) VALUES ";
        $values = array();
        foreach ($players as $player_id => $player)
        {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "','" . addslashes($arenaValidUsers[0]) . "')";
            array_splice($arenaValidUsers, 0, 1);
        }
        $sql .= implode($values, ',');
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        self::initStat('table', 'turns_played', 0);

        // Setup the initial game situation here
        $destinies = self::getArenaValidDestinies();
        $origins = self::getArenaValidOrigins();
        $authTokens = self::getArenaValidAuthTokens();

        $request = new \arenaApiWrapper\requests\CreateGameRequest();
        $request->gameType = 'bga';
        $request->players = array(
            array(
                'token' => $authTokens[0],
                'destiny' => $destinies[rand(1, 3)],
                'origin' => $origins[rand(1, 3)],
            ),
            array(
                'token' => $authTokens[1],
                'destiny' => $destinies[rand(1, 3)],
                'origin' => $origins[rand(1, 3)],
            ),
        );
        $game = \arenaApiWrapper\core\ArenaApiWrapper::createGame($request);

        // Save the ArenaGame instance data
        $this->storeObject(self::STORAGE__ARENA_GAME, $game);

        // Load basic data
        $this->reloadActions($game['arena_game_id']);
        $this->reloadCards($game['arena_game_id']);
        $this->reloadMessages($game['arena_game_id']);

        // Save blank JWTs
        $jwt = array();
        foreach ($players as $player_id => $player)
        {
            $jwt[$player_id] = null;
        }
        $this->storeObject('jwt', $jwt);

        // Activate the next player
        $this->activeNextPlayer();

        // Call the actions processor that will calculate all the potentials actions for the players
        $this->actionsProcessor();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        // Reload all the data from the server
        $game = $this->reloadGame();
        $actions = $this->reloadActions($game['arena_game_id']);
        $cards = $this->reloadCards($game['arena_game_id']);
        $messages = $this->reloadMessages($game['arena_game_id']);

        $result = array();

        $currentPlayerId = self::getCurrentPlayerId();
        $currentPlayer = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$currentPlayerId}");

        // Get information about the player
        $result['user_id'] = $currentPlayer['tfs_user_id'];

        // Gather all information about current game situation (visible by player $current_player_id).
        $result['game'] = $game;
        $result['actions'] = array();
        foreach ($actions as $action)
        {
            if ($action['user_id'] == $currentPlayer['tfs_user_id'])
            {
                $result['actions'][] = $action;
            }
        }
        $result['hand'] = array();
        foreach ($cards as $card)
        {
            if ($card['user_id'] == $currentPlayer['tfs_user_id'] && $card['location'] == 'hand')
            {
                $result['hand'][] = $card;
            }
        }

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $totLife = 20;
        $cards = $this->retrieveStoredObject(self::STORAGE__CARDS);
        foreach ($cards as $card)
        {
            if (
                isset($card['card']['type']) &&
                isset($card['options']['life']) &&
                $card['card']['type'] === 'player'
            )
            {
                $totLife += $card['options']['life'];
            }
        }

        return 100 - ($totLife * (100 / 20));
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    public static function getArenaValidUserIds()
    {
        return array(
            827,
            828
        );
    }

    public static function getArenaValidAuthTokens()
    {
        return array(
            827 => 'FSSEe8DZnNRkUy2ntsln6bY7iNnmU9FGjayU5Ka552dcaNyv6HS38Ff9Im4WyO8w6uvjzInsR0PHS3YUPvh0kyWmxY3oehABYDA7',
            828 =>  'uVMFdNupqj8BrHWjf3v4Y6jonv5SOw4Skraso0DUXH4O9iBc907jp14hukBQ9ftS3bI3x6ccZmqLIyUwupfJqe89Zw3tziEUV3Ss',
        );
    }

    public static function getArenaValidDestinies()
    {
        return array(
            'conjurer',
            'summoner',
            'sorcerer',
            'hunter',
        );
    }

    public static function getArenaValidOrigins()
    {
        return array(
            'healer',
            'surgeon',
            'ignorant',
            'architect',
        );
    }

    protected function storeObject($name, $object)
    {
        // Sanatize strings to prevent mysql injections
        $json = json_encode($object);
        $jsonEscaped = addslashes($json);
        $nameEscaped = addslashes($name);

        // Execute mysql query
        $sql = "INSERT INTO storage (`name`, `value`) VALUES ('{$nameEscaped}','{$jsonEscaped}') ON DUPLICATE KEY UPDATE `value` = '{$jsonEscaped}'";
        self::DbQuery($sql);

        // Return object (cloned by json decoding)
        return json_decode($json, true);
    }

    protected function retrieveStoredObject($name)
    {
        // Sanatize strings to prevent mysql injections
        $nameEscaped = addslashes($name);

        // Get object from DB
        $obj = self::getObjectFromDB("SELECT `value` FROM storage WHERE `name` = '{$nameEscaped}'");
        $json = $obj['value'];

        // Decode & return
        return json_decode($json, true);
    }

    protected function beforeAction($category)
    {
    }

    protected function afterAction($category)
    {
        if ($category === self::ACTION_CATEGORY__USER_MOVE)
        {
            // Reload the changed data after the move
            $game = $this->reloadGame();
            $this->reloadActions($game['arena_game_id']);
            $cards = $this->reloadCards($game['arena_game_id']);
            $messages = $this->reloadMessages($game['arena_game_id']);

            // Update BGA-powered stats
            foreach ($cards as $card)
            {
                if ($card['card']['type'] === 'player')
                {
                    $userId = $card['user_id'];
                    $score = isset($card['options']['life']) ?
                        10 + $card['options']['life'] :
                        10;
                    $scoreAux = 0;
                    foreach ($cards as $card2)
                    {
                        if (
                            $card2['user_id'] === $userId &&
                            ($card2['location'] === 'hand' || $card2['location'] === 'deck')
                        )
                        {
                            $scoreAux ++;
                        }
                    }
                    self::DbQuery("UPDATE player SET player_score = {$score}, player_score_aux = {$scoreAux} WHERE tfs_user_id = {$userId}");
                }
            }

            // Change the player if needed & send notifications to the right player
            $this->actionsProcessor();

            // Notify everyone that the game was updated
            $this->notifyAllPlayers(
                'gameUpdated',
                '',
                $game
            );

            // Notify everyone that their hand was changed
            $players = self::getCollectionFromDB('SELECT * FROM player');
            foreach ($players as $playerId => $player)
            {
                $hand = array();
                foreach ($cards as $card)
                {
                    if ($card['user_id'] == $player['tfs_user_id'] && $card['location'] == 'hand')
                    {
                        $hand[] = $card;
                    }
                }
                $this->notifyPlayer(
                    $player['player_id'],
                    'handUpdated',
                    '',
                    $hand
                );
            }

            // Check for the game end
            $state = $this->gamestate->state();
            if ($game['is_opened'] !== 1 && $state['name'] !== 'gameEnd')
            {
                // Go endgame state
                $this->gamestate->nextState('gameEnd');
            }
        }
    }

    protected function reloadGame()
    {
        $oldGame = $this->retrieveStoredObject(self::STORAGE__ARENA_GAME);
        $arenaGameId = $oldGame['arena_game_id'];

        $request = new \arenaApiWrapper\requests\GetGameRequest();
        $request->arena_game_id = $arenaGameId;
        $game = \arenaApiWrapper\core\ArenaApiWrapper::getGame($request);

        return $this->storeObject(self::STORAGE__ARENA_GAME, $game);
    }

    protected function reloadActions($arenaGameId)
    {
        // Get the possible actions from the ArenaGame instance
        $request = new \arenaApiWrapper\requests\GetGameActionsRequest();
        $request->arena_game_id = $arenaGameId;
        $actions = \arenaApiWrapper\core\ArenaApiWrapper::getGameActions($request);

        // Sort actions
        if (!function_exists('usortCmp'))
        {
            function usortCmp($a, $b)
            {
                if ($a['priority'] == $b['priority']) {
                    return 0;
                }
                return ($a['priority'] > $b['priority']) ? -1 : 1;
            }
        }
        usort(
            $actions,
            'usortCmp'
        );

        // Filter actions with the maximum priority
        $highestPriority = $actions[0]['priority'];
        $filteredActions = array();
        foreach ($actions as $action)
        {
            if ($action['priority'] === $highestPriority)
            {
                $filteredActions[] = $action;
            }
        }

        // Save the actions data
        $actionsAttributes = array();
        foreach ($filteredActions as $filteredAction)
        {
            $actionsAttributes[] = $filteredAction;
        }
        return $this->storeObject(self::STORAGE__ACTIONS, $actionsAttributes);
    }

    protected function reloadCards($arenaGameId)
    {
        // Get the cards of the ArenaGame instance
        $request = new \arenaApiWrapper\requests\GetCardsRequest();
        $request->arena_game_id = $arenaGameId;
        $cards = \arenaApiWrapper\core\ArenaApiWrapper::getCards($request);

        return $this->storeObject(self::STORAGE__CARDS, $cards);
    }

    protected function reloadMessages($arenaGameId)
    {
        // Get the send logs
        $messagesSent = $this->retrieveStoredObject(self::STORAGE__MESSAGES_SENT);
        $messagesSent = is_null($messagesSent) ? array() : $messagesSent;

        // Get the messages of the ArenaGame instance
        $request = new \arenaApiWrapper\requests\GetMessagesRequest();
        $request->arena_game_id = $arenaGameId;
        $messages = \arenaApiWrapper\core\ArenaApiWrapper::getMessages($request);

        // Send & save the messages data
        foreach ($messages as $message)
        {
            // Send the message to the game logs
            if (!in_array((int) $message['arena_message_id'], $messagesSent))
            {
                $player = $this->getObjectFromDB("SELECT * FROM player WHERE tfs_user_id = {$message['user_id']}");
                $playerName = $player['player_name'];
                $messageStr = str_replace('*', '${player_name}', $message['message']);
                $this->notifyAllPlayers(
                    'noType',
                    totranslate($messageStr),
                    array('player_name' => $playerName)
                );
                $messagesSent[] = (int) $message['arena_message_id'];
            }
        }

        // Save the sent messages
        $this->storeObject(self::STORAGE__MESSAGES_SENT, $messagesSent);

        return $this->storeObject(self::STORAGE__MESSAGES, $messages);
    }

    protected function actionsProcessor()
    {
        // Get the current actions
        $actions = $this->retrieveStoredObject(self::STORAGE__ACTIONS);

        // All the actions should be the same for a same game ID in the API
        $userId = $actions[0]['user_id'];
        $playerToPlay = $this->getObjectFromDB("SELECT * FROM player WHERE tfs_user_id = {$userId}");

        // Select the right player according to the actions
        $this->gamestate->setAllPlayersMultiactive();
        $players = self::getCollectionFromDB('SELECT * FROM player');
        foreach ($players as $playerId => $player)
        {
            if ($player['player_id'] != $playerToPlay['player_id'])
            {
                $this->gamestate->setPlayerNonMultiactive($player['player_id'], 'deactivatePlayer');
            }
        }

        // Give some time to the player
        $this->giveExtraTime(self::getActivePlayerId());

        // Notify the player that he can play some move =)
        $this->notifyPlayer(
            $playerToPlay['player_id'],
            'actionRequired',
            '',
            $actions
        );
    }



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in tfsbga.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    public function setJWTAction($jwt)
    {
        $this->beforeAction(self::ACTION_CATEGORY__UTILITY);

        // Retrieve the JWT data
        $jwtExploded = explode('.', $jwt);
        $userJwt = base64_decode($jwtExploded[0]);
        $userJwtObject = json_decode($userJwt, true);

        // Save it
        $storedJwt = $this->retrieveStoredObject('jwt');
        $storedJwt[$this->getCurrentPlayerId()] = $userJwtObject;
        $this->storeObject(self::STORAGE__JWT, $storedJwt);

        $this->afterAction(self::ACTION_CATEGORY__UTILITY);
    }

    public function responseToAction($arenaGameActionId, $response)
    {
        $this->beforeAction(self::ACTION_CATEGORY__USER_MOVE);

        // Getting if we are in a replay sequence or not
        $isReplay = (
            preg_match('/replayLastTurn/', $_SERVER['HTTP_REFERER']) ||
            preg_match('/replayFrom/', $_SERVER['HTTP_REFERER'])
        );

        // Get the action to respond
        $request = new \arenaApiWrapper\requests\GetGameActionRequest();
        $request->arena_game_action_id = $arenaGameActionId;
        $action = \arenaApiWrapper\core\ArenaApiWrapper::getGameAction($request);

        // Only respond to the server out of a replay
        if (!$isReplay)
        {
            $action['response'] = $response;
        }

        if ($action['reference'] === 'EndTurn')
        {
            // Increase turn stats
            self::incStat(1, 'turns_played');
        }

        $this->afterAction(self::ACTION_CATEGORY__USER_MOVE);

        return true;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    function zombieTurn($state, $active_player)
    {
        // Get the game REST entity
        $oldGame = $this->retrieveStoredObject(self::STORAGE__ARENA_GAME);
        $arenaGameId = $oldGame['arena_game_id'];

        $request = new \arenaApiWrapper\requests\GetGameRequest();
        $request->arena_game_id = $arenaGameId;
        $game = \arenaApiWrapper\core\ArenaApiWrapper::getGame($request);

        // Get the player
        $currentPlayer = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$active_player}");
        $tfsUserId = $currentPlayer['tfs_user_id'];

        // Set the player to a zombie in TFS game instance
        //TODO: Add method to zombify the players
        /*
        $game->zombies = "[{$tfsUserId}]";
        $game->save();
        */

        // Update game local
        $this->reloadGame();
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */

    function upgradeTableDb($from_version)
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            $sql = "ALTER TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            $sql = "CREATE TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }
}
