/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * tfsbga implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * tfsbga.js
 *
 * tfsbga user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
        "dojo", "dojo/_base/declare",
        "ebg/core/gamegui",
        "ebg/counter"
    ],
    function (dojo, declare) {
        return declare("bgagame.tfsbga", ebg.core.gamegui, {

            currentUserId: null,
            currentGame: null,
            currentHand: null,
            actions: null,
            currentAction: null,
            currentActionResponse: {},
            currentActionScriptIndex: 0,

            constructor: function () {
                window['$scope'] = this;

                // Here, you can init the global variables of your user interface
                // Example:
                // this.myGlobalValue = 0;

            },

            /*
             setup:

             This method must set up the game user interface according to current game situation specified
             in parameters.

             The method is called each time the game interface is displayed to a player, ie:
             _ when the game starts
             _ when a player refreshes the game page (F5)

             "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
             */

            setup: function (gamedata) {
                // Build the squares of the board
                for (var y = 0; y < 8; y ++) {
                    for (var x = 0; x < 8; x ++) {
                        dojo.place(
                            this.format_block(
                                'jstpl_square',
                                {
                                    x: x,
                                    y: y
                                }
                            ),
                            'board'
                        ).addEventListener('click', this.onChoseSquare);
                    }
                }

                // Set up your game interface here, according to "gamedatas"
                const self = this;
                setTimeout(
                    function () {
                        self.setGame(gamedata.game);
                        self.setHand(gamedata.hand);
                        self.setActions(gamedata.actions);
                    },
                    1000
                );

                // Rotate the board according to the user position
                this.currentUserId = gamedata.user_id;
                if (gamedata.game.users[0].user_id == gamedata.user_id) {
                    $('board').style['transform'] = 'rotate(180deg)';
                }

                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                // Get the user ID according with the TFS SSO
                this.setupSSO();

                // Setup events listeners
                $('zoom').addEventListener('click', function(e) {
                    $('zoom').classList.remove('displayed');
                });
            },


            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                switch (stateName) {

                    /* Example:

                     case 'myGameState':

                     // Show some HTML block at this game state
                     dojo.style( 'my_html_block_id', 'display', 'block' );

                     break;
                     */


                    case 'dummmy':
                        break;
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                switch (stateName) {

                    /* Example:

                     case 'myGameState':

                     // Hide the HTML block we are displaying only during this game state
                     dojo.style( 'my_html_block_id', 'display', 'none' );

                     break;
                     */


                    case 'dummmy':
                        break;
                }
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //
            onUpdateActionButtons: function (stateName, args) {
                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        /*
                         Example:

                         case 'myGameState':

                         // Add 3 action buttons in the action status bar:

                         this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' );
                         this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' );
                         this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' );
                         break;
                         */
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*

             Here, you can defines some utility methods that you can use everywhere in your javascript
             script.

             */

            zoom: function (btn) {
                const image = btn.parentNode.getAttribute('data-spritesheet-id');
                const name = btn.parentNode.getAttribute('data-name');
                const description = base64Decode(btn.parentNode.getAttribute('data-descriptionEscaped'));

                $('zoom').classList.add('displayed');
                $('zoom-image').className = 'card-spritesheet card-spritesheet-'+image;
                $('zoom-name').textContent = _(name);
                $('zoom-description').innerHTML = _(description);
            },

            setupSSO: function () {
                const url = 'https://www.thefirstspine.fr/api/sso?key=bga-studio';
                const request = new XMLHttpRequest();
                const self = this;
                request.withCredentials = true;
                request.open('GET', url, true);
                request.send();
                request.onreadystatechange = function () {
                    if (request.readyState == 4) {
                        if (request.status == 200) {
                            const jwt = request.response.split('.');
                            self.ajaxcall("/tfsbga/tfsbga/setJWT.html", {
                                    jwt: jwt[1]
                                },
                                self,
                                function (result) {
                                    // What to do after the server call if it succeeded
                                    // (most of the time: nothing)
                                },
                                function (is_error) {
                                    // What to do after the server call in anyway (success or failure)
                                    // (most of the time: nothing)
                                }
                            );
                        }
                    }
                }
            },

            setGame: function (game) {
                if (!game || !game.board) return;
                this.currentGame = game;

                // Reset the cells
                for (var x = 0; x < 7; x++) {
                    for (var y = 0; y < 7; y++) {
                        $('cell_' + x + '-' + y).innerHTML = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=" />';
                    }
                }

                // Add the board
                for (var i = 0; i < game.board.length; i++) {
                    // Calculated elements
                    if (!game.board[i].view) {
                        game.board[i].view = {};
                        if (game.board[i].card) {
                            game.board[i].view.spritesheetId = game.board[i].card.image.replace('images/cards70', '').replace('.png', '');
                        } else if (game.board[i].square_type) {
                            game.board[i].view.spritesheetId = game.board[i].square_type;
                            game.board[i].card = {
                                name: '',
                                type: game.board[i].square_type,
                                description: ''
                            };
                        } else {
                            game.board[i].card = {
                                name: '',
                                type: '',
                                description: ''
                            };
                            game.board[i].view.spritesheetId = '';
                        }
                    }
                    if (game.board[i].user_id == game.users[0].user_id) {
                        game.board[i].view.rotation = 180;
                    }
                    if (game.board[i].user_id == game.users[2].user_id) {
                        game.board[i].view.rotation = 0;
                    }
                    if (game.board[i].user_id == this.currentUserId) {
                        game.board[i].view.color = 'black';
                    } else {
                        game.board[i].view.color = 'white';
                    }
                    game.board[i].view.descriptionEscaped = base64Encode(game.board[i].card.description);
                    // Place template
                    dojo.place(
                        this.format_block(
                            'jstpl_card',
                            game.board[i]
                        ),
                        'cell_' + game.board[i].position
                    );
                    if (game.board[i].options !== null) {
                        // Add life marker
                        if (game.board[i].options.life && game.board[i].options.life != 0) {
                            dojo.place(
                                this.format_block(
                                    'jstpl_marker',
                                    {
                                        type: 'life',
                                        value: game.board[i].options.life
                                    }
                                ),
                                'card-'+game.board[i].arena_card_id
                            );
                        }
                        // Add strenght marker
                        if (game.board[i].options.str && game.board[i].options.str != 0) {
                            dojo.place(
                                this.format_block(
                                    'jstpl_marker',
                                    {
                                        type: 'str',
                                        value: game.board[i].options.str
                                    }
                                ),
                                'card-'+game.board[i].arena_card_id
                            );
                        }
                        // Add capacities markers
                        if (game.board[i].options.capacities) {
                            for (var j = 0; j < game.board[i].options.capacities.length; j ++) {
                                dojo.place(
                                    this.format_block(
                                        'jstpl_marker',
                                        {
                                            type: 'capacity-' + game.board[i].options.capacities[j],
                                            value: ''
                                        }
                                    ),
                                    'card-'+game.board[i].arena_card_id
                                );
                            }
                        }
                    }
                }
            },

            setHand: function (hand) {
                if (!hand) return;

                this.currentHand = hand;

                // Reset the hand
                $('hand').innerHTML = '';

                // Add the cards to the hand
                for (var i = 0; i < hand.length; i++) {
                    // Calculatd elements
                    hand[i].view = {
                        rotation: 0,
                        color: 'black',
                        spritesheetId: hand[i].card.image.replace('images/cards70', '').replace('.png', ''),
                        descriptionEscaped: base64Encode(hand[i].card.description)
                    };
                    // Place template
                    var card = dojo.place(
                        this.format_block(
                            'jstpl_card',
                            hand[i]
                        ),
                        'hand'
                    );
                    card.addEventListener('click', this.onChoseCard);
                }
            },

            setActions: function (actions) {
                if (!actions || !this.currentGame || this.currentGame.is_opened == 0) return;
                this.actions = actions;

                if (this.actions.length > 0) {
                    $('generalactions').innerHTML = '<br />';
                }

                const self = this;
                for (var i = 0; i < this.actions.length; i++) {
                    this.addActionButton(
                        'action_' + this.actions[i].arena_game_action_id,
                        _(this.actions[i].title),
                        function (event) {
                            const id = event.target.id;
                            self.choseAction(id.replace('action_', ''));
                        },
                        'generalactions'
                    );
                }

                if (this.actions.length === 1) {
                    this.choseAction(this.actions[0].arena_game_action_id);
                }

                if (this.actions.length === 2) {
                    if (this.actions[0].reference === 'SkipSpell') {
                        this.choseAction(this.actions[1].arena_game_action_id);
                    } else if (this.actions[1].reference === 'SkipSpell') {
                        this.choseAction(this.actions[0].arena_game_action_id);
                    }
                }
            },

            choseAction: function (actionId) {
                $('generalactions').innerHTML = '';

                for (var i = 0; i < this.actions.length; i++) {
                    if (this.actions[i].arena_game_action_id == actionId) {
                        this.currentAction = this.actions[i];
                        this.currentActionScriptIndex = -1;
                        this.currentActionResponse = {};
                        this.nextActionScript();
                        return;
                    }
                }
            },

            nextActionScript: function () {
                if (this.currentActionScriptIndex >= 0) {
                    // Reset the hand & the board
                    this.setHand(this.currentHand);
                    this.setGame(this.currentGame);
                    for (var y = 0; y < 8; y ++) {
                        for (var x = 0; x < 8; x++) {
                            $('cell_'+x+'-'+y).classList.remove('selected');
                            $('cell_'+x+'-'+y).classList.remove('selectable');
                        }
                    }
                }

                this.currentActionScriptIndex++;

                const script = this.getCurrentActionScript();

                if (script === null) {
                    // We are out of the range of the script, send the action to the server
                    $('generalactions').innerHTML = '';
                    this.ajaxcall("/tfsbga/tfsbga/respondToAction.html", {
                            lock: true,
                            arena_game_action_id: this.currentAction.arena_game_action_id,
                            base64_response: base64Encode(JSON.stringify(this.currentActionResponse))
                        },
                        this,
                        function (result) {
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                        }, function (is_error) {
                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)
                        });
                    return;
                }

                // Set the message
                $('generalactions').innerHTML = '<br />' + _(script.message) + '<br />';
                this.addActionButton('validate', _('Valider'), 'validateActionScript', 'generalactions');
                this.addActionButton('cancel', _('Annuler'), 'cancelActionScript', 'generalactions');

                // Call the script
                this['action_'+script.type]();
            },

            getCurrentActionScript: function () {
                if (this.currentAction === null) {
                    return null;
                }
                const scriptName = getIndexByNum(this.currentAction.script, this.currentActionScriptIndex);
                return this.currentAction.script[scriptName] ? this.bindScript(this.currentAction.script[scriptName]) : null;
            },

            bindScript: function (input) {
                str = JSON.stringify(input);
                for (var param in this.currentActionResponse) {
                    str = str.replace('$' + param, this.currentActionResponse[param]);
                }
                return JSON.parse(str);
            },

            validateActionScript: function () {
                const scriptName = getIndexByNum(this.currentAction.script, this.currentActionScriptIndex);
                const actionScript = this.getCurrentActionScript();
                var response = null;

                if (actionScript.type === 'choseCards') {
                    response = [];
                    for (var i = 0; i < this.currentHand.length; i ++) {
                        if (inArray('selected', $('card-' + this.currentHand[i].arena_card_id).classList)) {
                            response.push(this.currentHand[i].arena_card_id);
                        }
                    }
                    if (
                        (actionScript.params.hasOwnProperty('min') && actionScript.params.min <= response.length) &&
                        (actionScript.params.hasOwnProperty('max') && actionScript.params.max >= response.length)
                    ) {
                        this.currentActionResponse[scriptName] = response;
                        this.nextActionScript();
                    }
                }

                if (actionScript.type === 'choseSquare') {
                    for (var y = 0; y < 8; y ++) {
                        for (var x = 0; x < 8; x++) {
                            if (inArray('selected', $('cell_'+x+'-'+y).classList)) {
                                response = x+'-'+y;
                            }
                        }
                    }
                    if (response !== null) {
                        this.currentActionResponse[scriptName] = response;
                        this.nextActionScript();
                    }
                }

                if (actionScript.type === 'skip') {
                    this.currentActionResponse[scriptName] = '';
                    this.nextActionScript();
                }
            },

            cancelActionScript: function () {
                this.setActions(this.actions);
            },

            action_choseCards: function () {
                for (var i = 0; i < this.currentHand.length; i ++) {
                    if (this.canChoseCard(this.currentHand[i])) {
                        $('card-'+this.currentHand[i].arena_card_id).classList.add('selectable');
                    }
                }
            },

            canChoseCard: function (arenaCard) {
                const actionScript = this.getCurrentActionScript();

                if (actionScript === null || actionScript.type !== 'choseCards') {
                    return false;
                }

                var canChose = true;
                if (actionScript.params.hasOwnProperty('types')) {
                    if (!inArray(arenaCard.card.type, actionScript.params.types)) {
                        canChose = false;
                    }
                }
                return canChose;
            },

            onChoseCard: function (event) {
                if (!inArray('card', event.target.classList)) {
                    return;
                }

                const arenaCardId = event.target.getAttribute('data-id');

                var arenaCard = null;
                for (var i = 0; i < $scope.currentHand.length; i ++) {
                    if (arenaCardId == $scope.currentHand[i].arena_card_id) {
                        arenaCard = $scope.currentHand[i];
                    }
                }
                // Toggle card selection
                if ($scope.canChoseCard(arenaCard)) {
                    const cardDomElement = $('card-'+arenaCardId);
                    if (cardDomElement) {
                        if (inArray('selected', cardDomElement.classList)) {
                            cardDomElement.classList.remove('selected');
                        } else {
                            cardDomElement.classList.add('selected');
                        }
                    }
                }

                if ($scope.getCurrentActionScript() && $scope.getCurrentActionScript().params && $scope.getCurrentActionScript().params.min === 1 && $scope.getCurrentActionScript().params.max === 1) {
                    $scope.validateActionScript();
                }
            },

            action_choseSquare: function () {
                for (var x = 0; x < 7; x ++) {
                    for (var y = 0; y < 7; y ++) {
                        if (this.canChoseSquare(x, y)) {
                            $('cell_'+x+'-'+y).classList.add('selectable');
                        }
                    }
                }
            },

            canChoseSquare: function (x, y) {
                const actionScript = this.getCurrentActionScript();

                if (actionScript === null || actionScript.type !== 'choseSquare') {
                    return false;
                }

                // Get the parameters of the script
                const params = actionScript.params;

                // Filter by range
                var range = true;
                if (typeof(params.range) !== 'undefined') {
                    range = false;
                    for (var i = 0; i < params.range.length; i++) {
                        if (params.range[i] == x + '-' + y) {
                            range = true;
                        }
                    }
                }

                // Filter the squares by 'controlled' filter
                var controlled = true;
                if (typeof(params.controlled) !== 'undefined' && params.controlled == true) {
                    if (this.getCardAt(x, y) === null) {
                        controlled = false;
                    } else {
                        controlled = (this.getCardAt(x, y).user_id == this.currentUserId);
                    }
                }

                // Filter the squares by 'nearControlled' filter
                var nearControlled = true;
                if (typeof(params.nearControlled) !== 'undefined' && params.nearControlled == true) {
                    var currentUserId = this.currentUserId;
                    if (
                        (this.getCardAt(x, y - 1) !== null && this.getCardAt(x, y - 1).user_id == currentUserId && this.getCardAt(x, y - 1).square_type === null) ||
                        (this.getCardAt(x + 1, y) !== null && this.getCardAt(x + 1, y).user_id == currentUserId && this.getCardAt(x + 1, y).square_type === null) ||
                        (this.getCardAt(x, y + 1) !== null && this.getCardAt(x, y + 1).user_id == currentUserId && this.getCardAt(x, y + 1).square_type === null) ||
                        (this.getCardAt(x - 1, y) !== null && this.getCardAt(x - 1, y).user_id == currentUserId && this.getCardAt(x - 1, y).square_type === null)
                    ) {
                    } else {
                        nearControlled = false;
                    }
                }

                // Filter the squares by 'isEmpty' filter
                var isEmpty = true;
                if (typeof(params.isEmpty) !== 'undefined' && params.isEmpty == true) {
                    if (this.getCardAt(x, y) !== null) {
                        isEmpty = false;
                    }
                }

                // Filter the squares by 'types' filter
                var types = true;
                if (typeof(params.types) !== 'undefined') {
                    if (this.getCardAt(x, y) === null) {
                        types = false;
                    } else {
                        types = inArray(this.getCardAt(x, y).card.type, params.types);
                    }
                }

                // Filter the squares by 'nearPlayer' filter
                var nearPlayer = true;
                if (typeof(params.nearPlayer) !== 'undefined' && params.nearPlayer == true) {
                    // The tiles coords
                    var tilesByPlayerPosition = [
                        {x: 3, y: 0},
                        {x: 6, y: 3},
                        {x: 3, y: 6},
                        {x: 0, y: 3}
                    ];
                    var currentPlayerPosition = 0;
                    for (var i = 0; i < tilesByPlayerPosition.length; i ++) {
                        var cardDomElement = $('cell_'+tilesByPlayerPosition[i].x+'-'+tilesByPlayerPosition[i].y)
                            .getElementsByClassName('card')
                            .item(0);
                        if (cardDomElement && cardDomElement.getAttribute('data-user_id') == this.currentUserId) {
                            currentPlayerPosition = i;
                        }
                    }
                    var playerSquarePosition = tilesByPlayerPosition[currentPlayerPosition];
                    if (
                        (playerSquarePosition.y - 1 == y && playerSquarePosition.x == x) || // top
                        (playerSquarePosition.y == y && playerSquarePosition.x + 1 == x) || // right
                        (playerSquarePosition.y + 1 == y && playerSquarePosition.x == x) || // bottom
                        (playerSquarePosition.y == y && playerSquarePosition.x - 1 == x) // left
                    ) {
                    } else {
                        nearPlayer = false;
                    }
                }

                 // Filter the squares by 'nextTo' filter
                var nextTo = true;
                if (typeof(params.nextTo) !== 'undefined') {
                    var xy = params.nextTo.split('-');
                    xy[0] = parseInt(xy[0]);
                    xy[1] = parseInt(xy[1]);
                    if (
                        (xy[0] === x && xy[1] - 1 === y) ||
                        (xy[0] + 1 === x && xy[1] === y) ||
                        (xy[0] === x && xy[1] + 1 === y) ||
                        (xy[0] - 1 === x && xy[1] === y)
                    ) {
                    } else {
                        nextTo = false;
                    }
                }

                return nearControlled && isEmpty && types && range && controlled && nextTo && nearPlayer;
            },

            onChoseSquare: function (event) {
                var effectiveTarget = event.target.parentNode;
                if (!inArray('cell', effectiveTarget.classList)) {
                    return;
                }

                const x = parseInt(effectiveTarget.getAttribute('data-x'));
                const y = parseInt(effectiveTarget.getAttribute('data-y'));

                // Toggle card selection
                if ($scope.canChoseSquare(x, y)) {
                    const cardDomElement = $('cell_'+x+'-'+y);
                    cardDomElement.classList.add('selected');
                    $scope.validateActionScript();
                }
            },

            getCardAt: function (x, y) {
                for (var i = 0; i < this.currentGame.board.length; i ++) {
                    if (this.currentGame.board[i].position == x + '-' + y) {
                        return this.currentGame.board[i];
                    }
                }
                return null;
            },

            action_skip: function () {
                this.validateActionScript();
            },


            ///////////////////////////////////////////////////
            //// Player's action

            /*

             Here, you are defining methods to handle player's action (ex: results of mouse click on
             game objects).

             Most of the time, these methods:
             _ check the action is possible at this game state.
             _ make a call to the game server

             */

            /* Example:

             onMyMethodToCall1: function( evt )
             {
             console.log( 'onMyMethodToCall1' );

             // Preventing default browser reaction
             dojo.stopEvent( evt );

             // Check that this action is possible (see "possibleactions" in states.inc.php)
             if( ! this.checkAction( 'myAction' ) )
             {   return; }

             this.ajaxcall( "/tfsbga/tfsbga/myAction.html", {
             lock: true,
             myArgument1: arg1,
             myArgument2: arg2,
             ...
             },
             this, function( result ) {

             // What to do after the server call if it succeeded
             // (most of the time: nothing)

             }, function( is_error) {

             // What to do after the server call in anyway (success or failure)
             // (most of the time: nothing)

             } );
             },

             */


            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
             setupNotifications:

             In this method, you associate each of your game notifications with your local method to handle it.

             Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
             your tfsbga.game.php file.

             */

            setupNotifications: function () {
                // Here, associate your game notifications with local methods
                dojo.subscribe('gameUpdated', this, 'notif_gameUpdated');
                dojo.subscribe('handUpdated', this, 'notif_handUpdated');
                dojo.subscribe('actionRequired', this, 'notif_actionRequired');

                // Example 1: standard notification handling
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

                // Example 2: standard notification handling + tell the user interface to wait
                //            during 3 seconds after calling the method in order to let the players
                //            see what is happening in the game.
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
                // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
                //
            },

            // TODO: from this point and below, you can write your game notifications handling methods

            /*
             Example:

             notif_cardPlayed: function( notif )
             {
             console.log( 'notif_cardPlayed' );
             console.log( notif );

             // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call

             // TODO: play the card in the user interface.
             },

             */

            notif_gameUpdated: function (notif) {
                this.setGame(notif.args);
            },

            notif_handUpdated: function (notif) {
                this.setHand(notif.args);
            },

            notif_actionRequired: function (notif) {
                this.setActions(notif.args);
            }
        });
    });

getIndexByNum = function (object, num) {
    var i = 0;
    for (var name in object) {
        if (i === num) {
            return name;
        }
        i++;
    }
    return null;
};

inArray = function (needle, haystack) {
    for (var i = 0; i < haystack.length; i++) {
        if (needle == haystack[i]) return true;
    }
    return false;
};

base64Encode = function (input) {
    return window.btoa(input.replace(/[\u0250-\ue007]/g, ''));
};


base64Decode = function (input) {
    return window.atob(input);
};
