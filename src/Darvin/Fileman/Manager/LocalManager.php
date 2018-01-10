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
     * @var array
     */
    private $archiveFilenames;

    /**
     * @var string[]|null
     */
    private $dirs;

    /**
     * @param \Darvin\Fileman\Directory\DirectoryFetcher $dirFetcher       Directory fetcher
     * @param string                                     $projectPath      Project path
     * @param array                                      $archiveFilenames Archive filenames
     */
    public function __construct(DirectoryFetcher $dirFetcher, $projectPath, array $archiveFilenames)
    {
        if (!empty($projectPath)) {
            $projectPath = rtrim($projectPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }

        $this->dirFetcher = $dirFetcher;
        $this->projectPath = $projectPath;
        $this->archiveFilenames = $archiveFilenames;

        $this->dirs = null;
    }

    public function extractFiles()
    {

    }

    /**
     * @return string
     */
    public function getProjectPath()
    {
        return $this->projectPath;
    }

    /**
     * @return string[]
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