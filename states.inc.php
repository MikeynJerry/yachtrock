<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * yachtrock implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * yachtrock game states description
 *
 */

use Bga\GameFramework\GameStateBuilder;
use Bga\GameFramework\StateType;

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with `StateType::GAME` type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by 'st' (ex: 'stMyGameStateName').
   _ possibleactions: array that specify possible player actions on this step. It allows you to use `checkAction`
                      method on both client side (Javacript: `this.checkAction`) and server side (PHP: `$this->checkAction`).
                      Note that autowired actions and calls with this.bgaPerformAction call the checkAction except if it's explicitely disabled in the call
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in `nextState` PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on `onEnteringState` or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!


$machinestates = [
    // Initial state
    1 => GameStateBuilder::gameSetup(2)->build(),

    // Game setup
    2 => GameStateBuilder::create()
        ->name('gameSetup')
        ->description('')
        ->type(StateType::GAME)
        ->action('stGameSetup')
        ->transitions([
            'setupComplete' => 3,
        ])
        ->build(),

    // Player turn - choose style slot
    3 => GameStateBuilder::create()
        ->name('playerTurn')
        ->description(clienttranslate('${actplayer} must choose a style slot'))
        ->descriptionmyturn(clienttranslate('${you} must choose a style slot'))
        ->type(StateType::ACTIVE_PLAYER)
        ->args('argPlayerTurn')
        ->possibleactions([
            'actChooseStyleSlot',
        ])
        ->transitions([
            'styleSlotChosen' => 4,
        ])
        ->build(),

    // Deal new style cards
    4 => GameStateBuilder::create()
        ->name('dealStyleCards')
        ->description('')
        ->type(StateType::GAME)
        ->action('stDealStyleCards')
        ->transitions([
            'cardsDealt' => 5,
        ])
        ->build(),

    // Optional actions phase
    5 => GameStateBuilder::create()
        ->name('optionalActions')
        ->description(clienttranslate('${actplayer} may sell clothing cards and/or record singles'))
        ->descriptionmyturn(clienttranslate('${you} may sell clothing cards and/or record singles'))
        ->type(StateType::ACTIVE_PLAYER)
        ->args('argOptionalActions')
        ->possibleactions([
            'actSellClothing',
            'actRecordSingle',
            'actPassOptional',
        ])
        ->transitions([
            'clothingSold' => 5,
            'singleRecorded' => 5,
            'passOptional' => 6,
        ])
        ->build(),

    // Next player
    6 => GameStateBuilder::create()
        ->name('nextPlayer')
        ->description('')
        ->type(StateType::GAME)
        ->action('stNextPlayer')
        ->updateGameProgression(true)
        ->transitions([
            'partyPhase' => 7,
            'nextPlayer' => 3,
        ])
        ->build(),

    // Party phase - choose parties
    7 => GameStateBuilder::create()
        ->name('chooseParties')
        ->description(clienttranslate('All players must choose which party to attend'))
        ->descriptionmyturn(clienttranslate('${you} must choose which party to attend'))
        ->type(StateType::MULTIPLE_ACTIVE_PLAYER)
        ->args('argChooseParties')
        ->possibleactions([
            'actChooseParty',
        ])
        ->transitions([
            'partiesChosen' => 8,
        ])
        ->build(),

    // Party phase - scoring
    8 => GameStateBuilder::create()
        ->name('partyScoring')
        ->description('')
        ->type(StateType::GAME)
        ->action('stPartyScoring')
        ->transitions([
            'scoringComplete' => 9,
        ])
        ->build(),

    // Round end - check if game over
    9 => GameStateBuilder::create()
        ->name('roundEnd')
        ->description('')
        ->type(StateType::GAME)
        ->action('stRoundEnd')
        ->transitions([
            'gameOver' => 98,
            'nextRound' => 10,
        ])
        ->build(),

    // Prepare next round
    10 => GameStateBuilder::create()
        ->name('prepareNextRound')
        ->description('')
        ->type(StateType::GAME)
        ->action('stPrepareNextRound')
        ->transitions([
            'roundReady' => 3,
        ])
        ->build(),

    // End score
    98 => GameStateBuilder::endScore()->build(),
];
