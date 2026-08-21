<?php

declare(strict_types=1);

namespace App\Enums;

enum Courier: string
{
    case JNE = 'JNE';
    case JNT = 'JNT';
    case SICEPAT = 'SICEPAT';
    case ANTERAJA = 'ANTERAJA';
    case POS = 'POS';
    case NINJA = 'NINJA';

    public function label(): string
    {
        return match ($this) {
            self::JNE => 'JNE',
            self::JNT => 'J&T Express',
            self::SICEPAT => 'SiCepat',
            self::ANTERAJA => 'AnterAja',
            self::POS => 'POS Indonesia',
            self::NINJA => 'Ninja Xpress',
        };
    }
}
