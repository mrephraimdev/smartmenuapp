/**
 * Menu Client Entry Point
 * Separates menu client code from main bundle for better performance
 */

import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';
import menuClientStore from './stores/menu-client.js';

// Register persist plugin
Alpine.plugin(persist);

// Register menu client store
Alpine.data('menuClientStore', menuClientStore);

// Expose Alpine globally
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

console.log('Menu Client initialized');
