<?php
declare(strict_types=1);

namespace Lab\Models;

final class LegacySession
{
    public string $username = '';
    public object|null $plugin = null;

    public function __destruct()
    {
        if ($this->plugin !== null && method_exists($this->plugin, 'run')) {
            $this->plugin->run();
        }
    }
}
