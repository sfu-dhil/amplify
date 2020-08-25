<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Language|null find($id, $lockMode = null, $lockVersion = null)
 * @method Language|null findOneBy(array $criteria, array $orderBy = null)
 * @method Language[]    findAll()
 * @method Language[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LanguageRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Language::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('lanugage')->orderBy('lanugage.id')->getQuery();
    }

    /**
     * @param string $q
     *
     * @return Collection|Language[]
     */
    public function typeaheadSearch($q) {
        $qb = $this->createQueryBuilder('lanugage');
        $qb->andWhere('lanugage.label LIKE :q');
        $qb->orderBy('lanugage.label', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }
}
