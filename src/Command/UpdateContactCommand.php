<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Message\Contact\DeleteContactMessage;
use App\Service\Import\ContactProcessors;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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
        private readonly ContactProcessors $contactProcessors,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        // Process contacts
        $this->io->section('Processing Contacts');
        // $countContacts = $this->processContacts($this->io);
        $countContacts = 0;
        $deleteContacts = $this->deleteContacts();

        $this->io->info('Created/Updated: '.$countContacts);
        $this->io->info('Deleted: '.$deleteContacts);

        // Process organizations
        // @TODO : Create or update organizations based on the CSV data
        $this->io->section('Processing Organizations');
        $this->io->info('Created/Updated: 0');
        $this->io->info('Deleted: 0');

        // Process contact organizations
        // @TODO : Create or update contact organizations based on the CSV data
        $this->io->section('Processing Contact Organizations');
        $this->io->info('Created/Updated: 0');
        $this->io->info('Deleted: 0');

        return Command::SUCCESS;
    }

    private function processContacts(SymfonyStyle $io): int
    {
        return $this->contactProcessors->processContacts($io);
    }

    private function deleteContacts(): int
    {
        $this->io->writeln('Deleting contacts');

        $deleteContactMessage = new DeleteContactMessage();
        $envelope = $this->messageBus->dispatch($deleteContactMessage);

        return $envelope->last(HandledStamp::class)->getResult();
    }
}
