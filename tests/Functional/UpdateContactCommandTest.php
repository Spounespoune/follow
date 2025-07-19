<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Contact;
use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateContactCommandTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $container = self::getContainer();
        $kernel = self::getContainer()->get('kernel');
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $entityManager = $container->get('doctrine')->getManager();
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $command = $application->find('app:update-contact');
        $this->commandTester = new CommandTester($command);
    }

    public function testUpdateContact()
    {
        $this->commandTester->execute([
            '--contacts-file' => __DIR__.'/../Fixture/contacts_test.csv',
            '--organizations-file' => __DIR__.'/../Fixture/organizations_test.csv',
            '--contact-organizations-file' => __DIR__.'/../Fixture/contacts_organizations.csv',
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());

        $countContact = $this->entityManager->getRepository(Contact::class)->count();
        $this->assertEquals(3, $countContact);

        $countOrganization = $this->entityManager->getRepository(Organization::class)->count();
        $this->assertEquals(3, $countOrganization);

        $countContactOrganization = $this->entityManager->getRepository(Contact::class)->count();
        $this->assertEquals(3, $countContactOrganization);
    }
}
