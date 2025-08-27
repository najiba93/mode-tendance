<?php


namespace App\Form;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UserType extends AbstractType
{
    // Fonction qui construit les champs du formulaire
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ 
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
       
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
          
            ->add('nom', TextType::class, [
                'label' => 'Nom d’utilisateur',
            ])
           
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
         
            ->add('adressePostale', TextType::class, [
                'label' => 'Adresse postale',
            ])
          
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
            ])
          
            ->add('adresseLivraison', TextType::class, [
                'label' => 'Adresse de livraison',
            ]);
    }

    // Fonction qui configure les options du formulaire
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Le formulaire est lié à l'entité User
            'data_class' => User::class,
        ]);
    }
}
