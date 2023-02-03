<?php

namespace App\Form;

use App\Entity\Todo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TodoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('end_date', DateType::class, [
                'label' => 'Ajouter une tâche',
                'widget' => 'single_text',
                'input' => 'string'
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre'
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['rows' => 5],
                'label' => 'La description'
            ])
            ->add('status', ChoiceType::class, [
                'choices'=> [
                    'Pending' => 'pending', 
                    'In progress' => 'inprogress', 
                    'Completed' => 'completed'
                ],
                'label' => 'Statut'
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (Images)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        // 'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/*'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image File',
                    ])
                ],
                'label' => 'Photo'
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary btn-block'],
                'label' => 'Sauvegarder'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
        ]);
    }
}
