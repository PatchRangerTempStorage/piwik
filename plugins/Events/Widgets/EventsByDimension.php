<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Widgets;

use Piwik\Translation\Translator;
use Piwik\Widget\WidgetContainerConfig;

class EventsByDimension extends WidgetContainerConfig
{
    protected $layout = 'ByDimension';
    protected $id = 'Events';
    protected $category = 'General_Actions';
    protected $subCategory = 'Events_Events';

}
