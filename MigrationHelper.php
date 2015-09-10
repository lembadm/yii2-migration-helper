<?php
/**
 * @author    Alexander Vizhanov <lembadm@gmail.com>
 * @copyright 2015 Astwell Soft <astwellsoft.com>
 */

namespace lembadm\migration;

use Symfony\Component\Process\ProcessBuilder;
use yii;
use yii\base\Component;

/**
 * MigrationHelper helps to up/down migrations from PHP script
 *
 * ```php
 * return [
 *     //....
 *     'components' => [
 *         'migration' => [
 *             'class'          => 'lembadm\migration\MigrationHelper',
 *             'migrationTable' => '<migrationTable>',
 *             'idleTimeout'    => '<idleTimeout>',
 *             'timeout'        => '<timeout>',
 *         ],
 *     ],
 * ];
 * ```
 *
 * @package lembadm\migration
 * @author  Alexander Vizhanov <lembadm@gmail.com>
 */
class MigrationHelper extends Component
{
    /**
     * @var int Sets the process timeout (max. runtime).
     *
     * To disable the timeout, set this value to null.
     */
    public $timeout = 3600;

    /**
     * @var int Sets the process idle timeout (max. time since last output).
     *
     * To disable the timeout, set this value to null.
     */
    public $idleTimeout = 60;

    /**
     * @var string the name of the table for keeping applied migration information.
     */
    public $migrationTable = '{{%migration}}';

    /**
     * Upgrades the application by applying new migrations.
     *
     * If `$migrationPath` not set will be apply app migrations
     *
     * ```php
     * try {
     *     $process = Yii::$app->migration->up('<migrationPath>');
     *     echo $process->getOutput();
     * } catch (ProcessFailedException $e) {
     *     echo $e->getMessage();
     * }
     * ```
     *
     * @param string|null $migrationPath Path to migrations (`--migrationPath` argument for `yii migrate/up` command).
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Symfony\Component\Process\Exception\ProcessTimedOutException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return \Symfony\Component\Process\Process
     */
    public function up($migrationPath = null)
    {
        return $this->getProcessBuilder($migrationPath)->mustRun();
    }

    /**
     * Upgrades the application by applying new migrations async.
     *
     * If `$migrationPath` not set will be apply app migrations
     *
     * You can retrieving output and the status in your main process whenever you need it.
     *
     * Use the isRunning() method to check if the process is done and the getOutput() method to get the output:
     * ```php
     * $process = Yii::$app->migration->upAsync('<migrationPath>');
     * while ($process->isRunning()) {
     *     // waiting for process to finish
     * }
     * ```
     *
     * You can also wait for a process to end if you started it asynchronously and are done doing other stuff:
     * ```php
     * $process = Yii::$app->migration->upAsync('<migrationPath>');
     * // ... do other things
     * $process->wait(function ($type, $buffer) {
     *     echo (Process::ERR === $type)
     *         ? 'ERR > '.$buffer
     *         : 'OUT > '.$buffer;
     * });
     * ```
     *
     * @param string|null $migrationPath Path to migrations (`--migrationPath` argument for `yii migrate/up` command).
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Symfony\Component\Process\Exception\ProcessTimedOutException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return \Symfony\Component\Process\Process
     */
    public function upAsync($migrationPath = null)
    {
        $process = $this->getProcessBuilder($migrationPath);
        $process->start();

        return $process;
    }

    /**
     * Downgrades the application by reverting old migrations.
     *
     * If `$migrationPath` not set will be apply app migrations
     *
     * ```php
     * try {
     *     $process = Yii::$app->migration->down('<migrationPath>', 5);
     *     echo $process->getOutput();
     * } catch (ProcessFailedException $e) {
     *     echo $e->getMessage();
     * }
     * ```
     *
     * @param string|null $migrationPath Path to migrations (`--migrationPath` argument for `yii migrate/up` command).
     * @param int         $limit         The number of migrations to be reverted. Defaults to 1, meaning the last
     *                                   applied migration will be reverted.
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Symfony\Component\Process\Exception\ProcessTimedOutException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return \Symfony\Component\Process\Process
     */
    public function down($migrationPath = null, $limit = 1)
    {
        return $this->getProcessBuilder($migrationPath, $limit)->mustRun();
    }

    /**
     * Downgrades the application by reverting old migrations async.
     *
     * If `$migrationPath` not set will be apply app migrations
     *
     * You can retrieving output and the status in your main process whenever you need it.
     *
     * Use the isRunning() method to check if the process is done and the getOutput() method to get the output:
     * ```php
     * $process = Yii::$app->migration->downAsync('<migrationPath>', 5);
     * while ($process->isRunning()) {
     *     // waiting for process to finish
     * }
     * ```
     *
     * You can also wait for a process to end if you started it asynchronously and are done doing other stuff:
     * ```php
     * $process = Yii::$app->migration->downAsync('<migrationPath>', 5);
     * // ... do other things
     * $process->wait(function ($type, $buffer) {
     *     echo (Process::ERR === $type)
     *         ? 'ERR > '.$buffer
     *         : 'OUT > '.$buffer;
     * });
     * ```
     *
     * @param string|null $migrationPath Path to migrations (`--migrationPath` argument for `yii migrate/up` command).
     * @param int         $limit         The number of migrations to be reverted. Defaults to 1, meaning the last
     *                                   applied migration will be reverted.
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Symfony\Component\Process\Exception\ProcessTimedOutException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return \Symfony\Component\Process\Process
     */
    public function downAsync($migrationPath = null, $limit = 1)
    {
        $process = $this->getProcessBuilder($migrationPath, $limit);
        $process->start();

        return $process;
    }

    /**
     * Returns ProcessBuilder instance with predefined process command for migration command execution.
     *
     * @param string $migrationPath Path to migrations (`--migrationPath` argument for `yii migrate/up` command).
     * @param int    $limit         The number of migrations to be reverted. Defaults to 1, meaning the last applied
     *                              migration will be reverted.
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Symfony\Component\Process\Exception\ProcessTimedOutException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return \Symfony\Component\Process\Process
     */
    protected function getProcessBuilder($migrationPath = null, $limit = 0)
    {
        $builder = (new ProcessBuilder())
            ->setWorkingDirectory(Yii::getAlias('@app'))
            ->setPrefix(PHP_BINDIR . '/php')
            ->setArguments([
                realpath(Yii::getAlias('@app') . '/yii'),
                'migrate/' . ($limit ? 'down' : 'up'),
                '--color=0',
                '--interactive=0',
                '--migrationTable=' . $this->migrationTable,
                $limit
            ]);

        if ($migrationPath) {
            $builder->add('--migrationPath=' . $migrationPath);
        }

        return $builder
            ->getProcess()
            ->setTimeout($this->timeout)
            ->setIdleTimeout($this->idleTimeout);
    }
}