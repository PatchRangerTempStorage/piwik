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

    piwikReportingPage.$inject = ['$document', 'piwik', '$filter', 'piwikApi', '$rootScope', '$location'];

    function piwikReportingPage($document, piwik, $filter, piwikApi, $rootScope, $location){
        return {
            restrict: 'A',
            scope: {
            },
            templateUrl: 'plugins/CoreHome/angularjs/reporting-page/reportingpage.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs, ngModel) {

                    function resetPage(scope)
                    {
                        scope.widgets = [];
                        scope.pageContentUrl  = '';
                        scope.evolutionReports = [];
                        scope.sparklineReports = [];
                    }

                    resetPage(scope);

                    var reportMetadata = [];
                    var reportMetadataPromise = piwikApi.fetch({
                        method: 'API.getReportMetadata',
                        idSites: piwik.idSite || piwik.broadcast.getValueFromUrl('idSite'),
                    }).then(function (response) {
                        reportMetadata = response;
                    });

                    function getRelatedReports(widget)
                    {
                        var found = [];

                        if (widget.isReport) {
                            angular.forEach(reportMetadata, function (report) {
                                if (report.relatedReports && report.module === widget.module && report.action === widget.action) {
                                    found = report.relatedReports;
                                }
                            });
                        }

                        return found;
                    }

                    function isIgnoredReport(reportsToIgonre, widget)
                    {
                        var found = false;

                        if (widget.isReport) {
                            angular.forEach(reportsToIgonre, function (report) {
                                if (report.module === widget.module &&
                                    report.action === widget.action) {
                                    found = true;
                                }
                            });
                        }

                        return found;
                    }


                    scope.$on("$includeContentError", function(event, args){
                        scope.loadFailed = true;
                        scope.loading = false;
                    });
                    scope.$on("$includeContentLoaded", function(event, args){
                        scope.loadFailed = false;
                        scope.loading = false;
                    });
                    scope.$on("$includeContentRequested", function(event, args){
                        scope.loadFailed = false;
                        scope.loading = true;
                    });

                    scope.renderPage = function (init) {
                        resetPage(scope);

                        // all this should be done via ng routes, url depends otherwise on translated category/subcategory which is no good
                        // this might also fix related reports?!? we need to generate module/action for category/subcategory!
                        var category = piwik.broadcast.getValueFromHash('category');
                        var subcategory = piwik.broadcast.getValueFromHash('subcategory');

                        if ((!category || !subcategory) && init) {
                            var path = $location.path();
                            if (-1 === path.indexOf('module=CoreHome&action=index')) {
                                // eg if dashboard url is given in hash
                                scope.pageContentUrl = '?' + $location.path().substr(1);
                            }

                            return;
                        }

                        // todo also check for module & action
                        if (!category || !subcategory) {

                            // load old fashioned way
                            scope.pageContentUrl = '?' + $location.path().substr(1);
                            return;
                        }

                        scope.loading = true;

                        // todo we actually have this data already from reporting menu, we should use angular routes
                        // here for even faster performance
                        // could also extract it in service could solve it as well
                        piwikApi.fetch({
                            method: 'API.getPageMetadata',
                            categoryId: category,
                            subcategoryId: subcategory
                        }).then(function (response) {

                            // here we make sure both API requests are done, we should do this later in routers!
                            reportMetadataPromise.then(function () {
                                scope.loading = false;


                                var widgets = [];

                                var reportsToIgnore = [];

                                angular.forEach(response.widgets, function (widget) {

                                    if (isIgnoredReport(reportsToIgnore, widget)) {
                                        return;
                                    }

                                    reportsToIgnore = reportsToIgnore.concat(getRelatedReports(widget));

                                    if (widget.viewDataTable && widget.viewDataTable === 'graphEvolution') {
                                        scope.evolutionReports.push(widget);
                                    } else if (widget.viewDataTable && widget.viewDataTable === 'sparklines') {
                                        scope.sparklineReports.push(widget);
                                    } else {
                                        widgets.push(widget);
                                    }
                                });

                                widgets = $filter('orderBy')(widgets, 'order');

                                function shouldBeRenderedWithFullWidth(widget)
                                {
                                    if ((widget.isContainer && widget.layout && widget.layout === 'ByDimension')
                                        || widget.viewDataTable === 'bydimension') {
                                        return true;
                                    }

                                    return widget.viewDataTable && widget.viewDataTable === 'tableAllColumns';
                                }

                                var groupedWidgets = [];

                                if (widgets.length === 1) {
                                    // if there is only one widget, we always display it full width
                                    groupedWidgets = widgets;
                                } else {
                                    for (var i = 0; i < widgets.length; i++) {
                                        var widget = widgets[i];

                                        if (shouldBeRenderedWithFullWidth(widget)) {
                                            widget.widgets = $filter('orderBy')(widget.widgets, 'order');

                                            groupedWidgets.push(widget);
                                        } else {

                                            var counter = 0;
                                            var left = [widget];
                                            var right = [];

                                            while (widgets[i+1] && !shouldBeRenderedWithFullWidth(widgets[i+1])) {
                                                i++;
                                                counter++;
                                                if (counter % 2 === 0) {
                                                    left.push(widgets[i]);
                                                } else {
                                                    right.push(widgets[i]);
                                                }
                                            }

                                            groupedWidgets.push({group: true, left: left, right: right});
                                        }
                                    }
                                }

                                scope.widgets = groupedWidgets;


                            });
                        });
                    }

                    scope.renderPage(true);

                    $rootScope.$on('$locationChangeSuccess', function () {

                        scope.renderPage();
                    });

                };
            }
        };
    }
})();