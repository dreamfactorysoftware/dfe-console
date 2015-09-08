<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use Illuminate\Contracts\Bus\SelfHandling;
use Symfony\Component\Console\Input\InputOption;

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

    /** @inheritdoc */
    public function fire()
    {
        parent::fire();

        if (empty($_git = $this->shell('which git'))) {
            $this->error('"git" does not appear to be installed on this system. It is required for this command');

            return 1;
        }

        $_currentBranch = $this->shell($_git . ' rev-parse --abbrev-ref HEAD');

        if (0 != $this->shell($_git . ' remote update >/dev/null 2>&1')) {
            $this->error('Error retrieving update from remote origin.');

            return 1;
        }

        $_current = $this->shell($_git . ' rev-parse HEAD');
        $_remote = $this->shell($_git . ' rev-parse origin/' . $_currentBranch);

        if ($_current == $_remote) {
            $this->info('Your installation is up-to-date (revision: <comment>' . $_current . '</comment>)');

            return 0;
        }

        $this->info('Upgrading to revision <comment>' . $_remote . '</comment>');
        if (0 != $this->shell($_git . ' pull -q --ff-only origin ' . $_currentBranch, true)) {
            $this->error('Error while pulling current revision. Reverting.');
            if (0 != $this->shell($_git . ' revert --hard ' . $_current)) {
                $this->error('Error while reverting to prior version.');
            }

            return 1;
        }

        if (!$this->option('no-composer')) {
            $this->info('Updating composer dependencies');
            $this->shell('composer --quiet --no-interaction --no-ansi update');
        };

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

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [['no-composer', null, InputOption::VALUE_NONE,]]);
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
