// SPDX-FileCopyrightText: Copyright (C) 2025 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

(function () {
    'use strict';

    angular
        .module('opalAdmin.controllers.about', [])
        .controller('about', thirdPartyController);

    thirdPartyController.$inject = ['$scope', '$rootScope', '$filter', '$http', '$sce', '$location', '$anchorScroll'];

    function thirdPartyController($scope, $rootScope, $filter, $http, $sce, $location, $anchorScroll) {
        const customRenderExtension = {
            renderer: {
                // Turn all license text blocks into collapsible sections using <details><summary>
                code(code) {
                    return `
                        <details>
                          <summary>${$filter('translate')('SHOW_LICENSE_TEXT')}</summary>
                          <pre><code>${code}</code></pre>
                        </details>
                    `;
                },
                // Convert all links to open in a new tab using a _blank target
                link(href, title, text) {
                    const titleAttr = title ? ` title="${title}"` : '';
                    return `<a href="${href}"${titleAttr} target="_blank" rel="noopener">${text}</a>`;
                }
            }
        };
        
        marked.use(customRenderExtension);
        // Configure Marked (GFM for auto-linkifying bare URLs)
        marked.setOptions({ gfm: true });

        const thirdPartyURL = window.location.protocol + '//' + window.location.host + '/THIRDPARTY.md';

        // Fetch the Markdown file
        $http.get(thirdPartyURL)
            .then(function(response) {
                let mdContent = response.data;
                // Remove both the comment block and the section header
                mdContent = mdContent.replace(/<!--[\s\S]*?-->\s*# Third-Party Dependencies\s*\n/, '');

                // Process the Markdown content into HTML
                let parsedHtml = marked.parse(mdContent);

                // If applicable, add a paragraph at the beginning stating that the section has not been translated
                if ($rootScope.siteLanguage !== 'EN')
                    parsedHtml = `<p class="third-party-pre">
                            ${$filter('translate')('UNTRANSLATED_PAGE_DISCLAIMER')}
                        </p>
                        <hr>`
                    + parsedHtml;

                // Trust the HTML to bypass Angular's sanitizer
                $scope.thirdPartyContent = $sce.trustAsHtml(parsedHtml);
            })
            .catch(function(error) {
                console.error('Error fetching Markdown file:', error);
        });

        $scope.gotoDisclaimer = function() {
            // Set the location hash to the id of the element
            $location.hash('healthcare-disclaimer');
            $anchorScroll();
        };
    }
})();
