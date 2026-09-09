<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Hire Provider | SkillConnect</title>

<link rel="stylesheet" href="css/dashboard.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="dashboard">

<!-- SIDEBAR -->

<aside class="sidebar">

<div class="logo">
<h2></h2>
</div>

<ul>

<li>
<a href="customer-dashboard.php">
<i class="fas fa-home"></i>
Dashboard
</a>
</li>

<li>
<a href="search-results.php">
<i class="fas fa-search"></i>
Find Providers
</a>
</li>

<li class="active">
<a href="hire-booking.php">
<i class="fas fa-calendar-check"></i>
Book Service
</a>
</li>

<li>
<a href="messages.php">
<i class="fas fa-comments"></i>
Messages
</a>
</li>

<li>
<a href="settings.php">
<i class="fas fa-cog"></i>
Settings
</a>
</li>

<li>
<a href="logout.php">
<i class="fas fa-sign-out-alt"></i>
Logout
</a>
</li>

</ul>

</aside>

<!-- MAIN -->

<main class="content">

<header class="topbar">

<div>

<h1>Hire a Professional</h1>

<p>Complete the booking form below.</p>

</div>

</header>

<section class="table-section">

<h2>Provider Information</h2>

<div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">

<img src="images/provider1.jpg"
style="width:120px;height:120px;border-radius:50%;object-fit:cover;">

<div>

<h3>David Wilson</h3>

<p>Master Electrician</p>

<p>⭐⭐⭐⭐⭐ 4.9 Rating</p>

<p>₦8,000 per hour</p>

</div>

</div>

</section>

<section class="table-section">

<h2>Booking Details</h2>

<form action="process-booking.php" method="POST">

<div class="cards">

<div class="card">

<label>Service Required</label><br><br>

<select style="width:100%;padding:12px;">

<option>Electrical Installation</option>

<option>Generator Repair</option>

<option>Solar Installation</option>

<option>Maintenance</option>

</select>

</div>

<div class="card">

<label>Budget</label><br><br>

<input type="number"
placeholder="Enter Budget"
style="width:100%;padding:12px;">

</div>

<div class="card">

<label>Preferred Date</label><br><br>

<input type="date"
style="width:100%;padding:12px;">

</div>

<div class="card">

<label>Preferred Time</label><br><br>

<input type="time"
style="width:100%;padding:12px;">

</div>

</div>

<br>

<label>Service Address</label>

<input
type="text"
placeholder="Enter your address"
style="width:100%;padding:15px;margin-top:10px;margin-bottom:20px;">

<label>Describe the Job</label>

<textarea
rows="7"
placeholder="Describe the work you need..."
style="width:100%;padding:15px;"></textarea>

<br><br>

<button class="btn">

Submit Booking Request

</button>

</form>

</section>

<section class="table-section">

<h2>Booking Summary</h2>

<table>

<tr>

<td>Provider</td>

<td>David Wilson</td>

</tr>

<tr>

<td>Hourly Rate</td>

<td>₦8,000</td>

</tr>

<tr>

<td>Estimated Hours</td>

<td>5 Hours</td>

</tr>

<tr>

<td>Estimated Cost</td>

<td><strong>₦40,000</strong></td>

</tr>

</table>

</section>

</main>

</div>

<script src="js/main.js"></script>

</body>

</html>