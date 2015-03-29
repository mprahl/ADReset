<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Account Settings';
	require_once(__DIR__ . '/../templates/header.php');
?>
<body>
<!-- Navigation Menu Starts -->
<?php
	require_once(__DIR__ . '/../templates/admin_navigation.php'); 
?>
<!-- Navigation Menu Ends -->
<!-- Content Starts -->
<div class="container" id="mainContentBody">
	<h2 class="topHeader">Logged in</h2>
	<p>Welcome <?php echo $_SESSION['user_name']; ?>. You are now logged in!</p>
	<p>
		Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum gravida sollicitudin diam ut tincidunt. Vestibulum sit amet magna auctor, suscipit est consectetur, scelerisque sapien. Pellentesque malesuada, leo in pulvinar auctor, magna justo malesuada ipsum, vel iaculis quam tortor vitae justo. Quisque consectetur gravida risus, sed efficitur turpis venenatis a. Suspendisse lobortis laoreet tristique. Aliquam sodales iaculis magna, a tristique massa pharetra ac. Cras tristique nisl aliquet, suscipit neque id, elementum risus. Nullam rutrum felis sed varius ornare. Aenean sit amet ante ultricies leo condimentum hendrerit. Donec porta ipsum ut scelerisque semper.
	</p>
	<p>
		Integer maximus quam in mi imperdiet, quis convallis lectus luctus. Sed cursus diam mauris, at hendrerit est elementum ac. Nunc luctus, massa vel consectetur tristique, risus nunc viverra enim, nec commodo sem orci nec ante. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse egestas velit mi, pulvinar vulputate dolor semper eu. Aliquam sollicitudin dolor et nulla aliquam, id egestas quam porta. Proin posuere sit amet metus nec tempor. Praesent nec nunc mi. Duis vestibulum maximus purus, eu venenatis metus rhoncus accumsan. Sed eget ligula et purus elementum convallis in ac erat. Mauris eget ultrices elit. Donec eu finibus ex. Sed quis mi finibus, hendrerit tellus sit amet, finibus erat. Nunc ac convallis ante, a scelerisque quam. Sed mollis interdum nibh nec lobortis. Etiam euismod sapien nec arcu maximus, non interdum nisl suscipit.
	</p>
</div>
<!-- Content Ends -->
</body>
</html>