<form method='post' action='<?= $request->path() ?>' id='session-form'>
	<div class='inner'>
		
		<h1>Zing! CMS :: Reset Password</h1>
		
		<label>Email:</label>
		<input type='text' name='email' />
		
		<input type='submit' value='Send Instructions' />
	
		<p class='links'><a href='#'>Return to Sign In</a></p>
	
	</div>
</form>
