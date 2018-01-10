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

/**
 * Local manager
 */
class LocalManager
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
     * @var string[]
     */
    private $archiveFilenames;

    /**
     * @var array|null
     */
    private $dirs;

    /**
     * @param \Darvin\Fileman\Directory\DirectoryFetcher $dirFetcher  Directory fetcher
     * @param string                                     $projectPath Project path
     */
    public function __construct(DirectoryFetcher $dirFetcher, $projectPath)
    {
        if (!empty($projectPath)) {
            $projectPath = rtrim($projectPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }

        $this->dirFetcher = $dirFetcher;
        $this->projectPath = $projectPath;

        $this->archiveFilenames = [];
        $this->dirs = null;

        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, [$this, '__destruct']);
        }
    }

    /**
     * Removes archives.
     */
    public function __destruct()
    {
        foreach ($this->archiveFilenames as $filename) {
            @unlink($this->projectPath.$filename);
        }
    }

    /**
     * @param callable $callback         Success callback
     * @param array    $archiveFilenames Archive filenames
     *
     * @return LocalManager
     */
    public function extractFiles(callable $callback, array $archiveFilenames)
    {
        $zip = new \ZipArchive();

        foreach ($this->getDirs() as $param => $dir) {
            $filename = $archiveFilenames[$param];

            $pathname = $this->projectPath.$filename;

            if (true !== $zip->open($pathname)) {
                throw new \RuntimeException(sprintf('Unable to open archive "%s" using ZIP.', $pathname));
            }
            if (!$zip->extractTo(sprintf('%sweb/%s', $this->projectPath, $dir))) {
                throw new \RuntimeException(sprintf('Unable to extract files from archive "%s".', $pathname));
            }

            $callback($filename);

            $zip->close();

            $this->archiveFilenames[] = $filename;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getProjectPath()
    {
        return $this->projectPath;
    }

    /**
     * @return array
     */
    private function getDirs()
    {
        if (null === $this->dirs) {
            $pathname = $this->projectPath.'app/config/parameters.yml';

            $yaml = @file_get_contents($pathname);

            if (false === $yaml) {
                throw new \RuntimeException(sprintf('Unable to read configuration file "%s".', $pathname));
            }

            $this->dirs = $this->dirFetcher->fetchDirectories($yaml);
        }

        return $this->dirs;
    }
}
