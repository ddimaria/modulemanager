## Module Manager for Laravel 4

Modules is a module management for Laravel Version 4.

----------------
## Changelog 1.1.0

* rewritten package

#### Why the package was rewritten

There was an error in the previous version that I have not thought of. And it was a matter of great and lowercase the folder and file names. In Laravel 3 it was still whether the namespace is case insensitive. But at 4 Laravel not.

Now, the package is designed to

#### What does this mean exactly

The namespace represents the folder and file renaming. In other words, if you have the following namespace:

`namespace Modules\Dashboard\Controller\Admin`

then the path to folder must also be written as:

`root/levare/Modules/Dashboard/Controller/Admin`

for files the same:

`use Modules\Dashboard\Controller\Admin\Start`

and the path to file:

`root/levare/Modules/Dashboard/Controller/Admin/Start.php`


##### Please read the installation part ####
-----------

## Changelog 1.0.2

* added registerFolders() Method
* added permission check to checkModules() Method
* remove getPath() Method

#### Changes in detail

**registerFolder()**

This method registers all needed folders may be added to the classes of namespaces Laravel. In detail, the following class:

* View
* Config
* Translation

This allow you to use package style handling for your modules e.g Config::get('module::config.item');

**permission Check**

The permission check checks whether the modules folder are writable. If this is the case, all necessary files are copied, on the other hand, the aplication is stopped and an error. (Only in debug mode)

**getPath()**

The automatic folder registration make this method deprecated.


----------------


#### Installation

* <a href="https://packagist.org/packages/levare/modules">Modules on Packagist</a>
* <a href="https://github.com/LevareCMS/modulemanager">Modules on Github</a>

To get the latest Version of Modules simply require it in your `composer.json` file.

```
"levare\modules": "1.1.*"
```

After that, you'll need to run `composer install` to download the latest Version and updating the autoloader.

Once Modules is installed, you need to register the ServicProvider with the application. Open up `app/config/app.php` and search the `providers` key.

```
'providers' => array(
	// your other Providers
	'Levare\Modules\ModulesServiceProvider'
)
```

Modules also ships with a facade which help you to manage your Modules. You can register the facade in the `alias` key of your `app/config/app.php` file.

```
'aliases' => array(
	// your other aliases
	'Module' => 'Levare\Modules\Facades\Modules'
)
```

Last bot not least. Add the Namespace to your `composer.json` file. Search on this file the `autoload` key.

```
"autoload": {
	// Other Stuff for Autoload
	
	// If PSR-0 not exists then create it
	"psr-0": {
		"Modules": "levare/"
	}
}
``` 
Run `composer dump-autoload` to register the new Namespace.

## How use

There is a simple way to use this Package. Create a new folder named `modules` in your root directory. That's it!
Now you can create a module in a simple way. Add a new Folder to `modules` and request your Site. The Module Manager does everything else for you. It create all needed files and register the Module.

You can now add specific Folders like `Controllers`, `Models`, `views`, and so on.

#### module.json File
This file contains all the information from your module. You can autoload all files you need by adding the filename/path to the `autoloader` key.

```
"autoloader": [
	"routes.php",
	"helpers.php",
	"folder/file.php"
]
```

You can load whatever you want from your Module..

## Important

The Namespace for any Module is `Modules\ModuleNameE\FolderName`

Check if your modules folder and all under it have, `chmod 775`!

## Forgot something?
Write an email or create a issue.
