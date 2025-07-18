<?php

declare(strict_types=1);

namespace App\Service\Import;

use App\Application\Message\Organization\CreateOrganizationMessage;
use App\Application\Message\Organization\UpdateOrganizationMessage;
use App\Infrastructure\ForProduction\Repository\OrganizationRepository;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class OrganizationProcessor
{
    private const string TECHNICAL_ID = 'Identifiant technique de la structure';
    private const string NAME = 'Raison sociale site';
    private const string EMAIL_ADDRESS = 'Adresse e-mail (coord. structure)';
    private const string PHONE_NUMBER = 'Téléphone (coord. structure)';
    private const string PRIVATE = 'Libellé secteur d\'activité';
    private const string STREET_NUMBER = 'Numéro Voie (coord. structure)';
    private const string STREET_TYPE = 'Libellé type de voie (coord. structure)';
    private const string STREET_NAME = 'Libellé Voie (coord. structure)';
    private const string STREET2 = 'Complément destinataire (coord. structure)';
    private const string ZIPCODE = 'Code commune (coord. structure)';
    private const string CITY = 'Libellé commune (coord. structure)';

    private const array REQUIRED_HEADERS = [
        self::TECHNICAL_ID,
        self::NAME,
        self::EMAIL_ADDRESS,
        self::PHONE_NUMBER,
        self::PRIVATE,
        self::STREET_NUMBER,
        self::STREET_TYPE,
        self::STREET_NAME,
        self::STREET2,
        self::ZIPCODE,
        self::CITY,
    ];

    public function __construct(
        private MessageBusInterface $messageBus,
        private OrganizationRepository $organizationRepository,
    ) {
    }

    public function processOrganizations(SymfonyStyle $io): int
    {
        $io->writeln('Updating organizations');
        $progress = new ProgressBar($io);
        $progress->setFormat('debug_nomax');

        $count = 0;
        $reader = Reader::createFromPath('files/organizations.csv');
        $reader->setHeaderOffset(0);

        if (!$this->isValidHeader($reader->getHeader())) {
            $io->error('Invalid header');

            return Command::FAILURE;
        }

        foreach ($reader->getRecords() as $i => $record) {
            $progress->advance();

            $organisationMessage = $this->getOrganisationMessage($record);

            try {
                $this->messageBus->dispatch($organisationMessage);
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
        return count(array_intersect(self::REQUIRED_HEADERS, $header)) === count(self::REQUIRED_HEADERS);
    }

    private function getOrganisationMessage(mixed $record): UpdateOrganizationMessage|CreateOrganizationMessage
    {
        $technicalId = $record[self::TECHNICAL_ID];
        $name = $record[self::NAME];
        $emailAddress = $record[self::EMAIL_ADDRESS];
        $phoneNumber = $record[self::PHONE_NUMBER];
        $private = $record[self::PRIVATE];
        $street = $this->buildFullStreetAddress($record);
        $street2 = $record[self::STREET2];
        $zipCode = $record[self::ZIPCODE];
        $city = $record[self::CITY];

        if ($this->contactExistInDatabase($record[self::TECHNICAL_ID])) {
            return new UpdateOrganizationMessage(
                $technicalId,
                $name,
                $emailAddress,
                $phoneNumber,
                $private,
                $street,
                $street2,
                $zipCode,
                $city,
            );
        } else {
            return new CreateOrganizationMessage(
                $technicalId,
                $name,
                $emailAddress,
                $phoneNumber,
                $private,
                $street,
                $street2,
                $zipCode,
                $city,
            );
        }
    }

    private function contactExistInDatabase(string $ppIdentifier): bool
    {
        $contactRecord = $this->organizationRepository->findByTechnicalId($ppIdentifier);

        if (null === $contactRecord) {
            return false;
        }

        return true;
    }

    private function buildFullStreetAddress(mixed $record): ?string
    {
        $fullStreetAddress = $record[self::STREET_NUMBER].
            ' '.$record[self::STREET_TYPE].
            ' '.$record[self::STREET_NAME];

        if ('' === $fullStreetAddress) {
            return null;
        }

        return $fullStreetAddress;
    }
}
