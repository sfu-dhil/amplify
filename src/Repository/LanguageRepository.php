<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Language;
use Doctrine\Persistence\ManagerRegistry;
use Nines\UtilBundle\Repository\TermRepository;

/**
 * @method null|Language find($id, $lockMode = null, $lockVersion = null)
 * @method Language[] findAll()
 * @method Language[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Language findOneBy(array $criteria, array $orderBy = null)
 */
class LanguageRepository extends TermRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Language::class);
    }
}
