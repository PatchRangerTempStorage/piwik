<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreVisualizations\Visualizations;

use Piwik\DataTable;
use Piwik\FrontController;
use Piwik\Metrics;
use Piwik\Plugin\ViewDataTable;
use Piwik\Url;
use Piwik\View;

/**
 * This visualization will output HTML that displays a list of report names by category and
 * loads them by AJAX when clicked. The loaded report is displayed to the right
 * of the report listing.
 *
 * @property ByDimension\Config $config
 */
class ByDimension extends ViewDataTable
{
    const ID = 'bydimension';

    public static function getDefaultConfig()
    {
        return new ByDimension\Config();
    }

    /**
     * @see ViewDataTable::main()
     * @return mixed
     */
    public function render()
    {
        $view = new View('@CoreVisualizations/_dataTableViz_reportsByDimension.twig');

        $view->firstReport = "";

        // if there are reports & report categories added, render the first one so we can
        // display it initially
        $view->dimensionCategories = $this->config->getDimensionCategories();

        $firstReport = $this->config->getFirstReportWidget();

        if (!empty($firstReport)) {
            $oldGet  = $_GET;
            $oldPost = $_POST;

            foreach ($firstReport->getParameters() as $key => $value) {
                $_GET[$key] = $value;
            }

            $_POST = array();

            $module = $firstReport->getModule();
            $action = $firstReport->getAction();
            $view->firstReport = FrontController::getInstance()->fetchDispatch($module, $action);

            $_GET  = $oldGet;
            $_POST = $oldPost;
        }

        return $view->render();
    }
}
