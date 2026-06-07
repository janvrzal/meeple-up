<?php

class TournamentParticipation extends Model
{
    public function join(int $userId, int $tournamentId): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO tournament_participations (user_id, tournament_id) VALUES (:u, :t)'
        );
        $stmt->execute(['u' => $userId, 't' => $tournamentId]);
    }

    public function leave(int $userId, int $tournamentId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM tournament_participations WHERE user_id = :u AND tournament_id = :t'
        );
        $stmt->execute(['u' => $userId, 't' => $tournamentId]);
    }

    public function isMember(int $userId, int $tournamentId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM tournament_participations WHERE user_id = :u AND tournament_id = :t'
        );
        $stmt->execute(['u' => $userId, 't' => $tournamentId]);
        return $stmt->fetchColumn() !== false;
    }

    public function memberCount(int $tournamentId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM tournament_participations WHERE tournament_id = :t');
        $stmt->execute(['t' => $tournamentId]);
        return (int) $stmt->fetchColumn();
    }
}