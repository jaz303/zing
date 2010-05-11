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
  * Database migrations (yes, even for plugins) _(incomplete)_
  * Transparent request parameter handling for date, money and file types _(incomplete)_
  * Plugin API (inc. centralised online repository) _(incomplete)_
  * Raw PHP templating with cascaded static helpers _(complete)_
  * Full console integration (tasks, generators, console) _(generators incomplete)_
  * Task management via phake _(complete)_
  * Class autoloading management via superload _(complete)_
  * Multi-environment aware _(complete)_
  * Engineered from the ground-up for PHP 5.3
  
Quickstart
----------

  * `$ git clone ...`
  * `$ cd zing`
  * `$ git submodule init && git submodule update`
  
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

Why another PHP framework?
--------------------------

Zing! basically grew organically from a bunch of projects over the years. Recently I got the opportunity to refactor a large legacy client project so I thought I'd extract the common bits, tidy it up and release it.