<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
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
 * Push command
 */
class PushCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        if (null === $this->getName()) {
            $this->setName('push');
        }

        $this->setDescription('Pushes local files to remote directory');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $callback = [$io, 'success'];

        $dirFetcher = $this->createDirectoryFetcher($input);

        $localManager  = $this->createLocalManager($input, $dirFetcher);
        $remoteManager = $this->createRemoteManager($input, $dirFetcher, $io);

        $io->comment('Archiving files...');
        $archiveFilenames = $localManager->archiveFiles($callback);

        $io->comment('Uploading archives...');
        $remoteManager->uploadArchives($callback, $localManager->getProjectPath(), $archiveFilenames);

        $io->comment('Extracting files...');
        $remoteManager->extractFiles($callback);

        $io->comment('Removing remote archives...');
        $remoteManager->removeArchives($callback);
    }
}
