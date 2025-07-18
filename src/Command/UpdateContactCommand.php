<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Message\Contact\DeleteContactMessage;
use App\Service\Import\ContactOrganizationProcessor;
use App\Service\Import\ContactProcessor;
use App\Service\Import\OrganizationProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[AsCommand(
    name: 'app:update-contact',
    description: 'Update all contacts and their organizations from CSV files',
)]
class UpdateContactCommand extends Command
{
    private ?SymfonyStyle $io;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ContactProcessor $contactProcessors,
        private readonly OrganizationProcessor $organizationProcessors,
        private readonly ContactOrganizationProcessor $contactOrganizationProcessors,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'contacts-file',
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to contacts CSV file',
                'files/contacts.csv'
            )
            ->addOption(
                'organizations-file',
                'o',
                InputOption::VALUE_REQUIRED,
                'Path to organizations CSV file',
                'files/organizations.csv'
            )
            ->addOption(
                'contact-organizations-file',
                'r',
                InputOption::VALUE_REQUIRED,
                'Path to contact-organizations CSV file',
                'files/contacts_organizations.csv'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $contactsFile = $input->getOption('contacts-file');
        $organizationsFile = $input->getOption('organizations-file');
        $contactOrganizationsFile = $input->getOption('contact-organizations-file');

        // Process contacts
        $this->io->section('Processing Contacts');
        $countContacts = $this->contactProcessors->processContacts($this->io, $contactsFile);
        $deleteContacts = $this->deleteContacts();

        $this->io->info('Created/Updated: '.$countContacts);
        $this->io->info('Deleted: '.$deleteContacts);

        // Process organizations
        $countOrganizations = $this->organizationProcessors->processOrganizations($this->io, $organizationsFile);
        $deleteOrganizations = $this->deleteOrganizations();
        $this->io->section('Processing Organizations');
        $this->io->info('Created/Updated: '.$countOrganizations);
        $this->io->info('Deleted: '.$deleteOrganizations);

        // Process contact organizations
        $countContactOrganization = $this->contactOrganizationProcessors->processContactsOrganizations($this->io, $contactOrganizationsFile);
        $this->io->section('Processing Contact Organizations');
        $this->io->info('Created/Updated: '.$countContactOrganization);
        $this->io->info('Deleted: 0');

        return Command::SUCCESS;
    }

    private function deleteContacts(): int
    {
        $this->io->writeln('Deleting contacts');

        $deleteContactMessage = new DeleteContactMessage();
        $envelope = $this->messageBus->dispatch($deleteContactMessage);

        return $envelope->last(HandledStamp::class)->getResult();
    }

    private function deleteOrganizations(): int
    {
        $this->io->writeln('Deleting organizations');

        $deleteContactMessage = new DeleteContactMessage();
        $envelope = $this->messageBus->dispatch($deleteContactMessage);

        return $envelope->last(HandledStamp::class)->getResult();
    }
}
