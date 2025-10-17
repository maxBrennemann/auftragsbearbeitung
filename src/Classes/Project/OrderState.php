<?php

namespace Src\Classes\Project;

enum OrderState: string {
    case Default = "default";
    case Archived = "archived";
    case Finished = "finished";
    case Invoiced = "invoiced";

    public function isFinal(): bool
    {
        return match($this) {
            self::Invoiced => true,
            default => false,
        };
    }
}
