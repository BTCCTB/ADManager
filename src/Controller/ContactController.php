<?php

namespace App\Controller;

use BisBundle\Service\PhoneDirectory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ContactController
 *
 * @package App\Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @Route("/contact", name="contact_")
 * @IsGranted("ROLE_USER")
 */
class ContactController extends AbstractController
{

    /**
     * @var PhoneDirectory
     */
    private $phoneDirectory;

    public function __construct(PhoneDirectory $phoneDirectory)
    {
        $this->phoneDirectory = $phoneDirectory;
    }

    /**
     * @Route("/my-country", name="own_country")
     *
     * @return Response
     */
    public function myCountry()
    {
        return $this->render(
            'Contact/myCountry.html.twig'
        );
    }
}
