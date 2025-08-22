<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Votre prénom']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom de famille',
                'attr' => ['placeholder' => 'Votre nom de famille']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'votre@email.com']
            ])
            ->add('adressePostale', TextType::class, [
                'label' => 'Adresse postale',
                'attr' => ['placeholder' => 'Votre adresse complète']
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['placeholder' => 'Votre numéro de téléphone']
            ])
            ->add('adresseLivraison', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr' => ['placeholder' => 'Adresse de livraison (optionnel)']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}