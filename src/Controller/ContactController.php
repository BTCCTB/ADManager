<?php

namespace App\Controller;

use BisBundle\Service\BisPersonView;
use BisBundle\Service\PhoneDirectory;
use BisBundle\Service\Staff;
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

    /**
     * @var Staff
     */
    private $staff;

    public function __construct(PhoneDirectory $phoneDirectory, Staff $staff, TranslatorInterface $translator)
    {
        $this->phoneDirectory = $phoneDirectory;
        $this->translator = $translator;
        $this->staff = $staff;
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

    /**
     * @Route("/export", name="export")
     *
     * @return Response
     */
    public function export()
    {
        $contacts = $this->staff->getAll();

        $response = $this->render(
            'Contact/exports.csv.twig',
            [
                'contacts' => $contacts,
            ]
        );
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-all-enabel.csv"');
        return $response;
    }

    /**
     * @Route("/hq/export", name="hq_export")
     *
     * @return Response
     */
    public function exportHQ()
    {
        $contacts = $this->staff->getHQ();

        $response = $this->render(
            'Contact/exports.csv.twig',
            [
                'contacts' => $contacts,
            ]
        );
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-hq.csv"');
        return $response;
    }

    /**
     * @Route("/field/export", name="field_export")
     *
     * @return Response
     */
    public function exportField()
    {
        $contacts = $this->staff->getField();

        $response = $this->render(
            'Contact/exports.csv.twig',
            [
                'contacts' => $contacts,
            ]
        );
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-field.csv"');
        return $response;
    }

    /**
     * @Route("/resrep/export", name="resrep_export")
     *
     * @return Response
     */
    public function exportResRep()
    {
        $contacts = $this->staff->getResRep();

        $response = $this->render(
            'Contact/exports.csv.twig',
            [
                'contacts' => $contacts,
            ]
        );
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-resrep.csv"');
        return $response;
    }
}
