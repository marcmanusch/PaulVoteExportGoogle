<?php

/**
 * Frontend controller
 */
class Shopware_Controllers_Frontend_PaulVoteExportGoogle extends Enlight_Controller_Action
{
    public function indexAction()
    {
        # Here we load the productfeed out of the database s_plugin_vote_export_google

        # no template
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $this->Response()->setHeader('Content-Type', 'application/xml; charset=utf-8', true);


        print ($this->getFeed()[0]['feed']);
    }

    private function getFeed() {

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->container->get('dbal_connection');
        $builder = $connection->createQueryBuilder();
        $builder->select('feed')
            ->from('s_plugin_vote_export_google');
        $stmt = $builder->execute();
        return $stmt->fetchAll();
    }
}
