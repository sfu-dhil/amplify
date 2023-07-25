<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ExportCleanupMessage;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExportCleanupHandler {
    public function __construct(
        private KernelInterface $kernel,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExportCleanupMessage $exportCleanupMessage) : void {
        try {
            $this->logger->notice('------------------------------------------------------------------------------');
            $this->logger->notice('Starting Export Cleanup');

            // setup the console command
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'app:export:cleanup',
            ]);

            $output = new StreamOutput(fopen('php://stdout', 'w'));
            $application->run($input, $output);

            $this->logger->notice('Finished Export Cleanup');
            $this->logger->notice('------------------------------------------------------------------------------');
        } catch (Exception $e) {
            $this->logger->error("Error Export Cleanup \n{$e->getMessage()}");
            $this->logger->notice('------------------------------------------------------------------------------');

            throw $e;
        }
    }
}
