/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ReportingPageController', ReportingPageController);

    ReportingPageController.$inject = ['$scope', 'piwik', '$rootScope', '$location', 'reportingPageModel'];

    function ReportingPageController($scope, piwik, $rootScope, $location, pageModel){

        pageModel.resetPage();

        $scope.pageModel = pageModel;

        $scope.renderPage = function () {
            $scope.done = false;

            pageModel.resetPage();

            var category = piwik.broadcast.getValueFromHash('category');
            var subcategory = piwik.broadcast.getValueFromHash('subcategory');

            if ((!category || !subcategory)) {
                $scope.wrongParams = true;
                return;
            }

            $scope.wrongParams = false;

            category = decodeURIComponent(category);
            subcategory = decodeURIComponent(subcategory);

            pageModel.fetchPage(category, subcategory).then(function () {
                $scope.loading = false;
                $scope.done = true;
            });
        }

        $scope.loading = true; // we only set loading on initial load
        $scope.renderPage();

        $rootScope.$on('$locationChangeSuccess', function () {
            // should be handled by $route
            $scope.renderPage();
        });
    }
})();
