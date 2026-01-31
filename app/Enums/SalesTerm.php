<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SalesTerm: string implements HasLabel, HasColor
{
    case COD = 'COD';
    case COD_DOT = 'C.O.D.';
    case CREDITO = 'CREDITO';
    case CREDIT = 'CREDIT';
    case CREDIT_COD = 'CREDIT-COD';
    case CREDIT_COD_SPACE = 'CREDIT COD';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::COD, self::COD_DOT => 'C.O.D.',
            self::CREDITO, self::CREDIT => 'Crédito',
            self::CREDIT_COD, self::CREDIT_COD_SPACE => 'Crédito COD',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::COD, self::COD_DOT => 'success',
            self::CREDITO, self::CREDIT => 'warning',
            self::CREDIT_COD, self::CREDIT_COD_SPACE => 'info',
        };
    }
}
