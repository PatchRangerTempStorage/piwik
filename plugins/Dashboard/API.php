<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetsList;

/**
 * This API is the <a href='http://piwik.org/docs/analytics-api/reference/' rel='noreferrer' target='_blank'>Dashboard API</a>: it gives information about dashboards.
 *
 * @method static \Piwik\Plugins\Dashboard\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    private $dashboard = null;

    public function __construct(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    /**
     * Get each dashboard that belongs to a user including the containing widgets that are placed within each dashboard.
     * If the user has not created any dashboard yet, the default dashboard will be returned.
     *
     * @return array[]
     */
    public function getDashboards()
    {
        $widgetsList = WidgetsList::get();

        $dashboards = $this->getUserDashboards($widgetsList);

        if (empty($dashboards)) {
            $dashboards = array($this->getDefaultDashboard($widgetsList));
        }

        return $dashboards;
    }

    /**
     * Get the default dashboard.
     *
     * @return array[]
     */
    private function getDefaultDashboard(WidgetsList $widgetsList)
    {
        $defaultLayout = $this->dashboard->getDefaultLayout();
        $defaultLayout = $this->dashboard->decodeLayout($defaultLayout);

        $defaultDashboard = array('name' => Piwik::translate('Dashboard_Dashboard'), 'layout' => $defaultLayout);

        $widgets = $this->getExistingWidgetsWithinDashboard($defaultDashboard, $widgetsList);

        return $this->buildDashboard($defaultDashboard, $widgets);
    }

    /**
     * Get all dashboards which a user has created.
     *
     * @return array[]
     */
    private function getUserDashboards(WidgetsList $widgetsList)
    {
        $userLogin = Piwik::getCurrentUserLogin();
        $userDashboards = $this->dashboard->getAllDashboards($userLogin);

        $dashboards = array();

        foreach ($userDashboards as $userDashboard) {
            $widgets = $this->getExistingWidgetsWithinDashboard($userDashboard, $widgetsList);
            $dashboards[] = $this->buildDashboard($userDashboard, $widgets);
        }

        return $dashboards;
    }

    private function getExistingWidgetsWithinDashboard($dashboard, WidgetsList $widgetsList)
    {
        $columns = $this->getColumnsFromDashboard($dashboard);

        $widgets = array();
        $columns = array_filter($columns);

        foreach ($columns as $column) {
            foreach ($column as $widget) {

                if ($this->widgetIsNotHidden($widget) && $this->widgetExists($widget, $widgetsList)) {
                    $module = $widget->parameters->module;
                    $action = $widget->parameters->action;

                    $widgets[] = array('module' => $module, 'action' => $action);
                }
            }
        }

        return $widgets;
    }

    private function getColumnsFromDashboard($dashboard)
    {
        if (is_array($dashboard['layout'])) {

            return $dashboard['layout'];
        }

        return $dashboard['layout']->columns;
    }

    private function buildDashboard($dashboard, $widgets)
    {
        return array('name' => $dashboard['name'], 'id' => $dashboard['iddashboard'], 'widgets' => $widgets);
    }

    private function widgetExists($widget, WidgetsList $widgetsList)
    {
        if (empty($widget->parameters->module)) {
            return false;
        }

        $module = $widget->parameters->module;
        $action = $widget->parameters->action;

        return $widgetsList->isDefined($module, $action);
    }

    private function widgetIsNotHidden($widget)
    {
        return empty($widget->isHidden);
    }
}
