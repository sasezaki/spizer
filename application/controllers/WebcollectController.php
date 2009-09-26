<?php

class WebcollectController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $form = new Zend_Form();
        $form->addElement(new Zend_Form_Element_Text('username'));

        $this->view->form = $form;
    }

    public function queueAction()
    {
        // action body
    }


}



