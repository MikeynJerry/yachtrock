-- ------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- yachtrock implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----
-- dbmodel.sql
-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here
-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.
-- Create tables for Yacht Rock game
-- Style cards table (clothing and musical style cards)
CREATE TABLE IF NOT EXISTS `style_cards` (
    `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `card_type` varchar(16) NOT NULL,
    -- 'clothing' or 'musical'
    `card_type_arg` int(11) NOT NULL,
    -- specific type identifier
    `card_location` varchar(16) NOT NULL,
    -- 'deck', 'slot1', 'slot2', 'slot3', 'slot4', 'slot5', 'player_hand', 'discard'
    `card_location_arg` int(11) NOT NULL,
    -- player_id if in hand, slot number if in slot
    `card_color` varchar(16) NOT NULL,
    -- 'gold', 'coral', 'lavender', 'teal', 'none' for musical
    `card_clothing_type` varchar(16) NOT NULL,
    -- 'top', 'bottom', 'shoes', 'sunglasses', 'hat', 'none' for musical
    `card_points` int(11) NOT NULL DEFAULT 0,
    `card_musical_attribute` varchar(32) NOT NULL,
    -- musical attribute for musical style cards
    PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- Single cards table (hit records to record)
CREATE TABLE IF NOT EXISTS `single_cards` (
    `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `card_location` varchar(16) NOT NULL,
    -- 'deck', 'board1', 'board2', 'recorded'
    `card_location_arg` int(11) NOT NULL,
    -- board position or player_id if recorded
    `attribute1` varchar(32) NOT NULL,
    `attribute2` varchar(32) NOT NULL,
    `attribute3` varchar(32) NOT NULL,
    PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- Soirée cards table (party cards)
CREATE TABLE IF NOT EXISTS `soiree_deck` (
    `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `card_type` varchar(16) NOT NULL,
    -- 'soiree'
    `card_type_arg` int(11) NOT NULL,
    `card_location` varchar(16) NOT NULL,
    -- 'deck', 'board1', 'board2'
    `card_location_arg` int(11) NOT NULL,
    PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- Player outfits table (what clothing each player is wearing)
CREATE TABLE IF NOT EXISTS `player_outfits` (
    `player_id` int(10) unsigned NOT NULL,
    `top_card_id` int(10) unsigned NULL,
    `bottom_card_id` int(10) unsigned NULL,
    `shoes_card_id` int(10) unsigned NULL,
    `sunglasses_card_id` int(10) unsigned NULL,
    `hat_card_id` int(10) unsigned NULL,
    PRIMARY KEY (`player_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
-- Player musical style cards table
CREATE TABLE IF NOT EXISTS `player_musical_cards` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `player_id` int(10) unsigned NOT NULL,
    `card_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `player_id` (`player_id`),
    KEY `card_id` (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- Player single tokens table
CREATE TABLE IF NOT EXISTS `player_single_tokens` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `player_id` int(10) unsigned NOT NULL,
    `token_count` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `player_id` (`player_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- Game state table
CREATE TABLE IF NOT EXISTS `game_state` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `current_round` int(11) NOT NULL DEFAULT 1,
    `first_player_id` int(10) unsigned NOT NULL,
    `style_cards_dealt` int(11) NOT NULL DEFAULT 0,
    `party_phase` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
-- Add custom fields to player table
ALTER TABLE `player`
ADD `player_first_player_token` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `player`
ADD `player_style_cards_sold` INT UNSIGNED NOT NULL DEFAULT '0';