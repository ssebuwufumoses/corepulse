import { store } from '@wordpress/interactivity';

store( 'corepulse', {
    state: {
        get isHudOpen() {
            return state.isOpen;
        },
        isOpen: false,
    },
    actions: {
        toggleHud: ( { state } ) => {
            state.isOpen = ! state.isOpen;
        },
        closeHud: ( { state } ) => {
            state.isOpen = false;
        }
    },
} );