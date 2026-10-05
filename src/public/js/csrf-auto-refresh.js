/**
 * CSRF Auto-Refresh
 * 
 * This script automatically refreshes CSRF tokens before form submission
 * to prevent "Sesi formulir berakhir" errors when session expires.
 * 
 * Features:
 * - Intercepts all form submissions
 * - Fetches fresh CSRF token before submit
 * - Updates token in form and submits
 * - Handles both AJAX and regular form submissions
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        tokenEndpoint: '/api/csrf/token',
        tokenInputName: 'csrf_token',
        refreshTimeout: 30000, // 30 seconds - refresh token if form idle for this long
        maxRetries: 2
    };

    // Cache for last fetched token
    let lastToken = null;
    let lastTokenTime = 0;
    let isRefreshing = false;
    let refreshQueue = [];

    /**
     * Fetch a fresh CSRF token from the server
     */
    async function fetchFreshToken() {
        // If we have a recent token (less than 1 minute old), reuse it
        if (lastToken && (Date.now() - lastTokenTime) < 60000) {
            return lastToken;
        }

        // If already refreshing, queue the request
        if (isRefreshing) {
            return new Promise((resolve, reject) => {
                refreshQueue.push({ resolve, reject });
            });
        }

        isRefreshing = true;

        try {
            const response = await fetch(CONFIG.tokenEndpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!data.success || !data.token) {
                throw new Error('Invalid response from server');
            }

            lastToken = data.token;
            lastTokenTime = Date.now();

            // Resolve all queued requests
            refreshQueue.forEach(item => item.resolve(data.token));
            refreshQueue = [];

            return data.token;
        } catch (error) {
            // Reject all queued requests
            refreshQueue.forEach(item => item.reject(error));
            refreshQueue = [];

            console.error('Failed to refresh CSRF token:', error);
            throw error;
        } finally {
            isRefreshing = false;
        }
    }

    /**
     * Update CSRF token in a form
     */
    function updateFormToken(form, token) {
        let tokenInput = form.querySelector(`input[name="${CONFIG.tokenInputName}"]`);

        if (!tokenInput) {
            // Create token input if it doesn't exist
            tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = CONFIG.tokenInputName;
            form.appendChild(tokenInput);
        }

        tokenInput.value = token;
    }

    /**
     * Handle form submission with token refresh
     */
    async function handleFormSubmit(event) {
        const form = event.target;

        // Skip if form doesn't have CSRF token or is a GET form
        if (form.method.toLowerCase() === 'get') {
            return;
        }

        // Skip if form has data-no-csrf-refresh attribute
        if (form.hasAttribute('data-no-csrf-refresh')) {
            return;
        }

        // Prevent default submission
        event.preventDefault();

        // Show loading state
        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        const originalButtonText = submitButton ? (submitButton.value || submitButton.textContent) : '';

        if (submitButton) {
            submitButton.disabled = true;
            if (submitButton.tagName === 'BUTTON') {
                submitButton.textContent = 'Memproses...';
            } else {
                submitButton.value = 'Memproses...';
            }
        }

        try {
            // Fetch fresh token
            const freshToken = await fetchFreshToken();

            // Update token in form
            updateFormToken(form, freshToken);

            // Submit the form
            form.submit();

        } catch (error) {
            console.error('Failed to submit form:', error);

            // Show error message
            alert('Gagal memperbarui token keamanan. Silakan muat ulang halaman dan coba lagi.');

            // Reset button state
            if (submitButton) {
                submitButton.disabled = false;
                if (submitButton.tagName === 'BUTTON') {
                    submitButton.textContent = originalButtonText;
                } else {
                    submitButton.value = originalButtonText;
                }
            }

            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    }

    /**
     * Intercept AJAX requests and add fresh token
     */
    function interceptAjax() {
        // Intercept jQuery AJAX if available
        if (typeof $ !== 'undefined' && $.ajaxSetup) {
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    // Only for POST requests
                    if (settings.type && settings.type.toUpperCase() === 'POST') {
                        // Skip if URL is for CSRF token endpoint
                        if (settings.url && settings.url.indexOf(CONFIG.tokenEndpoint) !== -1) {
                            return;
                        }

                        // Get fresh token and add to data
                        fetchFreshToken().then(function(token) {
                            if (settings.data) {
                                if (typeof settings.data === 'string') {
                                    // Form data string
                                    settings.data += '&' + encodeURIComponent(CONFIG.tokenInputName) + '=' + encodeURIComponent(token);
                                } else if (settings.data instanceof FormData) {
                                    settings.data.append(CONFIG.tokenInputName, token);
                                } else if (typeof settings.data === 'object') {
                                    settings.data[CONFIG.tokenInputName] = token;
                                }
                            }
                        }).catch(function(error) {
                            console.error('Failed to refresh token for AJAX:', error);
                        });
                    }
                }
            });
        }
    }

    /**
     * Initialize the CSRF auto-refresh
     */
    function init() {
        // Attach submit event listeners to all forms
        document.addEventListener('submit', function(event) {
            if (event.target.tagName === 'FORM') {
                handleFormSubmit(event);
            }
        }, true); // Use capture phase to intercept before other handlers

        // Intercept AJAX requests
        interceptAjax();

        // Periodically refresh token for long-open forms
        setInterval(function() {
            const activeElement = document.activeElement;
            const isInForm = activeElement && activeElement.closest('form');

            // Only refresh if user is interacting with a form
            if (isInForm) {
                fetchFreshToken().catch(function(error) {
                    // Silently fail for background refresh
                    console.warn('Background token refresh failed:', error);
                });
            }
        }, CONFIG.refreshTimeout);

        console.log('CSRF Auto-Refresh initialized');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
