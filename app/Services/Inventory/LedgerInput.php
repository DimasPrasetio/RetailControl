<?php

namespace App\Services\Inventory;

use App\Models\User;

/**
 * Optional context for a ledger entry.
 * Group these into one object to keep InventoryLedgerService::record() concise.
 */
readonly class LedgerInput
{
    public function __construct(
        public ?string $sourceType = null,
        public ?int $sourceId = null,
        public ?string $referenceNumber = null,
        public array $notes = [],
        public ?User $performedBy = null,
        public ?int $transactionUomId = null,
    ) {}
}
