/**
 * Admin Dashboard Entry Point
 * Separates admin code from main bundle for better performance
 */

import Alpine from 'alpinejs';

// Admin-specific components
Alpine.data('dashboardStats', () => ({
    isLoading: true,
    stats: null,

    async init() {
        await this.loadStats();
    },

    async loadStats() {
        try {
            const response = await fetch('/api/admin/stats');
            this.stats = await response.json();
        } catch (e) {
            console.error('Failed to load stats:', e);
        } finally {
            this.isLoading = false;
        }
    }
}));

// Confirmation dialog component
Alpine.data('confirmDialog', () => ({
    show: false,
    title: '',
    message: '',
    confirmText: 'Confirmer',
    cancelText: 'Annuler',
    onConfirm: null,

    open(options) {
        this.title = options.title || 'Confirmation';
        this.message = options.message || 'Êtes-vous sûr ?';
        this.confirmText = options.confirmText || 'Confirmer';
        this.cancelText = options.cancelText || 'Annuler';
        this.onConfirm = options.onConfirm;
        this.show = true;
    },

    confirm() {
        if (this.onConfirm) this.onConfirm();
        this.show = false;
    },

    cancel() {
        this.show = false;
    }
}));

// Toast notification component
Alpine.data('toastNotification', () => ({
    toasts: [],
    nextId: 0,

    add(message, type = 'info', duration = 5000) {
        const id = this.nextId++;
        this.toasts.push({ id, message, type });

        setTimeout(() => {
            this.remove(id);
        }, duration);
    },

    remove(id) {
        const index = this.toasts.findIndex(t => t.id === id);
        if (index > -1) this.toasts.splice(index, 1);
    },

    success(message) { this.add(message, 'success'); },
    error(message) { this.add(message, 'error'); },
    warning(message) { this.add(message, 'warning'); },
    info(message) { this.add(message, 'info'); }
}));

// Expose Alpine globally
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

// Global toast helper
window.toast = {
    success: (msg) => document.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'success' } })),
    error: (msg) => document.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'error' } })),
    warning: (msg) => document.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'warning' } })),
    info: (msg) => document.dispatchEvent(new CustomEvent('toast', { detail: { message: msg, type: 'info' } })),
};

console.log('Admin dashboard initialized');
