<?php

declare(strict_types=1);

namespace Bga\Games\yachtrock\States;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\yachtrock\Game;
use Bga\Games\yachtrock\Constants;

class PlayerTurn extends GameState
{
    function __construct(
        protected Game $game,
    ) {
        error_log('Creating PlayerTurn...');
        parent::__construct(
            $game,
            id: 10,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must choose a style slot'),
            descriptionMyTurn: clienttranslate('${you} must choose a style slot'),
        );
    }

    public function getArgs(): array
    {
        if ((int) $this->game->getGameStateValue(Constants::LABEL_TURN_PHASE) === 1) {
            return ["chooseStyleSlot"];
        }

        $args = [];
        $opts = $this->game->getGameStateValue(Constants::LABEL_REMAINING_OPTIONALS);
        if ($opts & Constants::OPT_SINGLE) {
            $args[] = "recordSingle";
        }
        if ($opts & Constants::OPT_SELL) {
            $args[] = "sellStyleClothes";
        }
        return $args;
    }

    public function onEnteringState(int $activePlayerId)
    {
        if ($activePlayerId !== (int) $this->game->getGameStateValue(Constants::LABEL_ACTIVE_TURN_PLAYER)) {
            $this->game->setGameStateValue(Constants::LABEL_ACTIVE_TURN_PLAYER, $activePlayerId);
            $this->game->setGameStateValue(Constants::LABEL_TURN_PHASE, 1);
            $this->game->setGameStateValue(
                Constants::LABEL_REMAINING_OPTIONALS,
                Constants::OPT_SELL | Constants::OPT_SINGLE
            );
        }
    }

    #[PossibleAction]
    public function actChooseStyleSlot(int $slotNumber, int $activePlayerId)
    {
        // Check if slot is valid
        if ($slotNumber < 1 || $slotNumber > 5) {
            throw new \BgaUserException('Invalid slot choice');
        }

        /* TODO: Reenable later
        if ((int) $this->game->getGameStateValue(Constants::LABEL_TURN_PHASE) !== 1) {
            throw new \BgaUserException("Invalid move");
        }
        */

        $this->game->cards->moveAllCardsInLocation("style_slot_$slotNumber", 'player_hand', null, $activePlayerId);

        // Notify about cards taken
        $this->notify->all("styleCardsTaken", clienttranslate('${playerName} takes cards from style slot ${slotNumber}'), [
            "playerId" => $activePlayerId,
            "playerName" => $this->game->getPlayerNameById($activePlayerId),
            "slotNumber" => $slotNumber,
        ]);

        $this->dealStyleCards($slotNumber);

        $this->game->setGameStateValue(Constants::LABEL_TURN_PHASE, 2);
        return self::class;
    }

    private function dealStyleCards($lastChosenSlot)
    {
        // Deal one card to adjacent slots (left and right)
        $leftSlot = ($lastChosenSlot == 1) ? 5 : $lastChosenSlot - 1;
        $rightSlot = ($lastChosenSlot == 5) ? 1 : $lastChosenSlot + 1;

        $styleCards = [];
        foreach ([$leftSlot, $lastChosenSlot, $rightSlot] as $slotNumber) {
            // Get all cards currently in this slot
            $cardsInSlot = $this->game->cards->getCardsInLocation("style_slot_$slotNumber");
            // Pick a card and place it at the top of the stack
            $dbStyleCard = $this->game->cards->pickCardForLocation('style_deck', "style_slot_$slotNumber", count($cardsInSlot));
            $styleCard = $this->game->styleCards[$dbStyleCard['type_arg']];
            $styleCard['index'] = $dbStyleCard['type_arg'];
            $styleCard['slotNumber'] = $slotNumber;
            $styleCard['stackPosition'] = (int) $dbStyleCard['location_arg'];
            $styleCards[] = $styleCard;
        }

        $this->notify->all("styleCardsDealt", "", [
            "styleCards" => $styleCards,
        ]);
    }


    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: play a random card).
     * 
     * See more about Zombie Mode: https://en.doc.boardgamearena.com/Zombie_Mode
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, 
     * but use the $playerId passed in parameter and $this->game->getPlayerNameById($playerId) instead.
     */
    function zombie(int $playerId)
    {
        return $this->actChooseStyleSlot(rand(1, 5), $playerId);
    }
}
