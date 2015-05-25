<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Widgets;

use Piwik\Widget\WidgetContainerConfig;

class ContentsByDimension extends WidgetContainerConfig
{
    protected $layout = 'ByDimension';
    protected $id = 'Contents';
    protected $category = 'General_Actions';
    protected $subCategory = 'Contents_Contents';

}