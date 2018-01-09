<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Filer\Command;

use Darvin\Filer\Manager\LocalManager;
use Darvin\Filer\Manager\RemoteManager;
use Darvin\Filer\SSH\SSHClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command abstract implementation
 */
abstract class AbstractCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('user@host', InputArgument::REQUIRED, 'SSH user@host'),
            new InputArgument('project_path_remote', InputArgument::REQUIRED, <<<DESCRIPTION
Symfony project remote path absolute or relative to home directory
DESCRIPTION
            ),
            new InputArgument('project_path_local', InputArgument::OPTIONAL, <<<DESCRIPTION
Symfony project local path absolute or relative to home directory, if empty - current directory
DESCRIPTION
            ),
            new InputOption('key', 'k', InputOption::VALUE_OPTIONAL, 'SSH private RSA key pathname relative to home directory', '.ssh/id_rsa'),
            new InputOption('password', 'p', InputOption::VALUE_NONE, 'Ask for SSH or SSH key password'),
            new InputOption('port', 'P', InputOption::VALUE_OPTIONAL, 'SSH server port', 22),
            new InputOption('parameters', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Directory parameters', ['image_upload_path', 'upload_path']),
        ]);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input Input
     *
     * @return \Darvin\Filer\Manager\LocalManager
     */
    protected function createLocalManager(InputInterface $input)
    {
        return new LocalManager($input->getArgument('project_path_local'));
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input  Input
     * @param \Symfony\Component\Console\Output\OutputInterface $output Output
     *
     * @return \Darvin\Filer\Manager\RemoteManager
     */
    protected function createRemoteManager(InputInterface $input, OutputInterface $output)
    {
        list($user, $host) = $this->getUserAndHost($input);

        return new RemoteManager(
            $input->getArgument('project_path_remote'),
            new SSHClient(
                $user,
                $host,
                $input->getOption('key'),
                $input->getOption('password') ? (new SymfonyStyle($input, $output))->askHidden('Please enter password') : null,
                $input->getOption('port')
            )
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input Input
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getUserAndHost(InputInterface $input)
    {
        $text = $input->getArgument('user@host');

        if (1 !== substr_count($text, '@')) {
            throw new \InvalidArgumentException(sprintf('Argument "user@host" must contain single "@" symbol, got "%s".', $text));
        }

        return explode('@', $text);
    }
}
