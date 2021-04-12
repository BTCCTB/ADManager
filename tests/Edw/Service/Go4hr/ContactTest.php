<?php


namespace Tests\Edw\Service\Go4hr;


use Edw\Service\Go4hr\Contact;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ContactTest
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 * @group edw
 * @group external
 * @group api
 */
class ContactTest extends WebTestCase
{
    private $service;

    public function setUp(): void
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();

        $this->service = $container->get('test.edw_api_go4hr_contact');

        parent::setUp();
    }

    public function testGetCollections()
    {
        $response = $this->service->getCollections();

        $this->assertNotFalse($response);
        $this->assertIsArray($response);
    }

}