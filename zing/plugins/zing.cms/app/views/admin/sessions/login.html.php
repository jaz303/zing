<form method='post' action='<?= $request->path() ?>' id='session-form'>
  <div class='inner'>
		
		<h1><?= \zing\cms\Attribution::name() ?> :: Sign In</h1>
		
		<label>Username:</label>
		<input type='text' name='username' />
		
		<label>Password:</label>
		<input type='password' name='password' />
		
    <input type='submit' value='Login' />
    
    <!--
		<p class='links'>
		  <input type='checkbox' name='remember_me'> Remember me |
      <a href='#'>Forgot your password?</a>
		</p>
		-->
	
	</div>
</form>