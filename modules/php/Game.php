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

use Bga\Games\yachtrock\States\PlayerTurn;
use Bga\Games\yachtrock\Constants;

class Game extends \Bga\GameFramework\Table
{
    private const DATA_PATH = __DIR__ . "/../../data/";

    public $cards;
    public array $singleCards;
    public array $soireeCards;
    public array $styleCards;

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
            Constants::LABEL_ACTIVE_TURN_PLAYER => 12,
            Constants::LABEL_TURN_PHASE => 13,
            Constants::LABEL_REMAINING_OPTIONALS => 14
        ]);

        $this->cards = $this->deckFactory->createDeck("cards");

        $this->singleCards = $this->loadJson('single_cards.json');
        $this->soireeCards = $this->loadJson('soiree_cards.json');
        $this->styleCards  = $this->loadJson('style_cards.json');
    }

    private function loadJson(string $filename): array
    {
        $path = self::DATA_PATH . $filename;

        if (!is_file($path)) {
            throw new \RuntimeException("Missing data file: {$path}");
        }

        $content = file_get_contents($path);

        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException("Invalid JSON in {$path}: {$e->getMessage()}");
        }
    }

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

        $this->cards->shuffle('soiree_deck');
        $this->cards->shuffle('single_deck');
        $this->cards->shuffle('style_deck');
        $this->dealCards();


        $this->setGameStateInitialValue(Constants::LABEL_ACTIVE_TURN_PLAYER, -1);
        $this->setGameStateInitialValue(Constants::LABEL_TURN_PHASE, 1);
        $this->setGameStateInitialValue(
            Constants::LABEL_REMAINING_OPTIONALS,
            Constants::OPT_SELL | Constants::OPT_SINGLE
        );

        return PlayerTurn::class;
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

    public function getGameProgression()
    {
        $currentRound = $this->getGameStateValue("current_round");
        return ($currentRound - 1) * 33;
    }

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
    }

    public function getPage($page)
    {
        switch ($page) {
            case 'yachtrock':
                return 'yachtrock';
            default:
                return parent::getPage($page);
        }
    }
}
