{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- tfsbga implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    tfsbga_tfsbga.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="code" class="whiteblock clearfix text-center"></div>

<div class="whiteblock">
    <div id="hand"></div>
</div>

<div class="whiteblock clearfix text-center">
    <div id="board" class="clearfix" style="width: 400px; height: 400px;">
    </div>
</div>

<div id="zoom">
    <div class="zoom-content">
        <div id="zoom-name"></div>
        <div id="zoom-image"></div>
        <div id="zoom-description"></div>
    </div>
</div>

<script type="text/javascript">

    // Javascript HTML templates

    /*
     // Example:
     var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${id}"></div>';

     */

    const jstpl_square = '<div id="cell_${x}-${y}" data-x="${x}" data-y="${y}" class="cell"></div>';
    const jstpl_card = '<div id="card-${arena_card_id}" class="card card-spritesheet card-spritesheet-${view.spritesheetId}" style="transform: rotate(${view.rotation}deg); background-color: ${view.color}" title="${card.name}" data-type="${card.type}" data-id="${arena_card_id}" data-user_id="${user_id}" data-spritesheet-id="${view.spritesheetId}" data-name="${card.name}" data-descriptionEscaped="${view.descriptionEscaped}"><span onclick="$scope.zoom(this)" class="zoom bgabutton bgabutton_blue"><span class="fa fa-eye"></span></span></div>';
    const jstpl_marker = '<div class="marker marker-${type} marker-${type}-${value}">${value}</div>';

</script>

{OVERALL_GAME_FOOTER}
