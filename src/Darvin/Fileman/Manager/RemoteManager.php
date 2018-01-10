<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Manager;

use Darvin\Fileman\Directory\DirectoryFetcher;
use Darvin\Fileman\SSH\SSHClient;

/**
 * Remote manager
 */
class RemoteManager
{
    /**
     * @var \Darvin\Fileman\Directory\DirectoryFetcher
     */
    private $dirFetcher;

    /**
     * @var \Darvin\Fileman\SSH\SSHClient
     */
    private $sshClient;

    /**
     * @var string
     */
    private $projectPath;

    /**
     * @var string[]
     */
    private $archiveFilenames;

    /**
     * @var string[]|null
     */
    private $dirs;

    /**
     * @param \Darvin\Fileman\Directory\DirectoryFetcher $dirFetcher  Directory fetcher
     * @param \Darvin\Fileman\SSH\SSHClient              $sshClient   SSH client
     * @param string                                     $projectPath Project path
     */
    public function __construct(DirectoryFetcher $dirFetcher, SSHClient $sshClient, $projectPath)
    {
        $this->dirFetcher = $dirFetcher;
        $this->sshClient = $sshClient;
        $this->projectPath = $projectPath;

        $this->archiveFilenames = [];
        $this->dirs = null;
    }

    /**
     * @param callable $callback Success callback
     *
     * @return RemoteManager
     */
    public function archiveFiles(callable $callback)
    {
        $now = new \DateTimeImmutable();

        foreach ($this->getDirs() as $dir) {
            $dir = trim($dir, DIRECTORY_SEPARATOR);

            $filename = sprintf('%s_%s.zip', str_replace(DIRECTORY_SEPARATOR, '_', $dir), $now->format('d-m-Y_H-i-s'));

            $command = sprintf(
                'cd %s/web/%s && /usr/bin/env zip -r %s%s .',
                $this->projectPath,
                $dir,
                str_repeat('../', substr_count($dir, DIRECTORY_SEPARATOR) + 2),
                $filename
            );

            $this->sshClient->exec($command);

            $callback($filename);

            $this->archiveFilenames[] = $filename;
        }

        return $this;
    }

    /**
     * @param callable $callback         Success callback
     * @param string   $localProjectPath Local project path
     *
     * @return RemoteManager
     */
    public function downloadArchives(callable $callback, $localProjectPath)
    {
        foreach ($this->archiveFilenames as $filename) {
            $this->sshClient->get(sprintf('%s/%s', $this->projectPath, $filename), $localProjectPath.$filename);

            $callback($filename);
        }

        return $this;
    }

    /**
     * @param callable $callback Success callback
     *
     * @return RemoteManager
     */
    public function removeArchives(callable $callback)
    {
        foreach ($this->archiveFilenames as $filename) {
            $command = sprintf('rm %s/%s', $this->projectPath, $filename);

            $this->sshClient->exec($command);

            $callback($filename);
        }

        return $this;
    }

    /**
     * @return string[]
     */
    private function getDirs()
    {
        if (null === $this->dirs) {
            $yaml = $this->sshClient->exec(sprintf('cat %s/app/config/parameters.yml', $this->projectPath));

            $this->dirs = $this->dirFetcher->fetchDirectories($yaml);
        }

        return $this->dirs;
    }
}
