<?php
require_once 'models/Company.php';
require_once 'models/Contact.php';
require_once 'models/Meeting.php';


$companies = [];

$cA = new Company('Company A');
$contact1 = new Contact(1,'Yossi Levi','yossi@example.com');
$contact1->addMeeting(new Meeting('2025-10-01','Project Kickoff','Completed','Internal','Alice','All went well'));
$contact1->addMeeting(new Meeting('2025-10-05','Follow-up','Pending','Client','Bob','Client requested changes'));
$cA->addContact($contact1);

$contact2 = new Contact(2,'Danny Shemesh','danny@example.com');
$contact2->addMeeting(new Meeting('2025-10-03','Tech Discussion','Pending','Internal','Charlie','Internal review'));
$contact2->addMeeting(new Meeting('2025-10-04','Demo Presentation','Completed','Client','Alice','Client happy'));
$contact2->addMeeting(new Meeting('2025-10-06','Feedback','Completed','Client','Bob','Minor updates requested'));
$cA->addContact($contact2);

$companies[] = $cA;

$cB = new Company('Company B');
$contact3 = new Contact(3,'Ronit Cohen','ronit@example.com');
$contact3->addMeeting(new Meeting('2025-10-02','Sales Review','Completed','Client','Charlie','Met targets'));
$cB->addContact($contact3);

$companies[] = $cB;

// =====================
// יצירת CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="crm_export.csv"');
$out = fopen('php://output','w');
fputcsv($out,['Company','Contact ID','Name','Email','Meeting Date','Topic','Status','Type','Assigned','Notes']);

foreach($companies as $c){
    foreach($c->contacts as $contact){
        foreach($contact->meetings as $m){
            fputcsv($out,[$c->name,$contact->id,$contact->name,$contact->email,$m->date,$m->topic,$m->status,$m->type,$m->assigned,$m->notes]);
        }
    }
}
fclose($out);
exit;
