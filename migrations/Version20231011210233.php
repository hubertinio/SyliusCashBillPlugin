<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Webmozart\Assert\Assert;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231011210233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create CashBill payments';
    }

    public function up(Schema $schema): void
    {
        $this->insertGateway();
        $gatewayId = $this->getGatewayId();

        $this->insertPaymentMethod($gatewayId);
        $cashbillMethodId = $this->getPaymentMethodId();

        foreach ($this->getChannels() as $channel) {
            $this->assignPaymentMethodToChannel($cashbillMethodId, (int) $channel['id']);
        }

        $this->insertPaymentMethodTranslation($cashbillMethodId);
        $this->addSql("SELECT * FROM sylius_payment_method");
    }

    public function down(Schema $schema): void
    {
        try {
            $gatewayId = $this->getGatewayId();
            $paymentMethodId = $this->getPaymentMethodId();

            $query = "DELETE FROM sylius_payment_method_channels  WHERE payment_method_id = {$paymentMethodId}";
            $this->connection->executeQuery($query);

            $query = "DELETE FROM sylius_payment_method_translation WHERE translatable_id = {$paymentMethodId}";
            $this->connection->executeQuery($query);
        } catch (\Exception $e) {
        } finally {
            $query = "DELETE FROM sylius_payment_method  WHERE code = 'cashbill'";
            $this->connection->executeQuery($query);

            $query = "DELETE FROM sylius_gateway_config WHERE factory_name = 'cashbill'";
            $this->connection->executeQuery($query);
        }
    }

    private function getGatewayId(): int
    {
        $query = "SELECT id FROM sylius_gateway_config WHERE factory_name = 'cashbill'";
        $gateway = $this->connection->fetchOne($query);
        Assert::notEmpty($gateway, 'Cashbill sylius_gateway_config does not exist');

        return (int) $gateway;
    }

    private function insertGateway(): void
    {
        $query = "INSERT INTO sylius_gateway_config (config, gateway_name, factory_name) VALUES ('[]', 'CashBill', 'cashbill')";
        $this->connection->executeQuery($query);
    }

    private function insertPaymentMethod($gatewayId): void
    {
        $query = "INSERT INTO sylius_payment_method (code, environment, is_enabled, position, created_at, updated_at, gateway_config_id) VALUES ('cashbill', null, 1, 1, ?, ?, ?)";
        $this->connection->executeQuery($query, [
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            $gatewayId
        ]);
    }

    private function assignPaymentMethodToChannel(int $cashbillMethodId, int $channelId): void
    {
        $this->connection->executeQuery("INSERT INTO sylius_payment_method_channels (payment_method_id, channel_id) VALUES (?, ?)", [
            $cashbillMethodId, $channelId
        ]);
    }

    private function getChannels(): array
    {
        return $this->connection->fetchAllAssociative("SELECT id FROM sylius_channel");
    }

    private function getPaymentMethodId(): int
    {
        $cashbillMethodId = $this->connection->fetchOne("SELECT id FROM sylius_payment_method WHERE code = 'cashbill'");
        Assert::notEmpty($cashbillMethodId, 'Cashbill sylius_payment_method does not exist');

        return (int) $cashbillMethodId;
    }

    public function insertPaymentMethodTranslation(int $cashbillMethodId): void
    {
        foreach (['pl_PL', 'en_US'] as $code) {
            $query = "INSERT INTO sylius_payment_method_translation (translatable_id, name, description, instructions, locale) VALUES (?, ?, ?, null, ?)";
            $this->connection->executeQuery($query, [
                $cashbillMethodId,
                'CashBill',
                'Online payment',
                $code
            ]);

        }

    }
}
