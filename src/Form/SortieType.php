<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie : '
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure du début de la sortie : '
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => "Date limite de l'inscription : "
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => "Nombre de places :",
                'attr' => [
                    'min' => 2
                ]
            ])
            ->add('duree', IntegerType::class, [
                'data' => 60,
                'label'=> "Durée (en minutes) :",
                'attr' => [
                    'min' => 30
                ]
                /*'label' => 'Durée (en minutes) : ',
                'choices' => array_combine(range(30, 240, 15), range(30, 240, 15)), // de 30 à 240 minutes, par tranche de 15 minutes
                'data' => 30 // durée par défaut*/
            ])

            ->add('infosSortie', TextareaType::class, [
                'attr' => [
                    'rows' => 5,
                ],
            ])
            /*->add('etatE', EntityType::class, [
                'class' => Etat::class,
                    'label' => 'Etat : ',
                    'choice_label' => 'libelle',
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('e')
                            ->orderBy('e.libelle', 'ASC');
                    },
                 ])*/
            ->add('Lieu', EntityType::class,[
                'class' => Lieu::class,
                'label' => 'Lieu : ',
                'choice_label' => 'nom',
                'query_builder' => function(EntityRepository $er){
                return $er->createQueryBuilder('l')
                    ->orderBy('l.nom', 'ASC');
                }

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
