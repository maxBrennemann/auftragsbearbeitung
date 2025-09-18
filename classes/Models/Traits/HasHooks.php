<?php

namespace Classes\Models\Traits;

trait HasHooks
{
    protected array $hooks = [];

    protected function triggerHook(string $hookName, array $data): void
    {
        if (isset($this->hooks[$hookName]) && is_callable($this->hooks[$hookName])) {
            call_user_func($this->hooks[$hookName], $data);
        }
    }
}