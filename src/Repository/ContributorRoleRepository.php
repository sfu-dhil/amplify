<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ContributorRole;
use Doctrine\Persistence\ManagerRegistry;
use Nines\UtilBundle\Repository\TermRepository;

/**
 * @method null|ContributorRole find($id, $lockMode = null, $lockVersion = null)
 * @method ContributorRole[] findAll()
 * @method ContributorRole[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|ContributorRole findOneBy(array $criteria, array $orderBy = null)
 */
class ContributorRoleRepository extends TermRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, ContributorRole::class);
    }
}
