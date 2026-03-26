<?php

namespace App\Exceptions;

use Exception;

class StockInsuficienteException extends Exception
{
    public function __construct(string $message = 'Stock insuficiente para completar la operación.', int $code = 422)
    {
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error' => 'STOCK_INSUFICIENTE',
        ], $this->getCode());
    }
}
