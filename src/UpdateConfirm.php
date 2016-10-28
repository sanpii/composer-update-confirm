<?php
declare(strict_types = 1);

namespace Sanpi\Composer;

use Composer\Command\ShowCommand;
use Composer\Composer;
use Composer\Console\Application;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class UpdateConfirm implements PluginInterface, EventSubscriberInterface
{
    private $composer;
    private $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'pre-update-cmd' => 'confirm',
        ];
    }

    public function confirm(Event $event)
    {
        $this->outdated();

        if (!$this->io->askConfirmation('Continue? [Y/n] ')) {
            $this->io->error("Aborted");
            die;
        }
    }

    public function outdated()
    {
        $input = new ArrayInput([
            'show',
            '--latest' => true,
            '--outdated' => true,
        ]);

        $command = new ShowCommand();
        $command->setComposer($this->composer);
        $command->setApplication(new Application);
        $command->setIO($this->io);
        $command->run($input, $this->createOutput());
    }

    public function createOutput(): ConsoleOutput
    {
        $styles = \Composer\Factory::createAdditionalStyles();
        $formatter = new OutputFormatter(null, $styles);

        return new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, null, $formatter);
    }
}
