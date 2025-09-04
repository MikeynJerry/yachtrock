# Single Cards Sprite Integration Guide

## Overview

This document describes the integration of your `img/single_cards.png` sprite sheet (534px x 534px cards) into the Yacht Rock BGA game.

## Files Modified

### 1. yachtrock.css

- Added comprehensive sprite system for single cards
- Each card in your sprite sheet can be displayed at 534px x 534px, scaled down to ~80px for gameplay
- The top-left position (0,0) is designated as the card back
- Added positioning classes for individual cards: `.single-card-0`, `.single-card-1`, etc.

### 2. yachtrock.js

- Updated `createSingleCard()` function to use sprites when `sprite_position` is available
- Added fallback to text-based cards for compatibility
- Added `createSingleCardBack()` for face-down cards
- Added helper functions:
  - `getSingleCardSpriteClass(cardIndex)` - gets the appropriate CSS class
  - `createSingleCardWithSprite(cardId, spriteIndex)` - creates a card with specific sprite

### 3. material.inc.php

- Updated single_cards configuration to match the actual sprite file
- Added sprite dimensions and positioning information

## How to Use

### Adding New Card Positions

To add a new card position in your sprite sheet:

1. **Determine Position**: Count the card position starting from 0 (top-left is card back)
2. **Calculate CSS Position**:
   - X: -(column_number \* 534)px
   - Y: -(row_number \* 534)px
3. **Add CSS Class**:
   ```css
   .single-card-N {
     background-position: Xpx Ypx;
   }
   ```

### Example Grid Layout (5 cards per row)

```
Position 0 (back): 0, 0
Position 1: -534px, 0
Position 2: -1068px, 0
Position 3: -1602px, 0
Position 4: -2136px, 0
Position 5: 0, -534px
Position 6: -534px, -534px
...and so on
```

### JavaScript Usage

```javascript
// Create a card with sprite position 5
const card = this.createSingleCardWithSprite("card_123", 5);

// Create a card back
const cardBack = this.createSingleCardBack();

// Get sprite class name
const spriteClass = this.getSingleCardSpriteClass(3); // Returns 'single-card-sprite single-card-3'
```

## Current Sprite Positions Defined

- `.single-card-back` - Card back (position 0,0)
- `.single-card-0` through `.single-card-10` - First 11 card positions

## Next Steps

1. **Map Your Cards**: Create a mapping between your game's card data and sprite positions
2. **Extend Positions**: Add more `.single-card-N` classes as needed for your full deck
3. **Update Game Logic**: Modify your PHP backend to include `sprite_position` data for each card
4. **Test Integration**: Verify cards display correctly in your game

## Notes

- The system supports both sprite-based and text-based cards for flexibility
- Cards are automatically scaled from 534px to ~80px for optimal game display
- The sprite system is optimized for fast loading and good performance
