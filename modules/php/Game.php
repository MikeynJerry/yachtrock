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
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

declare(strict_types=1);

namespace Bga\Games\yachtrock;

use Bga\GameFramework\Actions\Action;

class Game extends \Bga\GameFramework\Table
{
    // Game constants
    const STYLE_CARDS_PER_ROUND = [
        1 => 38,
        2 => 20,
        3 => 29,
        4 => 38,
        5 => 47,
        6 => 56
    ];

    const CLOTHING_TYPES = ['top', 'bottom', 'shoes', 'sunglasses', 'hat'];
    const CARD_COLORS = ['gold', 'coral', 'lavender', 'teal'];
    const MUSICAL_ATTRIBUTES = [
        'Soothing Saxophone',
        'Heartfelt Anthem',
        'Guitar Interlude',
        'Timeless Refrain',
        'Poetic Lyrics',
        'Gentle Beat',
        'Smooth Harmony'
    ];

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If you want to store any type instead of int, use $this->globals instead.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();

        $this->initGameStateLabels([
            "current_round" => 10,
            "first_player_id" => 11,
            "style_cards_dealt" => 12,
            "party_phase" => 13,
            "last_chosen_slot" => 14,
        ]);

        $this->cards = $this->getNew("module.common.deck");
        $this->cards->init("cards");

        $this->soireeCards = json_decode(file_get_contents(__DIR__ . "/../../data/soiree_cards.json"), true);
        $this->singleCards = json_decode(file_get_contents(__DIR__ . "/../../data/single_cards.json"), true);
        $this->styleCards = json_decode(file_get_contents(__DIR__ . "/../../data/style_cards.json"), true);
    }

    /**
     * Setup new game
     */
    protected function setupNewGame($players, $options = [])
    {
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->createCards();
        //$this->gamestate->changeActivePlayer("2422642");
        $this->activeNextPlayer();
        // try {
        //     // Set the colors of the players
        //     $gameinfos = $this->getGameinfos();
        //     $default_colors = $gameinfos['player_colors'];
        //     $query_values = [];

        //     foreach ($players as $player_id => $player) {
        //         $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
        //             $player_id,
        //             array_shift($default_colors),
        //             $player["player_canal"],
        //             addslashes($player["player_name"]),
        //             addslashes($player["player_avatar"]),
        //         ]);
        //     }

        //     // Create players
        //     static::DbQuery(
        //         sprintf(
        //             "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
        //             implode(",", $query_values)
        //         )
        //     );

        //     $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        //     $this->reloadPlayersBasicInfos();

        //     // Activate first player
        //     $this->activeNextPlayer();

        //     // The game will automatically transition to state 2 (gameSetup) after setup
        // } catch (\Exception $e) {
        //     error_log("Error in setupNewGame: " . $e->getMessage());
        //     throw $e;
        // }
    }

    public function stGameSetup(): void
    {
        // // Very basic setup - just initialize game state variables
        // $this->setGameStateInitialValue("current_round", 1);
        // $this->setGameStateInitialValue("style_cards_dealt", 0);
        // $this->setGameStateInitialValue("party_phase", 0);
        // $this->setGameStateInitialValue("last_chosen_slot", 0);

        // // Set a default first player (will be updated later)
        // $this->setGameStateInitialValue("first_player_id", 0);

        $this->cards->shuffle('soiree_deck');
        $this->cards->shuffle('single_deck');
        $this->cards->shuffle('style_deck');

        $this->dealCards();

        // Transition to next state immediately
        $this->gamestate->nextState("setupComplete");
    }

    private function createCards(): void
    {
        $decks = ['soiree' => $this->soireeCards, 'single' => $this->singleCards, 'style' => $this->styleCards];
        foreach ($decks as $type => $deck) {
            $cards = [];
            foreach ($deck as $i => $card) {
                $cards[] = [
                    'type'     => $type,
                    'type_arg' => $i,
                    'nbr'      => 1,
                ];
            }
            $this->cards->createCards($cards, $type . '_deck');
        }
    }

    private function dealCards(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->cards->pickCardForLocation('style_deck', "style_slot_$i");
        }
        for ($i = 1; $i <= 2; $i++) {
            $this->cards->pickCardForLocation('single_deck', 'single_board', $i);
        }
        for ($i = 1; $i <= 2; $i++) {
            $this->cards->pickCardForLocation('soiree_deck', 'soiree_board', $i);
        }
    }

    /**
     * Game state arguments for player turn
     */
    public function argPlayerTurn(): array
    {
        $result = [];
        return $result;
        // $result = [];

        // // Get available style slots
        // $availableSlots = [];
        // for ($i = 1; $i <= 5; $i++) {
        //     $cards = $this->getCollectionFromDb(
        //         "SELECT COUNT(*) as count FROM style_cards WHERE card_location = 'slot{$i}'"
        //     );
        //     if (!empty($cards) && isset($cards[0]['count']) && $cards[0]['count'] > 0) {
        //         $availableSlots[] = $i;
        //     }
        // }

        // $result['availableSlots'] = $availableSlots;
        // return $result;
    }

    /**
     * Game state arguments for optional actions
     */
    public function argOptionalActions(): array
    {
        $styleCards = [];
        $singleCards = [];

        foreach ($this->cards->getCardsInLocation('player_hand') as $styleCard) {
            $card = $this->styleCards[$styleCard['type_arg']];
            $card['index'] = $styleCard['type_arg'];
            $card['owner'] = $styleCard['location_arg'];
            $styleCards[] = $card;
        }

        foreach ($this->cards->getCardsInLocation('single_board') as $singleCard) {
            $card = $this->singleCards[$singleCard['type_arg']];
            $card['index'] = $singleCard['type_arg'];
            $card['position'] = $singleCard['location_arg'];
            $singleCards[] = $card;
        }

        return [
            'styleCards' => $styleCards,
            'singleCards' => $singleCards,
        ];
    }

    /**
     * Game state arguments for choosing parties
     */
    public function argChooseParties(): array
    {
        // $soireeCards = $this->getCollectionFromDb(
        //     "SELECT * FROM soiree_cards WHERE card_location LIKE 'board%'"
        // );

        // return [
        //     'soireeCards' => $soireeCards
        // ];
    }

    /**
     * Player chooses a style slot
     */
    #[Action(name: "actChooseStyleSlot")]
    public function actChooseStyleSlot(int $slotNumber): void
    {
        $playerId = (int)$this->getActivePlayerId();

        // Check if slot is valid
        if ($slotNumber < 1 || $slotNumber > 5) {
            throw new \BgaUserException('Invalid slot choice');
        }

        $this->cards->moveAllCardsInLocation("style_slot_$slotNumber", 'player_hand', null, $playerId);

        // Store the chosen slot for dealing new cards
        $this->setGameStateValue("last_chosen_slot", $slotNumber);

        // Notify about cards taken
        $this->notify->all("styleCardsTaken", clienttranslate('${playerName} takes cards from style slot ${slotNumber}'), [
            "playerId" => $playerId,
            "playerName" => $this->getActivePlayerName(),
            "slotNumber" => $slotNumber,
        ]);

        $this->gamestate->nextState("styleSlotChosen");
    }

    /**
     * Deal new style cards after slot is chosen
     */
    public function stDealStyleCards(): void
    {
        // Get the last chosen slot from game state
        $lastChosenSlot = $this->getGameStateValue("last_chosen_slot");

        // Deal one card to adjacent slots (left and right)
        $leftSlot = ($lastChosenSlot == 1) ? 5 : $lastChosenSlot - 1;
        $rightSlot = ($lastChosenSlot == 5) ? 1 : $lastChosenSlot + 1;

        $styleCards = [];
        foreach ([$leftSlot, $lastChosenSlot, $rightSlot] as $slotNumber) {
            // Get all cards currently in this slot
            $cardsInSlot = $this->cards->getCardsInLocation("style_slot_$slotNumber");
            // Pick a card and place it at the top of the stack
            $dbStyleCard = $this->cards->pickCardForLocation('style_deck', "style_slot_$slotNumber", count($cardsInSlot));
            $styleCard = $this->styleCards[$dbStyleCard['type_arg']];
            $styleCard['index'] = $dbStyleCard['type_arg'];
            $styleCard['slotNumber'] = $slotNumber;
            $styleCard['stackPosition'] = (int) $dbStyleCard['location_arg'];
            $styleCards[] = $styleCard;
        }

        $this->notify->all("styleCardsDealt", "", [
            "styleCards" => $styleCards,
        ]);

        $this->gamestate->nextState("cardsDealt");
    }

    /**
     * Player sells clothing cards
     */
    #[Action(name: "actSellClothing")]
    public function actSellClothing(array $card_ids): void
    {
        // $player_id = (int)$this->getActivePlayerId();

        // $points = 0;
        // foreach ($card_ids as $card_id) {
        //     // Verify card belongs to player and is clothing
        //     $card = $this->getObjectFromDb(
        //         "SELECT * FROM style_cards WHERE card_id = $card_id AND card_location = 'player_hand' AND card_location_arg = $player_id AND card_type = 'clothing'"
        //     );

        //     if ($card) {
        //         // Move to discard
        //         $this->DbQuery(
        //             "UPDATE style_cards SET card_location = 'discard' WHERE card_id = $card_id"
        //         );

        //         // Award 1 point
        //         $points++;

        //         // Update player's sold cards count
        //         $this->DbQuery(
        //             "UPDATE player SET player_style_cards_sold = player_style_cards_sold + 1 WHERE player_id = $player_id"
        //         );
        //     }
        // }

        // if ($points > 0) {
        //     // Update score
        //     $this->DbQuery(
        //         "UPDATE player SET player_score = player_score + $points WHERE player_id = $player_id"
        //     );

        //     $this->notify->all("clothingSold", clienttranslate('${player_name} sells ${count} clothing cards for ${points} points'), [
        //         "player_id" => $player_id,
        //         "player_name" => $this->getActivePlayerName(),
        //         "count" => count($card_ids),
        //         "points" => $points
        //     ]);

        //     $this->gamestate->nextState("clothingSold");
        // }
    }

    /**
     * Player records a single
     */
    #[Action(name: "actRecordSingle")]
    public function actRecordSingle(int $single_card_id, array $musical_card_ids, int $collaborator_id = null): void
    {
        // $player_id = (int)$this->getActivePlayerId();

        // // Get single card
        // $singleCard = $this->getObjectFromDb(
        //     "SELECT * FROM single_cards WHERE card_id = $single_card_id AND card_location LIKE 'board%'"
        // );

        // if (!$singleCard) {
        //     throw new \BgaUserException('Invalid single card');
        // }

        // $requiredAttributes = [$singleCard['attribute1'], $singleCard['attribute2'], $singleCard['attribute3']];
        // $providedAttributes = [];

        // // Get musical cards
        // foreach ($musical_card_ids as $card_id) {
        //     $card = $this->getObjectFromDb(
        //         "SELECT * FROM style_cards sc 
        //          INNER JOIN player_musical_cards pmc ON sc.card_id = pmc.card_id 
        //          WHERE pmc.player_id = $player_id AND sc.card_id = $card_id"
        //     );

        //     if ($card) {
        //         $providedAttributes[] = $card['card_musical_attribute'];
        //     }
        // }

        // $points = 0;
        // if ($collaborator_id) {
        //     // Collaboration - need 2 different attributes
        //     if (count(array_unique($providedAttributes)) >= 2) {
        //         $points = 3;

        //         // Award points to both players
        //         $this->DbQuery(
        //             "UPDATE player SET player_score = player_score + 3 WHERE player_id IN ($player_id, $collaborator_id)"
        //         );

        //         // Award single tokens
        //         $this->awardSingleToken($player_id);
        //         $this->awardSingleToken($collaborator_id);
        //     }
        // } else {
        //     // Solo recording - need all 3 attributes
        //     if (count(array_intersect($requiredAttributes, $providedAttributes)) >= 3) {
        //         $points = 8;

        //         // Award points
        //         $this->DbQuery(
        //             "UPDATE player SET player_score = player_score + 8 WHERE player_id = $player_id"
        //         );

        //         // Award single token
        //         $this->awardSingleToken($player_id);
        //     }
        // }

        // if ($points > 0) {
        //     // Move single card to recorded
        //     $this->DbQuery(
        //         "UPDATE single_cards SET card_location = 'recorded', card_location_arg = $player_id WHERE card_id = $single_card_id"
        //     );

        //     // Remove musical cards
        //     foreach ($musical_card_ids as $card_id) {
        //         $this->DbQuery(
        //             "DELETE FROM player_musical_cards WHERE player_id = $player_id AND card_id = $card_id"
        //         );
        //     }

        //     $this->notify->all("singleRecorded", clienttranslate('${player_name} records a single for ${points} points'), [
        //         "player_id" => $player_id,
        //         "player_name" => $this->getActivePlayerName(),
        //         "points" => $points,
        //         "single_card" => $singleCard
        //     ]);

        //     $this->gamestate->nextState("singleRecorded");
        // } else {
        //     throw new \BgaUserException('Invalid musical attributes for recording');
        //}
    }

    /**
     * Player passes on optional actions
     */
    #[Action(name: "actPassOptional")]
    public function actPassOptional(): void
    {
        // $this->gamestate->nextState("passOptional");
    }

    /**
     * Player chooses a party
     */
    #[Action(name: "actChooseParty")]
    public function actChooseParty(int $soiree_card_id): void
    {
        // $player_id = (int)$this->getCurrentPlayerId();

        // // Verify card exists
        // $soireeCard = $this->getObjectFromDb(
        //     "SELECT * FROM soiree_cards WHERE card_id = $soiree_card_id AND card_location LIKE 'board%'"
        // );

        // if (!$soireeCard) {
        //     throw new \BgaUserException('Invalid party choice');
        // }

        // // Place guitar pick on party
        // $this->DbQuery(
        //     "UPDATE player SET player_first_player_token = 1 WHERE player_id = $player_id"
        // );

        // $this->notify->all("partyChosen", clienttranslate('${player_name} chooses to attend ${party_name}'), [
        //     "player_id" => $player_id,
        //     "player_name" => $this->getCurrentPlayerName(),
        //     "party_name" => $soireeCard['party_name']
        // ]);

        // $this->gamestate->setPlayerNonMultiactive($player_id, '');
    }

    /**
     * Award a single token to a player
     */
    private function awardSingleToken(int $player_id): void
    {
        // $existing = $this->getObjectFromDb(
        //     "SELECT * FROM player_single_tokens WHERE player_id = $player_id"
        // );

        // if ($existing) {
        //     $this->DbQuery(
        //         "UPDATE player_single_tokens SET token_count = token_count + 1 WHERE player_id = $player_id"
        //     );
        // } else {
        //     $this->DbQuery(
        //         "INSERT INTO player_single_tokens (player_id, token_count) VALUES ($player_id, 1)"
        //     );
        // }
    }

    /**
     * Next player logic
     */
    public function stNextPlayer(): void
    {
        $this->activeNextPlayer();

        // Check if we should go to party phase
        $styleCardsDealt = $this->getGameStateValue("style_cards_dealt");
        $playerCount = $this->getPlayersNumber();

        // Ensure player count is valid
        if (!isset(self::STYLE_CARDS_PER_ROUND[$playerCount])) {
            $playerCount = min(array_keys(self::STYLE_CARDS_PER_ROUND));
        }

        $cardsPerRound = self::STYLE_CARDS_PER_ROUND[$playerCount];

        if ($styleCardsDealt >= $cardsPerRound) {
            $this->gamestate->nextState("partyPhase");
        } else {
            $this->gamestate->nextState("nextPlayer");
        }
    }

    /**
     * Party scoring
     */
    public function stPartyScoring(): void
    {
        // $players = $this->getCollectionFromDb("SELECT * FROM player");

        // foreach ($players as $player) {
        //     $this->scorePlayer($player['player_id']);
        // }

        // $this->gamestate->nextState("scoringComplete");
    }

    /**
     * Round end logic
     */
    public function stRoundEnd(): void
    {
        // $currentRound = $this->getGameStateValue("current_round");

        // if ($currentRound >= 3) {
        //     $this->gamestate->nextState("gameOver");
        // } else {
        //     $this->gamestate->nextState("nextRound");
        // }
    }

    /**
     * Prepare next round
     */
    public function stPrepareNextRound(): void
    {
        // $currentRound = $this->getGameStateValue("current_round");
        // $this->setGameStateValue("current_round", $currentRound + 1);
        // $this->setGameStateValue("style_cards_dealt", 0);
        // $this->setGameStateValue("party_phase", 0);

        // // Reset first player token
        // $this->DbQuery("UPDATE player SET player_first_player_token = 0");

        // // Find player with most points
        // $topPlayer = $this->getObjectFromDb(
        //     "SELECT player_id FROM player ORDER BY player_score DESC LIMIT 1"
        // );

        // if ($topPlayer) {
        //     $this->setGameStateValue("first_player_id", $topPlayer['player_id']);
        //     $this->DbQuery(
        //         "UPDATE player SET player_first_player_token = 1 WHERE player_id = {$topPlayer['player_id']}"
        //     );
        // }

        // // Prepare new cards for next round
        // $this->prepareNextRoundCards();

        // $this->gamestate->nextState("roundReady");
    }

    /**
     * Game end logic
     */
    public function stGameEnd(): void
    {
        // // Final scoring - award points for remaining musical style cards
        // $players = $this->getCollectionFromDb("SELECT * FROM player");

        // foreach ($players as $player) {
        //     $musicalCards = $this->getCollectionFromDb(
        //         "SELECT COUNT(*) as count FROM player_musical_cards WHERE player_id = {$player['player_id']}"
        //     );

        //     $finalPoints = (!empty($musicalCards) && isset($musicalCards[0]['count'])) ? $musicalCards[0]['count'] : 0;

        //     if ($finalPoints > 0) {
        //         $this->DbQuery(
        //             "UPDATE player SET player_score = player_score + $finalPoints WHERE player_id = {$player['player_id']}"
        //         );

        //         $this->notify->all("finalScoring", clienttranslate('${player_name} scores ${points} final points for remaining musical style cards'), [
        //             "player_id" => $player['player_id'],
        //             "player_name" => $this->getPlayerNameById($player['player_id']),
        //             "points" => $finalPoints
        //         ]);
        //     }
        // }

        // $this->gamestate->nextState();
    }

    /**
     * Game end arguments
     */
    public function argGameEnd(): array
    {
        // return [
        //     'final_scores' => $this->getCollectionFromDb(
        //         "SELECT player_id, player_score FROM player ORDER BY player_score DESC"
        //     )
        // ];
    }

    /**
     * Score a player for the party phase
     */
    private function scorePlayer(int $player_id): void
    {
        // // Base points from clothing
        // $outfit = $this->getObjectFromDb(
        //     "SELECT * FROM player_outfits WHERE player_id = $player_id"
        // );

        // $basePoints = 0;
        // foreach (self::CLOTHING_TYPES as $type) {
        //     $cardId = $outfit[$type . '_card_id'];
        //     if ($cardId) {
        //         $card = $this->getObjectFromDb(
        //             "SELECT * FROM style_cards WHERE card_id = $cardId"
        //         );

        //         if ($card['card_color'] == 'gold') {
        //             $basePoints += 3;
        //         } else {
        //             $basePoints += 1;
        //         }
        //     }
        // }

        // // Bonus points from party
        // $party = $this->getObjectFromDb(
        //     "SELECT * FROM soiree_cards WHERE card_location = 'board1' OR card_location = 'board2'"
        // );

        // $bonusPoints = 0;
        // if ($party) {
        //     // Color bonus
        //     $colorCards = $this->getCollectionFromDb(
        //         "SELECT * FROM style_cards sc 
        //          INNER JOIN player_outfits po ON sc.card_id IN (po.top_card_id, po.bottom_card_id, po.shoes_card_id, po.sunglasses_card_id, po.hat_card_id)
        //          WHERE po.player_id = $player_id AND sc.card_color = '{$party['color_bonus']}'"
        //     );
        //     $bonusPoints += count($colorCards) * $party['color_bonus_points'];

        //     // Record bonus
        //     $singleTokens = $this->getObjectFromDb(
        //         "SELECT token_count FROM player_single_tokens WHERE player_id = $player_id"
        //     );
        //     if ($singleTokens && $party['record_bonus']) {
        //         $bonusPoints += $singleTokens['token_count'] * $party['record_bonus_points'];
        //     }

        //     // In Vogue bonus
        //     if ($party['in_vogue_bonus'] != 'multicolor') {
        //         $inVogueCard = $this->getObjectFromDb(
        //             "SELECT * FROM style_cards sc 
        //              INNER JOIN player_outfits po ON sc.card_id = po.{$party['in_vogue_bonus']}_card_id
        //              WHERE po.player_id = $player_id"
        //         );
        //         if ($inVogueCard) {
        //             $bonusPoints += $party['in_vogue_bonus_points'];
        //         }
        //     } else {
        //         // Multicolor bonus
        //         $colors = [];
        //         foreach (self::CLOTHING_TYPES as $type) {
        //             $cardId = $outfit[$type . '_card_id'];
        //             if ($cardId) {
        //                 $card = $this->getObjectFromDb(
        //                     "SELECT * FROM style_cards WHERE card_id = $cardId"
        //                 );
        //                 if ($card['card_color'] != 'none') {
        //                     $colors[] = $card['card_color'];
        //                 }
        //             }
        //         }
        //         $bonusPoints += count(array_unique($colors));
        //     }
        // }

        // $totalPoints = $basePoints + $bonusPoints;

        // // Update score
        // $this->DbQuery(
        //     "UPDATE player SET player_score = player_score + $totalPoints WHERE player_id = $player_id"
        // );

        // $this->notify->all("playerScored", clienttranslate('${player_name} scores ${base_points} base points and ${bonus_points} bonus points'), [
        //     "player_id" => $player_id,
        //     "player_name" => $this->getPlayerNameById($player_id),
        //     "base_points" => $basePoints,
        //     "bonus_points" => $bonusPoints,
        //     "total_points" => $totalPoints
        // ]);
    }

    /**
     * Deal initial style cards
     */
    private function dealInitialStyleCards(): void
    {
        // // Deal one card to each of the 5 style slots
        // for ($i = 1; $i <= 5; $i++) {
        //     $card = $this->getObjectFromDb(
        //         "SELECT * FROM style_cards WHERE card_location = 'deck' LIMIT 1"
        //     );

        //     if ($card) {
        //         $this->DbQuery(
        //             "UPDATE style_cards SET card_location = 'slot$i' WHERE card_id = {$card['card_id']}"
        //         );
        //     }
        // }
    }

    /**
     * Create single cards
     */
    private function createSingleCards(): void
    {
        // // Create single cards with different attribute combinations
        // $attributes = self::MUSICAL_ATTRIBUTES;

        // for ($i = 1; $i <= 35; $i++) {
        //     $attr1 = $attributes[array_rand($attributes)];
        //     $attr2 = $attributes[array_rand($attributes)];
        //     $attr3 = $attributes[array_rand($attributes)];

        //     $this->DbQuery(
        //         "INSERT INTO single_cards (card_location, card_location_arg, attribute1, attribute2, attribute3) 
        //          VALUES ('deck', 0, '$attr1', '$attr2', '$attr3')"
        //     );
        // }
    }

    /**
     * Deal single cards
     */
    private function dealSingleCards(): void
    {
        // // Deal one card to each board position
        // for ($i = 1; $i <= 2; $i++) {
        //     $card = $this->getObjectFromDb(
        //         "SELECT * FROM single_cards WHERE card_location = 'deck' LIMIT 1"
        //     );

        //     if ($card) {
        //         $this->DbQuery(
        //             "UPDATE single_cards SET card_location = 'board$i' WHERE card_id = {$card['card_id']}"
        //         );
        //     }
        // }
    }



    /**
     * Deal soiree cards
     */
    private function dealSoireeCards(): void
    {
        // // Deal two cards to board positions
        // for ($i = 1; $i <= 2; $i++) {
        //     $card = $this->getObjectFromDb(
        //         "SELECT * FROM soiree_cards WHERE card_location = 'deck' LIMIT 1"
        //     );

        //     if ($card) {
        //         $this->DbQuery(
        //             "UPDATE soiree_cards SET card_location = 'board$i' WHERE card_id = {$card['card_id']}"
        //         );
        //     }
        // }
    }

    /**
     * Prepare cards for next round
     */
    private function prepareNextRoundCards(): void
    {
        // // Reset all cards to deck
        // $this->DbQuery("UPDATE style_cards SET card_location = 'deck' WHERE card_location != 'deck'");
        // $this->DbQuery("UPDATE single_cards SET card_location = 'deck' WHERE card_location != 'deck'");
        // $this->DbQuery("UPDATE soiree_cards SET card_location = 'deck' WHERE card_location != 'deck'");

        // // Deal new cards
        // $this->dealInitialStyleCards();
        // $this->dealSingleCards();
        // $this->dealSoireeCards();
    }

    /**
     * Compute and return the current game progression.
     */
    public function getGameProgression()
    {
        $currentRound = $this->getGameStateValue("current_round");
        return ($currentRound - 1) * 33; // 33% per round
    }

    /**
     * Gather all information about current game situation (visible by the current player).
     */
    protected function getAllDatas(): array
    {
        $result = [];
        $cards = [];
        foreach ($this->cards->getCardsInLocation('soiree_board', null, 'card_type_arg') as $i => $soiree) {
            $card = $this->soireeCards[$soiree['type_arg']];
            $card['index'] = $soiree['type_arg'];
            $card['position'] = $soiree['location_arg'];
            $cards[] = $card;
        }
        $result["soireeCards"] = $cards;
        $cards = [];
        foreach ($this->cards->getCardsInLocation('single_board', null, 'card_type_arg') as $i => $single) {
            $card = $this->singleCards[$single['type_arg']];
            $card['index'] = $single['type_arg'];
            $card['position'] = $single['location_arg'];
            $cards[] = $card;
        }
        $result["singleCards"] = $cards;
        $cards = [];
        foreach ($this->getCollectionFromDb("SELECT * from cards WHERE card_location LIKE 'style_slot_%'") as $i => $style) {
            $card = $this->styleCards[$style['card_type_arg']];
            $card['index'] = (int) $style['card_type_arg'];
            $card['stackPosition'] = (int) $style['card_location_arg'];
            $card['slotNumber'] = (int) $style['card_location'][strlen($style['card_location']) - 1];
            $cards[] = $card;
        }
        $result["styleCards"] = $cards;
        $cards = [];
        foreach ($this->cards->getCardsInLocation('player_hand') as $styleCard) {
            $card = $this->styleCards[$styleCard['type_arg']];
            $card['index'] = (int) $styleCard['type_arg'];
            $card['playerId'] = $styleCard['location_arg'];
            $cards[] = $card;
        }
        $result["playerCards"] = $cards;
        return $result;
        // $current_player_id = (int) $this->getCurrentPlayerId();

        // // Get information about players
        // $result["players"] = $this->getCollectionFromDb(
        //     "SELECT `player_id` `id`, `player_score` `score`, `player_first_player_token` `first_player` FROM `player`"
        // );

        // // Get style cards in slots
        // $result["styleSlots"] = [];
        // for ($i = 1; $i <= 5; $i++) {
        //     $result["styleSlots"][$i] = $this->getCollectionFromDb(
        //         "SELECT * FROM style_cards WHERE card_location = 'slot$i'"
        //     );
        // }

        // // Get single cards on board
        // $result["singleCards"] = $this->getCollectionFromDb(
        //     "SELECT * FROM single_cards WHERE card_location LIKE 'board%'"
        // );

        // // Get soiree cards on board
        // $result["soireeCards"] = $this->getCollectionFromDb(
        //     "SELECT * FROM soiree_cards WHERE card_location LIKE 'board%'"
        // );

        // // Get current player's cards
        // $result["myClothingCards"] = $this->getCollectionFromDb(
        //     "SELECT * FROM style_cards WHERE card_location = 'player_hand' AND card_location_arg = $current_player_id AND card_type = 'clothing'"
        // );

        // $result["myMusicalCards"] = $this->getCollectionFromDb(
        //     "SELECT sc.* FROM style_cards sc 
        //      INNER JOIN player_musical_cards pmc ON sc.card_id = pmc.card_id 
        //      WHERE pmc.player_id = $current_player_id"
        // );

        // $result["myOutfit"] = $this->getObjectFromDb(
        //     "SELECT * FROM player_outfits WHERE player_id = $current_player_id"
        // );

        // $result["mySingleTokens"] = $this->getObjectFromDb(
        //     "SELECT token_count FROM player_single_tokens WHERE player_id = $current_player_id"
        // );

        // // Get game state
        // $result["gameState"] = [
        //     "current_round" => $this->getGameStateValue("current_round"),
        //     "first_player_id" => $this->getGameStateValue("first_player_id"),
        //     "style_cards_dealt" => $this->getGameStateValue("style_cards_dealt"),
        //     "party_phase" => $this->getGameStateValue("party_phase"),
        // ];

        // return $result;
    }


    /**
     * Zombie turn handling
     */
    protected function zombieTurn(array $state, int $active_player): void
    {
        // $state_name = $state["name"];

        // if ($state["type"] === "activeplayer") {
        //     switch ($state_name) {
        //         case 'playerTurn':
        //             // Choose random available slot
        //             $args = $this->argPlayerTurn();
        //             if (!empty($args['availableSlots'])) {
        //                 $slot = $args['availableSlots'][array_rand($args['availableSlots'])];
        //                 $this->actChooseStyleSlot($slot);
        //             }
        //             break;
        //         case 'optionalActions':
        //             $this->actPassOptional();
        //             break;
        //         default:
        //             $this->gamestate->nextState("zombiePass");
        //             break;
        //     }
        //     return;
        // }

        // if ($state["type"] === "multipleactiveplayer") {
        //     // Choose random party
        //     $args = $this->argChooseParties();
        //     if (!empty($args['soireeCards'])) {
        //         $party = $args['soireeCards'][array_rand($args['soireeCards'])];
        //         $this->actChooseParty($party['card_id']);
        //     }
        //     return;
        // }

        // throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }

    /**
     * Get the HTML template for the game interface
     */
    public function getPage($page)
    {
        switch ($page) {
            case 'yachtrock':
                return 'yachtrock';
            default:
                return parent::getPage($page);
        }
    }

    public function stEndScore(): void
    {
        $this->gamestate->nextState("gameOver");
    }
}
