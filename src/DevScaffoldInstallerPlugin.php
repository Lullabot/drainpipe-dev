<?php

declare(strict_types=1);

namespace Lullabot\DrainpipeDev;

use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Symfony\Component\Yaml\Yaml;

class DevScaffoldInstallerPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * Composer instance configuration.
     *
     * @var Config
     */
    protected $config;

    /**
     * Composer extra field configuration.
     *
     * @var array
     */
    protected $extra;

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->io = $io;
        $this->config = $composer->getConfig();
        $this->extra = $composer->getPackage()->getExtra();
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'onPostInstallCmd',
            ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
        ];
    }

    /**
     * Handle post install command events.
     *
     * @param Event $event the event to handle
     */
    public function onPostInstallCmd(Event $event)
    {
        $this->installCICommands();
    }

    /**
     * Handle post update command events.
     *
     * @param event $event The event to handle
     */
    public function onPostUpdateCmd(Event $event)
    {
        $this->installCICommands();
    }

    /**
     * Install CI Commands.
     */
    private function installCICommands(): void
    {
        $scaffoldPath = $this->config->get('vendor-dir') . '/lullabot/drainpipe-dev/scaffold';
        $fs = new Filesystem();
        // Nightwatch
        $fs->removeDirectory('./test/nightwatch');
        if (isset($this->extra['drainpipe']['testing']) && is_array($this->extra['drainpipe']['testing'])) {
            $fs->ensureDirectoryExists('./test');
            foreach ($this->extra['drainpipe']['testing'] as $testing) {
                if ($testing === 'Nightwatch') {
                    $fs->copy("$scaffoldPath/nightwatch/nightwatch.conf.js", './nightwatch.conf.js');
                    $fs->copy("$scaffoldPath/nightwatch/chrome.settings.php", './web/sites/chrome/settings.php');
                    $fs->copy("$scaffoldPath/nightwatch/firefox.settings.php", './web/sites/firefox/settings.php');
                    $fs->copy("$scaffoldPath/nightwatch/sites.php", './web/sites/sites.php');
                    $fs->copy("$scaffoldPath/nightwatch/example.nightwatch.js", './test/nightwatch/example.nightwatch.js');
                    if (file_exists('./.ddev/config.yaml')) {
                        $fs->copy("$scaffoldPath/nightwatch/docker-compose.selenium.yaml", './.ddev/docker-compose.selenium.yaml');
                    }
                }
            }
        }
    }
}
