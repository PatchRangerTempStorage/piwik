<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CorePluginsAdmin
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Url;
use Piwik\Version;
use Piwik\View;
use Piwik\PluginsManager;

/**
 *
 * @package CorePluginsAdmin
 */
class Controller extends \Piwik\Controller\Admin
{
    private $validSortMethods  = array('popular', 'newest', 'alpha');
    private $defaultSortMethod = 'popular';

    public function updatePlugin()
    {
        Piwik::checkUserIsSuperUser();

        $view = $this->configureView('@CorePluginsAdmin/updatePlugin');
        $view->errorMessage = '';

        $pluginName = Common::getRequestVar('pluginName', '', 'string');
        $pluginName = strip_tags($pluginName);
        $nonce      = Common::getRequestVar('nonce', '', 'string');

        if (empty($pluginName)) {
            throw new \Exception('Plugin parameter is missing');
        }

        $view->plugin = array('name' => $pluginName);

        if (!Nonce::verifyNonce('CorePluginsAdmin.updatePlugin', $nonce)) {
            $view->errorMessage = Piwik_Translate('ExceptionNonceMismatch');
            echo $view->render();
            return;
        }

        Nonce::discardNonce('CorePluginsAdmin.updatePlugin');

        try {
            $pluginInstaller = new PluginInstaller($pluginName);
            $pluginInstaller->installOrUpdatePluginFromMarketplace();

        } catch (PluginInstallerException $e) {
            $view->errorMessage = $e->getMessage();
            echo $view->render();
            return;
        }

        $marketplace  = new MarketplaceApiClient();
        $view->plugin = $marketplace->getPluginInfo($pluginName);

        echo $view->render();
    }

    public function installPlugin()
    {
        Piwik::checkUserIsSuperUser();

        $view = $this->configureView('@CorePluginsAdmin/installPlugin');
        $view->errorMessage = '';

        $pluginName = Common::getRequestVar('pluginName', '', 'string');
        $pluginName = strip_tags($pluginName);
        $nonce      = Common::getRequestVar('nonce', '', 'string');

        if (empty($pluginName)) {
            throw new \Exception('Plugin parameter is missing');
        }

        $view->plugin = array('name' => $pluginName);

        if (!Nonce::verifyNonce('CorePluginsAdmin.installPlugin', $nonce)) {
            $view->errorMessage = Piwik_Translate('ExceptionNonceMismatch');
            echo $view->render();
            return;
        }

        Nonce::discardNonce('CorePluginsAdmin.installPlugin');

        try {
            $pluginInstaller = new PluginInstaller($pluginName);
            $pluginInstaller->installOrUpdatePluginFromMarketplace();

        } catch (PluginInstallerException $e) {
            $view->errorMessage = $e->getMessage();
            echo $view->render();
            return;
        }

        $marketplace  = new MarketplaceApiClient();
        $view->plugin = $marketplace->getPluginInfo($pluginName);
        $view->nonce  = Nonce::getNonce('CorePluginsAdmin.activatePlugin');

        echo $view->render();
    }

    public function pluginDetails()
    {
        $pluginName = Common::getRequestVar('pluginName', '', 'string');

        if (empty($pluginName)) {
            return;
        }

        $view = $this->configureView('@CorePluginsAdmin/pluginDetails');

        $marketplace  = new MarketplaceApiClient();
        $view->plugin = $marketplace->getPluginInfo($pluginName);

        echo $view->render();
    }

    public function browsePlugins()
    {
        $query = Common::getRequestVar('query', '', 'string', $_POST);
        $query = strip_tags($query);
        $sort  = Common::getRequestVar('sort', $this->defaultSortMethod, 'string');

        if (!in_array($sort, $this->validSortMethods)) {
            $sort = $this->defaultSortMethod;
        }

        $view = $this->configureView('@CorePluginsAdmin/browsePlugins');

        $marketplace   = new Marketplace();
        $view->plugins = $marketplace->searchPlugins($query, $sort, $themesOnly = false);

        $view->query   = $query;
        $view->sort    = $sort;
        $view->installNonce = Nonce::getNonce('CorePluginsAdmin.installPlugin');
        $view->updateNonce  = Nonce::getNonce('CorePluginsAdmin.updatePlugin');
        $view->isSuperUser  = Piwik::isUserIsSuperUser();

        echo $view->render();
    }

    public function browseThemes()
    {
        $query = Common::getRequestVar('query', '', 'string', $_POST);
        $query = strip_tags($query);
        $sort  = Common::getRequestVar('sort', $this->defaultSortMethod, 'string');

        if (!in_array($sort, $this->validSortMethods)) {
            $sort = $this->defaultSortMethod;
        }

        $view = $this->configureView('@CorePluginsAdmin/browseThemes');

        $marketplace   = new Marketplace();
        $view->plugins = $marketplace->searchPlugins($query, $sort, $themesOnly = true);

        $view->query   = $query;
        $view->sort    = $sort;
        $view->installNonce = Nonce::getNonce('CorePluginsAdmin.installPlugin');
        $view->updateNonce  = Nonce::getNonce('CorePluginsAdmin.updatePlugin');
        $view->isSuperUser  = Piwik::isUserIsSuperUser();

        echo $view->render();
    }

    function extend()
    {
        $view = $this->configureView('@CorePluginsAdmin/extend');
        echo $view->render();
    }

    function plugins()
    {
        Piwik::checkUserIsSuperUser();

        $activated  = Common::getRequestVar('activated', false, 'integer', $_GET);
        $pluginName = Common::getRequestVar('pluginName', '', 'string');

        $view = $this->configureView('@CorePluginsAdmin/plugins');

        $view->activatedPluginName = '';
        if ($activated && $pluginName) {
            $view->activatedPluginName = $pluginName;
        }

        $view->updateNonce   = Nonce::getNonce('CorePluginsAdmin.updatePlugin');
        $view->activateNonce = Nonce::getNonce('CorePluginsAdmin.activatePlugin');
        $view->pluginsInfo   = $this->getPluginsInfo();

        $marketplace = new Marketplace();
        $view->pluginsHavingUpdate = $marketplace->getPluginsHavingUpdate($themesOnly = false);

        echo $view->render();
    }

    function themes()
    {
        Piwik::checkUserIsSuperUser();

        $activated  = Common::getRequestVar('activated', false, 'integer', $_GET);
        $pluginName = Common::getRequestVar('pluginName', '', 'string');
        $pluginName = strip_tags($pluginName);

        $view = $this->configureView('@CorePluginsAdmin/themes');

        $view->activatedPluginName = '';
        if ($activated && $pluginName) {
            $view->activatedPluginName = $pluginName;
        }

        $pluginsInfo = $this->getPluginsInfo($themesOnly = true);

        $view->updateNonce   = Nonce::getNonce('CorePluginsAdmin.updatePlugin');
        $view->activateNonce = Nonce::getNonce('CorePluginsAdmin.activatePlugin');
        $view->pluginsInfo   = $pluginsInfo;

        $marketplace = new Marketplace();
        $view->pluginsHavingUpdate = $marketplace->getPluginsHavingUpdate($pluginsInfo, $themesOnly = true);

        echo $view->render();
    }

    protected function configureView($template)
    {
        Piwik::checkUserIsNotAnonymous();
        $view = new View($template);
        $this->setBasicVariablesView($view);
        $this->displayWarningIfConfigFileNotWritable($view);
        return $view;
    }

    protected function getPluginsInfo($themesOnly = false)
    {
        $plugins = PluginsManager::getInstance()->returnLoadedPluginsInfo();

        foreach ($plugins as $pluginName => &$plugin) {
            if (!isset($plugin['info'])) {
                    $description = '<strong><em>'
                                . Piwik_Translate('CorePluginsAdmin_PluginNotCompatibleWith', array($pluginName, self::getPiwikVersion()))
                                . '</strong> <br/> '
                                . Piwik_Translate('CorePluginsAdmin_PluginAskDevToUpdate')
                                . '</em>';
                $plugin['info'] = array(
                    'description' => $description,
                    'version'     => Piwik_Translate('General_Unknown'),
                    'theme'       => false,
                );
            }
        }

        $pluginsFiltered = $this->keepPluginsOrThemes($themesOnly, $plugins);
        return $pluginsFiltered;
    }
    
    protected function keepPluginsOrThemes($themesOnly, $plugins)
    {
        $pluginsFiltered = array();
        foreach ($plugins as $name => $thisPlugin) {

            $isTheme = false;
            if (!empty($thisPlugin['info']['theme'])) {
                $isTheme = (bool)$thisPlugin['info']['theme'];
            }
            if (($themesOnly && $isTheme)
                || (!$themesOnly && !$isTheme)
            ) {
                $pluginsFiltered[$name] = $thisPlugin;
            }
        }
        return $pluginsFiltered;
    }

    public function deactivate($redirectAfter = true)
    {
        $pluginName = $this->initPluginModification();
        \Piwik\PluginsManager::getInstance()->deactivatePlugin($pluginName);
        $this->redirectAfterModification($redirectAfter);
    }

    protected function redirectAfterModification($redirectAfter)
    {
        if ($redirectAfter) {
            Url::redirectToReferer();
        }
    }

    protected function initPluginModification()
    {
        Piwik::checkUserIsSuperUser();
        $this->checkTokenInUrl();
        $pluginName = Common::getRequestVar('pluginName', null, 'string');
        return $pluginName;
    }

    public function activate($redirectAfter = true)
    {
        Piwik::checkUserIsSuperUser();

        $pluginName = Common::getRequestVar('pluginName', '', 'string');
        $pluginName = strip_tags($pluginName);
        $nonce      = Common::getRequestVar('nonce', '', 'string');

        if (empty($pluginName)) {
            throw new \Exception('Plugin parameter is missing');
        }

        if (!Nonce::verifyNonce('CorePluginsAdmin.activatePlugin', $nonce)) {
            throw new \Exception(Piwik_Translate('ExceptionNonceMismatch'));
        }

        Nonce::discardNonce('CorePluginsAdmin.activatePlugin');

        \Piwik\PluginsManager::getInstance()->activatePlugin($pluginName);

        if ($redirectAfter) {
            $params = array('activated' => 1, 'pluginName' => $pluginName);
            $plugin = PluginsManager::getInstance()->loadPlugin($pluginName);

            if ($plugin->isTheme()) {
                $this->redirectToIndex('CorePluginsAdmin', 'themes', null, null, null, $params);
            } else {
                $this->redirectToIndex('CorePluginsAdmin', 'plugins', null, null, null, $params);
            }
        }
    }

    public function uninstall($redirectAfter = true)
    {
        $pluginName = $this->initPluginModification();
        $uninstalled = \Piwik\PluginsManager::getInstance()->uninstallPlugin($pluginName);
        if (!$uninstalled) {
            $path = Filesystem::getPathToPiwikRoot() . '/plugins/' . $pluginName . '/';
            $messagePermissions = Filechecks::getErrorMessageMissingPermissions($path);

            $messageIntro = Piwik_Translate("Warning: \"%s\" could not be uninstalled. Piwik did not have enough permission to delete the files in $path. ",
                $pluginName);
            $exitMessage = $messageIntro . "<br/><br/>" . $messagePermissions;
            Piwik_ExitWithMessage($exitMessage, $optionalTrace = false, $optionalLinks = false, $optionalLinkBack = true);
        }
        $this->redirectAfterModification($redirectAfter);
    }

}
