<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ExportMessage;
use App\Repository\ExportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExportHandler {
    public function __construct(
        private KernelInterface $kernel,
        private EntityManagerInterface $entityManager,
        private ExportRepository $exportRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExportMessage $exportMessage) : void {
        $export = $this->exportRepository->find($exportMessage->getExportId());

        try {
            $podcastId = $export->getPodcast()->getId();

            $this->logger->notice('------------------------------------------------------------------------------');
            $this->logger->notice("Starting Export {$export->getId()} on Podcast {$podcastId}");

            $export->setWorkingStatus();
            $export->setProgress(0);
            $export->setMessage('Generating export files.');
            $this->entityManager->persist($export);
            $this->entityManager->flush();

            // setup the console command
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'app:export:podcast',
                'podcastId' => $podcastId,
                'format' => $export->getFormat(),
                'exportId' => $export->getId(),
            ]);

            $output = new StreamOutput(fopen('php://stdout', 'w'));
            $success = $application->run($input, $output);

            if ($success) {
                $export->setSuccessStatus();
                $export->setMessage('Export complete!');
            } else {
                $export->setFailureStatus();
            }
            $export->setProgress(100);
            $this->entityManager->persist($export);
            $this->entityManager->flush();

            $this->logger->notice("Finished Export {$export->getId()} on Podcast {$podcastId}");
            $this->logger->notice('------------------------------------------------------------------------------');
        } catch (Exception $e) {
            $export->setFailureStatus();
            $export->setMessage('There was a problem exporting the podcast.');
            $this->entityManager->persist($export);
            $this->entityManager->flush();

            $this->logger->error("Error Export {$export->getId()} Podcast {$podcastId} \n{$e->getMessage()}");
            $this->logger->notice('------------------------------------------------------------------------------');

            throw $e;
        }
    }
}
