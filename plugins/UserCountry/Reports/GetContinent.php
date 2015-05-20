<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\UserCountry\Columns\Continent;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetContainerConfig;
use Piwik\Widget\WidgetsList;

class GetContinent extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension      = new Continent();
        $this->name           = Piwik::translate('UserCountry_Continent');
        $this->documentation  = Piwik::translate('UserCountry_getContinentDocumentation');
        $this->metrics        = array('nb_visits', 'nb_uniq_visitors', 'nb_actions');
        $this->hasGoalMetrics = true;
        $this->order = 6;

        $this->subCategory = 'UserCountry_SubmenuLocations';
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addContainer($factory->createContainerWidget('Continent'));

        // $name   = Piwik::translate('UserCountry_WidgetLocation') . ' (' . Piwik::translate('UserCountry_Continent') . ')';
        // $widget = $factory->createWidget()->setName($name);
        $widget = $factory->createWidget();
        $widgetsList->addToContainerWidget('Continent', $widget);

        $widget = $factory->createWidget()->setAction('getDistinctCountries')->setName('');
        $widgetsList->addToContainerWidget('Continent', $widget);
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_exclude_low_population = false;
        $view->config->show_search = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control = false;
        $view->config->documentation = $this->documentation;
        $view->config->addTranslation('label', $this->dimension->getName());
    }

}
