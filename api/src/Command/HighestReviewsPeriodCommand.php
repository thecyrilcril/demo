<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

class HighestReviewsPeriodCommand extends Command
{
    protected static $defaultName = 'app:highest-reviews-period';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Displays the period with the highest number of reviews published.')
            ->addOption(
                'period',
                null,
                InputOption::VALUE_OPTIONAL,
                'The period to group by (day or month)',
                'day'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $period = $input->getOption('period');
        $groupBy = 'DATE(published_at)';
        $dateFormat = 'Y-m-d';

        if ($period === 'month') {
            $groupBy = "TO_CHAR(published_at, 'YYYY-MM')";
            $dateFormat = 'Y-m';
        }

        $conn = $this->entityManager->getConnection();

        $sql = sprintf('
            SELECT %s AS review_date, COUNT(*) AS review_count
            FROM reviews
            GROUP BY review_date
            ORDER BY review_count DESC, review_date DESC
            LIMIT 1
        ', $groupBy);

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        $result = $resultSet->fetchAssociative();

        if ($result) {
            $io->success('The '. $period .' with the highest number of reviews published is: ' . $result['review_date']);
        } else {
            $io->warning('No reviews found in the database.');
        }

        return Command::SUCCESS;
    }
}
