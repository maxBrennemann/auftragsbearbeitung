<?php

namespace Classes\Project;

class Priority
{

    public static function getPriorityLevel($prio)
    {
        $prio = (int) $prio;
        if ($prio > 95)
            return "Dringend";
        if ($prio > 85)
            return "Sehr hoch";
        if ($prio > 70)
            return "Hoch";
        if ($prio > 50)
            return "Mittel";
        if ($prio > 30)
            return "Gering";
        if ($prio > 15)
            return "Sehr gering";
        return "Niedrig";
    }
}
