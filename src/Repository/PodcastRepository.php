<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Podcast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Podcast|null find($id, $lockMode = null, $lockVersion = null)
 * @method Podcast|null findOneBy(array $criteria, array $orderBy = null)
 * @method Podcast[]    findAll()
 * @method Podcast[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PodcastRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Podcast::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('podcast')
            ->orderBy('podcast.id')
            ->getQuery();
    }

    /**
     * @param string $q
     *
     * @return Collection|Podcast[]
     */
    public function typeaheadSearch($q) {
        throw new \RuntimeException("Not implemented yet.");
        $qb = $this->createQueryBuilder('podcast');
        $qb->andWhere('podcast.column LIKE :q');
        $qb->orderBy('podcast.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    
}
