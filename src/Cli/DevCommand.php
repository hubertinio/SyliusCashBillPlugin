<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Cli;

use Faker\Factory;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Api\Amount;
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
        $this->createTransaction($input, $output);

        return Command::SUCCESS;
    }

    public function createTransaction(InputInterface $input, OutputInterface $output): void
    {
        $faker = Factory::create('pl_PL');

        $amount = new Amount(
            $faker->randomFloat(2, 20, 100),
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

//        $request->description = $faker->words(3, true);

        /** @var TransactionResponse $response */
        $response = $this->apiClient->createTransaction($request);

        $output->writeln($response->id);
        $output->writeln($response->redirectUrl);
    }
}