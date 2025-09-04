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
 * material.inc.php
 *
 * yachtrock material description
 *
 */

$this->materials = [
    // Style Cards
    'style_cards' => [
        'name' => clienttranslate('Style Cards'),
        'nametr' => self::_('Style Cards'),
        'type' => 'card',
        'number' => 74,
        'sprite' => 'style_card',
        'sprite_pos_x' => 3,
        'sprite_pos_y' => 0,
        'width' => 80,
        'height' => 120,
        'tooltip' => clienttranslate('Style cards include clothing and musical style cards'),
    ],

    // Single Cards
    'single_cards' => [
        'name' => clienttranslate('Single Cards'),
        'nametr' => self::_('Single Cards'),
        'type' => 'card',
        'number' => 35,
        'sprite' => 'single_cards', // Updated to match actual sprite file name
        'sprite_pos_x' => 0, // Starting position (card back)
        'sprite_pos_y' => 0,
        'width' => 80, // Display size (scaled down from 534px)
        'height' => 80,
        'sprite_width' => 534, // Actual sprite dimensions
        'sprite_height' => 534,
        'tooltip' => clienttranslate('Single cards show musical attributes needed to record hits'),
    ],

    // Soirée Cards
    'soiree_cards' => [
        'name' => clienttranslate('Soirée Cards'),
        'nametr' => self::_('Soirée Cards'),
        'type' => 'card',
        'number' => 18,
        'sprite' => 'soiree_card',
        'sprite_pos_x' => 3,
        'sprite_pos_y' => 2,
        'width' => 80,
        'height' => 120,
        'tooltip' => clienttranslate('Soirée cards represent different parties with scoring bonuses'),
    ],

    // Single Tokens
    'single_tokens' => [
        'name' => clienttranslate('Single Tokens'),
        'nametr' => self::_('Single Tokens'),
        'type' => 'token',
        'number' => 60,
        'sprite' => 'single_token',
        'sprite_pos_x' => 3,
        'sprite_pos_y' => 3,
        'width' => 30,
        'height' => 30,
        'tooltip' => clienttranslate('Single tokens represent recorded hit songs'),
    ],

    // Guitar Picks
    'guitar_picks' => [
        'name' => clienttranslate('Guitar Picks'),
        'nametr' => self::_('Guitar Picks'),
        'type' => 'token',
        'number' => 6,
        'sprite' => 'guitar_pick',
        'sprite_pos_x' => 3,
        'sprite_pos_y' => 4,
        'width' => 40,
        'height' => 40,
        'tooltip' => clienttranslate('Guitar picks are used to mark party choices'),
    ],

    // Score Cubes
    'score_cubes' => [
        'name' => clienttranslate('Score Cubes'),
        'nametr' => self::_('Score Cubes'),
        'type' => 'token',
        'number' => 6,
        'sprite' => 'score_cube',
        'sprite_pos_x' => 3,
        'sprite_pos_y' => 5,
        'width' => 20,
        'height' => 20,
        'tooltip' => clienttranslate('Score cubes track player scores on the score track'),
    ],
];
