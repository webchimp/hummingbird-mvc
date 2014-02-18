Hummingbird MVC
===============

MVC layer for Hummingbird Lite.

**Current version:** 1.3

### Requirements ###

 - Hummingbird Lite 2.x

### Installation ###

Copy `plugins/mvc` into your Hummingbird Lite `/plugins` folder.

Add the 'mvc' plugin to the `/include/config.inc.php` file.

That's all.

### Configuration ###

The plugin extends the default router, so make sure your Hummingbird Lite can resolve its default routes properly (check your `.htaccess` and `config.inc.php` files).

### Usage ###

Well, this is not for script kiddies, you must have a strong OOP background and some decent PHP knowledge.

The plugin sits atop of the default router, so it can dispatch requests to the appropiate controllers while allowing Hummigbird to work with static pages if there are no controllers that match the route.

You create your controllers, models and views by extending the Controller, Model and View classes. Pay special attention to the abstract methods that you must override on your subclasses.

Check the included controller samples to know more about the naming convention: it only applies to controllers, but the general rule is, if your route is '/cats', your class will be 'CatsController'.

Since version 1.3 '/my-fancy-route' => 'MyFancyRouteController' controllers are supported: dashes will be camelCased for actions and UpperCased for controllers.

Also check the View class, it has some helper methods for rendering templates and including partials. All the code is fully documented as usual, so take a look the source when in doubt.

There are two utility classes, the Request and the Response, they provide some helper methods, so be sure to check 'em out too.

And lastly, the Model class, that's just an empty abstract class but it will be beefed up with some ORM functionality on the future.

### Troubleshooting ###

This is a bare-bones plugin, if there's something wrong, check your code first, then submit an issue (see below).

Check your routing settings (at `.htaccess` and `config.inc.php`)

If you find an odd error, feel free to create an issue with the problem description (a FULL test-case would be great!).

### Credits ###

**Lead coder:** biohzrdmx [&lt;github.com/biohzrdmx&gt;](http://github.com/biohzrdmx)

## License ##
Copyright &copy; 2014 biohzrdmx

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
