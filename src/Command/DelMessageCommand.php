<?php

namespace App\Command;

use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class DelMessageCommand extends Command
{
    protected static $defaultName = 'app:del-message';

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
        $this->setDescription('Delete an existing message.')
            ->addArgument('id', InputArgument::OPTIONAL, 'The id of the message.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');
        $em = $this->registry->getManager();
        $data = $em->getRepository(Message::class)->find($id);
        if (!$data) {
            $io->error(sprintf('ID %s does not exist in the database.', $id));
            return 1;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action (y/n)? ', false);
        if ($helper->ask($input, $output, $question)) {
            $em->remove($data);
            $em->flush();
            $io->success('Message deleted.');
            return 0;
        }
        $io->note('Message not delete.');
        return 1;
    }
}
