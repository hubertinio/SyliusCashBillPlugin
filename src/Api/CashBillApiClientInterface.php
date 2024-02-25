<?php

namespace Hubertinio\SyliusCashBillPlugin\Api;

use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsResponse;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;

/**
 * @see https://api.cashbill.pl/category/api/payment-gateway
 */
interface CashBillApiClientInterface
{
    public function setConfig(array $data): void;

    /**
     * @see https://api.cashbill.pl/api/payment-gateway/requesting-available-payment-channels
     */
    public function paymentChannels(): iterable;

    /**
     * @see https://api.cashbill.pl/api/payment-gateway/requesting-details-of-transaction
     */
    public function transactionDetails(DetailsRequest $request): DetailsResponse;

    /**
     * @see https://api.cashbill.pl/api/payment-gateway/creating-new-transaction
     */
    public function createTransaction(TransactionRequest $request): TransactionResponse;
}
