<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;

use App\Form\CalendarEventFormType;
use App\Service\MiteService;
use App\Entity\Project;
use App\Entity\Service;
use App\Entity\CalendarMiteEntries;



// This form list for you Calendar Events
class CalendarEventsFormType extends AbstractType
{

    public function __construct( MiteService $miteService)
    {
        $this->miteService = $miteService;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('events', CollectionType::class, [
                 'required' => false, // #TODO: a minimum of one selected checkbox is required
                 'entry_type' => CalendarEventFormType::class,
                 'entry_options' => [
                       'label' => false 
                     ],
                 ])
            ->add('project', ChoiceType::class,  [
//                'class' => Project::class,
                'mapped' => false,
                'label' => false,
                'placeholder' => 'Select a project',
                'choices' => $this->getMiteProjects(), // request all available mite projects
                'choice_label' => 'name', 
                'choice_value' => 'id', 
            ])
            ->add('service', ChoiceType::class,  [
                'mapped' => false,
                'label' => false,
                'placeholder' => 'Select a service',
                'choices' => $this->getMiteServices(), 
                'choice_label' => 'name', 
                'choice_value' => 'id' 
            ])
            ->add('date', HiddenType::class, [
                'mapped' => false
            ] )
            ->add('saveBtn', SubmitType::class);


        /** @var \closure $myExtraFieldValidator **/
        $myExtraFieldValidator = function(FormEvent $event){
            $form = $event->getForm();

            echo "test";
            //$myExtraField = $form->get('events')->getData();
            $calEventForm = $form->get('events');
            foreach ($calEventForm as $key => $value) {
                echo $value->get('toSuggestions');
            }

            // if (empty($myExtraField)) {
            //   $form['myExtraField']->addError(new FormError("myExtraField must not be empty"));
            // } else {
            //     foreach ($myExtraField as $calEvent) {
            //         if ($calEvent->get)
            //     }
            // }
        };

        // adding the validator to the FormBuilderInterface
        $builder->addEventListener(FormEvents::POST_SUBMIT, $myExtraFieldValidator);
    }

    function getMiteProjects()
    {
        $projectsStdClass = $this->miteService->getMiteProjects();
        $projects = array();
        foreach ($projectsStdClass as $key => $value) {
            $project = new Project($value->project->id, $value->project->name);
            array_push($projects, $project);
        }
        return $projects;
    }

    function getMiteServices()
    {
        $servicesStdClass = $this->miteService->getMiteServices();
        $services = array();
        foreach ($servicesStdClass as $key => $value) {
            $service = new Service($value->service->id, $value->service->name);
            array_push($services, $service);
        }

        return $services;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CalendarMiteEntries::class,
        ]);
    }

}