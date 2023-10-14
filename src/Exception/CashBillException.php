<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Exception;

use Payum\Core\Exception\Http\HttpException;

final class CashBillException extends HttpException
{
    public const LABEL = 'CashBillException';

    public static function createFromStatus($status): self
    {
        $parts = [self::LABEL];

        if (property_exists($status, 'statusLiteral')) {
            $parts[] = '[reason literal] ' . $status->statusLiteral;
        }

        if (property_exists($status, 'statusCode')) {
            $parts[] = '[status code] ' . $status->statusCode;
        }

        if (property_exists($status, 'statusDesc')) {
            $parts[] = '[reason phrase] ' . $status->statusDesc;
        }

        $message = implode(\PHP_EOL, $parts);

        return new self($message);
    }
}