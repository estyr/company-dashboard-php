<?php
require_once 'Meeting.php';

class Contact {
    public int $id;
    public string $name;
    public string $email;
    public array $meetings = [];

    public function __construct(int $id, string $name, string $email){
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }

    public function addMeeting(Meeting $meeting){
        $this->meetings[] = $meeting;
    }

    public function totalMeetings(): int {
        return count($this->meetings);
    }

    public function completionPercentage(): int {
        if(!$this->meetings) return 0;
        $completed = count(array_filter($this->meetings, fn($m)=>$m->status==='Completed'));
        return round(($completed / count($this->meetings)) * 100);
    }

    public function countByType(string $type): int {
        return count(array_filter($this->meetings, fn($m)=>$m->type === $type));
    }
}
