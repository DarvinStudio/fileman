<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Filer\Directory;

use Symfony\Component\Yaml\Yaml;

/**
 * Directory fetcher
 */
class DirectoryFetcher
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
     * @param string $yaml Configuration YAML
     *
     * @return array
     */
    public function fetchDirectories($yaml)
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

            $dirs[$param] = $config[$param];
        }

        return $dirs;
    }
}
