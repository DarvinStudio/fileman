<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Directory;

/**
 * Directory fetcher
 */
interface DirectoryFetcherInterface
{
    public const FORMAT_DOTENV = 'dotenv';
    public const FORMAT_YAML   = 'yaml';

    /**
     * @param string $config  Configuration file content
     * @param string $format  Configuration file format
     * @param string $rootDir Root directory
     *
     * @return array
     * @throws \RuntimeException
     */
    public function fetchDirectories(string $config, string $format, string $rootDir): array;
}