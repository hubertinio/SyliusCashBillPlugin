<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Form\Type;

use Hubertinio\SyliusCashBillPlugin\Bridge\CashBillBridgeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CashBillGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'environment',
                ChoiceType::class,
                [
                    'choices' => [
                        'hubertinio.cashbill.prod' => CashBillBridgeInterface::ENVIRONMENT_PROD,
                        'hubertinio.cashbill.sandbox' => CashBillBridgeInterface::ENVIRONMENT_SANDBOX,
                    ],
                    'label' => 'hubertinio.cashbill.environment',
                ]
            )
            ->add(
                'app_id',
                TextType::class,
                [
                    'label' => 'hubertinio.cashbill.app_id',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'hubertinio.cashbill.gateway_configuration.app_id.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            )->add(
                'app_secret',
                TextType::class,
                [
                    'label' => 'hubertinio.cashbill.app_secret',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'hubertinio.cashbill.gateway_configuration.app_secret.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            );
    }
}
