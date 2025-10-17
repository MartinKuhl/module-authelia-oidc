/**
 * Authelia OIDC Button Position Fix
 */
define([
    'jquery'
], function($) {
    'use strict';

    return function() {
        $(document).ready(function() {
            // Warte kurz, bis das DOM vollständig geladen ist
            setTimeout(function() {
                // Finde den Container und füge zusätzlichen Abstand hinzu
                var container = $('.authelia-oidc-button-container');
                if (container.length) {
                    // Überprüfe, ob der Button vom "Passwort vergessen" Link überdeckt wird
                    var forgotLink = $('.action-forgot');
                    if (forgotLink.length) {
                        // Berechne die Position und füge zusätzlichen Abstand hinzu
                        var forgotLinkBottom = forgotLink.offset().top + forgotLink.outerHeight();
                        var containerTop = container.offset().top;
                        
                        if (containerTop < forgotLinkBottom + 20) {
                            // Füge zusätzlichen Abstand hinzu, wenn nötig
                            container.css('margin-top', (forgotLinkBottom - containerTop + 20) + 'px');
                        }
                    }
                }
            }, 500);
        });
    };
});