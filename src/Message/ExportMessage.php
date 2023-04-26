<?php

declare(strict_types=1);

namespace App\Message;

class ExportMessage {
    public function __construct(
        private int $exportId,
    ) {
    }

    public function getExportId() : int {
        return $this->exportId;
    }
}
