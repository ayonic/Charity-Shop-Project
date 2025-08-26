<?php
/**
 * Footer Template
 * 
 * This file contains the HTML footer for all pages.
 */
?>
            </main>
            <?php include_once INCLUDES_PATH . '/creator-signature.php'; ?>
        </div>
    </div>

    <script>
        // Common JavaScript for all pages
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any common functionality here
            
            // Initialize Alpine.js dropdowns
            document.querySelectorAll('[data-dropdown]').forEach(dropdown => {
                if (!dropdown.hasAttribute('x-data')) {
                    dropdown.setAttribute('x-data', '{ open: false }');
                    dropdown.setAttribute('@mouseenter', 'open = true');
                    dropdown.setAttribute('@mouseleave', 'open = false');
                }
                
                const menu = dropdown.querySelector('[data-dropdown-menu]');
                if (menu) {
                    menu.setAttribute('x-show', 'open');
                    menu.setAttribute('x-transition:enter', 'transition ease-out duration-100');
                    menu.setAttribute('x-transition:enter-start', 'transform opacity-0 scale-95');
                    menu.setAttribute('x-transition:enter-end', 'transform opacity-100 scale-100');
                    menu.setAttribute('x-transition:leave', 'transition ease-in duration-75');
                    menu.setAttribute('x-transition:leave-start', 'transform opacity-100 scale-100');
                    menu.setAttribute('x-transition:leave-end', 'transform opacity-0 scale-95');
                }
            });
            
            // Initialize all modals with improved event handling
            const modalTriggers = document.querySelectorAll('[data-modal-trigger]');
            const activeModals = new Set();
            let lastFocusedElement = null;

            // Function to get focusable elements
            const getFocusableElements = (element) => {
                return Array.from(element.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'))
                .filter(el => !el.hasAttribute('disabled') && !el.hasAttribute('aria-hidden'));
            };

            // Function to handle tab key for focus trap
            const handleTabKey = (e, focusableElements) => {
                if (!focusableElements.length) return;
                
                const firstFocusableEl = focusableElements[0];
                const lastFocusableEl = focusableElements[focusableElements.length - 1];
                const isTabPressed = e.key === 'Tab' || e.keyCode === 9;

                if (!isTabPressed) return;

                if (e.shiftKey) {
                    if (document.activeElement === firstFocusableEl) {
                        lastFocusableEl.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastFocusableEl) {
                        firstFocusableEl.focus();
                        e.preventDefault();
                    }
                }
            };

            // Global keyboard event handler
            document.addEventListener('keydown', (e) => {
                if (activeModals.size > 0) {
                    const currentModal = Array.from(activeModals).pop();
                    if (!currentModal) return;

                    const focusableElements = getFocusableElements(currentModal);

                    if (e.key === 'Escape') {
                        e.preventDefault();
                        closeModal(currentModal);
                    } else if (e.key === 'Tab') {
                        handleTabKey(e, focusableElements);
                    } else if (e.key === 'Enter') {
                        const activeElement = document.activeElement;
                        if (activeElement && 
                            activeElement.tagName !== 'TEXTAREA' && 
                            activeElement.tagName !== 'BUTTON' && 
                            !activeElement.closest('form')) {
                            e.preventDefault();
                        }
                    }
                }
            });

            // Function to open modal
            const openModal = (modal) => {
                if (!modal || activeModals.has(modal)) return;

                lastFocusedElement = document.activeElement;
                modal.classList.remove('hidden');
                activeModals.add(modal);
                document.body.style.overflow = 'hidden';
                modal.setAttribute('aria-hidden', 'false');

                const focusableElements = getFocusableElements(modal);
                if (focusableElements.length) {
                    setTimeout(() => {
                        const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
                        if (firstInput) {
                            firstInput.focus();
                        } else {
                            focusableElements[0].focus();
                        }
                    }, 50);
                }
                    });
                });
            };

            // Function to close modal
            const closeModal = (modal) => {
                if (!modal || !activeModals.has(modal)) return;

                modal.classList.add('hidden');
                activeModals.delete(modal);
                modal.setAttribute('aria-hidden', 'true');

                if (activeModals.size === 0) {
                    document.body.style.overflow = '';
                }

                if (lastFocusedElement && document.body.contains(lastFocusedElement)) {
                    lastFocusedElement.focus();
                }
            };

            modalTriggers.forEach(trigger => {
                const modalId = trigger.getAttribute('data-modal-target');
                const modal = document.getElementById(modalId);
                
                if (modal) {
                    // Set initial ARIA attributes
                    modal.setAttribute('role', 'dialog');
                    modal.setAttribute('aria-modal', 'true');
                    modal.setAttribute('aria-hidden', 'true');

                    const closeButtons = modal.querySelectorAll('[data-modal-close]');
                    const overlay = modal.querySelector('[data-modal-overlay]');
                    const modalContent = modal.querySelector('[data-modal-content]');

                    if (modalContent) {
                        modalContent.setAttribute('role', 'document');
                    }

                    // Open modal
                    trigger.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        openModal(modal);
                    });

                    // Close buttons
                    closeButtons.forEach(button => {
                        button.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            closeModal(modal);
                        });
                    });

                    // Overlay click
                    if (overlay) {
                        overlay.addEventListener('click', (e) => {
                            if (e.target === overlay) {
                                e.preventDefault();
                                closeModal(modal);
                            }
                        });
                    }

                    // Handle form submissions
                    const forms = modal.querySelectorAll('form');
                    forms.forEach(form => {
                        form.addEventListener('submit', async (e) => {
                            e.preventDefault();
                            const submitButton = form.querySelector('button[type="submit"]');
                            
                            if (submitButton) {
                                const originalText = submitButton.textContent;
                                submitButton.disabled = true;
                                submitButton.textContent = 'Processing...';
                                
                                try {
                                    // Your form submission logic here
                                    // After successful submission:
                                    // closeModal(modal);
                                } catch (error) {
                                    console.error('Form submission error:', error);
                                } finally {
                                    submitButton.disabled = false;
                                }
                            }
                        });
                    });

                    // Prevent Enter key from submitting form unless focus is on a submit button or textarea
                    modal.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' && 
                            e.target.type !== 'submit' && 
                            e.target.tagName !== 'TEXTAREA' && 
                            !e.target.closest('button[type="submit"]')) {
                                    e.preventDefault();
                                }
                            });
                        });

                        // Trap focus within modal
                        modalContent.addEventListener('keydown', (e) => {
                            if (e.key === 'Tab') {
                                const focusable = modalContent.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                                const firstFocusable = focusable[0];
                                const lastFocusable = focusable[focusable.length - 1];

                                if (e.shiftKey) {
                                    if (document.activeElement === firstFocusable) {
                                        e.preventDefault();
                                        lastFocusable.focus();
                                    }
                                } else {
                                    if (document.activeElement === lastFocusable) {
                                        e.preventDefault();
                                        firstFocusable.focus();
                                    }
                                }
                            }
                        });
                    }
                        });
                    }
                }
            });

            // Global escape key handler
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && activeModals.size > 0) {
                    const lastModal = Array.from(activeModals).pop();
                    if (lastModal) {
                        lastModal.classList.add('hidden');
                        activeModals.delete(lastModal);
                        if (activeModals.size === 0) {
                            document.body.style.overflow = '';
                        }
                    }
                }
            });
                }
            });
        });
    </script>
</body>
</html>
