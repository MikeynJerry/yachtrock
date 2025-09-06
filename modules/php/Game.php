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
        $this->cards->init("soiree_deck");

        $this->soiree_cards = json_decode(file_get_contents(__DIR__ . "/../../soiree_cards.json"), true);
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

    private function createCards(): void
    {

        $cards = [];
        foreach ($this->soiree_cards as $i => $soiree) {
            $cards[] = [
                'type'     => 'soiree',
                'type_arg' => $i,
                'nbr'      => 1,
            ];
        }

        $this->cards->createCards($cards, 'soiree_deck');
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
        // $player_id = $this->getActivePlayerId();

        // // Get player's clothing cards
        // $clothingCards = $this->getCollectionFromDb(
        //     "SELECT * FROM style_cards WHERE card_location = 'player_hand' AND card_location_arg = $player_id AND card_type = 'clothing'"
        // );

        // // Get available single cards
        // $singleCards = $this->getCollectionFromDb(
        //     "SELECT * FROM single_cards WHERE card_location LIKE 'board%'"
        // );

        // // Get player's musical style cards
        // $musicalCards = $this->getCollectionFromDb(
        //     "SELECT sc.* FROM style_cards sc 
        //      INNER JOIN player_musical_cards pmc ON sc.card_id = pmc.card_id 
        //      WHERE pmc.player_id = $player_id"
        // );

        // return [
        //     'clothingCards' => $clothingCards,
        //     'singleCards' => $singleCards,
        //     'musicalCards' => $musicalCards
        // ];
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
    public function actChooseStyleSlot(int $slot_number): void
    {
        // $player_id = (int)$this->getActivePlayerId();

        // // Check if slot is valid
        // $args = $this->argPlayerTurn();
        // if (!in_array($slot_number, $args['availableSlots'])) {
        //     throw new \BgaUserException('Invalid slot choice');
        // }

        // // Take all cards from the chosen slot
        // $cards = $this->getCollectionFromDb(
        //     "SELECT * FROM style_cards WHERE card_location = 'slot{$slot_number}'"
        // );

        // foreach ($cards as $card) {
        //     if ($card['card_type'] == 'clothing') {
        //         // Add to player's hand
        //         $this->DbQuery(
        //             "UPDATE style_cards SET card_location = 'player_hand', card_location_arg = $player_id WHERE card_id = {$card['card_id']}"
        //         );
        //     } else {
        //         // Add musical style card to player's collection
        //         $this->DbQuery(
        //             "INSERT INTO player_musical_cards (player_id, card_id) VALUES ($player_id, {$card['card_id']})"
        //         );
        //     }
        // }

        // // Store the chosen slot for dealing new cards
        // $this->setGameStateValue("last_chosen_slot", $slot_number);

        // // Notify about cards taken
        // $this->notify->all("cardsTaken", clienttranslate('${player_name} takes cards from style slot ${slot_number}'), [
        //     "player_id" => $player_id,
        //     "player_name" => $this->getActivePlayerName(),
        //     "slot_number" => $slot_number,
        //     "cards" => $cards
        // ]);

        // $this->gamestate->nextState("styleSlotChosen");
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
     * Game setup
     */
    public function stGameSetup(): void
    {
        // // Very basic setup - just initialize game state variables
        // $this->setGameStateInitialValue("current_round", 1);
        // $this->setGameStateInitialValue("style_cards_dealt", 0);
        // $this->setGameStateInitialValue("party_phase", 0);
        // $this->setGameStateInitialValue("last_chosen_slot", 0);

        // // Set a default first player (will be updated later)
        // $this->setGameStateInitialValue("first_player_id", 0);

        // // Transition to next state immediately
        // $this->gamestate->nextState("setupComplete");
    }

    /**
     * Deal new style cards after slot is chosen
     */
    public function stDealStyleCards(): void
    {
        // // Get the last chosen slot from game state
        // $lastChosenSlot = $this->getGameStateValue("last_chosen_slot");

        // if ($lastChosenSlot) {
        //     // Deal one card to the chosen slot
        //     $this->dealCardToSlot($lastChosenSlot);

        //     // Deal one card to adjacent slots (left and right)
        //     $leftSlot = ($lastChosenSlot == 1) ? 5 : $lastChosenSlot - 1;
        //     $rightSlot = ($lastChosenSlot == 5) ? 1 : $lastChosenSlot + 1;

        //     $this->dealCardToSlot($leftSlot);
        //     $this->dealCardToSlot($rightSlot);

        //     // Update style cards dealt count
        //     $currentDealt = $this->getGameStateValue("style_cards_dealt");
        //     $this->setGameStateValue("style_cards_dealt", $currentDealt + 3);
        // }

        // $this->gamestate->nextState("cardsDealt");
    }

    /**
     * Helper method to deal a card to a specific slot
     */
    private function dealCardToSlot(int $slotNumber): void
    {
        // $card = $this->getObjectFromDb(
        //     "SELECT * FROM style_cards WHERE card_location = 'deck' LIMIT 1"
        // );

        // if ($card) {
        //     $this->DbQuery(
        //         "UPDATE style_cards SET card_location = 'slot{$slotNumber}' WHERE card_id = {$card['card_id']}"
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
     * Create style cards for the game
     */
    private function createStyleCards(int $playerCount): void
    {
        // // This would create the actual style cards based on the game components
        // // For now, creating placeholder cards
        // $cardId = 1;

        // // Clothing cards
        // foreach (self::CLOTHING_TYPES as $clothingType) {
        //     foreach (self::CARD_COLORS as $color) {
        //         $points = ($color == 'gold') ? 3 : 1;
        //         $this->DbQuery(
        //             "INSERT INTO style_cards (card_id, card_type, card_type_arg, card_location, card_location_arg, card_color, card_clothing_type, card_points, card_musical_attribute) 
        //              VALUES ($cardId, 'clothing', 1, 'deck', 0, '$color', '$clothingType', $points, 'none')"
        //         );
        //         $cardId++;
        //     }
        // }

        // // Musical style cards
        // foreach (self::MUSICAL_ATTRIBUTES as $attribute) {
        //     $this->DbQuery(
        //         "INSERT INTO style_cards (card_id, card_type, card_type_arg, card_location, card_location_arg, card_color, card_clothing_type, card_points, card_musical_attribute) 
        //          VALUES ($cardId, 'musical', 2, 'deck', 0, 'none', 'none', 0, '$attribute')"
        //     );
        //     $cardId++;
        // }
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
        foreach ($this->cards->getCardsInLocation('soiree_deck', null, 'card_type_arg') as $i => $soiree) {
            $cards[] = $this->soiree_cards[$soiree['type_arg']];
        }
        $result["soireeCards"] = $cards;
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
