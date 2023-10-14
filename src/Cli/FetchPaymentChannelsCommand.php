<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Cli;

use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FetchPaymentChannelsCommand extends Command
{
    protected static $defaultName = 'sylius:payment:cashbill:channels';

    protected static $defaultDescription = 'Get a list of available payment channels';

    public function __construct(private CashBillApiClientInterface $apiClient)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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

        return  Command::SUCCESS;
    }
}