<?php

declare(strict_types=1);

namespace Rpn\Enum;

enum OperatorType
{
    case Binary; // e.g., a + b
    case UnaryPrefix; // e.g., -a, !a, log(a)
    case UnaryPostfix; // e.g., a!
    case Function; // e.g., pow(a,b)
}
