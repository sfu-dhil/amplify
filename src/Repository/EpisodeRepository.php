<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Episode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Episode find($id, $lockMode = null, $lockVersion = null)
 * @method Episode[] findAll()
 * @method Episode[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Episode findOneBy(array $criteria, array $orderBy = null)
 *
 * @phpstan-extends ServiceEntityRepository<Episode>
 */
class EpisodeRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Episode::class);
    }
}
