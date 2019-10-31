<?php

namespace App\Controller;

use BisBundle\Service\BisPersonView;
use BisBundle\Service\PhoneDirectory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ContactController
 *
 * @package App\Controller
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 *
 * @Route("/contacts", name="contact_")
 * @Route("/contact", name="contact_old_")
 *
 * @IsGranted("ROLE_USER")
 */
class ContactController extends AbstractController
{

    /**
     * @var PhoneDirectory
     */
    private $phoneDirectory;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(PhoneDirectory $phoneDirectory, TranslatorInterface $translator)
    {
        $this->phoneDirectory = $phoneDirectory;
        $this->translator = $translator;
    }

    /**
     * @Route("/my-country", name="own_country")
     *
     * @param BisPersonView $bisPersonView
     *
     * @return Response
     */
    public function myCountry(BisPersonView $bisPersonView)
    {
        $user = $bisPersonView->getUser($this->getUser()->getEmail());
        $contacts = $this->phoneDirectory->getByCountry($user->getCountry());

        return $this->render(
            'Contact/listing.html.twig',
            [
                'contacts' => $contacts,
                'title' => $this->translator->trans('contact.my_country.title.h3'),
            ]
        );
    }

    /**
     * @Route("/", name="all")
     *
     * @return Response
     */
    public function index()
    {
        $contacts = $this->phoneDirectory->getAll();

        return $this->render(
            'Contact/listing.html.twig',
            [
                'contacts' => $contacts,
                'title' => $this->translator->trans('contact.all.title.h3'),
            ]
        );
    }

    /**
     * @Route("/hq", name="hq")
     *
     * @return Response
     */
    public function hq()
    {
        $contacts = $this->phoneDirectory->getHQ();

        return $this->render(
            'Contact/listing.html.twig',
            [
                'contacts' => $contacts,
                'title' => $this->translator->trans('contact.hq.title.h3'),
            ]
        );
    }

    /**
     * @Route("/field", name="field")
     *
     * @return Response
     */
    public function field()
    {
        $contacts = $this->phoneDirectory->getField();

        return $this->render(
            'Contact/listing.html.twig',
            [
                'contacts' => $contacts,
                'title' => $this->translator->trans('contact.field.title.h3'),
            ]
        );
    }
}
