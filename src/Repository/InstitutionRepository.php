<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Institution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Institution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Institution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Institution[]    findAll()
 * @method Institution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstitutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Institution::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('institution')
            ->orderBy('institution.id')
            ->getQuery();
    }

    /**
     * @param string $q
     *
     * @return Collection|Institution[]
     */
    public function typeaheadSearch($q) {
        $qb = $this->createQueryBuilder('institution');
        $qb->andWhere('institution.name LIKE :q');
        $qb->orderBy('institution.name', 'ASC');
        $qb->addOrderBy('institution.province', 'ASC');
        $qb->setParameter('q', "%{$q}%");

        return $qb->getQuery()->execute();
    }


}
