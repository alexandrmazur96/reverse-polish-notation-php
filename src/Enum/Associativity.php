<?php

declare(strict_types=1);

namespace Rpn\Enum;

enum Associativity
{
    case Left;
    case Right;
    case None;
}
