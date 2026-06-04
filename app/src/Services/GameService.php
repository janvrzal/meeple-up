<?php

class GameService
{
    private Game $games;
    private GameSource $source;

    public function __construct()
    {
        $this->games  = new Game();
        // přepínač zdroje podle .env
        $this->source = (BGG_SOURCE === 'api') ? new BggApiSource() : new CatalogSource();
    }

    public function search(string $query): array
    {
        return $this->source->search($query);
    }

    /** Vrátí lokální games.id pro danou hru — z cache, nebo ji stáhne a uloží. */
    public function resolve(int $bggId): ?int
    {
        // cache HIT
        $existing = $this->games->findByBggId($bggId);
        if ($existing !== null) {
            return (int) $existing['id'];
        }

        // cache MISS → vezmi data ze zdroje a ulož
        $data = $this->source->fetch($bggId);
        if ($data === null) {
            return null;   // hra neexistuje / API selhalo
        }

        return $this->games->create($data);
    }
}