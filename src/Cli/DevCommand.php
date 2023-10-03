<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Cli;

use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClient;
use Hubertinio\SyliusCashBillPlugin\Api\CashBillApiClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DevCommand extends Command
{
    protected   static $defaultName = 'sylius:shipping:cashbill:dev';

    protected static $defaultDescription = 'Dev API tests';

    private CashBillApiClientInterface $apiClient;

    private CashBillApiClientInterface $cachedApiClient;

    public function __construct(
        CashBillApiClientInterface $apiClient,
        CashBillApiClientInterface $cachedApiClient,
    ) {
        parent::__construct();

        $this->apiClient = $apiClient;
        $this->cachedApiClient = $cachedApiClient;
    }

    private function getOrder(): array
    {
        return $order = [
            'service_id'  => 41, // endpoint: service_structure
            'address' => [
                'sender' => [
                    'country_code'       => 'PL', // Kod ISO 3166-1 alpha-2
                    'name'               => 'Rocket Design Michał Nowak',
                    'line1'              => 'Wierzbowa 33 m. 39',
                    'line2'              => '',
                    'postal_code'        => '90-245',
                    'city'               => 'Lodz',
                    'is_residential'     => 0,  // adres prywatny: 0 / 1
                    'contact_person'     => 'Michał Nowak',
                    'email'              => 'sylius@hubertmiazek.com',
                    'phone'              => '600824141',
                    'foreign_address_id' => 'LOD129M',
                ],
                'receiver' => [
                    'country_code'       => 'PL', // Kod ISO 3166-1 alpha-2
                    'name'               => 'Hubert Miazek',
                    'line1'              => 'Niciarniana 16 m. 50',
                    'line2'              => '',
                    'postal_code'        => '92-334',
                    'city'               => 'Lodz',
                    'is_residential'     => 1,  // adres prywatny: 0 / 1
                    'contact_person'     => 'Hubert Miazek',
                    'email'              => 'b2b@hubertmiazek.com',
                    'phone'              => '513671443',
                    'foreign_address_id' => 'LOD48N'  // endpoint: points
                ]
            ],
            'option'         => [
                '31' => 0, // powiadomienie sms,
                '11' => 0, // rod
                '19' => 0, // dostawa w sobotę,
                '25' => 0, // dostawa w godzinach,
                '58' => 0, // ostrożnie
            ],
            'notification' => [
                'new' => [ // Powiadomienia o utworzeniu przesyłki
                    'isReceiverEmail' => 1, // 0 / 1
                    'isReceiverSms'   => 0, // 0 / 1
                    'isSenderEmail'   => 1  // 0 / 1
                ],
                'sent' => [ // Powiadomienia o wysłaniu przesyłki
                    'isReceiverEmail' => 1, // 0 / 1
                    'isReceiverSms'   => 0, // 0 / 1
                    'isSenderEmail'   => 1, // 0 / 1
                    'isSenderSms'     => 0, // 0 / 1
                ],
                'exception' => [ // Powiadomienia o wyjątku
                    'isReceiverEmail' => 1, // 0 / 1
                    'isReceiverSms'   => 0, // 0 / 1
                    'isSenderEmail'   => 1, // 0 / 1
                    'isSenderSms'     => 0, // 0 / 1
                ],
                'delivered' => [ // Powiadomienia o doręczeniu
                    'isReceiverEmail' => 1, // 0 / 1
                    'isReceiverSms'   => 0, // 0 / 1
                    'isSenderEmail'   => 0, // 0 / 1
                    'isSenderSms'     => 0, // 0 / 1
                ]
            ],
            'shipment_value' => 9900,  // wartość w groszach
            'cod'            => [
                'amount'      => 0, // wartość w groszach
                'bankaccount' => ''
            ],
            'pickup'         => [
                'type'       => 'SELF', // endpoint: service_structure
//                'date'       => date('Y-m-d', strtotime('tomorrow')),     // Y-m-d
//                'hours_from' => '08:00',     // H:i - pickup_hours
//                'hours_to'   => '16:00'      // H:i - pickup_hours
            ],
            'shipment' => [
                [
                    'dimension1' => 10, // długość (length) cm
                    'dimension2' => 20, // szerokość (width) cm
                    'dimension3'  => 30, // wysokość (height) cm
                    'weight' => 1,  // kg
                    'is_nstd' => 0,  // 0 / 1
                    'shipment_type_code' => 'PACZKA', // endpoint: service_structure
                ],
            ],
//            'comment' => 'TEST ' . date('Y-m-d H:i:s'),
            'comment' => '',
            'content' => '',
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $order = $this->getOrder();
        $this->getOrderValuation($input, $output, $order);
//        $this->getOrderSend($input, $output, $order);

        $this->getOrders($input, $output);

        return Command::SUCCESS;
    }

    public function getOrderValuation(InputInterface $input, OutputInterface $output, array $order): void
    {
        $data = $this->apiClient::order_valuation($order);
        $output->writeln($data);
    }

    public function getOrders(InputInterface $input, OutputInterface $output): void
    {
        $data = $this->apiClient::orders();
        $output->writeln($data);
    }

    public function getOrderSend(InputInterface $input, OutputInterface $output, array $order): void
    {
        $data = $this->apiClient::order_send($order);
        $output->writeln($data);
    }
}