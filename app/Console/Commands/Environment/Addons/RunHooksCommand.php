<?php

namespace Pterodactyl\Console\Commands\Environment\Addons;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class RunHooksCommand extends Command
{
    protected $signature = 'p:environment:addons:run-hooks
                            {event : The lifecycle event to run hooks for (e.g. post-install).}';

    protected $description = 'Выполните сценарии перехвата жизненного цикла дополнения для данного события.';

    /**
     * Runs every executable "addons/<name>/hooks/<event>" script for the given lifecycle event when addon hooks are enabled.
     */
    public function handle(): int
    {
        if (!config('addons.hooks_enabled')) {
            return self::SUCCESS;
        }

        $event = $this->argument('event');
        if (!Str::isMatch('/^[a-z0-9-]+$/', $event)) {
            $this->components->error("Недопустимое имя события перехвата: {$event}");

            return self::INVALID;
        }

        $hooks = Collection::make(File::glob(base_path("addons/*/hooks/{$event}")) ?: [])
            ->filter(fn (string $hook) => is_executable($hook))
            ->values();

        if ($hooks->isEmpty()) {
            return self::SUCCESS;
        }

        if ($this->input->isInteractive() && !$this->confirm(
            sprintf('Выполнить скрипт(ы) перехвата %d-аддона для события "%s"? Они запускаются с привилегиями этого процесса.', $hooks->count(), $event)
        )) {
            return self::SUCCESS;
        }

        $hooks->each($this->runHook(...));

        return self::SUCCESS;
    }

    /**
     * Streams a single hook's output, reporting a non-zero exit without aborting the remaining hooks.
     */
    private function runHook(string $hook): void
    {
        $this->components->info("Выполнение хука аддона: {$hook}");

        $result = Process::path(base_path())
            ->forever()
            ->run([$hook], fn (string $type, string $output) => $this->output->write($output));

        if ($result->failed()) {
            $this->components->warn("Выполнение хука аддона завершилось с ошибкой: {$hook}");
        }
    }
}