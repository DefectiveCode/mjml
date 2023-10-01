<?php

declare(strict_types=1);

namespace DefectiveCode\MJML;

enum ValidationLevel: string
{
    case strict = 'strict';
    case soft = 'soft';
    case skip = 'skip';
}
