<?php

namespace App\Enums;

enum SentenceFunctionWorld: string
{
    case T = 'T';
    case TSharp = 'T#';
    case S = 'S';
    case SSharp = 'S#';
    case D = 'D';
    case DSharp = 'D#';
    case DFlat = 'Db';

    public function color(): string
    {
        return match ($this) {
            self::T, self::TSharp => '#3B82F6',
            self::S, self::SSharp => '#16A34A',
            self::D => '#DC2626',
            self::DSharp => '#7C3AED',
            self::DFlat => '#DB2777',
        };
    }

    public function leadingNote(): string
    {
        return match ($this) {
            self::T, self::TSharp => '3',
            self::S, self::SSharp => '6',
            self::D => '2',
            self::DSharp => '7',
            self::DFlat => '4',
        };
    }

    public static function normalize(?string $world): string
    {
        $world = trim((string) $world);

        return match ($world) {
            'T♯' => self::TSharp->value,
            'S♯' => self::SSharp->value,
            'D♯' => self::DSharp->value,
            'D♭', 'D♭️' => self::DFlat->value,
            default => $world !== '' ? $world : self::T->value,
        };
    }

    public static function colorFor(?string $world): string
    {
        $normalized = self::normalize($world);

        return self::tryFrom($normalized)?->color() ?? '#3B82F6';
    }

    public static function leadingNoteFor(?string $world): string
    {
        $normalized = self::normalize($world);

        return self::tryFrom($normalized)?->leadingNote() ?? '';
    }
}
