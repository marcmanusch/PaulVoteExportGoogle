<?php

namespace PaulVoteExportGoogle\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="s_plugin_vote_export_google")
 * @ORM\Entity(repositoryClass="Repository")
 */
class VoteExportGoogle extends ModelEntity
{
    /**
     * Primary Key - autoincrement value
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $feed
     *
     * @ORM\Column(name="feed", type="text", nullable=false)
     */
    private $feed;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFeed()
    {
        return $this->feed;
    }

    /**
     * @param $feed string
     */
    public function setFeed($feed)
    {
        $this->feed = $feed;
    }
}
