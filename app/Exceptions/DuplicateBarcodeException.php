<?php

namespace App\Exceptions;

use RuntimeException;

class DuplicateBarcodeException extends RuntimeException
{
    public function __construct(string $barcode)
    {
        parent::__construct("Barcode [{$barcode}] sudah dipakai oleh produk lain di tenant yang sama.");
    }
}
