<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\ByDimension;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\Goals\Goals;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Get extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('Goals_Goals');
        $this->processedMetrics = array('conversion_rate');
        $this->documentation = ''; // TODO
        $this->order = 1;
        $this->orderGoal = 50;
        $this->metrics = array('nb_conversions', 'nb_visits_converted', 'revenue');
        $this->parameters = null;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $orderId = 1;

        $idSite = Common::getRequestVar('idSite', null, 'int');
        $goals  = API::getInstance()->getGoals($idSite);

        $config = $factory->createWidget();
        $config->forceViewDataTable(Evolution::ID);
        $config->setSubCategory('General_Overview');
        $config->setName('General_EvolutionOverPeriod');
        $config->setAction('getEvolutionGraph');
        $config->setOrder(++$orderId);
        $config->setParameters(array('columns' => 'nb_conversions'));
        $widgetsList->addWidget($config);

        $config = $factory->createWidget();
        $config->forceViewDataTable(Sparklines::ID);
        $config->setSubCategory('General_Overview');
        $config->setName('');
        $config->setOrder(++$orderId);
        $widgetsList->addWidget($config);

        $config = $factory->createWidget();
        $config->forceViewDataTable(ByDimension::ID);
        $config->setSubCategory('General_Overview');
        $config->setName('Goals_ConversionsOverviewBy');
        $config->setOrder(++$orderId);
        $widgetsList->addWidget($config);

        $config = $factory->createWidget();
        $config->forceViewDataTable(Evolution::ID);
        $config->setCategory('Goals_Ecommerce');
        $config->setSubCategory('General_Overview');
        $config->setName('General_EvolutionOverPeriod');
        $config->setAction('getEvolutionGraph');
        $config->setOrder(++$orderId);
        $config->setParameters(array('columns' => 'nb_conversions', 'idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
        $widgetsList->addWidget($config);

        $config = $factory->createWidget();
        $config->setCategory('Goals_Ecommerce');
        $config->forceViewDataTable(Sparklines::ID);
        $config->setSubCategory('General_Overview');
        $config->setName('');
        $config->setModule('Ecommerce');
        $config->setAction('getSparklines');
        $config->setParameters(array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
        $config->setOrder(++$orderId);
        $widgetsList->addWidget($config);

        $config = $factory->createWidget();
        $config->forceViewDataTable(ByDimension::ID);
        $config->setCategory('Goals_Ecommerce');
        $config->setSubCategory('Ecommerce_Sales');
        $config->setName('Ecommerce_Sales');
        $config->setParameters(array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
        $config->setOrder(++$orderId);
        $widgetsList->addWidget($config);

        $numGoals = count($goals);
        if ($numGoals > 0) {
            // TODO
            $showGoalsGrouped = $numGoals > 3;

            foreach ($goals as $goal) {
                $name   = Common::sanitizeInputValue($goal['name']);
                $params = array('idGoal' => $goal['idgoal']);

                $goalTranslated = Piwik::translate('Goals_GoalX', array($name));

                $config = $factory->createWidget();
                $config->setName($goalTranslated);
                $config->setSubCategory($name);
                $config->forceViewDataTable(Evolution::ID);
                $config->setAction('getEvolutionGraph');
                $config->setParameters($params);
                $config->setOrder(++$orderId);
                $widgetsList->addWidget($config);

                $config = $factory->createWidget();
                $config->setSubCategory($name);
                $config->setName('');
                $config->forceViewDataTable(Sparklines::ID);
                $config->setParameters($params);
                $config->setOrder(++$orderId);
                $widgetsList->addWidget($config);


                $config = $factory->createWidget();
                $config->setAction('goalConversionsOverview');
                $config->setSubCategory($name);
                $config->setName('Goals_ConversionsOverview');
                $config->setParameters($params);
                $config->setOrder(++$orderId);
                $widgetsList->addWidget($config);


                $config = $factory->createWidget();
                $config->setName(Piwik::translate('Goals_GoalConversionsBy', array($name)));
                $config->setSubCategory($name);
                $config->forceViewDataTable(ByDimension::ID);
                $config->setParameters($params);
                $config->setOrder(++$orderId);
                $widgetsList->addWidget($config);


                $config = $factory->createWidget();
                $config->setName($goalTranslated);
                $config->setSubCategory('General_Overview');
                $config->forceViewDataTable(Sparklines::ID);
                $config->setParameters($params);
                $config->setOrder(++$orderId);
                $config->setIsNotWidgetizable();
                $config->addParameters(array('allow_multiple' => (int) $goal['allow_multiple']));
                $widgetsList->addWidget($config);
            }
        }

    }

    private function getConversionForGoal($idGoal = '')
    {
        $request = new Request("method=Goals.get&format=original&idGoal=$idGoal");
        $datatable = $request->process();
        $dataRow = $datatable->getFirstRow();

        return $dataRow->getColumn('nb_conversions');
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(ByDimension::ID)) {
            $idGoal = Common::getRequestVar('idGoal', '', 'string');
            $ecommerce = $idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;

            $conversions = $this->getConversionForGoal();
            if ($ecommerce) {
                $cartNbConversions = $this->getConversionForGoal($idGoal);
            } else {
                $cartNbConversions = false;
            }

            $preloadAbandonedCart = $cartNbConversions !== false && $conversions == 0;

            // add ecommerce reports
            $ecommerceCustomParams = array();
            if ($ecommerce) {
                if ($preloadAbandonedCart) {
                    $ecommerceCustomParams['abandonedCarts'] = '1';
                } else {
                    $ecommerceCustomParams['abandonedCarts'] = '0';
                }
            }

            if (Common::getRequestVar('idGoal', '') === '') // if no idGoal, use 0 for overview
            {
                $customParams['idGoal'] = '0'; // NOTE: Must be string! Otherwise Piwik_View_HtmlTable_Goals fails.
            }

            if ($conversions > 0 || $ecommerce) {
                // for non-Goals reports, we show the goals table
                $customParams = $ecommerceCustomParams + array('documentationForGoalsPage' => '1');

                if (Common::getRequestVar('idGoal', '') === '') // if no idGoal, use 0 for overview
                {
                    $customParams['idGoal'] = '0'; // NOTE: Must be string! Otherwise Piwik_View_HtmlTable_Goals fails.
                }

                $allReports = Goals::getReportsWithGoalMetrics();
                foreach ($allReports as $category => $reports) {
                    if ($ecommerce) {
                        $categoryText = Piwik::translate('Ecommerce_ViewSalesBy', $category);
                    } else {
                        $categoryText = Piwik::translate('Goals_ViewGoalsBy', $category);
                    }

                    foreach ($reports as $report) {
                        if (empty($report['viewDataTable'])
                            && empty($report['abandonedCarts'])
                        ) {
                            $report['viewDataTable'] = 'tableGoals';
                        }

                        $instance = Report::factory($report['module'], $report['action']);
                        $widget = $view->config->addReport($instance);
                        $widget->setParameters($customParams);
                        $widget->setCategory($categoryText);

                        if (!empty($report['viewDataTable'])) {
                            $widget->setDefaultView($report['viewDataTable']);
                        }
                    }
                }
            }

        } elseif ($view->isViewDataTableId(Sparklines::ID)) {
            $idGoal = Common::getRequestVar('idGoal', 0, 'int');

            if (empty($idGoal)) {

                $view->config->addSparklineMetricsToDisplay(array('nb_conversions'));
                $view->config->addSparklineMetricsToDisplay(array('conversion_rate'));
                $view->config->addSparklineMetricsToDisplay(array('revenue'));

                $view->config->addTranslations(array(
                    'nb_conversions' => Piwik::translate('Goals_Conversions'),
                    'conversion_rate' => Piwik::translate('Goals_OverallConversionRate'),
                    'revenue' => Piwik::translate('Goals_OverallRevenue'),
                ));

            } else {
                $allowMultiple = Common::getRequestVar('allow_multiple', 0, 'int');

                $view->config->addSparklineMetricsToDisplay(array('nb_conversions'));

                if ($allowMultiple) {
                    $view->config->addSparklineMetricsToDisplay(array('nb_visits_converted'));
                }

                $view->config->addSparklineMetricsToDisplay(array('conversion_rate'));
                $view->config->addTranslations(array(
                    'nb_conversions' => Piwik::translate('Goals_Conversions'),
                    'nb_visits_converted' => Piwik::translate('General_NVisits'),
                    'conversion_rate' => Piwik::translate('Goals_ConversionRate'),
                ));
            }
        }
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        if (!$this->isEnabled()) {
            return;
        }

        parent::configureReportMetadata($availableReports, $infos);

        $this->addReportMetadataForEachGoal($availableReports, $infos, function ($goal) {
            return Piwik::translate('Goals_GoalX', $goal['name']);
        });
    }
}
