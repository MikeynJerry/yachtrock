# Yacht Rock - BoardGameArena Implementation

## Overview

This is a BoardGameArena (BGA) implementation of the **Yacht Rock** board game by Prospero Hall. Players take on the role of 1970s soft rock musicians in Southern California, scoring points over three rounds by creating swanky outfits, recording hit songs, and attending schmoozy parties.

## Game Rules Summary

### Objective

Score the most points over three rounds by:

- Collecting clothing style cards to create outfits
- Recording hit singles using musical style cards
- Attending parties to earn bonus points

### Game Flow

1. **Setup**: Deal style cards to 5 slots, single cards to board, and soirée cards face-down
2. **Player Turns**: Each player chooses a style slot, takes all cards, then deals new cards to that slot and adjacent slots
3. **Optional Actions**: Players may sell duplicate clothing cards (1 point each) and/or record singles
4. **Party Phase**: When style cards run out, players choose which party to attend
5. **Scoring**: Players score base points from clothing + bonus points from party attendance
6. **Next Round**: Reset cards, pass first player token to highest scorer, repeat

### Card Types

#### Style Cards

- **Clothing Style Cards**: Top, Bottom, Shoes, Sunglasses, Hat in Gold (3pts), Coral (1pt), Lavender (1pt), Teal (1pt)
- **Musical Style Cards**: 7 different musical attributes for recording singles

#### Single Cards

- Show 3 musical attributes required to record a hit
- Solo recording: 8 points (need all 3 attributes)
- Collaboration: 3 points each (need 2 different attributes)

#### Soirée Cards

- 3 types of bonus points: Color bonus, Record bonus, In Vogue bonus
- Players must choose party before seeing bonuses

## Implementation Features

### Backend (PHP)

- **Game States**: Complete state machine for game flow
- **Database Schema**: Tables for cards, players, outfits, and game state
- **Game Logic**: Card management, scoring, turn progression
- **Player Actions**: Slot selection, card selling, single recording, party choice

### Frontend (JavaScript/CSS)

- **Game Board**: Visual representation of style slots, single cards, and soirée cards
- **Player Areas**: Individual player displays with scores, outfits, and tokens
- **Interactive Elements**: Clickable slots, selectable cards, action buttons
- **Responsive Design**: Works on desktop and mobile devices

### Visual Design

- **1970s Aesthetic**: Retro color scheme with gold, coral, lavender, and teal
- **Card Design**: Distinct visual styles for different card types
- **Animations**: Hover effects, selections, and transitions
- **Modern UI**: Clean, intuitive interface with clear visual hierarchy

## File Structure

```
yachtrock/
├── modules/
│   └── php/
│       ├── Game.php          # Main game logic
│       └── yachtrock.tpl     # HTML template
├── gameinfos.inc.php         # Game metadata
├── states.inc.php            # Game state definitions
├── dbmodel.sql               # Database schema
├── yachtrock.js              # Client-side JavaScript
├── yachtrock.css             # Styling
└── README.md                 # This file
```

## Testing the Game

### Prerequisites

- BoardGameArena Studio access
- PHP development environment
- Modern web browser

### Setup Steps

1. **Upload to BGA Studio**: Place all files in your BGA Studio project
2. **Create Database**: The `dbmodel.sql` will create required tables
3. **Test Game Flow**: Use the debug functions to test different states
4. **Verify Actions**: Test all player actions and state transitions

### Debug Functions

- `debug_goToState(state_id)`: Jump to specific game state
- Use BGA Studio's built-in debugging tools

### Testing Scenarios

1. **Basic Game Flow**: Complete a full round from setup to scoring
2. **Card Interactions**: Test style slot selection, card selling, single recording
3. **Party Phase**: Test party selection and scoring
4. **Multiplayer**: Test with 2-6 players
5. **Edge Cases**: Test with maximum players, various card combinations

## Game States

1. **gameSetup** (2): Initialize game components
2. **playerTurn** (3): Player chooses style slot
3. **dealStyleCards** (4): Deal new cards to slots
4. **optionalActions** (5): Player may sell cards/record singles
5. **nextPlayer** (6): Move to next player or party phase
6. **chooseParties** (7): All players choose parties
7. **partyScoring** (8): Calculate and award party points
8. **roundEnd** (9): Check if game over or prepare next round
9. **prepareNextRound** (10): Reset for next round
10. **endScore** (98): Final scoring and game end

## Customization

### Adding New Cards

- Modify the card creation methods in `Game.php`
- Update the constants for card types and attributes
- Add new visual styles in CSS

### Changing Game Rules

- Modify state transitions in `states.inc.php`
- Update game logic in `Game.php`
- Adjust scoring calculations as needed

### Visual Modifications

- Update colors and styles in `yachtrock.css`
- Modify card layouts in the HTML template
- Add new animations or effects

## Known Limitations

- **Card Pool**: Currently uses simplified card generation (can be expanded)
- **AI Players**: Basic zombie mode implementation
- **Animations**: Limited to CSS transitions (can be enhanced with JavaScript)
- **Mobile**: Basic responsive design (can be improved)

## Future Enhancements

- **Enhanced Card Pool**: Full 74 style cards, 35 single cards, 18 soirée cards
- **Advanced AI**: Smarter computer players
- **Sound Effects**: 1970s music and sound effects
- **Achievements**: Unlockable content and statistics
- **Tournament Mode**: Competitive play features

## Support

For issues or questions:

1. Check the BGA Studio documentation
2. Review the game state machine
3. Test individual components
4. Use debug functions to isolate problems

## License

This implementation follows the BGA Studio framework and is intended for educational and development purposes.

---

**Yacht Rock** - Record the Hits, Live the Life, Be the Party! 🎸✨

TODOs:
The file gameoptions.inc.php is deprecated. If your gameoptions.json and gamepreferences.json files are correct, you can delete the gameoptions.inc.php file. Else, delete the JSON files and click "Reload game options configuration" to generate the JSON content you can then put on a gameoptions.json and gamepreferences.json file. Then you can safely delete the gameoptions.inc.php file.
The file stats.inc.php is deprecated. If your stats.json file is correct, you can delete the stats.inc.php file. Else, delete the JSON file and click "Reload statistics configuration" to generate the JSON content you can then put on a stats.json file. Then you can safely delete the stats.inc.php file.

The ajaxcall function is deprecated, check the bgaPerformAction function.
yachtrock/yachtrock.js:472 => this.ajaxcall('/yachtrock/yachtrock/act' + action.charAt(0).toUpperCase() + action.slice(1), data, this, function (result) {
