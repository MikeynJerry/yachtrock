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
 * stats.inc.php
 *
 * yachtrock statistics description
 *
 */

$stats_type = [
    // Statistics global to each table
    "table" => [
        "style_cards_played" => [
            "id" => 10,
            "name" => totranslate("Style cards played"),
            "type" => "int"
        ],
        "singles_recorded" => [
            "id" => 11,
            "name" => totranslate("Singles recorded"),
            "type" => "int"
        ],
        "parties_attended" => [
            "id" => 12,
            "name" => totranslate("Parties attended"),
            "type" => "int"
        ],
    ],

    // Statistics existing for each player
    "player" => [
        "style_cards_collected" => [
            "id" => 10,
            "name" => totranslate("Style cards collected"),
            "type" => "int"
        ],
        "clothing_cards_sold" => [
            "id" => 11,
            "name" => totranslate("Clothing cards sold"),
            "type" => "int"
        ],
        "musical_cards_used" => [
            "id" => 12,
            "name" => totranslate("Musical style cards used"),
            "type" => "int"
        ],
        "singles_recorded_solo" => [
            "id" => 13,
            "name" => totranslate("Singles recorded solo"),
            "type" => "int"
        ],
        "singles_recorded_collaboration" => [
            "id" => 14,
            "name" => totranslate("Singles recorded in collaboration"),
            "type" => "int"
        ],
        "single_tokens_earned" => [
            "id" => 15,
            "name" => totranslate("Single tokens earned"),
            "type" => "int"
        ],
        "party_bonus_points" => [
            "id" => 16,
            "name" => totranslate("Party bonus points earned"),
            "type" => "int"
        ],
        "gold_cards_collected" => [
            "id" => 17,
            "name" => totranslate("Gold clothing cards collected"),
            "type" => "int"
        ],
        "outfits_completed" => [
            "id" => 18,
            "name" => totranslate("Complete outfits worn"),
            "type" => "int"
        ],
    ]
];
