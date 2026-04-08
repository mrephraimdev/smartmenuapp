import './bootstrap';

/**
 * Alpine.js Configuration
 *
 * Framework réactif léger pour remplacer le JavaScript vanilla inline
 * Plugins : persist (sauvegarde localStorage)
 */
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

// Importer les stores
import menuClientStore from './stores/menu-client.js';
import kdsStore from './stores/kds.js';

// Enregistrer le plugin persist
Alpine.plugin(persist);

// Enregistrer les stores globalement
Alpine.data('menuClientStore', menuClientStore);
Alpine.data('kdsStore', kdsStore);

// Exposer Alpine globalement
window.Alpine = Alpine;

// Démarrer Alpine
Alpine.start();

console.log('✅ Alpine.js initialized with stores: menuClientStore, kdsStore');
