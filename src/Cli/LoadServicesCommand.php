<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Cli;

use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClient;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * @TODO opcja save dla zapisu do tabeli shipping_method
 * @TODO opcja filter na service_id
 */
final class LoadServicesCommand extends Command
{
    protected   static $defaultName = 'sylius:payment:cachebill:load-services';

    protected static $defaultDescription = 'Get a list of available payment channels';

    private CashBillApiClientInterface $apiClient;

    public function __construct(CashBillApiClientInterface $apiClient)
    {
        parent::__construct();

        $this->apiClient = $apiClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = $this->apiClient::service_structure();
        $data = json_decode($data, true);

        $table = new Table($output);
        $table->setHeaders(['ID', 'Supplier', 'Method',  'Poland']);

        foreach ($data['response']['services'] ?? [] as $row) {
            $table->addRow([
                $row['service_id'],
                $row['supplier'],
                $row['name'],
                ((int) $row['domestic'])  ?  'x': '' ,
            ]);
        }

        $table->render();

        return  Command::SUCCESS;
    }
}