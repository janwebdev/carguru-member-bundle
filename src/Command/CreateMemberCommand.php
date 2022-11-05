<?php

namespace Carguru\MemberBundle\Command;

use Carguru\MemberBundle\Model\Member;
use Carguru\MemberBundle\Service\MemberManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMemberCommand extends Command
{
    protected static $defaultName = 'carguru:member:create';

    private MemberManager $memberManager;

    public function __construct(MemberManager $memberManager)
    {
        $this->memberManager = $memberManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a member')
            ->setDefinition([
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
            ])
            ->setHelp(<<<'EOT'
The <info>carguru:member:create</info> command creates a member:

  <info>php carguru:member:create myUserName myPa$$w0rd</info>

EOT
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $this->memberManager->save(
            new Member(
                $username,
                $password,
            )
        );

        return 0;
    }
}