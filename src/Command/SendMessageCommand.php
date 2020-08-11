<?php

namespace App\Command;

use App\Entity\Message;
use App\Service\TwitterClient;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendMessageCommand extends Command
{
    protected static $defaultName = 'app:send-message';
    /**
     * @var ManagerRegistry
     */
    private $registry;
    /**
     * @var TwitterClient
     */
    private $client;

    public function __construct(ManagerRegistry $registry, TwitterClient $client)
    {
        $this->registry = $registry;
        $this->client = $client;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Send saved messages to Twitter')
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Message ID number');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getOption('id');
        if ($id && !is_numeric($id)) {
            $io->error('Invalid ID value.');
            return 1;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getOption('id');

        $em = $this->registry->getManager();
        $repository = $em->getRepository(Message::class);
        if ($id) {
            $msg = $repository->find($id);
        } else {
            $msg = $repository->nextRandomMsg(strtotime('-5 day'));
        }

        if (!$msg) {
            // Message not found
            $io->error('No message found');
            return 1;
        }

        $client = $this->client;
        if (!$client->isConnected()) {
            // Client auth problem
            $io->error('Connection problem');
            return 1;
        }

        $client->post("statuses/update", ["status" => $msg->getBody()]);
        if ($client->getLastHttpCode() != 200) {
            $io->error('Error posting message');
            return 1;
        }

        $msg->setPublished(new \DateTime());
        $em->persist($msg);
        $em->flush();
        $io->success('Message published');

        return 0;
    }
}
