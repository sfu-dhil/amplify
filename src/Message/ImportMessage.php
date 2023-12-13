<?php

declare(strict_types=1);

namespace App\Message;

class ImportMessage {
    public function __construct(
        private int $importId,
    ) {}

    public function getImportId() : int {
        return $this->importId;
    }
}
