<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Export;
use App\Entity\Podcast;
use App\Repository\ExportRepository;
use App\Repository\PodcastRepository;
use App\Service\ModsExport;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:export:podcast')]
class ExportPodcastCommand extends Command {
    public function __construct(
        private EntityManagerInterface $em,
        private ModsExport $modsExport,
        private PodcastRepository $podcastRepository,
        private ExportRepository $exportRepository,
        private ?OutputInterface $output = null,
        private ?Podcast $podcast = null,
        private ?Export $export = null,
    ) {
        parent::__construct();
    }

    protected function configure() : void {
        $this->setDescription('Export Podcast to a supported format.');
        $this->addArgument(
            'podcastId',
            InputArgument::REQUIRED,
            'ID of podcast.'
        );
        $this->addArgument(
            'format',
            InputArgument::REQUIRED,
            'Format to export to. One of ["mods", "bepress"]'
        );
        $this->addArgument(
            'exportId',
            InputArgument::OPTIONAL,
            'ID of export.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $podcastId = $input->getArgument('podcastId');
        $format = $input->getArgument('format');
        $exportId = $input->getArgument('exportId') ?? '';

        $podcast = $this->podcastRepository->find($podcastId);
        if ( ! $podcast) {
            $output->writeln('No podcast found.');

            return 0;
        }

        if ('mods' === $format) {
            $isMods = true;
        } elseif ('bepress' === $format) {
            $isBepress = true;
        }

        if ( ! $isMods && ! $isBepress) {
            $output->writeln("Invalid export format {$format}");

            return 0;
        }

        $export = $exportId ? $this->exportRepository->find($exportId) : null;
        if ($exportId && ! $export) {
            $output->writeln('No export found.');

            return 0;
        }
        if (null === $export) {
            $export = new Export();
            $export->setWorkingStatus();
            $export->setMessage('');
            $export->setProgress(0);
            $export->setFormat($format);

            $podcast->addExport($export);
            $this->em->persist($export);
            $this->em->flush();
        }

        $startTime = microtime(true);

        try {
            if ($isMods) {
                $this->modsExport->exportPodcast($output, $podcast, $export);
            } elseif ($isBepress) {

            }
        } catch (Exception $e) {
            $export->setMessage('An unexpected error occurred.');
            $this->em->persist($export);
            $this->em->flush();

            $output->writeln('An unexpected error occurred.');
            $output->writeln("Message: {$e->getMessage()}");
            $output->writeln("Trace: {$e->getTraceAsString()}");
            $output->writeln("Error Export {$export->getId()} Message: {$e->getMessage()}");

            return 0;
        }
        $executionTime = microtime(true) - $startTime;
        $timeInMinutes = number_format($executionTime / 60.0, 2);
        $output->writeln("Export completed in {$timeInMinutes} minutes");
        $export->setMessage("Export completed in {$timeInMinutes} minutes");
        $this->em->persist($export);
        $this->em->flush();

        return 1;
    }
}
