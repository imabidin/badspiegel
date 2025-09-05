/**
 * Modern Favourites JavaScript - WordPress Best Practices
 *
 * Key improvements:
 * 1. Pre-loaded states (no initialization AJAX)
 * 2. Optimistic UI updates
 * 3. Single event handler
 * 4. Simple state management
 * 5. Automatic error recovery
 */

class FavouritesManager {
    constructor() {
        this.states = favouritesData.states || {};
        this.count = favouritesData.count || 0;
        this.isProcessing = new Set(); // Track processing products
        this.initialized = false; // Track if we're initializing

        this.init();
    }

    init() {
        console.log('🚀 [Favourites] Initializing with pre-loaded states:', this.states);

        // Initialize button states from server data
        this.initializeButtonStates();

        // Single event handler
        this.attachEventHandlers();

        // Update badge without animation on initial load
        this.updateBadge(false);

        // Mark as initialized
        this.initialized = true;
    }

    /**
     * Initialize button states from pre-loaded server data
     * No AJAX calls needed!
     */
    initializeButtonStates() {
        document.querySelectorAll('.btn-favourite-loop').forEach(button => {
            const productId = parseInt(button.dataset.productId);
            const configCode = this.extractConfigCode(button);

            if (!productId || !this.states[productId]) {
                console.warn('⚠️ [Favourites] No state data for product:', productId);
                return;
            }

            const productState = this.states[productId];
            const isFavourite = this.isProductFavourite(productId, configCode, productState);

            this.updateButtonVisualState(button, isFavourite);

            console.log(`✅ [Favourites] Initialized button for product ${productId}:`, {
                configCode,
                isFavourite,
                availableConfigs: productState.config_codes
            });
        });
    }

    /**
     * Check if product+config combination is favourite
     */
    isProductFavourite(productId, configCode, productState) {
        if (!productState) return false;

        // If no config code, check if product has any favourites
        if (!configCode) {
            return productState.is_favourite;
        }

        // Check specific config
        return productState.config_codes.includes(configCode);
    }

    /**
     * Extract config code from button context
     */
    extractConfigCode(button) {
        // Method 1: Direct data attribute
        if (button.dataset.configCode) {
            return button.dataset.configCode;
        }

        // Method 2: From configurator (if available)
        if (typeof window.getActiveConfiguration === 'function') {
            const config = window.getActiveConfiguration();
            return config?.code || null;
        }

        // Method 3: From URL params
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('config') || null;
    }

    /**
     * Single event handler for all favourite buttons
     */
    attachEventHandlers() {
        document.addEventListener('click', (e) => {
            // Handle toggle buttons (add/remove)
            const toggleButton = e.target.closest('.btn-favourite-loop');
            if (toggleButton) {
                e.preventDefault();
                e.stopPropagation();
                this.handleButtonClick(toggleButton);
                return;
            }

            // Handle dedicated remove buttons (in favourites shortcode)
            const removeButton = e.target.closest('.btn-favourite-remove');
            if (removeButton) {
                e.preventDefault();
                e.stopPropagation();
                this.handleRemoveClick(removeButton);
                return;
            }
        });
    }

    /**
     * Handle button click with optimistic updates and debouncing
     */
    async handleButtonClick(button) {
        const productId = parseInt(button.dataset.productId);
        const configCode = this.extractConfigCode(button);

        // Get current state first
        const currentState = this.isProductFavourite(
            productId,
            configCode,
            this.states[productId]
        );

        const newState = !currentState;

        // Prevent concurrent requests for same product/action
        const action = newState ? 'add' : 'remove';
        const key = `${action}-${productId}-${configCode || 'null'}`;
        if (this.isProcessing.has(key)) {
            console.log('⏸️ [Favourites] Already processing:', key);
            return;
        }

        console.log('🖱️ [Favourites] Button clicked:', { productId, configCode });

        this.isProcessing.add(key);

        try {
            // Optimistic update
            this.updateButtonVisualState(button, newState);
            this.updateLocalState(productId, configCode, newState);

            console.log(`🔄 [Favourites] Optimistic update: ${currentState} → ${newState}`);

            // Send AJAX request
            const response = await this.sendToggleRequest(productId, configCode);

            if (response.success) {
                console.log('✅ [Favourites] Server confirmed:', response.data);

                // Update global count
                this.count = response.data.count;
                this.updateBadge();

                // Cache invalidation hint for next page load
                if (favouritesData.cacheKey) {
                    sessionStorage.setItem('bsawesome_cache_invalid_' + favouritesData.cacheKey, '1');
                }

                // Server state should match our optimistic update
                if (response.data.is_favourite !== newState) {
                    console.warn('⚠️ [Favourites] State mismatch, correcting...');
                    this.updateButtonVisualState(button, response.data.is_favourite);
                    this.updateLocalState(productId, configCode, response.data.is_favourite);
                }
            } else {
                throw new Error(response.data?.message || 'Server error');
            }

        } catch (error) {
            console.error('❌ [Favourites] Error:', error);

            // Rollback optimistic update
            const originalState = !this.isProductFavourite(
                productId,
                configCode,
                this.states[productId]
            );

            this.updateButtonVisualState(button, originalState);
            this.updateLocalState(productId, configCode, originalState);

            // Show user-friendly error
            this.showError('Fehler beim Aktualisieren der Favoriten. Bitte versuchen Sie es erneut.');

        } finally {
            this.isProcessing.delete(key);
        }
    }

    /**
     * Handle dedicated remove button click (for favourites shortcode)
     */
    async handleRemoveClick(button) {
        const productId = parseInt(button.dataset.productId);
        const configCode = button.dataset.configCode || null;

        // Prevent concurrent requests for same product
        const key = `remove-${productId}-${configCode || 'null'}`;
        if (this.isProcessing.has(key)) {
            console.log('⏸️ [Favourites] Already processing remove:', key);
            return;
        }

        console.log('🗑️ [Favourites] Remove button clicked:', { productId, configCode });

        this.isProcessing.add(key);

        // Add loading state
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fa-sharp fa-light fa-spinner fa-spin"></i>';
        button.disabled = true;

        try {
            // Send AJAX remove request (we know it's currently a favourite)
            const response = await this.sendToggleRequest(productId, configCode);

            if (response.success) {
                console.log('✅ [Favourites] Remove confirmed:', response.data);

                // Update global count
                this.count = response.data.count;
                this.updateBadge();

                // Remove the entire product item from view
                const productItem = button.closest('.favourite-product-item, .product, li');
                if (productItem) {
                    // Smooth fade-out animation
                    productItem.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                    productItem.style.opacity = '0';
                    productItem.style.transform = 'scale(0.95)';

                    setTimeout(() => {
                        productItem.remove();

                        // Check if no more favourites
                        const container = document.querySelector('.favourites-container');
                        const remainingProducts = container ? container.querySelectorAll('.favourite-product-item, .product, li').length : 0;

                        if (remainingProducts === 0) {
                            // Show empty state
                            this.showEmptyFavouritesMessage(container);
                        }
                    }, 300);
                }

                // Update local state
                this.updateLocalState(productId, configCode, false);

            } else {
                throw new Error(response.data?.message || 'Remove failed');
            }

        } catch (error) {
            console.error('❌ [Favourites] Remove error:', error);

            // Restore button state
            button.innerHTML = originalContent;
            button.disabled = false;

            // Show user-friendly error
            this.showError('Fehler beim Entfernen aus den Favoriten. Bitte versuchen Sie es erneut.');

        } finally {
            this.isProcessing.delete(key);
        }
    }

    /**
     * Show empty favourites message when all items are removed
     */
    showEmptyFavouritesMessage(container) {
        const emptyMessage = `
            <div class="favourites-empty alert alert-light text-center py-5 border-2 border-dashed">
                <i class="fa-sharp fa-light fa-heart fa-4x text-muted mb-3 d-block"></i>
                <h4 class="text-muted mb-3">Keine Favoriten gefunden</h4>
                <p class="text-muted mb-4">Sie haben alle Favoriten entfernt.</p>
                <a href="${this.getShopUrl()}" class="btn btn-primary">
                    <i class="fa-sharp fa-light fa-shopping-bag me-2"></i>
                    Jetzt Produkte entdecken
                </a>
            </div>
        `;

        // Replace content but keep container structure
        const productLoop = container.querySelector('.woocommerce ul.products, .products');
        if (productLoop) {
            productLoop.outerHTML = emptyMessage;
        } else {
            container.innerHTML = emptyMessage;
        }
    }

    /**
     * Get shop URL for empty state button
     */
    getShopUrl() {
        // Try to get shop URL from various sources
        return favouritesData.shopUrl || '/shop/';
    }

    /**
     * Update button visual state
     */
    updateButtonVisualState(button, isFavourite) {
        const icon = button.querySelector('i');
        if (!icon) return;

        // Clear all states
        icon.className = 'fa-sharp fa-heart';

        if (isFavourite) {
            icon.classList.add('fa-solid', 'text-warning');
            button.setAttribute('title', 'Aus Favoriten entfernen');
            button.setAttribute('aria-pressed', 'true');
        } else {
            icon.classList.add('fa-light');
            button.setAttribute('title', 'Zu Favoriten hinzufügen');
            button.setAttribute('aria-pressed', 'false');
        }
    }

    /**
     * Update local state cache
     */
    updateLocalState(productId, configCode, isFavourite) {
        if (!this.states[productId]) {
            this.states[productId] = { is_favourite: false, config_codes: [] };
        }

        if (configCode) {
            if (isFavourite) {
                if (!this.states[productId].config_codes.includes(configCode)) {
                    this.states[productId].config_codes.push(configCode);
                }
            } else {
                this.states[productId].config_codes = this.states[productId].config_codes
                    .filter(code => code !== configCode);
            }

            // Update general favourite state
            this.states[productId].is_favourite = this.states[productId].config_codes.length > 0;
        } else {
            this.states[productId].is_favourite = isFavourite;
            if (!isFavourite) {
                this.states[productId].config_codes = [];
            }
        }
    }

    /**
     * Send AJAX toggle request
     */
    async sendToggleRequest(productId, configCode) {
        const formData = new FormData();
        formData.append('action', 'favourite_toggle');
        formData.append('product_id', productId);
        formData.append('nonce', favouritesData.nonce);

        if (configCode) {
            formData.append('config_code', configCode);
        }

        const response = await fetch(favouritesData.ajaxUrl, {
            method: 'POST',
            body: formData
        });

        return await response.json();
    }

    /**
     * Update badge counter
     * @param {boolean} animate - Whether to animate the badge change
     */
    updateBadge(animate = true) {
        const badges = document.querySelectorAll('#favourites-count-badge, .favourites-count');

        console.log(`🔢 [Favourites] Updating badge count to: ${this.count}, found ${badges.length} badges, animate: ${animate}`);

        badges.forEach((badge, index) => {
            console.log(`🏷️ [Favourites] Badge ${index}: current display = ${badge.style.display}, computed = ${window.getComputedStyle(badge).display}`);

            badge.textContent = this.count;

            // Show/hide badge based on count
            if (this.count > 0) {
                // Force show the badge
                badge.style.display = 'inline-block';
                badge.style.visibility = 'visible';

                console.log(`🟢 [Favourites] Badge ${index} should be visible now: ${badge.style.display}`);

                // Add animation only if requested and not during initialization
                if (animate && this.initialized) {
                    badge.style.transition = 'transform 0.2s ease';
                    badge.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        badge.style.transform = 'scale(1)';
                    }, 200);
                }
            } else {
                badge.style.display = 'none';
                badge.style.visibility = 'hidden';
                console.log(`🔴 [Favourites] Badge ${index} hidden`);
            }
        });

        // Update heart icon in header - keep it simple since we have a badge
        const heartIcons = document.querySelectorAll('#site-favourites i');
        heartIcons.forEach(icon => {
            // Always keep heart icon thin and neutral since badge shows the count
            icon.className = 'fa-sharp fa-thin fa-heart';
        });
    }

    /**
     * Show error message
     */
    showError(message) {
        // Could integrate with your existing notification system
        alert(message);
    }
}

// Initialize the favourites manager
if (!window.favouritesManagerInstance) {
    window.favouritesManagerInstance = new FavouritesManager();
}

// Global function for backwards compatibility with configurator
window.updateFavouritesBadgeDisplay = function(count) {
    console.log('🔗 [Favourites] Legacy badge update called with count:', count);

    if (window.favouritesManagerInstance) {
        // Update the count and refresh badge
        window.favouritesManagerInstance.count = parseInt(count) || 0;
        window.favouritesManagerInstance.updateBadge(true); // Animate for external updates
    }
};

// Make the manager instance globally available for debugging
window.favouritesManager = window.favouritesManagerInstance;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Verify required data is available
    if (typeof favouritesData === 'undefined') {
        console.error('❌ [Favourites] favouritesData not found');
        return;
    }

    // Prevent double initialization
    if (window.favouritesManagerInstance) {
        console.warn('⚠️ [Favourites] Already initialized, skipping');
        return;
    }

    window.favouritesManagerInstance = new FavouritesManager();
});
