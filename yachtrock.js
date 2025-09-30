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
    return declare("bgagame.yachtrock", gamegui, {
        constructor: function () {
            this.styleCardSlots = [[], [], [], [], []]
            this.singleCards = [];
            this.soireeCards = [];
        },

        setup: function(gamedatas) {
            console.log("Yacht Rock game setup");
            console.log(gamedatas);
            this.initializeGameAreas();
            this.initializePlayerAreas(gamedatas.players);
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
            const stackOffset = 50; // vertical shift per card

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
                        }, parent); // create directly inside parent
                    }

                    // update style and z-index
                    cardDiv.style.top = `${styleCard.stackPosition * stackOffset}px`;
                    cardDiv.style.zIndex = styleCard.stackPosition + 1;
                    cardDiv.dataset.position = styleCard.stackPosition;

                    seenIds.add(styleCardId);
                }

                // Remove any old cards not in the current slot
                Array.from(parent.querySelectorAll('.style-card')).forEach(child => {
                    if (!seenIds.has(child.id)) {
                        child.remove();
                    }
                });

                // Update stack height to fit all cards
                const cards = parent.querySelectorAll('.style-card');
                if (cards.length > 0) {
                    const stackHeight = (cards.length - 1) * stackOffset + cards[0].offsetHeight;
                    parent.style.height = stackHeight + 'px';
                } else {
                    parent.style.height = '0px';
                }
            }

            // Connect click handler
            dojo.query('.style-card').connect('onclick', this, 'onStyleCardClick');
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
                <div id="board-area">
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
    
        notif_styleCardsTaken: function (args) {
            this.styleCardSlots[args.slotNumber - 1] = []
            this.updateStyleCards()
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
