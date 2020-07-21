<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ContributorRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContributorRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContributorRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContributorRole[]    findAll()
 * @method ContributorRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributorRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContributorRole::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('contributorRole')
            ->orderBy('contributorRole.id')
            ->getQuery();
    }

    /**
     * @param string $q
     *
     * @return Collection|ContributorRole[]
     */
    public function typeaheadSearch($q) {
        throw new \RuntimeException("Not implemented yet.");
        $qb = $this->createQueryBuilder('contributorRole');
        $qb->andWhere('contributorRole.column LIKE :q');
        $qb->orderBy('contributorRole.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    
}
