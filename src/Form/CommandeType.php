<?php  

namespace App\Form;

use App\Entity\Commande;
use App\Entity\User;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateCommande', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('montantTotal', MoneyType::class, [
                'currency' => 'EUR', // Adaptez selon votre devise
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom', // Adaptez selon la propriété à afficher (ex: username, email)
            ])
            ->add('produits', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom', // Adaptez selon la propriété à afficher
                'multiple' => true,
                'expanded' => false, // Pour afficher une liste déroulante avec sélection multiple
            ])
            
            
            
            
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
