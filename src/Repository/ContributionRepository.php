<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contribution[]    findAll()
 * @method Contribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contribution::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('contribution')
            ->orderBy('contribution.id')
            ->getQuery();
    }

    /**
     * @param string $q
     *
     * @return Collection|Contribution[]
     */
    public function typeaheadSearch($q) {
        throw new \RuntimeException("Not implemented yet.");
        $qb = $this->createQueryBuilder('contribution');
        $qb->andWhere('contribution.column LIKE :q');
        $qb->orderBy('contribution.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    
}
