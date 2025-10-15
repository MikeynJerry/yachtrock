<?php

declare(strict_types=1);

namespace Bga\Games\YachtRock;

final class Constants
{
    public const LABEL_ACTIVE_TURN_PLAYER = 'active_turn_player';
    public const LABEL_TURN_PHASE = 'turn_phase';
    public const LABEL_REMAINING_OPTIONALS = 'remaining_optionals';

    // Phases
    public const PHASE_MAIN = 1;
    public const PHASE_OPTIONAL = 2;

    // Option flags
    public const OPT_SINGLE = 1;
    public const OPT_SELL   = 2;
}
