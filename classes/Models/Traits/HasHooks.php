<?php

namespace Classes\Models\Traits;

trait HasHooks
{

    /** @var array<string, array{class-string, string}> */
    protected array $hooks = [];

    /**
     * @param string $hookName
     * @param array<mixed, mixed> $data
     * @return void
     */
    protected function triggerHook(string $hookName, array $data): void
    {
        if (isset($this->hooks[$hookName]) && is_callable($this->hooks[$hookName])) {
            call_user_func($this->hooks[$hookName], $data);
        }
    }
}