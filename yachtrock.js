/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * yachtrock implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * yachtrock.js
 *
 * yachtrock user interface script
 *
 */

function getScaleFactor(element) {
    const style = window.getComputedStyle(element);
    const transform = style.transform || style.webkitTransform;
    if (transform && transform !== "none") {
        const match = transform.match(/matrix\((.+)\)/);
        if (match) {
            const values = match[1].split(','); // matrix(a, b, c, d, e, f)
            const scaleX = parseFloat(values[0]);
            const scaleY = parseFloat(values[3]);
            return { scaleX, scaleY };
        }
    }
    return { scaleX: 1, scaleY: 1 };
}


class PlayerHand {
    constructor(cards) {
        this.cards = cards;
    }

    count(value) {
        return this.cards.filter(card => card.value === value).length
    }
}

function itemIdToCoordsVerticalFit(i, control_width, control_height, items_nbr) {
    const styleCardRect = document.getElementsByClassName("style-card")[0].getBoundingClientRect();
    const clothingCardDiv = document.getElementById(`player-area-clothing-cards-${this.playerId}`);

    // Use a fraction of the current rendered card height for overlap
    const overlapFraction = 0.67; // 30% overlap
    const verticalSpacing = styleCardRect.height * (1 - overlapFraction);

    const res = {
        w: styleCardRect.width,
        h: styleCardRect.height,
        x: 0,
        y: Math.round(i * verticalSpacing)
    };

    const totalHeight = (items_nbr - 1) * verticalSpacing + styleCardRect.height;

    // Set minHeight to at least totalHeight
    clothingCardDiv.style.minHeight = Math.max(
        clothingCardDiv.getBoundingClientRect().height,
        totalHeight
    ) + "px";

    return res;
}





define([
    "dojo",
    "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock",
    "ebg/zone"
],
function (dojo, declare, gamegui, counter, stock, zone) {
    return declare("bgagame.yachtrock", gamegui, {
        constructor: function () {
            this.styleCardSlots = [[], [], [], [], []]
            this.singleCards = [];
            this.soireeCards = [];
            this.playerHands = {};
            this.globalZones = {};
            this.playerZones = {};
            zone.prototype.itemIdToCoordsVerticalFit = itemIdToCoordsVerticalFit
        },

        setup: function(gamedatas) {
            console.log("Yacht Rock game setup", gamedatas);

            const globalZoneNames = ["style_slot_1", "style_slot_2", "style_slot_3", "style_slot_4", "style_slot_5"]
            for (let globalZoneName of globalZoneNames) this.globalZones[globalZoneName] = new zone()

            for (let playerId of Object.keys(gamedatas.players)){ 
                this.playerHands[playerId] = new PlayerHand([])
                const playerZoneNames = ["musical", "tokens", "singles", "clothing_hat", "clothing_sunglasses", "clothing_top", "clothing_bottom", "clothing_shoes"];
                this.playerZones[playerId] = {}
                for (let playerZoneName of playerZoneNames) {
                    let playerZone = new zone()
                    playerZone.playerId = playerId
                    this.playerZones[playerId][playerZoneName] = playerZone
                }
            }

            for (let card of gamedatas.playerCards) {
                this.playerHands[card.playerId].cards.push(card)
            }
            this.initializeGameAreas();
            this.initializePlayerAreas(gamedatas.players, gamedatas.playerorder);
            this.createBoard();
            this.createStyleCardStacks();
            this.showSingleCards(gamedatas.singleCards);
            this.showSoireeCards(gamedatas.soireeCards);
            this.initializeStyleCards(gamedatas.styleCards);
            this.setupNotifications();
        },

        setupNotifications: function () {
            this.bgaSetupPromiseNotifications();
        },

        createBoard: function () {
            const parent = document.getElementById('board-area');
            parent.insertAdjacentHTML('beforeend', `
                <div id="board">
                </div>
            `);
        },

        createStyleCardStacks: function () {
            const parent = document.getElementById('board');
            for (let i = 1; i <= 5; i++) {
                parent.insertAdjacentHTML('beforeend', `<div id="style-card-stack-${i}" class="style-card-stack"></div>`)
            }
        },

        showSingleCards: function (singleCards) {
            console.log("Showing single cards", singleCards);
            const parent = document.getElementById('board');
            for (let i = 0; i < singleCards.length; i++) {
                parent.insertAdjacentHTML('beforeend', `
                    <div id="single-card-${singleCards[i].position}" class="single-card single-card-${singleCards[i].index} single-card-pos-${singleCards[i].position}">
                    </div>
                `);
            }
        },

        showSoireeCards: function (soireeCards) {
            const parent = document.getElementById('board');
            for (let i = 0; i < soireeCards.length; i++) {
                parent.insertAdjacentHTML('beforeend', `
                    <div id="soiree-card-${soireeCards[i].position}" class="soiree-card-back soiree-card-${soireeCards[i].index} soiree-card-pos-${soireeCards[i].position}">
                    </div>
                `);
            }
        },

        initializeStyleCards: function (styleCards) {
            for (let styleCard of styleCards) {
                this.styleCardSlots[styleCard.slotNumber - 1].push(styleCard)
            }
            for (let styleCardSlot of this.styleCardSlots) {
                styleCardSlot.sort((a, b) => a.stackPosition - b.stackPosition)
            }
            this.updateStyleCards()
        },

        updateStyleCards: function () {
            const stackOffset = 32; // vertical shift per card
            let tallestStack = 0;   // track bottom edge of tallest stack

            for (let slotNumber = 0; slotNumber < this.styleCardSlots.length; slotNumber++) {
                const parent = document.getElementById(`style-card-stack-${slotNumber + 1}`);
                if (!parent) continue;

                const cardsInSlot = this.styleCardSlots[slotNumber];
                const seenIds = new Set();

                for (let styleCard of cardsInSlot) {
                    const styleCardId = `style-card-${styleCard.index}`;
                    let cardDiv = document.getElementById(styleCardId);

                    if (!cardDiv) {
                        cardDiv = dojo.create('div', {
                            id: styleCardId,
                            className: 'style-card',
                        }, parent);
                    }

                    // absolutely position the card inside its stack
                    cardDiv.style.position = 'absolute';
                    cardDiv.style.top = `${styleCard.stackPosition * stackOffset}px`;
                    cardDiv.style.zIndex = styleCard.stackPosition + 1;
                    cardDiv.dataset.position = styleCard.stackPosition;

                    seenIds.add(styleCardId);
                }

                // remove old cards not in the slot anymore
                Array.from(parent.querySelectorAll('.style-card')).forEach(child => {
                    if (!seenIds.has(child.id)) {
                        child.remove();
                    }
                });

                // update stack container height
                const cards = parent.querySelectorAll('.style-card');
                const stackHeight =
                    cards.length > 0
                        ? (cards.length - 1) * stackOffset + cards[0].offsetHeight
                        : 0;
                parent.style.height = stackHeight + 'px';

                // track tallest stack bottom relative to #board
                tallestStack = Math.max(tallestStack, parent.offsetTop + stackHeight);
            }

            // -------------------------
            // SPACER: push #player-areas below overflowing stacks
            // -------------------------
            const playerAreas = document.getElementById('player-areas');
            if (playerAreas) {
                let spacer = document.getElementById('board-spacer-before-player-areas');
                if (!spacer) {
                    spacer = document.createElement('div');
                    spacer.id = 'board-spacer-before-player-areas';
                    spacer.style.width = '100%';
                    spacer.style.pointerEvents = 'none';
                    spacer.style.visibility = 'invisible'; // invisible but takes space
                    playerAreas.parentNode.insertBefore(spacer, playerAreas);
                }

                const board = document.getElementById('board');
                const boardRect = board.getBoundingClientRect();
                const boardHeight = boardRect.height; // use rect height for consistency

                // compute maximum bottom of any stack relative to the top of the board
                let maxBottomInsideBoard = 0;

                for (let slotNumber = 0; slotNumber < this.styleCardSlots.length; slotNumber++) {
                    const parent = document.getElementById(`style-card-stack-${slotNumber + 1}`);
                    if (!parent) continue;

                    const parentRect = parent.getBoundingClientRect();

                    // bottomInsideBoard is how many pixels from the top of #board the bottom of this stack is
                    maxBottomInsideBoard = Math.max(maxBottomInsideBoard, parentRect.bottom - boardRect.top)
                }

                // Spacer = extra needed beyond board’s fixed height
                const extraNeeded = Math.max(0, Math.ceil(maxBottomInsideBoard - boardHeight));
                spacer.style.height = (extraNeeded + 10) + 'px'; // +10px padding
            }

            // reconnect card click handler
            dojo.query('.style-card').connect('onclick', this, 'onStyleCardClick');
        },


        verticalOverlapPattern(offset) {
            return {
                pattern: (index, element, total) => {
                    return {
                        x: 0,
                        y: index * offset,  // 30px spacing per card
                        zIndex: index + 1,
                    };
                },
                // tell the zone how to size itself
                updateDisplay: (container, patternData, itemCount) => {
                    container.style.height = (itemCount * offset + 50) + 'px';
                }
            };
        },

        onStyleCardClick: function (evt) {
            dojo.stopEvent(evt);

            const parentId = evt.currentTarget.parentNode.id; // e.g. "style-card-stack-2"
            const slotNumber = parseInt(parentId.replace('style-card-stack-', ''));

            console.log('onStyleCardClick slotNumber', slotNumber);

            if (this.checkAction('actChooseStyleSlot', true)) {
                this.bgaPerformAction('actChooseStyleSlot', { slotNumber: slotNumber });
            } else {
                this.showMoveUnauthorized();
            }
        },

        initializeGameAreas: function () {
            const gameArea = document.getElementById('page-content');
            gameArea.insertAdjacentHTML('beforeend', `
                <div id="active-area">
                </div>
            `);
            gameArea.insertAdjacentHTML('beforeend', `
                <div id="board-area">
                </div>
            `);
            gameArea.insertAdjacentHTML('beforeend', `
                <div id="player-areas">
                </div>
            `);

            gameArea.insertAdjacentHTML('beforeend', `
                    <div class="singles-recorded-area">
                        <p>Recorded Singles</p> 
                    </div>
            `);
        },

        // Initialize player areas
        initializePlayerAreas: function (players, playerOrder) {
            const playerAreas = document.getElementById('player-areas');
            
            // Apply token sprite background positions
            const tokenSpriteIcon = { // icon for sidebar
                "6B0095": "0px -132px", // purple
                "006078": "-13px -132px", // blue
                "506400": "-26px -132px", // green
                "8A0007": "-39px -132px", // red
                "7D5500": "-52px -132px", // yellow
                "BB0051": "-65px -132px" // pink
            };

            const tokenSpriteMain = { // larger token for player board
                "6B0095": "0px 0px", // purple
                "006078": "-72px 0px", // blue
                "506400": "-144px 0px", // green
                "8A0007": "-216px 0px", // red
                "7D5500": "-288px 0px", // yellow
                "BB0051": "-360px 0px" // pink
            };

            const firstPlayerId = playerOrder[0];

            for (let playerId of playerOrder) { // this is what chatgpt calls the "player setup loop"
                const playerName = players[playerId].name

                // Insert player area HTML
                playerAreas.insertAdjacentHTML('beforeend',
                    `<div class="player-area-main" id="player-area-${playerId}">
                        <p class="player-area-name">${playerName}'s cards</p>
                        <div class="player-area-flex">
                            <div class="player-area-cards">
                                <div id="player-area-clothing-cards-${playerId}" class="player-area-clothing-cards">
                                    <div id="clothing-slot-hat-${playerId}" class="clothing-slot clothing-slot-hat"></div>
                                    <div id="clothing-slot-sunglasses-${playerId}" class="clothing-slot clothing-slot-sunglasses"></div>
                                    <div id="clothing-slot-top-${playerId}" class="clothing-slot clothing-slot-top"></div>
                                    <div id="clothing-slot-bottom-${playerId}" class="clothing-slot clothing-slot-bottom"></div>
                                    <div id="clothing-slot-shoes-${playerId}" class="clothing-slot clothing-slot-shoes"></div>
                                </div>
                                <div id="player-area-musical-cards-${playerId}" class="player-area-musical-cards">
                                </div>
                            </div>
                            <div id="player-area-tokens-${playerId}" class="player-area-tokens">
                                <div class="player-tokens-scaled-container"></div>
                            </div>
                        </div>
                    </div>`
                );

                // Create zones for the style card areas in player hands
                const clothingTypes = ["hat", "sunglasses", "top", "bottom", "shoes"]
                for (let clothingType of clothingTypes) {
                    this.playerZones[playerId][`clothing_${clothingType}`].create(this, `clothing-slot-${clothingType}-${playerId}`, 80.43, 105.36);
                    this.playerZones[playerId][`clothing_${clothingType}`].setPattern('verticalfit');
                }

                this.playerZones[playerId]["musical"].create(this, `player-area-musical-cards-${playerId}`, 100, 75);
                this.playerZones[playerId]["musical"].setPattern('horizontalfit', 'max-width', '150px', { align: 'left' });


                // Insert token icon next to icon_point (the points star icon) in sidebar
                const playerTokenIcon = document.getElementById(`icon_point_${playerId}`);
                if (playerTokenIcon) {
                    playerTokenIcon.insertAdjacentHTML(
                        'beforeend',
                        `<div class="player_token_icon" id="player_token_icon_${playerId}"></div>`
                    );
                }

                // Insert main player token in player board
                const playerTokenContainer = document.querySelector(`#player-area-${playerId} .player-tokens-scaled-container`);
                if (playerTokenContainer) {
                    playerTokenContainer.insertAdjacentHTML(
                        'beforeend',
                        `<div class="player-token-container"><div class="player_token_main" id="player_token_main_${playerId}"></div></div>`
                    );

                    // Add first-player token if this is the first player
                    if (playerId == firstPlayerId) {
                        playerTokenContainer.insertAdjacentHTML(
                            'beforeend',
                            `<div class="first-player-container"><div class="first-player-token"></div></div>`
                        );
                    }
                }

                // use the color assigned once per player
                const assignedColor = players[playerId].color;

                const smallTokenEl = document.getElementById(`player_token_icon_${playerId}`);
                        if (smallTokenEl) smallTokenEl.style.backgroundPosition = tokenSpriteIcon[assignedColor];

                const largeTokenEl = document.getElementById(`player_token_main_${playerId}`);
                        if (largeTokenEl) largeTokenEl.style.backgroundPosition = tokenSpriteMain[assignedColor];


                // Insert player info box HTML.  (FYI use ${playerName} in HTML for player name variable)
                const playerInfoBox = document.getElementById(`player_board_${playerId}`);
                playerInfoBox.insertAdjacentHTML(
                    'beforeend',
                    `<div class="custom-player-area">
                        <table class="player-infobox-musical-count">
                            <tr>
                                <th class="infobox_musical_purple_icon infobox_icons"></th>
                                <th class="infobox_musical_icon_pink infobox_icons"></th>
                                <th class="infobox_musical_icon_orange infobox_icons"></th>
                                <th class="infobox_musical_icon_red infobox_icons"></th>
                                <th class="infobox_musical_icon_green infobox_icons"></th>
                                <th class="infobox_musical_icon_navy infobox_icons"></th>
                                <th class="infobox_musical_icon_blue infobox_icons"></th>
                            </tr>
                            <tr>
                                <td>${this.playerHands[playerId].count('duet')}</td>
                                <td>${this.playerHands[playerId].count('anthem')}</td>
                                <td>${this.playerHands[playerId].count('beat')}</td>
                                <td>${this.playerHands[playerId].count('refrain')}</td>
                                <td>${this.playerHands[playerId].count('guitar')}</td>
                                <td>${this.playerHands[playerId].count('saxophone')}</td>
                                <td>${this.playerHands[playerId].count('lyrics')}</td>
                            </tr>
                        </table>

                        <table class="player-infobox-clothing-singles-count">
                            <tr>
                                <td class="infobox_clothing_icon_hat infobox_icons"></td>
                                <td class="infobox_clothing_icon_sunglasses infobox_icons"></td>
                                <td class="infobox_clothing_icon_top infobox_icons"></td>
                                <td class="infobox_clothing_icon_bottom infobox_icons"></td>
                                <td class="infobox_clothing_icon_shoes infobox_icons"></td>
                                <td align="right">${this.playerHands[playerId].count('singles')}</td>
                                <td class="infobox_icon_singles infobox_icons"></td>
                            </tr>
                            
                        </table>                
                    </div>`
                );
            };
        },

        notif_styleCardsTaken: function (args) {
            // Loop over all cards in the taken slot
            for (let styleCard of this.styleCardSlots[args.slotNumber - 1]) {
                let playerZoneName = styleCard.type == "musical" ?  "musical" : `clothing_${styleCard.value}` //finds the name of the zone it needs to go in, based on the type and value of the card and stores it as the variable playerZoneName
                let theZoneItself = this.playerZones[args.playerId][playerZoneName] //finds that specific zone object for the specific player in the playerZones dictionary, and stores it as the variable theZoneItself (which is a div)
                let cardId = `style-card-${styleCard.index}`
                theZoneItself.placeInZone(cardId); //tells the card to be placed in the appropriate zone
                cardDiv = document.getElementById(cardId)
                cardDiv.style.zIndex = null
            }

            // Clear the slot and refresh stacks
            this.styleCardSlots[args.slotNumber - 1] = [];
            this.updateStyleCards();
        },

        notif_styleCardsDealt: function (args) {
            for (const styleCard of args.styleCards) {
                this.styleCardSlots[styleCard.slotNumber - 1].push(styleCard)
            }
            this.updateStyleCards()
        },
    
        // Update single cards
        updateSingleCards: function (singleCards) {
            singleCards.forEach((card, index) => {
                const cardElement = document.getElementById('single-card-' + (index + 1));
                if (cardElement) {
                    cardElement.querySelector('.attribute:nth-child(1)').textContent = card.attribute1;
                    cardElement.querySelector('.attribute:nth-child(2)').textContent = card.attribute2;
                    cardElement.querySelector('.attribute:nth-child(3)').textContent = card.attribute3;
                }
            });
        },
    
        // Update soirée cards
        updateSoireeCards: function (soireeCards) {
            soireeCards.forEach((card, index) => {
                const cardElement = document.getElementById('soiree-card-' + (index + 1));
                if (cardElement) {
                    cardElement.querySelector('.card-title').textContent = card.party_name;
                    cardElement.querySelector('.color-bonus').textContent = card.color_bonus + ' +' + card
                        .color_bonus_points;
                    cardElement.querySelector('.record-bonus').textContent = 'Record +' + card.record_bonus_points;
                    cardElement.querySelector('.in-vogue-bonus').textContent = card.in_vogue_bonus + ' +' + card
                        .in_vogue_bonus_points;
                }
            })
        },

        // Update player scores
        updatePlayerScores: function (players) {
            players.forEach(player => {
                const scoreElement = document.getElementById('player-score-' + player.id);
                if (scoreElement) {
                    scoreElement.textContent = player.score;
                }
            });
        },

        onEnteringState: function (stateName, args) {
            console.log('Entering state: ' + stateName);
            
            switch (stateName) {
                case 'playerTurn':
                    //this.setupPlayerTurn(args);
                    break;
                case 'optionalActions':
                    //this.setupOptionalActions(args);
                    break;
                case 'chooseParties':
                    //this.setupChooseParties(args);
                    break;
                case 'partyScoring':
                    //this.setupPartyScoring(args);
                    break;
            }
        },

        onLeavingState: function (stateName) {
            console.log('Leaving state: ' + stateName);
            
            // Clear any active selections
            //this.clearSelections();
        },

        setupPlayerTurn: function (args) {
            this.clearSelections();
            
            // Enable style slot selection
            if (args.availableSlots) {
                args.availableSlots.forEach(slot => {
                    dojo.addClass('style-slot-' + slot, 'selectable');
                    dojo.connect(dojo.byId('style-slot-' + slot), 'onclick', this, 'onStyleSlotClick');
                });
            }
        },

        setupOptionalActions: function (args) {

            console.log(args.args);
            return


            // Show sell clothing button if player has clothing cards
            if (args.clothingCards && args.clothingCards.length > 0) {
                dojo.style('sell-clothing-btn', 'display', 'block');
                dojo.connect(dojo.byId('sell-clothing-btn'), 'onclick', this, 'onSellClothingClick');
            }
            
            // Show record single button if player has musical cards
            if (args.musicalCards && args.musicalCards.length > 0) {
                dojo.style('record-single-btn', 'display', 'block');
                dojo.connect(dojo.byId('record-single-btn'), 'onclick', this, 'onRecordSingleClick');
            }
            
            // Show pass button
            dojo.style('pass-optional-btn', 'display', 'block');
            dojo.connect(dojo.byId('pass-optional-btn'), 'onclick', this, 'onPassOptionalClick');
        },

        setupChooseParties: function (args) {
            this.clearSelections();
            
            // Enable party selection for all players
            if (args.soireeCards) {
                args.soireeCards.forEach(card => {
                    dojo.addClass('soiree-card-' + card.card_id, 'selectable');
                    dojo.connect(dojo.byId('soiree-card-' + card.card_id), 'onclick', this, 'onSoireeCardClick');
                });
            }
        },

        setupPartyScoring: function (args) {
            // Show scoring animation
            this.showScoringAnimation();
        },

        onStyleSlotClick: function (evt) {
            const slotId = evt.currentTarget.id.replace('style-slot-', '');
            this.selectedSlot = parseInt(slotId);
            
            // Highlight selected slot
            dojo.removeClass('style-slot-' + slotId, 'selectable');
            dojo.addClass('style-slot-' + slotId, 'selected');
            
            // Confirm selection
            this.confirmAction('chooseStyleSlot', slotId);
        },

        onSellClothingClick: function (evt) {
            // Show clothing card selection
            this.showClothingSelection();
        },

        onRecordSingleClick: function (evt) {
            // Show single recording interface
            this.showSingleRecording();
        },

        onPassOptionalClick: function (evt) {
            this.confirmAction('passOptional');
        },

        onSoireeCardClick: function (evt) {
            const cardId = evt.currentTarget.id.replace('soiree-card-', '');
            
            // Highlight selected party
            dojo.removeClass('soiree-card-' + cardId, 'selectable');
            dojo.addClass('soiree-card-' + cardId, 'selected');
            
            // Confirm party choice
            this.confirmAction('chooseParty', cardId);
        },

        showClothingSelection: function () {
            // Create modal for clothing selection
            const modal = dojo.create('div', {
                id: 'clothing-selection-modal',
                className: 'modal-overlay'
            }, dojo.body());
            
            const content = dojo.create('div', {
                className: 'modal-content'
            }, modal);
            
            // Add clothing cards
            const playerHand = this.playerHands[this.player_id];
            if (playerHand && playerHand.clothing) {
                playerHand.clothing.forEach(card => {
                    const cardElement = this.createClothingCard(card);
                    dojo.place(cardElement, content);
                });
            }
            
            // Add confirm button
            const confirmBtn = dojo.create('button', {
                innerHTML: 'Sell Selected Cards',
                className: 'btn btn-primary'
            }, content);
            
            dojo.connect(confirmBtn, 'onclick', this, 'confirmSellClothing');
        },

        showSingleRecording: function () {
            // Create modal for single recording
            const modal = dojo.create('div', {
                id: 'single-recording-modal',
                className: 'modal-overlay'
            }, dojo.body());
            
            const content = dojo.create('div', {
                className: 'modal-content'
            }, modal);
            
            // Show available singles
            Object.values(this.singleCards).forEach(single => {
                if (single.location.startsWith('board')) {
                    const singleElement = this.createSingleCard(single);
                    dojo.place(singleElement, content);
                }
            });
            
            // Add confirm button
            const confirmBtn = dojo.create('button', {
                innerHTML: 'Record Single',
                className: 'btn btn-primary'
            }, content);
            
            dojo.connect(confirmBtn, 'onclick', this, 'confirmRecordSingle');
        },

        createClothingCard: function (card) {
            const cardElement = dojo.create('div', {
                className: 'clothing-card ' + card.color + ' ' + card.clothing_type,
                'data-card-id': card.card_id
            });
            
            const title = dojo.create('div', {
                className: 'card-title',
                innerHTML: card.clothing_type.charAt(0).toUpperCase() + card.clothing_type.slice(1)
            }, cardElement);
            
            const points = dojo.create('div', {
                className: 'card-points',
                innerHTML: card.points
            }, cardElement);
            
            const color = dojo.create('div', {
                className: 'card-color',
                innerHTML: card.color
            }, cardElement);
            
            // Make selectable
            dojo.addClass(cardElement, 'selectable');
            dojo.connect(cardElement, 'onclick', this, 'onClothingCardClick');
            
            return cardElement;
        },

        createSingleCard: function (single) {
            const cardElement = dojo.create('div', {
                className: 'single-card',
                'data-card-id': single.card_id
            });
            
            // Create sprite element for the card image
            if (single.sprite_position !== undefined) {
                const spriteElement = dojo.create('div', {
                    className: 'single-card-sprite single-card-' + single.sprite_position
                }, cardElement);
            } else {
                // Fallback to text-based card if no sprite position
                dojo.addClass(cardElement, 'text-based');
                
                const title = dojo.create('div', {
                    className: 'card-title',
                    innerHTML: 'Single'
                }, cardElement);
                
                const attributes = dojo.create('div', {
                    className: 'card-attributes'
                }, cardElement);
                
                [single.attribute1, single.attribute2, single.attribute3].forEach(attr => {
                    const attrElement = dojo.create('div', {
                        className: 'attribute',
                        innerHTML: attr
                    }, attributes);
                });
            }
            
            return cardElement;
        },

        // Create single card back (for face-down cards)
        createSingleCardBack: function () {
            const cardElement = dojo.create('div', {
                className: 'single-card'
            });
            
            const spriteElement = dojo.create('div', {
                className: 'single-card-sprite single-card-back'
            }, cardElement);
            
            return cardElement;
        },

        // Helper function to get sprite position class for a given card index
        getSingleCardSpriteClass: function (cardIndex) {
            return 'single-card-sprite single-card-' + cardIndex;
        },

        // Helper function to create a single card with specific sprite position
        createSingleCardWithSprite: function (cardId, spriteIndex) {
            const cardElement = dojo.create('div', {
                className: 'single-card',
                'data-card-id': cardId
            });
            
            const spriteElement = dojo.create('div', {
                className: this.getSingleCardSpriteClass(spriteIndex)
            }, cardElement);
            
            return cardElement;
        },

        createSoireeCard: function (soiree) {
            const cardElement = dojo.create('div', {
                className: 'soiree-card',
                'data-card-id': soiree.card_id
            });
            
            const title = dojo.create('div', {
                className: 'card-title',
                innerHTML: soiree.party_name
            }, cardElement);
            
            const bonuses = dojo.create('div', {
                className: 'card-bonuses'
            }, cardElement);
            
            // Color bonus
            const colorBonus = dojo.create('div', {
                className: 'bonus color-bonus',
                innerHTML: soiree.color_bonus + ' +' + soiree.color_bonus_points
            }, bonuses);
            
            // Record bonus
            if (soiree.record_bonus) {
                const recordBonus = dojo.create('div', {
                    className: 'bonus record-bonus',
                    innerHTML: 'Record +' + soiree.record_bonus_points
                }, bonuses);
            }
            
            // In Vogue bonus
            const inVogueBonus = dojo.create('div', {
                className: 'bonus in-vogue-bonus',
                innerHTML: soiree.in_vogue_bonus + ' +' + soiree.in_vogue_bonus_points
            }, bonuses);
            
            return cardElement;
        },

        onClothingCardClick: function (evt) {
            const cardId = evt.currentTarget.getAttribute('data-card-id');
            
            if (dojo.hasClass(evt.currentTarget, 'selected')) {
                dojo.removeClass(evt.currentTarget, 'selected');
                this.selectedCards = this.selectedCards.filter(id => id != cardId);
            } else {
                dojo.addClass(evt.currentTarget, 'selected');
                this.selectedCards.push(cardId);
            }
        },

        confirmSellClothing: function () {
            if (this.selectedCards.length > 0) {
                this.confirmAction('sellClothing', this.selectedCards);
                this.closeModal('clothing-selection-modal');
            }
        },

        confirmRecordSingle: function () {
            // Implementation for recording single
            this.closeModal('single-recording-modal');
        },

         showScoringAnimation: function () {
            // Create scoring animation
            const animation = dojo.create('div', {
                className: 'scoring-animation'
            }, dojo.body());
            
            const text = dojo.create('div', {
                className: 'scoring-text',
                innerHTML: 'Scoring Party Points...'
            }, animation);
            
            // Remove after 3 seconds
            setTimeout(() => {
                dojo.destroy(animation);
            }, 3000);
        },
    });
});
