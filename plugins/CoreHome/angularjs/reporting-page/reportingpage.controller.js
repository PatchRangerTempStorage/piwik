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

        function isIncludeRequestedFromThisTemplate(event)
        {
            // when including scope.pageContentUrl
            return event.targetScope === event.currentScope || event.targetScope.$parent === event.currentScope;
        }

        pageModel.resetPage();

        $scope.pageModel = pageModel;

        $scope.$on("$includeContentError", function(event) {
            if (isIncludeRequestedFromThisTemplate(event)) {
                $scope.loadingFailed = true;
                $scope.loading = false;
            }
        });

        $scope.pageContentLoaded = function () {
            $scope.loadingFailed = false;
            $scope.loading = false;
        };

        $scope.$on("$includeContentRequested", function(event) {
            if (isIncludeRequestedFromThisTemplate(event)) {
                $scope.loadingFailed = false;
                $scope.loading = true;
            }
        });

        $scope.renderPage = function () {
            pageModel.resetPage();

            var category = piwik.broadcast.getValueFromHash('category');
            var subcategory = piwik.broadcast.getValueFromHash('subcategory');

            if ((!category || !subcategory)) {
                var path = $location.path();
                if (-1 === path.indexOf('module=CoreHome&action=index')) {
                    // eg if dashboard url is given in hash
                    $scope.pageContentUrl = '?' + $location.path().substr(1);
                }

                return;
            }

            category = decodeURIComponent(category);
            subcategory = decodeURIComponent(subcategory);

            pageModel.fetchPage(category, subcategory).then(function () {
                $scope.loading = false;
            });
        }

        $scope.loading = true; // we only set loading on initial load
        $scope.renderPage();

        $rootScope.$on('$locationChangeSuccess', function () {
            $scope.renderPage();
        });
    }
})();
