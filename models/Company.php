<?php
require_once 'Contact.php';

class Company {
    public string $name;
    public array $contacts = [];

    public function __construct(string $name){
        $this->name = $name;
    }

    public function addContact(Contact $contact){
        $this->contacts[] = $contact;
    }

    public function totalMeetings(): int {
        $sum = 0;
        foreach($this->contacts as $c) $sum += $c->totalMeetings();
        return $sum;
    }

    public function completedMeetings(): int {
        $sum = 0;
        foreach($this->contacts as $c) $sum += count(array_filter($c->meetings, fn($m)=>$m->status==='Completed'));
        return $sum;
    }
}
