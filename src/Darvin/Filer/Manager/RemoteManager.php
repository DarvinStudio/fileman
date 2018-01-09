<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Filer\Manager;

use Darvin\Filer\SSH\SSHClient;

/**
 * Remote manager
 */
class RemoteManager
{
    /**
     * @var string
     */
    private $projectPath;

    /**
     * @var \Darvin\Filer\SSH\SSHClient
     */
    private $sshClient;

    /**
     * @param string                      $projectPath Project path
     * @param \Darvin\Filer\SSH\SSHClient $sshClient   SSH client
     */
    public function __construct($projectPath, SSHClient $sshClient)
    {
        $this->projectPath = $projectPath;
        $this->sshClient = $sshClient;
    }
}