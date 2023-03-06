<?php

declare(strict_types=1);

namespace App\Message;

class ExportMessage {
    private $exportId;

    public function __construct(int $exportId) {
        $this->exportId = $exportId;
    }

    public function getExportId(): int {
        return $this->exportId;
    }
}