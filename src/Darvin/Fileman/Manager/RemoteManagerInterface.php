<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Manager;

/**
 * Remote manager
 */
interface RemoteManagerInterface extends ManagerInterface
{
    /**
     * @param callable $callback Success callback
     *
     * @return array
     */
    public function archiveFiles(callable $callback): array;

    /**
     * @param callable $callback         Success callback
     * @param string   $localProjectPath Local project path
     */
    public function downloadArchives(callable $callback, string $localProjectPath): void;

    /**
     * @param callable $callback Success callback
     */
    public function extractFiles(callable $callback): void;

    /**
     * @param callable $callback Success callback
     */
    public function removeArchives(callable $callback): void;

    /**
     * @param callable $callback         Success callback
     * @param string   $localProjectPath Local project path
     * @param array    $archiveFilenames Archive filenames
     */
    public function uploadArchives(callable $callback, string $localProjectPath, array $archiveFilenames): void;
}
