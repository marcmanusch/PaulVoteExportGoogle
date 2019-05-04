<?php

namespace PaulVoteExportGoogle;

use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PaulVoteExportGoogle\Bootstrap\Database;

/**
 * Shopware-Plugin PaulVoteExportGoogle.
 */
class PaulVoteExportGoogle extends Plugin
{

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('paul_vote_export_google.plugin_dir', $this->getPath());
        parent::build($container);
    }

    /**
     * @param InstallContext $installContext
     */
    public function install(InstallContext $installContext)
    {
        $database = new Database(
            $this->container->get('models')
        );

        $database->install();
    }

    /**
     * @param UninstallContext $uninstallContext
     */
    public function uninstall(UninstallContext $uninstallContext)
    {
        $database = new Database(
            $this->container->get('models')
        );

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $database->uninstall();
    }

}
