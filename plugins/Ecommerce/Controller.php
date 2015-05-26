<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce;

use Exception;
use Piwik\DataTable;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Translation\Translator;
use Piwik\View;

class Controller extends \Piwik\Plugins\Goals\Controller
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct($translator);
    }

    public function getSparklines()
    {
        $view = $this->getGoalReportView($idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);
        $view->displayFullReport = false;
        $view->conversionsOverViewEnabled = false;
        $view->headline = false;

        return $view->render();
    }

    public function ecommerceLogReport($fetch = false)
    {
        $view = new View('@Ecommerce/ecommerceLog');
        $this->setGeneralVariablesView($view);

        $view->ecommerceLog = $this->getEcommerceLog($fetch);

        return $view->render();
    }

    public function getEcommerceLog($fetch = false)
    {
        $saveGET = $_GET;
        $_GET['segment'] = urlencode('visitEcommerceStatus!=none');
        $_GET['widget'] = 1;
        $output = FrontController::getInstance()->dispatch('Live', 'getVisitorLog', array($fetch));
        $_GET   = $saveGET;

        return $output;
    }

    public function index()
    {
        return $this->ecommerceReport();
    }

}
