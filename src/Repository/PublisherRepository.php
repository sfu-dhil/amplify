<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Publisher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Publisher|null find($id, $lockMode = null, $lockVersion = null)
 * @method Publisher|null findOneBy(array $criteria, array $orderBy = null)
 * @method Publisher[]    findAll()
 * @method Publisher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublisherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publisher::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('publisher')
            ->orderBy('publisher.id')
            ->getQuery();
    }

    /**
     * @param string $q
     *
     * @return Collection|Publisher[]
     */
    public function typeaheadSearch($q) {
        throw new \RuntimeException("Not implemented yet.");
        $qb = $this->createQueryBuilder('publisher');
        $qb->andWhere('publisher.column LIKE :q');
        $qb->orderBy('publisher.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    
}
