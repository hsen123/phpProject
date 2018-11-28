<?php

namespace App\Command;

use App\Entity\Result;
use App\Repository\ResultRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveDiscardedResultsCommand extends Command
{
    const DAYS_UNTIL_DISCARDED_RESULT_IS_REMOVED = 30;

    protected $resultRepository;
    protected $em;

    public function __construct(ResultRepository $resultRepository, EntityManagerInterface $em)
    {
        $this->resultRepository = $resultRepository;
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:remove-discarded-results')
            ->setDescription('removes all discarded results that are older than '.self::DAYS_UNTIL_DISCARDED_RESULT_IS_REMOVED.' days');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Starting to remove old discarded results', true);
        /** @var Result[] $results */
        $results = $this->resultRepository->findDiscardedResultsolderThanXDays(self::DAYS_UNTIL_DISCARDED_RESULT_IS_REMOVED);

        if (empty($results)) {
            $output->write('No old results to discard', true);

            return;
        }

        $output->write('Removing IDs: ');
        foreach ($results as $result) {
            $output->write($result->getId().', ');
            //$this->em->remove($result);
        }
        $this->em->flush();
        $output->write('', true);
        $output->write('Finished to remove old discarded results', true);
    }
}
