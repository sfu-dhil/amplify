<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ExportRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'app:export:cleanup')]
class ExportCleanupCommand extends Command {
    public function __construct(
        private EntityManagerInterface $em,
        private ExportRepository $exportRepository,
        private ParameterBagInterface $parameterBagInterface,
        private Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        // TODO: switch from 7 day grace to ~2 day after initial user testing is completed
        // (want to keep exports around longer for potential debugging)
        $exports = $this->exportRepository->createQueryBuilder('export')
            ->andWhere('export.updated <= :d')
            ->setParameter('d', (new DateTimeImmutable())->sub(new DateInterval('P7D')))
            ->getQuery()
            ->getResult()
        ;

        foreach ($exports as $export) {
            $exportId = $export->getId();
            $lastUpdated = $export->getUpdated()->format('Y-m-d H:i:s');
            $relativePath = $export->getPath();
            $fullPath = $this->parameterBagInterface->get('export_root_dir') . "/{$relativePath}";

            // cleanup the zip file
            if ($relativePath && $this->filesystem->exists($fullPath)) {
                $this->filesystem->remove($fullPath);
            }
            $this->em->remove($export);
            $this->em->flush();

            $output->writeln("Removed Export {$exportId} last updated {$lastUpdated}");
        }

        return 1;
    }
}
