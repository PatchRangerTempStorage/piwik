<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Widget;

use \Piwik\Plugin\Manager as PluginManager;

/**
 * Base type for metric metadata classes that describe aggregated metrics. These metrics are
 * computed in the backend data store and are aggregated in PHP when Piwik archives period reports.
 *
 * Note: This class is a placeholder. It will be filled out at a later date. Right now, only
 * processed metrics can be defined this way.
 */
class SubCategory
{
    protected $category = '';
    protected $name = '';

    /**
     * @var WidgetConfig[]
     */
    protected $widgets = array();

    protected $order = 99;

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getWidgetConfigs()
    {
        return $this->widgets;
    }

    public function addWidgetConfig(WidgetConfig $widget)
    {
        $this->widgets[] = $widget;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->name;
    }

    /** @return \Piwik\Widget\SubCategory[] */
    public static function getAllSubCategories()
    {
        $manager = PluginManager::getInstance();
        // todo move to Piwik\Widget\SubCategory
        $subcategories = $manager->findMultipleComponents('Widgets/SubCategories', '\\Piwik\\Widget\\SubCategory');

        $instances = array();
        foreach ($subcategories as $subcategory) {
            $instances[] = new $subcategory;
        }

        return $instances;
    }
}