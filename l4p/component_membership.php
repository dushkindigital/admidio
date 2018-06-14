<?php

require_once (__DIR__ . '/../adm_program/system/common.php');

require_once (__DIR__ . '/engine/bootstrap.php');

?><!doctype html>
<html lang="en">
	<head>
		<title>Membership</title>
		
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		
		<link rel="stylesheet" href="<?php echo \ADMIDIO_URL . '/l4p/asset/css/component_membership.css'; ?>" />
	</head>
	<body>
		
		<form action="<?php echo \ADMIDIO_URL . '/l4p/handle_membership.php'; ?>" method="post">
			<h1>Sign Up</h1>
			<!-- -->
			<div class="box clearfix first_name">
				<label>First name</label>
				<input type="text" name="first_name" value="" placeholder="first name" minlength="1" maxlength="100" required />
			</div>
			<!-- -->
			<div  class="box clearfix last_name">
				<label>Last name</label>
				<input type="text" name="last_name" value="" placeholder="last name" minlength="1" maxlength="100" required />
			</div>
			<!-- -->
			<div  class="box clearfix email">
				<label>Email</label>
				<input type="email" name="email" value="" placeholder="email" minlength="1" maxlength="100" required />
				<br />
				<p>
					If you are applying as a Member, you must register with your school-issued cantab.net email ID.
					<a href="https://www.alumni.cam.ac.uk/benefits/email-for-life" target="_blank">more...</a>
				</p>
			</div>
			<!-- -->
			<div  class="box clearfix membership">
				<label>Membership</label>
				<select name="membership" required>
					<option disabled></option>
					<option value="member">member</option>
					<option value="associate">associate</option>
				</select>
				<br />
				<p>
					<a href="https://www.cantabnyc.org/p/membership.html" target="_blank">membership types</a>
				</p>
			</div>
			<!-- -->
			<div  class="box clearfix school">
				<label>School</label>
				<select name="school" required>
					<option disabled></option>
					<?php foreach (\cantabnyc\get_configs()->colleges as $value) { ?>
						<option value="<?php echo \htmlentities($value); ?>"><?php echo \htmlentities($value); ?></option>
					<?php } ?>
				</select>
			</div>
			<!-- -->
			<div  class="box clearfix affiliation">
				<label>Affiliation</label>
				<textarea name="affiliation" value="" placeholder="affiliation" minlength="1" maxlength="250" required></textarea>
			</div>
			<!-- -->
			<div  class="box clearfix matriculation">
				<label>Matriculation</label>
				<input type="text" name="matriculation" value="" placeholder="affiliation" minlength="4" maxlength="4" required />
			</div>
			<!-- -->
			<div  class="box clearfix message">
				<label>Message to membership committee</label>
				<textarea name="message" value="" placeholder="message to membership committee" minlength="0" maxlength="4000"></textarea>
			</div>
			
			<!-- -->
			<div  class="box clearfix">
				<input type="submit" name="Send" value="Send" />
			</div>
		</form>
	</body>
</html>
