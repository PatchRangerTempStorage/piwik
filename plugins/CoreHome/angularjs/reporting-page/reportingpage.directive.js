/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Example:
 * <div piwik-reporting-menu></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikReportingPage', piwikReportingPage);

    piwikReportingPage.$inject = ['$document', 'piwik', '$filter', 'piwikApi', '$rootScope', '$location', 'reportingPageModel'];

    function piwikReportingPage($document, piwik, $filter, piwikApi, $rootScope, $location, pageModel){

        function isIncludeRequestedFromThisTemplate(event)
        {
            // when including scope.pageContentUrl
            return event.targetScope === event.currentScope || event.targetScope.$parent === event.currentScope;
        }

        return {
            restrict: 'A',
            scope: {
            },
            templateUrl: 'plugins/CoreHome/angularjs/reporting-page/reportingpage.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs, ngModel) {
                    pageModel.resetPage();

                    scope.pageModel = pageModel;

                    scope.$on("$includeContentError", function(event) {
                        if (isIncludeRequestedFromThisTemplate(event)) {
                            scope.loadingFailed = true;
                            scope.loading = false;
                        }
                    });

                    scope.pageLoaded = function () {
                        scope.loadingFailed = false;
                        scope.loading = false;
                    };

                    scope.$on("$includeContentRequested", function(event) {
                        if (isIncludeRequestedFromThisTemplate(event)) {
                            scope.loadingFailed = false;
                            scope.loading = true;
                        }
                    });

                    scope.renderPage = function () {
                        pageModel.resetPage();

                        var category = piwik.broadcast.getValueFromHash('category');
                        var subcategory = piwik.broadcast.getValueFromHash('subcategory');

                        if ((!category || !subcategory)) {
                            var path = $location.path();
                            if (-1 === path.indexOf('module=CoreHome&action=index')) {
                                // eg if dashboard url is given in hash
                                scope.pageContentUrl = '?' + $location.path().substr(1);
                            }

                            return;
                        }

                        category = decodeURIComponent(category);
                        subcategory = decodeURIComponent(subcategory);

                        pageModel.fetchPage(category, subcategory).then(function () {
                            scope.loading = false;
                        });
                    }

                    scope.loading = true; // we only set loading on initial load
                    scope.renderPage();

                    $rootScope.$on('$locationChangeSuccess', function () {
                        scope.renderPage();
                    });

                };
            }
        };
    }
})();