<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('category')
            ->orderBy('category.id')
            ->getQuery();
    }

    /**
     * @param string $q
     *
     * @return Collection|Category[]
     */
    public function typeaheadSearch($q) {
        $qb = $this->createQueryBuilder('category');
        $qb->andWhere('category.label LIKE :q');
        $qb->orderBy('category.label', 'ASC');
        $qb->setParameter('q', "%{$q}%");

        return $qb->getQuery()->execute();
    }


}
