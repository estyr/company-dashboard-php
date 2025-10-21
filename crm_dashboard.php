<?php

// מערכת ניהול לקוחות לדוגמה -י2025

require_once 'models/Company.php';
require_once 'models/Contact.php';
require_once 'models/Meeting.php';

// Mock Data
$companies = [];

$cA = new Company('Alpha Corp');
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

$cB = new Company('Beta Ltd');
$contact3 = new Contact(3,'Ronit Cohen','ronit@example.com');
$contact3->addMeeting(new Meeting('2025-10-02','Sales Review','Completed','Client','Charlie','Met targets'));
$cB->addContact($contact3);

$companies[] = $cB;

// CSV Export
if(isset($_GET['export']) && $_GET['export']=='csv'){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="crm_export.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Company','Contact ID','Name','Email','Meeting Date','Topic','Status','Type','Assigned','Notes']);
    foreach($companies as $c){
        foreach($c->contacts as $contact){
            foreach($contact->meetings as $m){
                fputcsv($output,[$c->name,$contact->id,$contact->name,$contact->email,$m->date,$m->topic,$m->status,$m->type,$m->assigned,$m->notes]);
            }
        }
    }
    fclose($output);
    exit;
}

// Filter/Search
$companyFilter = $_GET['company'] ?? '';
$search = $_GET['search'] ?? '';

function filterData($companies, $companyFilter, $search){
    $result = [];
    foreach($companies as $company){
        if($companyFilter && $company->name !== $companyFilter) continue;
        $filteredContacts = [];
        foreach($company->contacts as $contact){
            if($search){
                $matchContact = stripos($contact->name,$search)!==false;
                $matchMeeting = false;
                foreach($contact->meetings as $m){
                    if(stripos($m->topic,$search)!==false) $matchMeeting=true;
                }
                if(!$matchContact && !$matchMeeting) continue;
            }
            $filteredContacts[]=$contact;
        }
        if($filteredContacts){
            $company->contacts = $filteredContacts;
            $result[]=$company;
        }
    }
    return $result;
}

$companies = filterData($companies,$companyFilter,$search);
?>
<!DOCTYPE html>
<html lang="he">
<head>
<meta charset="UTF-8">
<title>Company Dashboard</title>
<link rel="stylesheet" href="assets/style.css">
<script src="assets/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<h1>Company Dashboard - Professional</h1>

<form method="GET" class="company-filter">
<label for="company">Filter by Company:</label>
<select name="company" id="company" onchange="this.form.submit()">
<option value="">All Companies</option>
<?php foreach($companies as $c): ?>
<option value="<?= $c->name ?>" <?= $companyFilter===$c->name?'selected':'' ?>><?= $c->name ?></option>
<?php endforeach; ?>
</select>

<label for="search">Search:</label>
<input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Contact or topic"/>
<button type="submit">Go</button>
<a href="?export=csv" style="margin-left:20px;">Export CSV</a>
</form>

<?php foreach($companies as $company): ?>
<h2 class="collapse" onclick="toggle('company-<?= md5($company->name) ?>')">
<?= $company->name ?> (<?= count($company->contacts) ?> contacts)
</h2>
<tbody id="company-<?= md5($company->name) ?>" style="display:table-row-group;">
<table>
<tr><th>ID</th><th>Name</th><th>Email</th><th>Meetings</th><th>Completion %</th></tr>
<?php 
$maxMeetings = 0;
$topContacts = [];
foreach($company->contacts as $contact){
    $total = $contact->totalMeetings();
    if($total>$maxMeetings){ $maxMeetings=$total; $topContacts=[$contact->id]; }
    elseif($total==$maxMeetings){ $topContacts[]=$contact->id; }
}
foreach($company->contacts as $contact):
$highlightClass = in_array($contact->id,$topContacts)?'highlight':'';
$percent = $contact->completionPercentage();
?>
<tr class="<?= $highlightClass ?>">
<td><?= $contact->id ?></td>
<td><?= $contact->name ?></td>
<td><?= $contact->email ?></td>
<td><ul class="meetings">
<?php foreach($contact->meetings as $m): ?>
<li><?= $m->date ?> - <?= $m->topic ?> <span class="badge <?= $m->status ?>"><?= $m->status ?></span> <em>(<?= $m->type ?>, assigned: <?= $m->assigned ?>)</em></li>
<?php endforeach; ?>
</ul></td>
<td><?= $percent ?>%
<div class="bar-container"><div class="bar" style="width: <?= $percent ?>%"></div></div>
</td>
</tr>
<?php endforeach; ?>
</table>
</tbody>
<?php endforeach; ?>

<div class="chart-container"><canvas id="meetingsChart"></canvas></div>
<script>
const ctx = document.getElementById('meetingsChart').getContext('2d');
const data = {
labels:['Internal','Client'],
datasets:[{label:'Meetings Count',data:[
<?php
$totalInternal=0;$totalClient=0;
foreach($companies as $c){ foreach($c->contacts as $ct){ foreach($ct->meetings as $m){
if($m->type=='Internal') $totalInternal++; else $totalClient++;
}}}
echo $totalInternal.','.$totalClient;
?>
],backgroundColor:['#004080','#28a745']}]
};
new Chart(ctx,{type:'bar',data:data});
</script>
</body>
</html>
