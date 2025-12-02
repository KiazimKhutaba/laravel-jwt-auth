<?php

namespace Devkit2026\JwtAuth\Exceptions;

use Exception;

class JwtAuthException extends Exception
{
    protected string $errorCode;

    public function __construct(string $message, string $errorCode, int $code = 400)
    {
        parent::__construct($message, $code);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
    
    public function render($request)
    {
        return response()->json([
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
            ]
        ], $this->getCode());
    }
}
