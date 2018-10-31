<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Command;

use Darvin\Fileman\Archiver\Archiver;
use Darvin\Fileman\Directory\DirectoryFetcher;
use Darvin\Fileman\Manager\LocalManager;
use Darvin\Fileman\Manager\RemoteManager;
use Darvin\Fileman\SSH\SSHClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $currentDir = $this->getCurrentDir();

        $remotePathDescription = 'Symfony project remote path absolute or relative to home directory';

        if (!empty($currentDir)) {
            $remotePathDescription .= sprintf(' <comment>[default: "www/%s.%%HOST%%"]</comment>', $currentDir);
        }

        $this->setDefinition([
            new InputArgument('user@host', InputArgument::REQUIRED, 'SSH user@host'),
            new InputArgument('project_path_remote', !empty($currentDir) ? InputArgument::OPTIONAL : InputArgument::REQUIRED, $remotePathDescription),
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
     * @return \Darvin\Fileman\Directory\DirectoryFetcher
     */
    protected function createDirectoryFetcher(InputInterface $input)
    {
        return new DirectoryFetcher($input->getOption('parameters'));
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input      Input
     * @param \Darvin\Fileman\Directory\DirectoryFetcher      $dirFetcher Directory fetcher
     *
     * @return \Darvin\Fileman\Manager\LocalManager
     */
    protected function createLocalManager(InputInterface $input, DirectoryFetcher $dirFetcher)
    {
        return new LocalManager($dirFetcher, $input->getArgument('project_path_local'), new Archiver());
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input      Input
     * @param \Darvin\Fileman\Directory\DirectoryFetcher      $dirFetcher Directory fetcher
     * @param \Symfony\Component\Console\Style\SymfonyStyle   $io         I/O
     *
     * @return \Darvin\Fileman\Manager\RemoteManager
     */
    protected function createRemoteManager(InputInterface $input, DirectoryFetcher $dirFetcher, SymfonyStyle $io)
    {
        list($user, $host) = $this->getUserAndHost($input);

        return new RemoteManager(
            $dirFetcher,
            $this->getProjectPathRemote($input, $host),
            new SSHClient(
                $user,
                $host,
                $input->getOption('key'),
                $input->getOption('password') ? $io->askHidden('Please enter password') : null,
                $input->getOption('port')
            )
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input Input
     * @param string                                          $host  Host
     *
     * @return string
     */
    private function getProjectPathRemote(InputInterface $input, $host)
    {
        $path = $input->getArgument('project_path_remote');

        if (!empty($path)) {
            return $path;
        }

        return implode('.', [$this->getCurrentDir(), $host]);
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

    /**
     * @return string|null
     */
    private function getCurrentDir()
    {
        $cwd = getcwd();

        if (false === $cwd) {
            return null;
        }

        $parts = explode(DIRECTORY_SEPARATOR, $cwd);

        return array_pop($parts);
    }
}
