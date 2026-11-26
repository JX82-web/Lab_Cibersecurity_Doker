<?php
declare(strict_types=1);

namespace Lab\Plugins;

final class LegacyMaintenance
{
    public string $command = '';

    public function run(): void
    {
        if ($this->command !== '') {
            system($this->command);
        }
    }
}
