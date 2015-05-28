/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReportingPageController', ReportingPageController);

    ReportingPageController.$inject = ['$scope', 'piwik', '$rootScope', '$location', 'reportingPageModel'];

    function ReportingPageController($scope, piwik, $rootScope, $location, pageModel) {
        pageModel.resetPage();
        $scope.pageModel = pageModel;

        $scope.renderPage = function () {
            $scope.hasPage = true;

            var category = $location.search().category;
            var subcategory = $location.search().subcategory;

            if ((!category || !subcategory)) {
                pageModel.resetPage();
                $scope.loading = false;
                return;
            }

            pageModel.fetchPage(category, subcategory).then(function () {
                $scope.hasPage = !!pageModel.page;
                $scope.loading = false;
            });
        }

        $scope.loading = true; // we only set loading on initial load
        $scope.renderPage();

        $rootScope.$on('$locationChangeSuccess', function () {
            // should be handled by $route
            if (!$location.search().popover && !$location.search().forceChange) {
                $scope.renderPage();
            }
        });
    }
})();
