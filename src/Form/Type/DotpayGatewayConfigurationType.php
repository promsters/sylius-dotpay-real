<?php
/**
 * Created by PhpStorm.
 * User: tomasz.ptak
 * Date: 30.10.2018
 * Time: 10:02
 */

namespace Enis\SyliusDotpayPlugin\Form\Type;


use Enis\SyliusDotpayPlugin\Bridge\DotpayBridgeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DotpayGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shop_id', TextType::class, [
                'label' => 'enis_sylius_dotpay_plugin.ui.shop_id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'enis_sylius_dotpay_plugin.shop_id.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('secret_key', TextType::class, [
                'label' => 'enis_sylius_dotpay_plugin.ui.secret_key',
                'constraints' => [
                    new NotBlank([
                        'message' => 'enis_sylius_dotpay_plugin.secret_key.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('environment', ChoiceType::class, [
                'choices' => [
                    'enis_sylius_dotpay_plugin.ui.sandbox' => DotpayBridgeInterface::SANDBOX_ENVIRONMENT,
                    'enis_sylius_dotpay_plugin.ui.production' => DotpayBridgeInterface::PRODUCTION_ENVIRONMENT,
                ],
                'label' => 'enis_sylius_dotpay_plugin.ui.environment',
                'constraints' => [
                    new NotBlank([
                        'message' => 'enis_sylius_dotpay_plugin.environment.not_blank',
                        'groups' => ['sylius'],
                    ])
                ],
            ])
        ;
    }
}