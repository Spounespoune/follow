<?php

declare(strict_types=1);

namespace App\Service\Import;

use App\Application\Port\IContactRepository;
use App\Application\Port\IOrganizationRepository;
use App\Entity\Contact;
use App\Entity\Organization;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

class ContactOrganizationProcessor
{
    private const string IDENTIFIANT_PP = 'Identifiant PP';
    private const string TECHNICAL_ID = 'Identifiant technique de la structure';

    private const array REQUIRED_HEADERS = [
        self::IDENTIFIANT_PP,
        self::TECHNICAL_ID,
    ];

    private ?Contact $contactRecord;
    private ?Organization $organizationRecord;

    public function __construct(
        private IContactRepository $contactRepository,
        private IOrganizationRepository $organizationRepository,
    ) {
        $this->contactRecord = null;
        $this->organizationRecord = null;
    }

    public function processContactsOrganizations(SymfonyStyle $io, string $filePath): int
    {
        $io->writeln('Updating contact organizations');
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
            dump($this->canLinkContactToOrganization($record));
            if ($this->canLinkContactToOrganization($record)) {
                try {
                    dump('add');
                    $this->contactRecord->addOrganization($this->organizationRecord);
                    $this->contactRepository->save($this->contactRecord);
                    ++$count;
                } catch (\Exception $e) {
                    // TODO maybe log instead show error on terminal and use $i
                    $io->error($e->getMessage());
                }
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

    private function canLinkContactToOrganization(mixed $record): bool
    {
        $identifiantPP = $record[self::IDENTIFIANT_PP];
        $technicalId = $record[self::TECHNICAL_ID];

        if ($this->contactExistInDatabase($identifiantPP)
            && $this->organisationExistInDatabase($technicalId)
            && !$this->contactRecord->hasOrganization($this->organizationRecord)) {
            return true;
        }

        return false;
    }

    private function contactExistInDatabase(string $ppIdentifier): bool
    {
        $this->contactRecord = $this->contactRepository->findByPpIdentifier($ppIdentifier);

        if (null === $this->contactRecord) {
            return false;
        }

        return true;
    }

    private function organisationExistInDatabase(string $ppIdentifier): bool
    {
        $this->organizationRecord = $this->organizationRepository->findByTechnicalId($ppIdentifier);

        if (null === $this->organizationRecord) {
            return false;
        }

        return true;
    }
}
