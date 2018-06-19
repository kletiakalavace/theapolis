<?php

namespace Theaterjobs\MainBundle\Utility\Traits;


use Symfony\Component\Form\Form;

/**
 * Trait for form helper methods
 * Trait FormTrait
 * @package Theaterjobs\MainBundle\Utility
 */
trait FormTrait
{

    /**
     * Generate an array contains a key -> value with the errors where the key is the name of the form field
     * @param Form $form
     * @return array
     */
    protected function getErrorMessagesAJAX(Form $form)
    {
        $errors = [];

        foreach ($form->getErrors(true) as $key => $err) {
            $cause = '\[' . $err->getOrigin()->getConfig()->getName() . '\]';
            $cause = str_replace(['children', '.', 'data'], '', $cause);
            $message = $err->getMessage();
            $key = $form->getName() . $cause;
            array_push($errors, ['field' => $key, 'message' => $message]);
        }
        return $errors;
    }
}