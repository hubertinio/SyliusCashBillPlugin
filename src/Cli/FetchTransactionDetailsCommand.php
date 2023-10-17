<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Cli;

use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Hubertinio\SyliusCashBillPlugin\Model\Api\DetailsRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FetchTransactionDetailsCommand extends Command
{
    protected static $defaultName = 'sylius:payment:cashbill:details';

    protected static $defaultDescription = 'Fetch transaction details by id';

    public function __construct(
        private CashBillApiClientInterface $apiClient,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument(
            'id',
            InputArgument::REQUIRED,
            'You will find some in table sylius_payment.details with key cashBillId',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $request = new DetailsRequest("TEST_mbabsr6");
        $response = $this->apiClient->transactionDetails($request);

        $output->writeln(json_encode($response));
        return Command::SUCCESS;
    }
}