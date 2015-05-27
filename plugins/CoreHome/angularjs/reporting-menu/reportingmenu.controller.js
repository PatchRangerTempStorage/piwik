/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReportingMenuController', ReportingMenuController);

    ReportingMenuController.$inject = ['$scope', 'piwik', '$location', '$timeout', 'reportingMenuModel'];

    function ReportingMenuController($scope, piwik, $location, $timeout, menuModel){
        function markAllCategoriesAsInactive()
        {
            angular.forEach(menuModel.menu, function (cat) {
                cat.active = false;
                cat.hover = false;
                angular.forEach(cat.subcategories, function (subcat) {
                    subcat.active = false;
                });
            });
        }

        $scope.menuModel = menuModel;

        var timeoutPromise = null;

        $scope.enterCategory = function (category) {

            if (timeoutPromise) {
                $timeout.cancel(timeoutPromise);
            }

            angular.forEach(menuModel.menu, function (cat) {
                cat.hover = false;
            });

            category.hover = true;
        };

        $scope.leaveCategory = function (category) {

            if (timeoutPromise) {
                $timeout.cancel(timeoutPromise);
            }

            angular.forEach(menuModel.menu, function (cat) {
                if (!cat.active) {
                    cat.hover = false;
                }
            });

            timeoutPromise = $timeout(function () {
                angular.forEach(menuModel.menu, function (cat) {
                    if (cat.active) {
                        cat.hover = true;
                    }
                });
            }, 2000);
        };

        $scope.loadSubcategory = function (category, subcategory) {
            markAllCategoriesAsInactive();

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

            $location.search({
                idSite: idSite,
                period: period,
                date: date,
                category: category.id,
                subcategory: subcategory.id,
                random: parseInt(Math.random()* 100000, 10) // make sure $locationChangeSuccess will be triggered
            });
        };

        menuModel.fetchMenuItems().then(function (menu) {
            if (!$location.search().subcategory) {
                $scope.loadSubcategory(menu[0], menu[0].subcategories[0]);
            }
        });
    }
})();
