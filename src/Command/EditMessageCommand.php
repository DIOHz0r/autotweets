<?php

namespace App\Command;

use App\Closure\Closure;
use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class EditMessageCommand extends Command
{
    protected static $defaultName = 'app:edit-message';

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var Message|object
     */
    private $data;

    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    private $em;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('id', InputArgument::REQUIRED, 'The message ID.')
            ->addArgument('body', InputArgument::OPTIONAL, 'Full body message.')
            ->addArgument('active', InputArgument::OPTIONAL, 'Set message active.');
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
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

        if (!$input->getArgument('body')) {
            $question = new ConfirmationQuestion('Update message body (y/n)?');

            if ($helper->ask($input, $output, $question)) {
                $argument = $this->getDefinition()->getArgument('body');
                $question = new Question($argument->getDescription() . "\n");
                $question->setNormalizer(Closure::trimValue());
                $question->setValidator(Closure::notBlank());
                $answer = $helper->ask($input, $output, $question);
                $input->setArgument('body', $answer);
            } else {
                $input->setArgument('body', $data->getBody());
            }
        }

        if (!$input->getArgument('active')) {
            $question = new ConfirmationQuestion('Update message status (y/n)?');
            if ($helper->ask($input, $output, $question)) {
                $argument = $this->getDefinition()->getArgument('active');
                $question = new ChoiceQuestion($argument->getDescription() . "\n", ['no', 'yes']);
                $question->setErrorMessage('Value %s is invalid.');
                $question->setNormalizer(Closure::checkChoices());
                $answer = $helper->ask($input, $output, $question);
                $input->setArgument('active', $answer == 'yes');
            } else {
                $input->setArgument('active', $data->getActive());
            }
        }

        $this->em = $em;
        $this->data = $data;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action (y/n)? ', false);
        if ($helper->ask($input, $output, $question)) {
            $em = $this->em;
            $data = $this->data;
            $data->setBody($input->getArgument('body'));
            $data->setActive($input->getArgument('active'));
            $em->persist($data);
            $em->flush();
            $io->success(sprintf('Message with ID %s was updated.', $data->getId()));
            return 0;
        }
        $io->error('Message not saved.');
        return 1;
    }
}
