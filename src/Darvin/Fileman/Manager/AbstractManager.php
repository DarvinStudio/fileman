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
 * Manager abstract implementation
 */
abstract class AbstractManager
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
     * @var array|null
     */
    private $dirs;

    /**
     * @var \DateTimeImmutable
     */
    private $now;

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

        $this->dirs = null;
        $this->now = new \DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getProjectPath()
    {
        return $this->projectPath;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    abstract protected function getConfigurationYaml();

    /**
     * @return array
     */
    protected function getDirs()
    {
        if (null === $this->dirs) {
            $this->dirs = $this->dirFetcher->fetchDirectories($this->getConfigurationYaml());
        }

        return $this->dirs;
    }

    /**
     * @param string      $dir    Directory
     * @param string|null $suffix Suffix
     *
     * @return string
     */
    protected function nameArchive($dir, $suffix = null)
    {
        if (!empty($suffix)) {
            $suffix = '_'.preg_replace('/[^0-9a-z]+/i', '-', $suffix);
        }

        return sprintf('%s%s_%s.zip', str_replace(DIRECTORY_SEPARATOR, '_', $dir), $suffix, $this->now->format('d-m-Y_H-i-s'));
    }
}
