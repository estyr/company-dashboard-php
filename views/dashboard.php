<!DOCTYPE html>
<html lang="he">
<head>
<meta charset="UTF-8">
<title>Company  Dashboard</title>
<link rel="stylesheet" href="assets/style.css">
<script src="assets/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<h1>Company  Dashboard</h1>

<form method="GET" class="company-filter">
<label for="company">Filter by Company:</label>
<select name="company" id="company" onchange="this.form.submit()">
<option value="">All Companies</option>
<?php foreach(array_map(fn($c)=>$c->name,$companies) as $cname): ?>
<option value="<?= $cname ?>" <?= $companyFilter===$cname?'selected':'' ?>><?= $cname ?></option>
<?php endforeach; ?>
</select>

<label for="search">Search:</label>
<input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Contact or topic"/>
<button type="submit">Go</button>
<a href="?export=csv" style="margin-left:20px;">Download CSV Report</a>
</form>

<?php if(empty($companies)): ?>
    <p style="font-weight:bold; color:#d9534f;">לא נמצאו תוצאות לחיפוש או לפילטר שהוזן.</p>
<?php endif; ?>

<?php 
$totalInternal = $totalClient = 0; // לגרף
foreach($companies as $company):
?>
<h2 class="collapse" onclick="toggle('company-<?= md5($company->name) ?>')">
    <?= $company->name ?> (<?= count($company->contacts) ?> contacts)
</h2>

<tbody id="company-<?= md5($company->name) ?>" style="display:table-row-group;">
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Meetings</th>
<th>Completion %</th>
</tr>

<?php
$maxMeetings = 0;
$topContacts = [];
foreach($company->contacts as $contact){
    $total = $contact->totalMeetings();
    if($total > $maxMeetings){
        $maxMeetings = $total;
        $topContacts = [$contact->id];
    } elseif($total === $maxMeetings){
        $topContacts[] = $contact->id;
    }
}

foreach($company->contacts as $contact):
$highlightClass = in_array($contact->id, $topContacts) ? 'highlight' : '';
$percent = $contact->completionPercentage();
$totalInternal += $contact->countByType('Internal');
$totalClient += $contact->countByType('Client');
?>
<tr class="<?= $highlightClass ?>">
<td><?= $contact->id ?></td>
<td><?= $contact->name ?></td>
<td><?= $contact->email ?></td>
<td>
<ul class="meetings">
<?php foreach($contact->meetings as $m): ?>
<li><?= $m->date ?> - <?= $m->topic ?>
<span class="badge <?= $m->status ?>"><?= $m->status ?></span>
<em>(<?= $m->type ?>, assigned: <?= $m->assigned ?>)</em>
</li>
<?php endforeach; ?>
</ul>
</td>
<td>
<?= $percent ?>%
<div class="bar-container">
<div class="bar" style="width: <?= $percent ?>%"></div>
</div>
</td>
</tr>
<?php endforeach; ?>
</table>
</tbody>
<?php endforeach; ?>

<!-- גרף פגישות לפי סוג -->
<div class="chart-container">
<canvas id="meetingsChart"></canvas>
</div>
<script>
const ctx = document.getElementById('meetingsChart').getContext('2d');
const data = {
    labels: ['Internal','Client'],
    datasets: [{
        label: 'Meetings Count',
        data: [<?= $totalInternal ?>, <?= $totalClient ?>],
        backgroundColor: ['#004080','#28a745']
    }]
};
new Chart(ctx,{type:'bar',data:data});
</script>
</body>
</html>
