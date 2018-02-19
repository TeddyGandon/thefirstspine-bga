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
 * tfsbga.action.php
 *
 * tfsbga main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/tfsbga/tfsbga/myAction.html", ...)
 *
 */
class action_tfsbga extends APP_GameAction
{
    // Constructor: please do not modify
    public function __default()
    {
        if (self::isArg('notifwindow'))
        {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
        }
        else
        {
            $this->view = "tfsbga_tfsbga";
            self::trace("Complete reinitialization of board game");
        }
    }

    // TODO: defines your action entry points there


    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

    public function respondToAction()
    {
        $arena_game_action_id = self::getArg('arena_game_action_id', AT_posint, true);
        $base64_response = $_GET['base64_response'];
        $this->game->responseToAction($arena_game_action_id, base64_decode($base64_response));
        self::ajaxResponse();
    }

    public function setJWT()
    {
        $arg = self::getArg('jwt', AT_alphanum, true);
        $this->game->setJWTAction($arg);
        self::ajaxResponse();
    }

}
  

