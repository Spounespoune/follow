<?php

declare(strict_types=1);

namespace App\Service\Import\Processor;

use App\Application\Message\Contact\CreateContactMessage;
use App\Application\Message\Contact\UpdateContactMessage;
use App\Application\Model\HandlerResult;
use App\Application\Port\IContactRepository;
use App\Service\Import\ImportLogger;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

readonly class ContactProcessor
{
    private const string PP_IDENTIFIER = 'Identifiant PP';
    private const string PP_IDENTIFIER_TYPE = 'Type d\'identifiant PP';
    private const string TITLE = 'Libellé profession';
    private const string FAMILY_NAME = 'Nom d\'exercice';
    private const string FIRST_NAME = 'Prénom d\'exercice';

    private const array REQUIRED_HEADERS = [
        self::PP_IDENTIFIER,
        self::FAMILY_NAME,
        self::FIRST_NAME,
        self::PP_IDENTIFIER_TYPE,
        self::TITLE,
    ];

    public function __construct(
        private MessageBusInterface $messageBus,
        private IContactRepository $contactRepository,
        private ImportLogger $importLogger,
    ) {
    }

    public function processContacts(SymfonyStyle $io, string $filePath): int
    {
        $io->writeln('Updating contacts');
        $progress = new ProgressBar($io);
        $progress->setFormat('debug_nomax');

        $count = 0;
        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);

        if (!$this->isValidHeader($reader->getHeader())) {
            $io->error('Invalid header');

            return Command::FAILURE;
        }

        foreach ($reader->getRecords() as $i => $record) {
            $progress->advance();
            $contactMessage = $this->getContactMessage($record);

            try {
                $envelope = $this->messageBus->dispatch($contactMessage);
                $result = $envelope->last(HandledStamp::class)->getResult();

                if ($result instanceof HandlerResult
                    && false === $result->success) {
                    $this->importLogger->logError('contact', $i, $record[self::PP_IDENTIFIER], $result->errorMessage);
                }
                ++$count;
            } catch (\Exception $e) {
                $this->importLogger->logError('contact', $i, $record[self::PP_IDENTIFIER], $e->getMessage());
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
        return count(array_intersect(self::REQUIRED_HEADERS, $header)) === count(self::REQUIRED_HEADERS);
    }

    private function getContactMessage(mixed $record): UpdateContactMessage|CreateContactMessage
    {
        $ppIdentifier = $record[self::PP_IDENTIFIER];
        $familyName = $record[self::FAMILY_NAME];
        $firstName = $record[self::FIRST_NAME];
        $ppIdentifierType = (int) $record[self::PP_IDENTIFIER_TYPE];
        $title = $record[self::TITLE];

        if ($this->contactExistInDatabase($record[self::PP_IDENTIFIER])) {
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
