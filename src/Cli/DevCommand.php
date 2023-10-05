<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Cli;

use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClient;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DevCommand extends Command
{
    protected static $defaultName = 'sylius:payment:cashbill:dev';

    protected static $defaultDescription = 'Dev API tests';

    private CashBillApiClientInterface $apiClient;

    private CashBillApiClientInterface $cachedApiClient;

    public function __construct(
        CashBillApiClientInterface $apiClient,
        CashBillApiClientInterface $cachedApiClient,
    ) {
        parent::__construct();

        $this->apiClient = $apiClient;
//        $this->cachedApiClient = $cachedApiClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getPayments($input, $output);

        return Command::SUCCESS;
    }

    public function getPayments(InputInterface $input, OutputInterface $output): void
    {
        $channels = $this->apiClient->paymentChannels();
        $table = new Table($output);
        $table->setHeaders(['id', 'name', 'description', 'logo', 'currencies']);

        foreach ($channels ?? [] as $channel) {
            $table->addRow(array_map(static function ($value) {
                if (!is_scalar($value)) {
                    return serialize($value);
                }

                return $value;
            }, $channel->toArray()));
        }

        $table->render();
    }

}