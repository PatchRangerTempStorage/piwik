/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').factory('reportingMenuModel', reportingMenuModelService);

    reportingMenuModelService.$inject = ['$filter', '$q', 'piwikApi', 'reportingPagesModel', 'dashboardsModel'];

    function reportingMenuModelService ($filter, $q, piwikApi, reportingPagesModel, dashboardsModel) {

        // those sites are going to be displayed
        var model = {
            menu: [],
            fetchMenuItems: fetchMenuItems
        };

        return model;

        function buildMenuFromPages(pages, allDashboards, activeCategory, activeSubCategory)
        {
            var menu = [];

            var categoriesHandled = {};
            angular.forEach(pages, function (page, key) {
                var category   = page.category;
                var categoryId = category.id;

                if (categoriesHandled[categoryId]) {
                    return;
                }

                categoriesHandled[categoryId] = true;

                if (activeCategory && category.id === activeCategory) {
                    // this doesn't really belong here but placed it here for convenience
                    category.active = true;
                    category.hover  = true;
                }

                category.subcategories = [];

                angular.forEach(pages, function (page, key) {
                    if (page.category.id === categoryId) {
                        var subcategory = page.subcategory;

                        if (subcategory.id === activeSubCategory) {
                            subcategory.active = true;
                        }

                        // also this rather controller logic, not model logic
                        subcategory.html_url = 'module=CoreHome&action=index&category=' + categoryId + '&subcategory='+ subcategory.id;
                        category.subcategories.push(subcategory);
                    }
                });

                category.subcategories = $filter('orderBy')(category.subcategories, 'order');

                menu.push(category);

                return menu;
            });

            var dashboards = {
                name: 'Dashboards',  // TODO use translation
                order: 1,
                subcategories: []
            }

            angular.forEach(allDashboards, function (dashboard, key) {
                var subcategory = dashboard.name;

                if (!activeCategory) {
                    dashboards.active = true;
                    dashboards.hover  = true;
                }

                dashboard.order = key;
                dashboard.html_url = 'module=Dashboard&action=embeddedIndex&idDashboard=' + dashboard.id;

                dashboards.subcategories.push(dashboard);
            });
            menu.push(dashboards);

            return menu;
        }

        function fetchMenuItems(activeCategory, activeSubCategory)
        {
            var pagesPromise = reportingPagesModel.fetchAllPages();
            var dashboardsPromise = dashboardsModel.fetchAllDashboards();

            return $q.all([pagesPromise, dashboardsPromise]).then(function (response) {
                var pages = response[0];
                var dashboards = response[1];

                var menu = buildMenuFromPages(pages, dashboards, activeCategory, activeSubCategory);

                model.menu = $filter('orderBy')(menu, 'order');

                return model.menu;
            });
        }
    }
})();