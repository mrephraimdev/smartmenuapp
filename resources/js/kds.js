/**
 * KDS (Kitchen Display System) Entry Point
 * Separates KDS code from main bundle for better performance
 */

import Alpine from 'alpinejs';
import kdsStore from './stores/kds.js';

// Register KDS store
Alpine.data('kdsStore', kdsStore);

// Notification sound handling
const NotificationManager = {
    audio: null,
    enabled: true,

    init() {
        this.audio = new Audio('/sounds/notification.mp3');
        this.audio.volume = 0.7;

        // Check for notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    },

    play() {
        if (!this.enabled) return;

        try {
            this.audio.currentTime = 0;
            this.audio.play().catch(() => {
                console.warn('Audio playback failed');
            });
        } catch (e) {
            console.warn('Notification sound not available');
        }
    },

    toggle() {
        this.enabled = !this.enabled;
        return this.enabled;
    },

    showBrowserNotification(message, body) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(message, {
                body: body,
                icon: '/images/logo.png',
                tag: 'kds-notification',
                requireInteraction: true
            });
        }
    }
};

// Expose globally for use in templates
window.NotificationManager = NotificationManager;

// Initialize notification manager
NotificationManager.init();

// Expose Alpine globally
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

console.log('KDS initialized with enhanced notifications');
