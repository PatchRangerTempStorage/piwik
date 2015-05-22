<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\ByDimension;
use Piwik\Metrics;
use Piwik\Plugin\Report;
use Piwik\Report\ReportWidgetConfig;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Url;

/**
 * DataTable Visualization that derives from ByDimension.
 */
class Config extends \Piwik\ViewDataTable\Config
{
    /**
     * @var ReportWidgetConfig[]
     */
    public $reportWidgets = array();

    /**
     * @return ReportWidgetConfig
     */
    public function getFirstReportWidget()
    {
        return reset($this->reportWidgets);
    }

    public function getDimensionCategories()
    {
        $categories = array();

        foreach ($this->reportWidgets as $reportWidget) {
            $category = $reportWidget->getCategory();

            if (!isset($categories[$category])) {
                $categories[$category] = array();
            }

            $params = $reportWidget->getParameters();

            $categories[$category][] = array(
                'title'  => $reportWidget->getName(),
                'params' => $params,
                'url'    => Url::getCurrentQueryStringWithParametersModified($params)
            );
        }

        return $categories;
    }

    public function addReportWidget(ReportWidgetConfig $reportWidgetConfig)
    {
        $this->reportWidgets[] = $reportWidgetConfig;
    }

    /**
     * Adds a set of reports to the list of reports to display.
     *
     * @param Report $report
     *
     * @return ReportWidgetConfig
     */
    public function addReport(Report $report)
    {
        if (empty($report)) {
            return;
        }

        $factory = new ReportWidgetFactory($report);
        $widget  = $factory->createWidget();
        $this->addReportWidget($widget);

        return $widget;
    }

}
