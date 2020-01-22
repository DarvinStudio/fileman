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

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\FormatException;
use Symfony\Component\Yaml\Yaml;

/**
 * Directory fetcher
 */
class DirectoryFetcher implements DirectoryFetcherInterface
{
    /**
     * @var \Symfony\Component\Dotenv\Dotenv|null
     */
    private $dotenv;

    /**
     * @var string[]
     */
    private $params;

    /**
     * @param \Symfony\Component\Dotenv\Dotenv|null $dotenv Dotenv
     * @param string[]                              $params Directory parameters
     */
    public function __construct(?Dotenv $dotenv, array $params)
    {
        $this->dotenv = $dotenv;
        $this->params = $params;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchDirectories(string $config, string $format, string $rootDir): array
    {
        switch ($format) {
            case self::FORMAT_DOTENV:
                if (null === $this->dotenv) {
                    throw new \RuntimeException('Symfony Dotenv component is not installed.');
                }

                $values = $this->dotenv->parse($config);

                break;
            case self::FORMAT_YAML:
                $yaml = Yaml::parse($config);

                if (!isset($yaml['parameters'])) {
                    throw new \RuntimeException(sprintf('Configuration must contain root node "parameters" (%s).', $config));
                }

                $values = $yaml['parameters'];

                break;
            default:
                throw new \RuntimeException(sprintf('Format "%s" is not supported.', $format));
        }

        $values = array_combine(array_map('mb_strtolower', array_keys($values)), $values);
        $dirs   = [];

        foreach ($this->params as $param) {
            if (!array_key_exists($param, $values)) {
                throw new \RuntimeException(sprintf('Parameter "%s" does not exist (%s).', $param, $config));
            }

            $dirs[$param] = implode(DIRECTORY_SEPARATOR, [$rootDir, trim($values[$param], DIRECTORY_SEPARATOR)]);
        }

        return $dirs;
    }
}
