<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Manager;

use Darvin\Fileman\Directory\DirectoryFetcherInterface;
use Darvin\Fileman\SSH\SSHClientInterface;

/**
 * Remote manager
 */
class RemoteManager extends AbstractManager implements RemoteManagerInterface
{
    /**
     * @var \Darvin\Fileman\SSH\SSHClientInterface
     */
    private $sshClient;

    /**
     * @var array
     */
    private $archiveFilenames;

    /**
     * @param \Darvin\Fileman\Directory\DirectoryFetcherInterface $dirFetcher  Directory fetcher
     * @param string                                              $projectPath Project path
     * @param \Darvin\Fileman\SSH\SSHClientInterface              $sshClient   SSH client
     */
    public function __construct(DirectoryFetcherInterface $dirFetcher, string $projectPath, SSHClientInterface $sshClient)
    {
        parent::__construct($dirFetcher, $projectPath);

        $this->sshClient = $sshClient;

        $this->archiveFilenames = [];
    }

    /**
     * {@inheritDoc}
     */
    public function archiveFiles(callable $callback): array
    {
        foreach ($this->getDirs() as $param => $dir) {
            $filename = $this->nameArchive($dir, $this->sshClient->getHost());

            $dirPathname = $this->getProjectPath().$dir;

            $command = sprintf(
                'if [ -n "$(ls -A %s 2>/dev/null)" ]
                then
                    cd %1$s && /usr/bin/env zip -r %s%s . &
                    PID=$!
                    while [ ps -p $PID > /dev/null ]
                    do
                        sleep 1
                    done
                fi',
                $dirPathname,
                str_repeat('../', substr_count($dir, DIRECTORY_SEPARATOR) + 1),
                $filename
            );

            $output = $this->sshClient->exec($command);

            if (empty($output)) {
                continue;
            }

            $callback($filename);

            $this->archiveFilenames[$param] = $filename;
        }

        return $this->archiveFilenames;
    }

    /**
     * {@inheritDoc}
     */
    public function downloadArchives(callable $callback, string $localProjectPath): void
    {
        foreach ($this->archiveFilenames as $filename) {
            $this->sshClient->get($this->getProjectPath().$filename, $localProjectPath.$filename);

            $callback($filename);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function extractFiles(callable $callback): void
    {
        foreach ($this->getDirs() as $param => $dir) {
            if (!isset($this->archiveFilenames[$param])) {
                continue;
            }

            $filename = $this->archiveFilenames[$param];

            $command = sprintf('cd %s && /usr/bin/env unzip -o %s -d %s', $this->getProjectPath(), $filename, $dir);

            $this->sshClient->exec($command);

            $callback($filename);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeArchives(callable $callback): void
    {
        foreach ($this->archiveFilenames as $param => $filename) {
            $command = sprintf('rm %s%s', $this->getProjectPath(), $filename);

            $this->sshClient->exec($command);

            $callback($filename);

            unset($this->archiveFilenames[$param]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function uploadArchives(callable $callback, string $localProjectPath, array $archiveFilenames): void
    {
        foreach ($archiveFilenames as $param => $filename) {
            $this->sshClient->put($localProjectPath.$filename, $this->getProjectPath().$filename);

            $callback($filename);

            $this->archiveFilenames[$param] = $filename;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function readConfiguration(string $pathname): string
    {
        return $this->sshClient->exec(sprintf('cat %s', $pathname));
    }
}
