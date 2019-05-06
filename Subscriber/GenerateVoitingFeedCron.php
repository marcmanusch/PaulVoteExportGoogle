<?php

namespace PaulVoteExportGoogle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GenerateVoitingFeedCron
 * @package PaulVoteExportGoogle\Subscriber
 */
class GenerateVoitingFeedCron implements SubscriberInterface
{

    /** @var  ContainerInterface */
    private $container;
    /**
     * Frontend contructor.
     * @param ContainerInterface $container
     **/
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_GenerateVoitingFeedCron' => 'GenerateVoitingFeedCron'
        ];
    }

    /**
     * @param \Shopware_Components_Cron_CronJob $job
     * @return string
     */
    public function GenerateVoitingFeedCron(\Shopware_Components_Cron_CronJob $job)
    {
        $start = $this->startTimer();

        # Load all votes out of the DB
        $votes = $this->getVotes();

        # Builde array structure to generate XML
        $voteArray = array();

        $host = $this->getHost();
        $host = $host[0]['host'] . $host[0]['base_path'];

        foreach ($votes as $key => $vote) {

            $url = $this->getSeoUrl($vote['id']);
            $url = $host .'/'. $url[0]['path'];

            $voteArray[$key]['reviewer']['name'] = $this->convertText($vote['nameBewertung']);
            $voteArray[$key]['review_timestamp'] = $vote['voteDate'];
            $voteArray[$key]['title'] = $this->convertText($vote['headline']);
            $voteArray[$key]['content'] = $this->convertText($vote['comment']);
            $voteArray[$key]['review_url'] = $url;
            $voteArray[$key]['ratings']['overall'] = $vote['points'];
            $voteArray[$key]['products']['product']['product_ids']['gtins']['gtin'] = $vote['ean'];
            $voteArray[$key]['products']['product']['product_ids']['skus']['sku'] = $vote['ordernumber'];
            $voteArray[$key]['products']['product']['product_ids']['brands']['brand'] = $vote['brand'];
            $voteArray[$key]['products']['product']['product_ids']['product_name'] = $this->convertText($vote['name']);
            $voteArray[$key]['products']['product']['product_ids']['product_url'] = $url;
            $voteArray[$key]['is_spam'] = 'false';
        }


        # create XML
        $xml_votes_info = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><feed xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"http://www.google.com/shopping/reviews/schema/product/2.1/product_reviews.xsd\"><reviews></reviews></feed>");

        function array_to_xml($voteArray, $xml_votes_info) {
            foreach($voteArray as $key => $value) {
                if(is_array($value)) {
                    if(!is_numeric($key)){
                        $subnode = $xml_votes_info->addChild("$key");
                        array_to_xml($value, $subnode);
                    }elseif ($key === '@attributes') {
                        foreach ($subnode as $n => $v) {
                            $subnode = $xml_votes_info->addChild("$key");
                            $subnode->addAttribute($n, $v);
                        }
                    }
                    else{
                        $subnode = $xml_votes_info->addChild("review");
                        array_to_xml($value, $subnode);
                    }
                }
                else {
                    $xml_votes_info->addChild("$key","$value");
                }
            }
        }

        array_to_xml($voteArray, $xml_votes_info);
        $xml = $xml_votes_info->asXML();


        #replace <overall> to add attributes
        $xml = str_replace('<overall>','<overall min="1" max="5">', $xml);

       $this->saveVotes($xml);

        return 'Laufzeit: ' . gmdate("H:i:s", $this->stopTimer($start));
    }

    private function convertText($text)
    {
        $text = str_replace('<br>', ' ', $text);
        $text = str_replace('<br>', ' ', $text);
        $text = str_replace('<br />', ' ', $text);
        $text = str_replace('&', '&amp;', $text);
        $text = str_replace('<', '&lt;', $text);
        return $text;
    }

    private function getVotes() {

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->container->get('dbal_connection');
        $builder = $connection->createQueryBuilder();
        $builder->select('sa.id, sav.name AS nameBewertung, sav.datum AS voteDate, sas.name AS brand, headline, comment, points, ean, ordernumber, sa.name')
            ->from('s_articles_vote', 'sav')
            ->innerJoin('sav',
                's_articles',
                'sa',
                'sav.articleID = sa.id')
            ->innerJoin('sa',
                's_articles_categories_ro',
                'sacr',
                'sa.id = sacr.articleID')
            ->innerJoin('sacr',
                's_categories',
                'sc',
                'sacr.categoryID = sc.id')
            ->innerJoin('sa',
                's_articles_details',
                'sad',
                'sad.articleID = sa.id')
            ->innerJoin('sa',
            's_articles_supplier',
            'sas',
            'sa.supplierID = sas.id')
            ->where('sav.active = 1')
            ->andWhere('sa.active = 1')
            ->andWhere('sav.comment is not NULL')
            ->groupBy('sav.id, sav.articleID ,sav.name, sa.name, headline, comment, points, sav.datum, sav.active, email, answer, answer_date');
        $stmt = $builder->execute();
        return $stmt->fetchAll();
    }

    private function getSeoUrl($articleID) {

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->container->get('dbal_connection');
        $builder = $connection->createQueryBuilder();
        $builder->select('*')
            ->from('s_core_rewrite_urls')
            ->where("org_path = 'sViewport=detail&sArticle=".$articleID . "'")
            ->andWhere('main = 1');
        $stmt = $builder->execute();
        return $stmt->fetchAll();
    }

    private function saveVotes($feed) {

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->container->get('dbal_connection');
        $builder = $connection->createQueryBuilder();
        $builder->update('s_plugin_vote_export_google')
            ->set('feed', '?')
            ->setParameter(0, $feed)
            ->where('id = 1');
        $builder->execute();
    }

    public function getHost() {

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->container->get('dbal_connection');
        $builder = $connection->createQueryBuilder();
        $builder->select('host, base_path')
            ->from('s_core_shops');
        $stmt = $builder->execute();
        return $stmt->fetchAll();
    }

    private function startTimer()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    private function stopTimer($start, $round=2)
    {
        $endtime = $this->startTimer()-$start;
        $round   = pow(10, $round);
        return round($endtime*$round)/$round;
    }
}