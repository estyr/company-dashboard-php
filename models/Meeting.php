<?php
class Meeting {
    public string $date;
    public string $topic;
    public string $status;
    public string $type;
    public string $assigned;
    public string $notes;

    public function __construct(string $date, string $topic, string $status, string $type, string $assigned, string $notes){
        $this->date = $date;
        $this->topic = $topic;
        $this->status = $status;
        $this->type = $type;
        $this->assigned = $assigned;
        $this->notes = $notes;
    }
}
