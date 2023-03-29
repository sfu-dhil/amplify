<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Export;
use App\Message\ExportMessage;
use App\Repository\ExportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExportHandler implements MessageHandlerInterface {
    private KernelInterface $kernel;

    private EntityManagerInterface $entityManager;

    private ExportRepository $exportRepository;

    private LoggerInterface $logger;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager, ExportRepository $exportRepository, LoggerInterface $logger) {
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
        $this->exportRepository = $exportRepository;
        $this->logger = $logger;
    }

    public function __invoke(ExportMessage $exportMessage) : void {
        $export = $this->exportRepository->find($exportMessage->getExportId());

        try {
            $season = $export->getSeason();
            $podcast = $season->getPodcast();

            $this->logger->notice('------------------------------------------------------------------------------');
            $this->logger->notice("Starting Export {$export->getId()} on Podcast {$podcast->getId()} Season {$season->getId()}");
            $export->setWorkingStatus();
            $this->updateExportMessage($export, 'Generating export files.');

            // setup the console command
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $projectRoot = $this->kernel->getProjectDir();
            $environment = $this->kernel->getEnvironment();
            $input = new ArrayInput([
                'command' => 'app:export:batch',
                // (optional) define the value of command arguments
                'seasonId' => $season->getId(),
                // (optional) pass options to the command
                'directory' => "{$projectRoot}/data/{$environment}/exports/podcast_{$podcast->getId()}/season_{$season->getId()}/{$export->getFormat()}/",
            ]);

            $output = new BufferedOutput();
            $application->run($input, $output);
            $this->logger->notice($output->fetch());

            $this->updateExportMessage($export, 'Packaging files into a zip.');
            // TODO: zip the export
            // TODO: store zip folder info in $export

            $export->setSuccessStatus();
            $this->updateExportMessage($export, 'The export has finished.');
            $this->logger->notice("Finished Export {$export->getId()} on Podcast {$podcast->getId()} Season {$season->getId()}");
            $this->logger->notice('------------------------------------------------------------------------------');
        } catch (Exception $e) {
            $export->setFailureStatus();
            $this->updateExportMessage($export, 'There was a problem exporting the podcast.');
            $this->logger->error("Error Export {$export->getId()} on Podcast {$podcast->getId()} Season {$season->getId()} \n{$e->getMessage()}");
            $this->logger->notice('------------------------------------------------------------------------------');

            throw $e;
        }
    }

    private function updateExportMessage(Export $export, string $message) : void {
        $export->setMessage($message);
        $this->entityManager->persist($export);
        $this->entityManager->flush();
    }
}
