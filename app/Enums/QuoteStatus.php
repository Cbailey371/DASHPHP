<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QuoteStatus: string implements HasLabel, HasColor
{
    case APPROVED = 'APPROVED';
    case APROVED = 'APROVED'; // Legacy typo
    case BILLED = 'BILLED';
    case ACTIVE = 'ACTIVE';
    case PENDING = 'PENDING';
    case CANCELLED = 'CANCELLED';
    case ABORTED = 'ABORTED';
    case VENTA_PERDIDA = 'VENTA-PERDIDA';
    case EXPIRED = 'EXPIRED';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::APPROVED, self::APROVED => 'Aprobada',
            self::BILLED => 'Facturada',
            self::ACTIVE => 'Activa',
            self::PENDING => 'Pendiente',
            self::CANCELLED => 'Cancelada',
            self::ABORTED => 'Abortada',
            self::VENTA_PERDIDA => 'Venta Perdida',
            self::EXPIRED => 'Expirada',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::APPROVED, self::APROVED, self::BILLED => 'success',
            self::ACTIVE => 'primary',
            self::PENDING => 'warning',
            self::CANCELLED, self::ABORTED, self::VENTA_PERDIDA => 'danger',
            self::EXPIRED => 'gray',
        };
    }
}
