<?php

class BggApiSource implements GameSource
{
    private const BASE = 'https://boardgamegeek.com/xmlapi2/';

    public function search(string $query): array
    {
        $body = $this->http(self::BASE . 'search?type=boardgame&query=' . urlencode($query));
        if ($body === null) {
            return [];
        }

        $xml = simplexml_load_string($body);
        if ($xml === false) {
            return [];
        }

        $results = [];
        foreach ($xml->item as $item) {
            $results[] = [
                'bgg_id'         => (int) $item['id'],
                'name'           => (string) $item->name['value'],
                'year_published' => isset($item->yearpublished) ? (int) $item->yearpublished['value'] : null,
            ];
        }
        return $results;
    }

    public function fetch(int $bggId): ?array
    {
        $body = $this->http(self::BASE . 'thing?id=' . $bggId);
        if ($body === null) {
            return null;
        }

        $xml = simplexml_load_string($body);
        if ($xml === false || !isset($xml->item)) {
            return null;
        }

        $item = $xml->item;

        // primární název (hra má víc názvů – bereme type="primary")
        $name = '';
        foreach ($item->name as $n) {
            if ((string) $n['type'] === 'primary') {
                $name = (string) $n['value'];
                break;
            }
        }

        return [
            'bgg_id'         => $bggId,
            'name'           => $name,
            'year_published' => isset($item->yearpublished) ? (int) $item->yearpublished['value'] : null,
            'min_players'    => isset($item->minplayers)   ? (int) $item->minplayers['value']   : null,
            'max_players'    => isset($item->maxplayers)   ? (int) $item->maxplayers['value']   : null,
            'playing_time'   => isset($item->playingtime)  ? (int) $item->playingtime['value']  : null,
            'thumbnail_url'  => isset($item->thumbnail)    ? (string) $item->thumbnail          : null,
            'description'    => isset($item->description)  ? (string) $item->description        : null,
        ];
    }

    /** HTTP GET s Bearer tokenem + retry na 202/429. Vrací tělo nebo null. */
    private function http(string $url): ?string
    {
        $context = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => 'Authorization: Bearer ' . BGG_TOKEN,
                'timeout'       => 10,
                'ignore_errors' => true,   // ať čteme tělo i u ne-200
            ],
        ]);

        for ($i = 0; $i < 3; $i++) {
            $body = @file_get_contents($url, false, $context);
            $status = $this->statusFromHeaders($http_response_header ?? []);

            if ($status === 200) return $body;
            if ($status === 202) { sleep(2); continue; }   // BGG zpracovává → zkus znovu
            if ($status === 429) { sleep(5); continue; }   // rate limit → počkej
            return null;                                    // jiná chyba
        }
        return null;
    }

    private function statusFromHeaders(array $headers): int
    {
        if (empty($headers[0]) || !preg_match('#\s(\d{3})\s#', $headers[0], $m)) {
            return 0;
        }
        return (int) $m[1];
    }
}