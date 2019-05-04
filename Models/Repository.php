<?php

namespace PaulVoteExportGoogle\Models;

use Shopware\Components\Model\ModelRepository;

class Repository extends ModelRepository
{

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a list of RatingExport
     *
     * @param null $filter
     * @param null $orderBy
     * @param      $offset
     * @param      $limit
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($filter = null, $orderBy = null, $offset, $limit)
    {
        $builder = $this->getListQueryBuilder($filter, $orderBy);
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $orderBy
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($filter = null, $orderBy = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(array('rating_export_google'))
            ->from($this->getEntityName(), 'rating_export_google');

        $this->addFilter($builder, $filter);
        $this->addOrderBy($builder, $orderBy);

        return $builder;
    }
}
