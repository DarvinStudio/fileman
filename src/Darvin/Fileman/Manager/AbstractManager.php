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

/**
 * Manager abstract implementation
 */
abstract class AbstractManager implements ManagerInterface
{
    /**
     * @var \Darvin\Fileman\Directory\DirectoryFetcherInterface
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
     * @param \Darvin\Fileman\Directory\DirectoryFetcherInterface $dirFetcher  Directory fetcher
     * @param string                                              $projectPath Project path
     */
    public function __construct(DirectoryFetcherInterface $dirFetcher, string $projectPath)
    {
        if ('' !== $projectPath) {
            $projectPath = rtrim($projectPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }

        $this->dirFetcher = $dirFetcher;
        $this->projectPath = $projectPath;

        $this->dirs = null;
        $this->now = new \DateTimeImmutable();
    }

    /**
     * {@inheritDoc}
     */
    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    /**
     * @param string $pathname Configuration file pathname
     *
     * @return string
     * @throws \RuntimeException
     */
    abstract protected function readConfiguration(string $pathname): string;

    /**
     * @return array
     * @throws \RuntimeException
     */
    protected function getDirs(): array
    {
        if (null === $this->dirs) {
            $config  = null;
            $rootDir = null;

            foreach ([
                'app/config/parameters.yml' => 'web',
                'config/parameters.yaml'    => 'public',
                '.env'                      => 'public',
            ] as $relativePathname => $rootDir) {
                try {
                    $config = $this->readConfiguration($this->getProjectPath().$relativePathname);

                    break;
                } catch (\RuntimeException $ex) {
                }
            }
            if (null === $config || null === $rootDir) {
                throw new \RuntimeException('Unable to find any supported Symfony configuration file.');
            }

            $this->dirs = $this->dirFetcher->fetchDirectories($config, $rootDir);
        }

        return $this->dirs;
    }

    /**
     * @param string      $dir    Directory
     * @param string|null $suffix Suffix
     *
     * @return string
     */
    protected function nameArchive(string $dir, ?string $suffix = null): string
    {
        if (!empty($suffix)) {
            $suffix = '_'.preg_replace('/[^0-9a-z]+/i', '-', $suffix);
        }

        return sprintf('%s%s_%s.zip', str_replace(DIRECTORY_SEPARATOR, '_', $dir), $suffix, $this->now->format('Y-m-d_H-i'));
    }
}
