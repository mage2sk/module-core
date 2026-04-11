/**
 * Panth Core - Config Dependency Control
 *
 * Automatically disables Save Config button for Panth modules
 * when Core module is not enabled
 *
 * @category  Panth
 * @package   Panth_Core
 */
require([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    // List of Panth module config sections that require Core
    var PANTH_SECTIONS = [
        'panth_megamenu',
        'panth_slider',
        'panth_builder',
        'panth_newsletter'
        // Add more Panth modules here as they are created
    ];

    /**
     * Get current config section from URL
     */
    function getCurrentSection() {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('section');
    }

    /**
     * Check if current page is a Panth module config page (excluding Core itself)
     */
    function isPanthModulePage() {
        var section = getCurrentSection();
        return section && PANTH_SECTIONS.indexOf(section) !== -1;
    }

    /**
     * Check if Core module is enabled by looking at the status block
     */
    function isCoreModuleEnabled() {
        var currentSection = getCurrentSection();
        if (!currentSection) {
            return true;
        }

        // Look for the core_check group's status message
        var $coreCheckGroup = $('#' + currentSection + '_core_check');
        if ($coreCheckGroup.length === 0) {
            return true; // No check group means no dependency
        }

        var coreCheckHtml = $coreCheckGroup.find('.value').html() || '';

        // Check for green success message containing "Core Module Enabled"
        return coreCheckHtml.indexOf('Core Module Enabled') !== -1 ||
               coreCheckHtml.indexOf('#d4edda') !== -1; // Green background color
    }

    /**
     * Disable/Enable save button and show warning
     */
    function updateSaveButton() {
        if (!isPanthModulePage()) {
            return; // Not a Panth module page, do nothing
        }

        var $saveButton = $('#save');
        var $pageActions = $('.page-actions');

        if (!isCoreModuleEnabled()) {
            // Disable save button
            $saveButton.prop('disabled', true);
            $saveButton.addClass('disabled');
            $saveButton.css({
                'opacity': '0.5',
                'cursor': 'not-allowed',
                'pointer-events': 'none'
            });

            // Add warning message if not already present
            if ($pageActions.find('.panth-core-warning').length === 0) {
                var coreConfigUrl = window.location.origin + window.location.pathname + '?section=panth_core';

                $pageActions.prepend(
                    '<div class="message message-error panth-core-warning" style="margin-bottom: 20px; padding: 15px; background: #fef5e7; border-left: 4px solid #e74c3c;">' +
                    '<strong style="color: #c0392b;">⚠ Configuration Save Disabled</strong><br/>' +
                    '<p style="margin: 10px 0; color: #555;">This Panth module requires <strong>Panth Core</strong> to be enabled.</p>' +
                    '<p style="margin: 0;">' +
                    '<a href="' + coreConfigUrl + '" style="display: inline-block; padding: 8px 16px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-weight: 600;">' +
                    '→ Enable Panth Core Module' +
                    '</a>' +
                    '</p>' +
                    '</div>'
                );
            }
        } else {
            // Enable save button
            $saveButton.prop('disabled', false);
            $saveButton.removeClass('disabled');
            $saveButton.css({
                'opacity': '1',
                'cursor': 'pointer',
                'pointer-events': 'auto'
            });

            // Remove warning message
            $('.panth-core-warning').remove();
        }
    }

    // Run on page load
    $(document).ready(function() {
        updateSaveButton();
    });

    // Re-check when accordion sections are opened/closed
    $(document).on('click', '.section-config', function() {
        setTimeout(updateSaveButton, 100);
    });

    // Monitor for dynamic content changes
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                updateSaveButton();
            }
        });
    });

    if (isPanthModulePage()) {
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
});
