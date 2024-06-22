<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class HighestReviewsDayCommand extends Command
{
    protected static $defaultName = 'app:highest-reviews-date';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Displays the date with the highest number of reviews published.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $conn = $this->entityManager->getConnection();

        $sql = '
            SELECT DATE(published_at) AS review_date, COUNT(*) AS review_count
            FROM reviews
            GROUP BY review_date
            ORDER BY review_count DESC, review_date DESC
            LIMIT 1
        ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        $result = $resultSet->fetchAssociative();

        if ($result) {
            $io->success('The day with the highest number of reviews published is: ' . $result['review_date']);
        } else {
            $io->warning('No reviews found in the database.');
        }

        return Command::SUCCESS;
    }
}
