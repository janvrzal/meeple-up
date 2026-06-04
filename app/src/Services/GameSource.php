<?php

interface GameSource
{
    /** Vyhledá hry podle názvu. Vrací pole [['bgg_id'=>, 'name'=>, 'year_published'=>], ...]. */
    public function search(string $query): array;

    /** Vrátí kompletní data hry k uložení do cache, nebo null. */
    public function fetch(int $bggId): ?array;
}