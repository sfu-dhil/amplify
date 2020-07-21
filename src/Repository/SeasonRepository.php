<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Season|null find($id, $lockMode = null, $lockVersion = null)
 * @method Season|null findOneBy(array $criteria, array $orderBy = null)
 * @method Season[]    findAll()
 * @method Season[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('season')
            ->orderBy('season.id')
            ->getQuery();
    }

    /**
     * @param string $q
     *
     * @return Collection|Season[]
     */
    public function typeaheadSearch($q) {
        throw new \RuntimeException("Not implemented yet.");
        $qb = $this->createQueryBuilder('season');
        $qb->andWhere('season.column LIKE :q');
        $qb->orderBy('season.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    
}
