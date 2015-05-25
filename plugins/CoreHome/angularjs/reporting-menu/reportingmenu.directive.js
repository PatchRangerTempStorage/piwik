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
    angular.module('piwikApp').directive('piwikReportingMenu', piwikReportingMenu);

    piwikReportingMenu.$inject = ['$document', 'piwik', '$location', '$timeout', 'reportingMenuModel'];

    function piwikReportingMenu($document, piwik, $location, $timeout, menuModel){

        return {
            restrict: 'A',
            scope: {
            },
            templateUrl: 'plugins/CoreHome/angularjs/reporting-menu/reportingmenu.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs, ngModel) {
                    scope.menuModel = menuModel;

                    scope.menu = {};

                    var timeoutPromise = null;
                    scope.enterCategory = function (category) {
                        if (timeoutPromise) {
                            $timeout.cancel(timeoutPromise);
                        }
                        angular.forEach(scope.menu, function (cat) {
                            cat.hover = false;
                        });
                        category.hover = true;
                    };
                    scope.leaveCategory = function (category) {

                        if (timeoutPromise) {
                            $timeout.cancel(timeoutPromise);
                        }

                        angular.forEach(scope.menu, function (cat) {
                            if (!cat.active) {
                                cat.hover = false;
                            }
                        });

                        timeoutPromise = $timeout(function () {
                            angular.forEach(scope.menu, function (cat) {
                                if (cat.active) {
                                    cat.hover = true;
                                }
                            });
                        }, 2000);
                    };

                    scope.loadSubcategory = function (category, subcategory) {
                        angular.forEach(scope.menu, function (cat) {
                            cat.active = false;
                            cat.hover = false;
                            angular.forEach(cat.subcategories, function (subcat) {
                                subcat.active = false;
                            });
                        });

                        category.active = true;
                        category.hover = true;
                        subcategory.active = true;

                        // TODO this is a hack to make the dashboard widget go away, need to handle this in a route or so
                        $('.top_controls .dashboard-manager').hide();
                        $('#dashboardWidgetsArea').dashboard('destroy');

                        var idSite = broadcast.getValueFromHash('idSite');
                        if (!idSite) {
                            idSite = broadcast.getValueFromUrl('idSite');
                        }
                        var period = broadcast.getValueFromHash('period');
                        if (!period) {
                            period = broadcast.getValueFromUrl('period');
                        }
                        var date   = broadcast.getValueFromHash('date');
                        if (!date) {
                            date = broadcast.getValueFromUrl('date');
                        }

                        var url = 'idSite=' + idSite + '&period=' + period + '&date=' + date + '&';
                        var rand = parseInt(Math.random()* 100000, 10);
                        url += 'random=' + rand+ '&';
                        url += subcategory.html_url;

                        $location.path(url);
                    };

                    var url = $location.path();
                    url = encodeURI(url);
                    var activeCategory = decodeURIComponent(piwik.broadcast.getParamValue('category', url));
                    var activeSubCategory = decodeURIComponent(piwik.broadcast.getParamValue('subcategory', url));

                    menuModel.fetchMenuItems(activeCategory, activeSubCategory).then(function (menu) {
                        scope.menu = menu;

                        if (!piwik.broadcast.isHashExists()) {
                            scope.loadSubcategory(menu[0], menu[0].subcategories[0]);
                        }
                    });
                };
            }
        };
    }
})();