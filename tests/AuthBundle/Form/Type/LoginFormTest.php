<?php

namespace Tests\AuthBundle\Form\Type;

use AuthBundle\Form\Type\LoginForm;
use Symfony\Component\Form\Test\TypeTestCase;

class LoginFormTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            '_username' => 'test@btcctb.org',
            '_password' => 'MyPassw0rd+',
            '_remember' => true,
        );

        $form = $this->factory->create(LoginForm::class);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
