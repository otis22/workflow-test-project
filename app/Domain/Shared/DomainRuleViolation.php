<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use DomainException;

final class DomainRuleViolation extends DomainException {}
