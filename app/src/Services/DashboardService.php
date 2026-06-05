<?php

class DashboardService
{
    private Session $sessions;
    private Participation $participations;

    public function __construct()
    {
        $this->sessions       = new Session();
        $this->participations = new Participation();
    }

    public function forUser(int $userId): array
    {
        return [
            'mySessions' => $this->sessions->forUser($userId),
            'pending'    => $this->participations->pendingForHost($userId),
            'stats'      => [
                'hosted' => $this->sessions->countHostedBy($userId),
                'joined' => $this->participations->countApprovedFor($userId),
            ],
        ];
    }
}