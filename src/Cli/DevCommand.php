<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Cli;

use Faker\Factory;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Api\Amount;
use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\PersonalData;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionRequest;
use Hubertinio\SyliusCashBillPlugin\Model\Api\TransactionResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DevCommand extends Command
{
    protected static $defaultName = 'sylius:payment:cashbill:dev';

    protected static $defaultDescription = 'Dev API tests';

    public function __construct(
        private CashBillApiClientInterface $apiClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->transactionDetails($input, $output);
//        $this->createTransaction($input, $output);

        return Command::SUCCESS;
    }

    /**
     * @see https://api.cashbill.pl/api/payment-gateway/requesting-details-of-transaction
     */
    public function transactionDetails(InputInterface $input, OutputInterface $output): void
    {
        $request = new DetailsRequest("TEST_mbabsr6");
        $response = $this->apiClient->transactionDetails($request);

        $output->writeln(json_encode($response));
    }

    /**
     * @see https://api.cashbill.pl/api/payment-gateway/creating-new-transaction
     */
    public function createTransaction(InputInterface $input, OutputInterface $output): void
    {
        $faker = Factory::create('pl_PL');

        $amount = Amount::createFromCent(
            $faker->randomNumber(5, true),
            $faker->randomElement(['PLN', 'USD', 'EUR']),
        );

        $personalData = new PersonalData();
        $personalData->firstName = $faker->firstName;
        $personalData->surname = $faker->lastName;
        $personalData->email = $faker->email;

        $request = new TransactionRequest(
            'Test ' . date('Y-m-d H:i:s'),
            $faker->randomElement(['PL', 'EN']),
            $amount,
            $personalData
        );

        $request->description = $faker->words(5, true);

        /** @var TransactionResponse $response */
        $response = $this->apiClient->createTransaction($request);

        $output->writeln($response->id);
        $output->writeln($response->redirectUrl);
    }
}