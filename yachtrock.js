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

define([
    "dojo",
    "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare, gamegui, counter, stock) {
    return declare("bgagame.babyyachtrock", gamegui, {
        constructor: function () {
            this.styleSlots = {};
            this.singleCards = {};
            this.soireeCards = {};
            this.playerHands = {};
            this.selectedCards = [];
            this.selectedSlot = null;
        },

        setup: function(gamedatas) {
            console.log("Yacht Rock game setup");
            console.log(gamedatas);
            this.initializeGameAreas();
            this.initializePlayerAreas(gamedatas.players);
            this.createBoard();
            this.showSingleCards(gamedatas.singleCards);
            this.showSoireeCards(gamedatas.soireeCards);
            this.showStyleCards(gamedatas.styleCards);
        },

        createBoard: function () {
            const parent = document.getElementById('board-area');
            parent.insertAdjacentHTML('beforeend', `
                <div id="board">
                </div>
            `);
        },

        showSingleCards: function (singleCards) {
            console.log("Showing single cards");
            const parent = document.getElementById('board');
            for (let i = 0; i < singleCards.length; i++) {
                parent.insertAdjacentHTML('beforeend', `
                    <div id="single-card-${singleCards[0].position}" class="single-card single-card-${singleCards[i].index} single-card-pos-${singleCards[i].position}">
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

        showStyleCards: function (styleCards) {
            const parent = document.getElementById('board');
            for (let i = 0; i < styleCards.length; i++) {
                parent.insertAdjacentHTML('beforeend', `
                    <div id="style-card-${styleCards[i].position}" class="style-card style-card-${styleCards[i].index} style-card-pos-${styleCards[i].position}">
                    </div>
                `);
                dojo.connect(dojo.byId('style-card-' + styleCards[i].position), 'onclick', this, 'onStyleCardClick');
            }
        },

        onStyleCardClick: function (evt) {
            const cardId = evt.currentTarget.id.replace('style-card-', '');
            console.log('onStyleCardClick ' + cardId);
            dojo.stopEvent(evt);
            this.selectedSlot = parseInt(cardId);
            if (this.gamedatas.gamestate.name == 'playerTurn') {
                  this.bgaPerformAction('actChooseStyleSlot', {card: this.selectedSlot});
            } else {
                  this.showMoveUnauthorized();
            }
        },

        initializeGameAreas: function () {
            const gameArea = document.getElementById('page-content');
            gameArea.insertAdjacentHTML('beforeend', `
                <div id="board-area">
                </div>
            `);
            gameArea.insertAdjacentHTML('beforeend', `
                <div id="score-track">
                </div>
            `);
            gameArea.insertAdjacentHTML('beforeend', `
                <div id="player-areas">
                </div>
            `);
        },

        // Initialize player areas
        initializePlayerAreas: function (players) {
            const playerAreas = document.getElementById('player-areas');
    
            // Convert players object to array if needed, or iterate over object properties
            const playerList = Array.isArray(players) ? players : Object.values(players);
            
            playerList.forEach(player => {
                const playerArea = document.createElement('div');
                playerArea.className = 'player-area';
                playerArea.id = 'player-area-' + player.id;

                playerArea.innerHTML = `
    <div class="player-name">${player.name}</div>
    <div class="player-score" id="player-score-${player.id}">${player.score}</div>
    <div class="player-outfit" id="player-outfit-${player.id}">
        <!-- Outfit items will be displayed here -->
    </div>
    <div class="player-tokens" id="player-tokens-${player.id}">
        Single Tokens: 0
    </div>
    <div class="party-choice" id="party-choice-${player.id}">
        <!-- Party choice will be displayed here -->
    </div>
                `;
    
                playerAreas.appendChild(playerArea);
            });
        },
    
        // Update style slots with cards
        updateStyleSlots: function (styleSlots) {
            for (let i = 1; i <= 5; i++) {
                const slot = document.getElementById('style-slot-' + i);
                const cards = styleSlots[i] || [];
    
                slot.innerHTML = '';
                cards.forEach(card => {
                    const cardElement = createCardElement(card);
                    slot.appendChild(cardElement);
                });
            }
        },
    
        // Create card element
        createCardElement: function (card) {
            if (card.card_type === 'clothing') {
                return createClothingCard(card);
            } else if (card.card_type === 'musical') {
                return createMusicalCard(card);
            }
            return null;
        },
    
        // Create clothing card
        /*createClothingCard: function (card) {
            const cardElement = document.createElement('div');
            cardElement.className = `clothing-card ${card.card_color} ${card.card_clothing_type}`;
            cardElement.setAttribute('data-card-id', card.card_id);
    
            cardElement.innerHTML = `
    <div class="card-title">${card.card_clothing_type.charAt(0).toUpperCase() + card.card_clothing_type.slice(1)}</div>
    <div class="card-points">${card.card_points}</div>
    <div class="card-color">${card.card_color}</div>
            `;
    
            return cardElement;
        },*/
    
        // Create musical card
        createMusicalCard: function (card) {
            const cardElement = document.createElement('div');
            cardElement.className = 'musical-card';
            cardElement.setAttribute('data-card-id', card.card_id);
    
            cardElement.innerHTML = `
    <div class="card-attribute">${card.card_musical_attribute}</div>
            `;
    
            return cardElement;
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
            return;
            console.log('Entering state: ' + stateName);
            
            switch (stateName) {
                case 'playerTurn':
                    this.setupPlayerTurn(args);
                    break;
                case 'optionalActions':
                    this.setupOptionalActions(args);
                    break;
                case 'chooseParties':
                    this.setupChooseParties(args);
                    break;
                case 'partyScoring':
                    this.setupPartyScoring(args);
                    break;
            }
        },

        onLeavingState: function (stateName) {
            console.log('Leaving state: ' + stateName);
            
            // Clear any active selections
            this.clearSelections();
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
            this.clearSelections();
            
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

        closeModal: function (modalId) {
            const modal = dojo.byId(modalId);
            if (modal) {
                dojo.destroy(modal);
            }
        },

        clearSelections: function () {
            // Clear all selections
            this.selectedCards = [];
            this.selectedSlot = null;
            
            // Remove selectable and selected classes
            dojo.query('.selectable, .selected').forEach(element => {
                dojo.removeClass(element, 'selectable selected');
            });
            
            // Hide action buttons
            dojo.style('sell-clothing-btn', 'display', 'none');
            dojo.style('record-single-btn', 'display', 'none');
            dojo.style('pass-optional-btn', 'display', 'none');
        },

        confirmAction: function (action, data) {
            if (this.checkAction(action)) {
                this.ajaxcall('/yachtrock/yachtrock/act' + action.charAt(0).toUpperCase() + action.slice(1), data, this, function (result) {
                    // Action completed successfully
                });
            }
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

        // Override methods
        setupNotifications: function () {
            console.log('yachtrock setupNotifications');
            
            // Handle notifications from server
            this.notif_cardsTaken = function (notif) {
                this.updateStyleSlots(notif.args);
            };
            
            this.notif_clothingSold = function (notif) {
                this.updatePlayerScore(notif.args);
            };
            
            this.notif_singleRecorded = function (notif) {
                this.updateSingleCards(notif.args);
            };
            
            this.notif_partyChosen = function (notif) {
                this.updatePartyChoices(notif.args);
            };
            
            this.notif_playerScored = function (notif) {
                this.updatePlayerScore(notif.args);
            };
        },

        /*
        updateStyleSlots: function (args) {
            // Update the style slots display
            if (args.slot_number && args.cards) {
                const slotElement = dojo.byId('style-slot-' + args.slot_number);
                if (slotElement) {
                    // Clear and repopulate slot
                    dojo.empty(slotElement);
                    args.cards.forEach(card => {
                        const cardElement = this.createCardElement(card);
                        dojo.place(cardElement, slotElement);
                    });
                }
            }
        },
        */

        updatePlayerScore: function (args) {
            // Update player score display
            if (args.player_id && args.total_points !== undefined) {
                const scoreElement = dojo.byId('player-score-' + args.player_id);
                if (scoreElement) {
                    scoreElement.innerHTML = args.total_points;
                }
            }
        },

        /*
        updateSingleCards: function (args) {
            // Update single cards display
            if (args.single_card) {
                // Remove recorded single from board
                const cardElement = dojo.query('[data-card-id="' + args.single_card.card_id + '"]')[0];
                if (cardElement) {
                    dojo.destroy(cardElement);
                }
            }
        },
        */

        updatePartyChoices: function (args) {
            // Update party choice display
            if (args.player_id && args.party_name) {
                // Show which party the player chose
                const choiceElement = dojo.byId('party-choice-' + args.player_id);
                if (choiceElement) {
                    choiceElement.innerHTML = args.party_name;
                }
            }
        },

        /*
        createCardElement: function (card) {
            if (card.card_type === 'clothing') {
                return this.createClothingCard(card);
            } else if (card.card_type === 'musical') {
                return this.createMusicalCard(card);
            }
            return null;
        },
        */

        createMusicalCard: function (card) {
            const cardElement = dojo.create('div', {
                className: 'musical-card',
                'data-card-id': card.card_id
            });
            
            const attribute = dojo.create('div', {
                className: 'card-attribute',
                innerHTML: card.card_musical_attribute
            }, cardElement);
            
            return cardElement;
        }
    });
});
