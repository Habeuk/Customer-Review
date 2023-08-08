<?php

namespace App\Command;

use App\Entity\Review;
use App\Entity\ReviewSummary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'app:update-review-summary',
    description: 'Update the summaries of the products',
    hidden: false,
    aliases: ['app:add-user']
)]
class UpdateReviewSummaryCommand extends Command
{
    private $em;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $summaryRepository = $this->em->getRepository(ReviewSummary::class);
        $reviewRepository = $this->em->getRepository(Review::class);
        $summaries = $summaryRepository->findAll();

        foreach ($summaries as $summary) {
            for ($i = 1; $i < 6; $i++) {
                $setNote = "setNote" . $i;
                $count = $reviewRepository->countReviewByNoteAndProduct($i, $summary->getProduct());
                $summary->$setNote($count["count"]);
                $total = $reviewRepository->countReviewsByProduct($summary->getProduct());


                $summary->setTotal($total["count"]);
                $summary->setMean($this->getMean($total["count"], $summary));
                $this->em->flush();
            }
        }

        return Command::SUCCESS;
    }

    public function getMean(int $total, ReviewSummary $summary)
    {
        $note = 0;
        $note += $summary->getNote1() * 1;
        $note += $summary->getNote2() * 2;
        $note += $summary->getNote3() * 3;
        $note += $summary->getNote4() * 4;
        $note += $summary->getNote5() * 5;

        $mean = $note / $total;

        return $mean * 100 / 5;
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to update the summaries of the products');
    }
}
