<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use Illuminate\Contracts\Bus\SelfHandling;

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

        if (empty($_git = $this->shell('which git'))) {
            $this->error('"git" does not appear to be installed on this system. It is required for this command');

            return 1;
        }

        $_currentBranch = $this->shell($_git . ' rev-parse --abbrev-ref HEAD');
        $_current = $this->shell($_git . ' rev-parse HEAD');
        $_remote = $this->shell($_git . ' rev-parse origin/' . $_currentBranch);

        if ($_current == $_remote) {
            $this->info('Your installation is up-to-date (revision: <comment>' . $_current . '</comment>)');

            return 0;
        }

        $this->info('Upgrading to revision ' . $_remote);
        if (0 != $this->shell($_git . ' pull -q --ff-only origin ' . $_currentBranch, true)) {
            $this->error('Error while pulling current revision. Reverting.');

            return 1;
        }

        if (!$this->option('no-composer')) {
            $this->info('Updating composer dependencies');

            if (0 == $this->shell('composer -qn update', true)) {
                $this->error('Error while running composer update. Manual intervention most likely will be necessary.');

                return 1;
            };
        }

        return 0;
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

    /**
     * Executes a shell command stripping newlines from result
     *
     * @param string $command
     * @param bool   $returnExitCode If true, the exit code is returned instead of the output
     *
     * @return string
     */
    protected function shell($command, $returnExitCode = false)
    {
        $_returnVar = $_output = null;
        $_result = trim(str_replace(PHP_EOL, ' ', exec($command, $_output, $_returnVar)));

        return $returnExitCode ? $_returnVar : (0 == $_returnVar ? $_result : null);
    }

}
