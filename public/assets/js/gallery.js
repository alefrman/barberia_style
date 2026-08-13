/**
 * Galería — Lightbox con GLightbox
 * Inicializa el visor de imágenes de la galería pública.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var links = document.querySelectorAll('.gallery-lightbox');

        if (!links.length || typeof GLightbox === 'undefined') {
            return;
        }

        GLightbox({
            elements: links,
            loop: true,
            zoomable: true,
            touchNavigation: true,
            draggable: true,
        });
    });
})();
