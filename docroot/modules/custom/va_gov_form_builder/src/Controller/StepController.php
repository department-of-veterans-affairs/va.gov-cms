<?php

namespace Drupal\va_gov_form_builder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

class StepController extends ControllerBase {

  protected $tempStore;

  public function __construct(PrivateTempStoreFactory $temp_store_factory) {
    $this->tempStore = $temp_store_factory->get('digital_form_steps');
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  public function stepAddChooseType(Request $request) {
    // Handle form submission
    if ($request->isMethod('POST')) {
      $form_data = $request->request->all();
      $stepType = $form_data['step_type'];

      // Redirect to paragraph edit page
      return new RedirectResponse('/node/add/digital_form/step/add/' . $stepType);
    }

    // Render the form for the paragraph
    $form = $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\StepAddChooseTypeForm');

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['step-add-choose-type-container']],
      'form' => $form,
    ];
  }

  public function stepAdd($paragraph_type, Request $request) {
    // Handle form submission
    if ($request->isMethod('POST')) {
      // Save data to temporary storage
      $form_data = $request->request->all();
      $this->tempStore->set('node_add_paragraph_add', $form_data);

      // Redirect back to the add-node page
      return new RedirectResponse('/node/add/digital_form');
    }

    // Render the form for the paragraph
    $form = $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\StepAddForm', $paragraph_type);

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['step-add-container']],
      'form' => $form,
    ];
  }
}
