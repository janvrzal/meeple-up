<?php

/**
 * Generates simple, deterministic initials-avatars (color + letter) from a name.
 * Later, when users can upload a profile picture, swap the letter for the image.
 */
class Avatar
{
    private const COLORS = [
        '#ef4444', '#f97316', '#f59e0b', '#10b981',
        '#14b8a6', '#3b82f6', '#6366f1', '#8b5cf6', '#ec4899',
    ];

    public static function initial(string $name): string
    {
        return mb_strtoupper(mb_substr(trim($name), 0, 1)) ?: '?';
    }

    public static function color(string $name): string
    {
        $index = (int) sprintf('%u', crc32($name)) % count(self::COLORS);
        return self::COLORS[$index];
    }

    /** Vrátí hotový HTML avatar (kolečko s iniciálou). */
    public static function html(string $name, string $size = 'w-8 h-8'): string
    {
        $color   = self::color($name);
        $initial = htmlspecialchars(self::initial($name));
        return '<div class="avatar placeholder">'
             . '<div class="' . $size . ' rounded-full text-white" style="background-color:' . $color . '">'
             . '<span class="text-sm font-medium">' . $initial . '</span>'
             . '</div></div>';
    }
}
