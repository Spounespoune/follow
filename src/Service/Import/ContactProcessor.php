<?php

declare(strict_types=1);

namespace App\Service\Import;

use App\Application\Message\Contact\CreateContactMessage;
use App\Application\Message\Contact\UpdateContactMessage;
use App\Application\Port\IContactRepository;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ContactProcessor
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private IContactRepository $contactRepository,
    ) {
    }

    public function processContacts(SymfonyStyle $io): int
    {
        $io->writeln('Updating contacts');
        $progress = new ProgressBar($io);
        $progress->setFormat('debug_nomax');

        $count = 0;
        $reader = Reader::createFromPath('files/contacts.csv');
        $reader->setHeaderOffset(0);

        if (!$this->isValidHeader($reader->getHeader())) {
            $io->error('Invalid header');

            return Command::FAILURE;
        }

        foreach ($reader->getRecords() as $i => $record) {
            $progress->advance();
            $contactMessage = $this->getContactMessage($record);

            try {
                $this->messageBus->dispatch($contactMessage);
                ++$count;
            } catch (\Exception $e) {
                // TODO maybe log instead show error on terminal and use $i
                $io->error($e->getMessage());
            }

            unset($record);

            if (0 === $i % 100) {
                gc_collect_cycles();
            }
        }

        $progress->finish();

        return $count;
    }

    private function isValidHeader(array $header): bool
    {
        $requiredHeaders = [
            'Identifiant PP',
            'Nom d\'exercice',
            'Prénom d\'exercice',
            'Type d\'identifiant PP',
            'Libellé profession',
        ];

        return count(array_intersect($requiredHeaders, $header)) === count($requiredHeaders);
    }

    private function getContactMessage(mixed $record): UpdateContactMessage|CreateContactMessage
    {
        $ppIdentifier = $record['Identifiant PP'];
        $familyName = $record['Nom d\'exercice'];
        $firstName = $record['Prénom d\'exercice'];
        $ppIdentifierType = (int) $record['Type d\'identifiant PP'];
        $title = $record['Libellé profession'];

        if ($this->contactExistInDatabase($record['Identifiant PP'])) {
            return new UpdateContactMessage(
                $ppIdentifier,
                $familyName,
                $firstName,
                $title,
            );
        } else {
            return new CreateContactMessage(
                $ppIdentifier,
                $familyName,
                $firstName,
                $ppIdentifierType,
                $title,
            );
        }
    }

    private function contactExistInDatabase(string $ppIdentifier): bool
    {
        $contactRecord = $this->contactRepository->findByPpIdentifier($ppIdentifier);

        if (null === $contactRecord) {
            return false;
        }

        return true;
    }
}
