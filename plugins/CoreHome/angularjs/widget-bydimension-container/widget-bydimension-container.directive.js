/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Example:
 * <div piwik-widget-by-dimension-container container="widget"></div>
 */
(function () {
    angular.module('piwikApp').directive('piwikWidgetByDimensionContainer', piwikWidgetContainer);

    piwikWidgetContainer.$inject = ['piwik', '$location'];

    function piwikWidgetContainer(piwik, $location){
        return {
            restrict: 'A',
            scope: {
                container: '='
            },
            templateUrl: 'plugins/CoreHome/angularjs/widget-bydimension-container/widget-bydimension-container.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs, ngModel) {

                    function getFullWidgetUrl(widget) {
                        var params_vals = widget.widget_url.substr(1).split("&");

                        // available in global scope
                        var currentHashStr = $location.path();

                        if (currentHashStr.length != 0) {
                            for (var i = 0; i < params_vals.length; i++) {
                                currentHashStr = piwik.broadcast.updateParamValue(params_vals[i], currentHashStr);
                            }
                        }

                        return '?' + currentHashStr.substr(1);
                    }

                    var widgetsByCategory = {};
                    var firstCategoryName = null;

                    angular.forEach(scope.container.widgets, function (widget) {
                        var category = widget.subcategory.name;
                        if (!widgetsByCategory[category]) {
                            widgetsByCategory[category] = [];
                        }
                        if (!firstCategoryName) {
                            firstCategoryName = category;
                        }

                        widgetsByCategory[category].push(widget);
                    });

                    scope.firstCategoryName = firstCategoryName;
                    scope.widgetsByCategory = widgetsByCategory;


                    scope.$on("$includeContentError", function(event) {
                        scope.loading = false;
                    });
                    scope.$on("$includeContentLoaded", function(event) {
                        scope.loading = false;
                    });
                    scope.$on("$includeContentRequested", function(event) {
                        scope.loading = true;
                    });

                    scope.selectWidget = function (widget) {
                        if (!widget.html_url) {
                            widget.html_url = getFullWidgetUrl(widget);
                        } else {
                            widget.html_url += '&rand=5';
                        }
                        scope.selectedWidget = widget;
                    }

                    if (scope.container.widgets && scope.container.widgets.length) {
                        scope.selectWidget(scope.container.widgets[0]);
                    }
                };
            }
        };
    }
})();