<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Export;
use App\Entity\Podcast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Export>
 *
 * @method null|Export find($id, $lockMode = null, $lockVersion = null)
 * @method null|Export findOneBy(array $criteria, array $orderBy = null)
 * @method Export[] findAll()
 * @method Export[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExportRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Export::class);
    }

    public function indexQuery(Podcast $podcast) : Query {
        return $this->createQueryBuilder('export')
            ->andWhere('export.podcast = :p')
            ->orderBy('export.created', 'ASC')
            ->setParameter('p', $podcast->getId())
            ->getQuery()
        ;
    }

    public function searchQuery(Podcast $podcast, string $q) : Query {
        return $this->createQueryBuilder('export')
            ->andWhere('export.podcast = :p')
            ->andWhere('export.format LIKE :q OR export.message LIKE :q')
            ->orderBy('export.created', 'ASC')
            ->setParameter('p', $podcast->getId())
            ->setParameter('q', "%{$q}%")
            ->getQuery()
        ;
    }
}
