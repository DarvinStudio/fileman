<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Directory;

use Symfony\Component\Yaml\Yaml;

/**
 * Directory fetcher
 */
class DirectoryFetcher implements DirectoryFetcherInterface
{
    /**
     * @var string[]
     */
    private $params;

    /**
     * @param string[] $params Directory parameters
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchDirectories(string $yaml): array
    {
        $parsed = Yaml::parse($yaml);

        if (!isset($parsed['parameters'])) {
            throw new \RuntimeException(sprintf('Configuration must contain root node "parameters" (%s).', $yaml));
        }

        $config = $parsed['parameters'];

        $dirs = [];

        foreach ($this->params as $param) {
            if (!array_key_exists($param, $config)) {
                throw new \RuntimeException(sprintf('Parameter "%s" does not exist (%s).', $param, $yaml));
            }

            $dirs[$param] = trim($config[$param], DIRECTORY_SEPARATOR);
        }

        return $dirs;
    }
}
