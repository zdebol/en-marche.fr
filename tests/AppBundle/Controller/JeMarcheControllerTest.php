<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\JeMarcheReport;
use AppBundle\Repository\JeMarcheReportRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\AppBundle\SqliteWebTestCase;

class JeMarcheControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /** @var Client */
    private $client;

    /** @var JeMarcheReportRepository */
    private $jeMarcheReportRepostitory;

    /**
     * @group functionnal
     */
    public function testReportJeMarche()
    {
        return;
        // There should not be any report at the moment
        $this->assertEmpty($this->jeMarcheReportRepostitory->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/jemarche');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_je_marche]')->form([
            'g-recaptcha-response' => 'dummy',
            'app_je_marche[type]' => JeMarcheReport::TYPE_DOOR_TO_DOOR,
            'app_je_marche[postalCode]' => '60200',
            'app_je_marche[convinced]' => "abc@en-marche.fr\ndef@en-marche.fr\nghi@en-marche.fr",
            'app_je_marche[almostConvinced]' => "xyz@en-marche.fr\ntuv@en-marche.fr",
            'app_je_marche[notConvinced]' => '4',
            'app_je_marche[reaction]' => 'Réaction des gens',
            'app_je_marche[emailAddress]' => 'foobar@en-marche.fr',
        ]));

        $this->assertClientIsRedirectedTo('/jemarche/merci', $this->client);

        // Report should have been saved
        /* @var JeMarcheReport $report */
        $this->assertCount(1, $reports = $this->jeMarcheReportRepostitory->findAll());
        $this->assertInstanceOf(JeMarcheReport::class, $report = $reports[0]);
        $this->assertSame(JeMarcheReport::TYPE_DOOR_TO_DOOR, $report->getType());
        $this->assertSame('60200', $report->getPostalCode());
        $this->assertSame('foobar@en-marche.fr', $report->getEmailAddress());
        $this->assertSame(4, $report->getNotConvinced());
        $this->assertSame(['abc@en-marche.fr', 'def@en-marche.fr', 'ghi@en-marche.fr'], $report->getConvinced());
        $this->assertSame(['xyz@en-marche.fr', 'tuv@en-marche.fr'], $report->getAlmostConvinced());
        $this->assertSame('Réaction des gens', $report->getReaction());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([]);

        $this->jeMarcheReportRepostitory = $this->getJeMarcheReportRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->jeMarcheReportRepostitory = null;

        parent::tearDown();
    }
}