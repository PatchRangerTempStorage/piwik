/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').factory('dashboardsModel', dashboardsModel);

    dashboardsModel.$inject = ['piwikApi'];

    function dashboardsModel (piwikApi) {

        var dashboardsPromise = null;

        var model = {
            dashboards: [],
            fetchAllDashboards: fetchAllDashboards
        };

        return model;

        function fetchAllDashboards()
        {
            if (!dashboardsPromise) {
                dashboardsPromise = piwikApi.fetch({method: 'Dashboard.getDashboards'}).then(function (response) {
                    model.dashboards = response;
                    return response;
                });
            }

            return dashboardsPromise;
        }
    }
})();