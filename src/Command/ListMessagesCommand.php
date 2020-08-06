<?php

namespace App\Command;

use App\Entity\Message;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListMessagesCommand extends Command
{
    protected static $defaultName = 'app:list-messages';

    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('List saved messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $data = $this->registry->getRepository(Message::class)
            ->createQueryBuilder('m')->getQuery()->getResult(Query::HYDRATE_ARRAY);
        if (!$data) {
            $io->writeln('No messages found');
            return 0;
        }
        $table = new Table($output);
        $table->setHeaders(['Id', 'Resume', 'Active', 'Body', 'Published']);
        $table->addRows($data);
        $table->render();
        return 0;
    }
}
