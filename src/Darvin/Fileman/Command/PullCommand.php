<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Pull command
 */
class PullCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        parent::configure();

        if (null === $this->getName()) {
            $this->setName('pull');
        }

        $this->setDescription('Pulls remote files to local directory');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $callback = [$io, 'success'];

        $dirFetcher = $this->createDirectoryFetcher($input);

        $localManager  = $this->createLocalManager($input, $dirFetcher);
        $remoteManager = $this->createRemoteManager($input, $dirFetcher, $io);

        $io->comment('Archiving files...');
        $archiveFilenames = $remoteManager->archiveFiles($callback);

        $io->comment('Downloading archives...');
        $remoteManager->downloadArchives($callback, $localManager->getProjectPath());

        $io->comment('Removing remote archives...');
        $remoteManager->removeArchives($callback);

        $io->comment('Extracting files...');
        $localManager->extractFiles($callback, $archiveFilenames);

        return 0;
    }
}
