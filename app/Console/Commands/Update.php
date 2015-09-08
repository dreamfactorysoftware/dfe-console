<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use Illuminate\Contracts\Bus\SelfHandling;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends ConsoleCommand implements SelfHandling
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:update';
    /** @inheritdoc */
    protected $description = 'Update DFE Console to the latest version.';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle the command
     *
     * @return mixed
     */
    public function fire()
    {
        parent::fire();

        $_noComposer = $this->option('no-composer');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = (extension_loaded('openssl') ? 'https' : 'http') . '://' . self::HOMEPAGE;
        $config = Composer\Factory::createConfig();
        $remoteFilesystem = new RemoteFilesystem($this->getIO(), $config);
        $cacheDir = $config->get('cache-dir');
        $rollbackDir = $config->get('home');
        $localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];

        // check if current dir is writable and if not try the cache dir from settings
        $tmpDir = is_writable(dirname($localFilename)) ? dirname($localFilename) : $cacheDir;

        // check for permissions in local filesystem before start connection process
        if (!is_writable($tmpDir)) {
            throw new FilesystemException('Composer update failed: the "' .
                $tmpDir .
                '" directory used to download the temp file could not be written');
        }
        if (!is_writable($localFilename)) {
            throw new FilesystemException('Composer update failed: the "' .
                $localFilename .
                '" file could not be written');
        }

        if ($input->getOption('rollback')) {
            return $this->rollback($output, $rollbackDir, $localFilename);
        }

        $latestVersion = trim($remoteFilesystem->getContents(self::HOMEPAGE, $baseUrl . '/version', false));
        $updateVersion = $input->getArgument('version') ?: $latestVersion;

        if (preg_match('{^[0-9a-f]{40}$}', $updateVersion) && $updateVersion !== $latestVersion) {
            $this->getIO()
                ->writeError('<error>You can not update to a specific SHA-1 as those phars are not available for download</error>');

            return 1;
        }

        if (\ComposerAutoloaderInitf3508d1ad5be1efead3214b64711cab1::VERSION === $updateVersion) {
            $this->getIO()->writeError('<info>You are already using composer version ' . $updateVersion . '.</info>');

            return 0;
        }

        $tempFilename = $tmpDir . '/' . basename($localFilename, '.phar') . '-temp.phar';
        $backupFile = sprintf('%s/%s-%s%s',
            $rollbackDir,
            strtr(Composer::RELEASE_DATE, ' :', '_-'),
            preg_replace('{^([0-9a-f]{7})[0-9a-f]{33}$}', '$1', Composer::VERSION),
            self::OLD_INSTALL_EXT);

        $this->getIO()->writeError(sprintf("Updating to version <info>%s</info>.", $updateVersion));
        $remoteFilename =
            $baseUrl .
            (preg_match('{^[0-9a-f]{40}$}', $updateVersion) ? '/composer.phar'
                : "/download/{$updateVersion}/composer.phar");
        $remoteFilesystem->copy(self::HOMEPAGE, $remoteFilename, $tempFilename, !$input->getOption('no-progress'));
        if (!file_exists($tempFilename)) {
            $this->getIO()
                ->writeError('<error>The download of the new composer version failed for an unexpected reason</error>');

            return 1;
        }

        // remove saved installations of composer
        if ($input->getOption('clean-backups')) {
            $finder = $this->getOldInstallationFinder($rollbackDir);

            $fs = new Filesystem;
            foreach ($finder as $file) {
                $file = (string)$file;
                $this->getIO()->writeError('<info>Removing: ' . $file . '</info>');
                $fs->remove($file);
            }
        }

        if ($err = $this->setLocalPhar($localFilename, $tempFilename, $backupFile)) {
            $this->getIO()->writeError('<error>The file is corrupted (' . $err->getMessage() . ').</error>');
            $this->getIO()->writeError('<error>Please re-run the self-update command to try again.</error>');

            return 1;
        }

        if (file_exists($backupFile)) {
            $this->getIO()->writeError('Use <info>composer self-update --rollback</info> to return to version ' .
                Composer::VERSION);
        } else {
            $this->getIO()->writeError('<warning>A backup of the current version could not be written to ' .
                $backupFile .
                ', no rollback possible</warning>');
        }
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), ['version', InputArgument::OPTIONAL, 'The version to update to']);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                ['rollback', 'r', InputOption::VALUE_NONE, 'Revert to an older version of DFE Console'],
                [
                    'no-composer',
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, "composer update" will not be executed.',
                ],
                [
                    'clean-backups',
                    null,
                    new InputOption('clean',
                        null,
                        InputOption::VALUE_NONE,
                        'Clean up old backups made during priore updates. Afterwards, the current version will be the only backup available'),
                ],
                [
                    'no-progress',
                    null,
                    InputOption::VALUE_NONE,
                    'If specified, no progress will be displayed during an update',
                ],
            ]);
    }

    /** @inheritdoc */
    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>dfe:update</info> command checks github.com for newer
versions of DFE Console and if found, installs the latest.

<info>php artisan dfe:update</info>

EOT
        );
    }

    protected function rollback(OutputInterface $output, $rollbackDir, $localFilename)
    {
        $rollbackVersion = $this->getLastBackupVersion($rollbackDir);
        if (!$rollbackVersion) {
            throw new \UnexpectedValueException('Composer rollback failed: no installation to roll back to in "' .
                $rollbackDir .
                '"');
        }

        if (!is_writable($rollbackDir)) {
            throw new FilesystemException('Composer rollback failed: the "' .
                $rollbackDir .
                '" dir could not be written to');
        }

        $old = $rollbackDir . '/' . $rollbackVersion . self::OLD_INSTALL_EXT;

        if (!is_file($old)) {
            throw new FilesystemException('Composer rollback failed: "' . $old . '" could not be found');
        }
        if (!is_readable($old)) {
            throw new FilesystemException('Composer rollback failed: "' . $old . '" could not be read');
        }

        $oldFile = $rollbackDir . "/{$rollbackVersion}" . self::OLD_INSTALL_EXT;
        $this->getIO()->writeError(sprintf("Rolling back to version <info>%s</info>.", $rollbackVersion));
        if ($err = $this->setLocalPhar($localFilename, $oldFile)) {
            $this->getIO()->writeError('<error>The backup file was corrupted (' .
                $err->getMessage() .
                ') and has been removed.</error>');

            return 1;
        }

        return 0;
    }

    protected function setLocalPhar($localFilename, $newFilename, $backupTarget = null)
    {
        try {
            @chmod($newFilename, fileperms($localFilename));
            if (!ini_get('phar.readonly')) {
                // test the phar validity
                $phar = new \Phar($newFilename);
                // free the variable to unlock the file
                unset($phar);
            }

            // copy current file into installations dir
            if ($backupTarget && file_exists($localFilename)) {
                @copy($localFilename, $backupTarget);
            }

            rename($newFilename, $localFilename);
        } catch (\Exception $e) {
            if ($backupTarget) {
                @unlink($newFilename);
            }
            if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                throw $e;
            }

            return $e;
        }
    }

    protected function getLastBackupVersion($rollbackDir)
    {
        $finder = $this->getOldInstallationFinder($rollbackDir);
        $finder->sortByName();
        $files = iterator_to_array($finder);

        if (count($files)) {
            return basename(end($files), self::OLD_INSTALL_EXT);
        }

        return false;
    }

    protected function getOldInstallationFinder($rollbackDir)
    {
        $finder = Finder::create()->depth(0)->files()->name('*' . self::OLD_INSTALL_EXT)->in($rollbackDir);

        return $finder;
    }
}
