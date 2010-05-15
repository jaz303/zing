Zing! - a barebones PHP 5.3 VC framework
============================================

&copy; 2010 Jason Frame [ [jason@onehackoranother.com](mailto:jason@onehackoranother.com) / [@jaz303](http://twitter.com/jaz303) ]  
Released under the MIT License.

Zing! is a "VC, BYOM" framework. That's View-Controller, Bring Your Own Model.

It's basically a flexible router, some HTTP classes, database migrations and a controller specification. There's a lean templating layer in there too but feel free to ignore it.

Core values: efficiency, flexibility, playing nice with others, getting the f**k out of your way  
Things we like: duck-typing  
Things we don't like: interfaces

Any resemblance to Ruby on Rails is entirely deliberate.

Features (complete and planned)
-------------------------------

  * Fast & efficient, although I have no data to back up this claim
  * Rapid Rails-style router (matcher code is compiled to PHP) _(almost complete)_
  * Database access with **no ORM** _(complete)_
  * Database migrations (yes, even for plugins) _(complete)_
  * Transparent request parameter handling for date, money and file types _(complete)_
  * Plugin API (inc. centralised online repository) _(partial)_
  * Raw PHP templating with cascaded static helpers _(complete)_
  * Full console integration (tasks, generators, console) _(generators incomplete)_
  * Task management via phake _(complete)_
  * Class autoloading management via superload _(complete)_
  * Multi-environment aware _(complete)_
  * Engineered from the ground-up for PHP 5.3
  
Quickstart
----------

Clone the repo and grab the submodules:

  * `$ git clone git://github.com/jaz303/zing.git`
  * `$ cd zing`
  * `$ git submodule init && git submodule update`
  
Next step is to get a webserver on the go. If you've got the
[lighty gem](http://github.com/jaz303/lighty) installed & configured, just invoke
`lighty` from Zing!'s root directory and you're up and running. Otherwise you'll need
to setup an Apache vhost. Something like this should do the trick, 
substituting the correct `$ZING_ROOT`:

    <VirtualHost *:4000>
        DocumentRoot $ZING_ROOT/public
        <Directory $ZING_ROOT/public>
            Order allow,deny
            Allow from all
        </Directory>
        RewriteEngine On
        RewriteCond $ZING_ROOT/public/%{REQUEST_FILENAME} !-f
        RewriteCond $ZING_ROOT/public/%{REQUEST_FILENAME} !-d
        RewriteRule ^.*$ /__dispatch.php [QSA,L]
    </VirtualHost>
    
Ensure `$ZING_ROOT/tmp` is writable by your webserver.

Hit `http://localhost:4000/test` to view a seriously underwhelming test page and maybe wonder
why the hell you just wasted 15 minutes on this crap.

Fast & Efficient
----------------

The request path is very short:

  1. `__dispatch.php` receives request (1 file)
  2. `boot.php` is loaded to perform minimal framework bootstrapping (1 file)
  3. Support functions loaded (1 file)
  3. Main environment is loaded (1 file)
  4. `zing\http\Request` object is instantiated (1 file)
  5. Compiled routing function resolves request (1 file)
  6. Controller is instantiated (min. 2 files - base controller, concrete controller)
  7. Action is invoked and `zing\http\Response` is returned (0 files)
  7. View is rendered (min. 2 files - template class, page template)
  
A simple request hits ~10 files. Not that that means much in the world of in-memory bytecode caching, but it's good to be efficient.

Transparent Date/Money/File Handling
------------------------------------

Given that people say money and time are two of the most important things in the world it's amazing
so many programming environments are crap at dealing with them.

Zing! attempts to treat date and money values as first-class citizens, supporting a prefix notation
for form inputs.

Submitting the following fields will create a single `Date` object and store it in `person[date_of_birth]`:

    <input type='hidden' name='person[@date_of_birth][year]' value='1980' />
    <input type='hidden' name='person[@date_of_birth][month]' value='12' />
    <input type='hidden' name='person[@date_of_birth][day]' value='12' />
    
Add in `hours`, `minutes` and `second` keys and you'll get a `Date_Time` instance instead.

The `$` prefix is used for currency values. This code stores an instance of `Money` in `person[salary]`:

    <input type='hidden' name='person[$salary][units]' value='10000000' />
    <input type='hidden' name='person[$salary][currency]' value='GBP' />
    
Also, strings can be used instead of arrays:

    <input type='hidden' name='person[@date_of_birth]' value='1980-12-12' />
    <input type='hidden' name='person[$salary]' value='10000000GBP' />
    
Finally, uploaded files are converted to instances of either `UploadedFile` or `UploadedFileError`,
based on the success status of the upload, then moved from `$_FILES` to their corresponding
location in `$_POST`.

    // UploadedFile and UploadedFileError each implement the ok() method for
    // detecting success
    if ($_POST['my_file']->ok()) { // file upload was successful
        $_POST['my_file]->move('/foo/bar');
    } else { // file upload failed
        // display error
    }

(This not a security risk because there is no way for a user to submit an object instance
into `$_POST`)

The overall result: user submitted data can be dealt with in a unified manner with no need to deal with
various, possibly oddly-structured, superglobal arrays.

Cascaded Static Helpers
-----------------------

It's possible to register helpers implementing overlapping method names, with the most recently registered helpers taking precedence:

    class ApplicationHelper
    {
        public static function page_title() { return "Generic Title"; }
    }
    
    class PageHelper
    {
        public static function page_title() { return "Specific Title"; }
    }
    
    $template->add_helper('ApplicationHelper');
    $template->add_helper('PageHelper');
    
In your template, you just do:

    <?= page_title(); ?>
  
Zing!'s view system will use a bit of ad-hoc static analysis to substitute a call to the correct helper, and cache the compiled template for efficiency.
  
Autoload Management
-------------------

Zing! uses Superload to trawl all defined class paths, detect all declared interfaces/classes, and compile a single autoload function. You don't need to use <code>require()</code>, ever.
  
What, No Model?
---------------

Doing ORM in PHP is full of so many trade-offs - manual labour vs. runtime data dictionary vs. runtime reflection vs. offline code generation vs. runtime code generation. And sometimes an ORM is just overkill. Or maybe you're using NoSQL. So it really isn't for me to decide what you should use.

FWIW, my take on the subject is spitfire, which will eventually be packaged as a Zing! plugin, but please use whatever makes you happy.

Migrations
----------

Database migrations allow changes to be made to your database schema in an automated, repeatable fashion through operations defined in PHP code. For anyone familiar with Ruby on Rails, the syntax will be immediately familiar:

    <?php
    class CreateUsers extends zing\db\Migration
    {
        public function up() {
            $this->create_table('users', function($t) {
                $t->string('username');
                $t->string('email');
                $t->datetime('date_of_birth');
                $t->text('bio', array('mysql.size' => 'long'));
            });
        
            $this->add_index('users', 'username', array('unique' => true));
            $this->add_index('users', 'email', array('unique' => true));
        }
        
        public function down() {
            $this->drop_table('users');
        }
    }
    ?>

Why another PHP framework?
--------------------------

Zing! basically grew organically from a bunch of projects over the years. Recently I got the opportunity to refactor a large legacy client project so I thought I'd extract the common bits, tidy it up and release it.