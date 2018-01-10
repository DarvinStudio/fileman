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
     * @var string
     */
    private $projectPath;

    /**
     * @var \Darvin\Fileman\SSH\SSHClient
     */
    private $sshClient;

    /**
     * @var array
     */
    private $archiveFilenames;

    /**
     * @var array|null
     */
    private $dirs;

    /**
     * @param \Darvin\Fileman\Directory\DirectoryFetcher $dirFetcher  Directory fetcher
     * @param string                                     $projectPath Project path
     * @param \Darvin\Fileman\SSH\SSHClient              $sshClient   SSH client
     */
    public function __construct(DirectoryFetcher $dirFetcher, $projectPath, SSHClient $sshClient)
    {
        if (!empty($projectPath)) {
            $projectPath = rtrim($projectPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }

        $this->dirFetcher = $dirFetcher;
        $this->projectPath = $projectPath;
        $this->sshClient = $sshClient;

        $this->archiveFilenames = [];
        $this->dirs = null;
    }

    /**
     * @param callable $callback Success callback
     *
     * @return array
     */
    public function archiveFiles(callable $callback)
    {
        $now = new \DateTimeImmutable();

        foreach ($this->getDirs() as $param => $dir) {
            $dir = trim($dir, DIRECTORY_SEPARATOR);

            $filename = sprintf('%s_%s.zip', str_replace(DIRECTORY_SEPARATOR, '_', $dir), $now->format('d-m-Y_H-i-s'));

            $command = sprintf(
                'cd %sweb/%s && /usr/bin/env zip -r %s%s .',
                $this->projectPath,
                $dir,
                str_repeat('../', substr_count($dir, DIRECTORY_SEPARATOR) + 2),
                $filename
            );

            $this->sshClient->exec($command);

            $callback($filename);

            $this->archiveFilenames[$param] = $filename;
        }

        return $this->archiveFilenames;
    }

    /**
     * @param callable $callback         Success callback
     * @param string   $localProjectPath Local project path
     */
    public function downloadArchives(callable $callback, $localProjectPath)
    {
        foreach ($this->archiveFilenames as $filename) {
            $this->sshClient->get($this->projectPath.$filename, $localProjectPath.$filename);

            $callback($filename);
        }
    }

    /**
     * @param callable $callback Success callback
     */
    public function removeArchives(callable $callback)
    {
        foreach ($this->archiveFilenames as $param => $filename) {
            $command = sprintf('rm %s%s', $this->projectPath, $filename);

            $this->sshClient->exec($command);

            $callback($filename);

            unset($this->archiveFilenames[$param]);
        }
    }

    /**
     * @return array
     */
    private function getDirs()
    {
        if (null === $this->dirs) {
            $yaml = $this->sshClient->exec(sprintf('cat %sapp/config/parameters.yml', $this->projectPath));

            $this->dirs = $this->dirFetcher->fetchDirectories($yaml);
        }

        return $this->dirs;
    }
}
