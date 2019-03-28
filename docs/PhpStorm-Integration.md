Do the following after installing FPP into your project.

1) First you'll need to install the [File Watchers plugin](https://www.jetbrains.com/help/phpstorm/settings-tools-file-watchers.html), if you haven't already done so.  
To install the plugin, go to the PhpStorm Settings/Preferences -> "Plugins" -> and search for "File Watchers". Check off the plugin to install it. You'll need to restart PhpStorm.  
![Enable the Plugin](https://raw.githubusercontent.com/prolic/fpp/master/docs/img/phpstorm_1.png)

2) Now we need to create an FPP file type.  
In the PhpStorm Settings/Preferences -> "Editor" -> "File Types".  
Add a new file type, click at the "Recognized File Types" section on the "+" icon.  
Put the name to "FPP" and description to "FPP files" (or whatever you'd like).  
Add `//` as Line comment, `/*` as Block comment start and `*/` as Block comment end.  
Set the keywords to:  

```
=>
_
data
deriving
namespace
where
with
{
|
}
```

Then click on OK.  
In the "Registered Patterns" section (with FPP selected above), click on the "+" icon and add `*.fpp`, then click OK.  
![Add FPP File Type](https://raw.githubusercontent.com/prolic/fpp/master/docs/img/phpstorm_2.png)

3) Configure the File Watcher.  
In the PhpStorm Settings/Preferences -> "Tools" -> "File Watchers".  
Click on the "+" icon and choose "<custom>" for the template.  
Put "FPP" as name.  
Select FPP as the File type.  
Select your Scope, if you don't have one, create it. Set it to `src` for example.  
Set "Programm" to `php`.  
Set this as "Arguments": `$ProjectFileDir$/vendor/prolic/fpp/bin/fpp.php $FilePath$`  
Enter an output path, such as `$Sourcepath$` (PHPStorm will refresh this path to look for changes to the generated classes)
Disable the checkbox "Auto-save edited files to trigger the watcher" – in my experience this is really awkward.  
Select "On error" for "Show Console".  
Click OK.  
![Configure the File Watcher](https://raw.githubusercontent.com/prolic/fpp/master/docs/img/phpstorm_3.png)

Try it out! Have fun!

----

# Advanced Options

## If you have `.fpp` files that reference classes defined in other `.fpp` files

The default "Arguments" setting works great if you only have one `.fpp` file *or* every `.fpp` file only references classes defined in that same file.

If you have multiple `.fpp` files and one or more of them reference a class defined in another `.fpp`, you will get an error. To combat this, you can specify a directory as a part of the "Arguments" instead of the exact file that changed.

For example, consider the following files that define value objects, events and commands for Accounts, Forms, Subscriptions and Users.

```
config
└── model
    ├── account.fpp
    ├── form.fpp
    ├── subscription.fpp
    └── user.fpp
```

In this case, the commands and events for Form (from `form.fpp`) and Subscription (from `subscription.fpp`) reference `AccountId` (from `account.fpp`).

Changes to `form.fpp` would trigger `$ProjectFileDir$/vendor/bin/fpp $ProjectFileDir$/config/model/form.fpp`. This will cause an error as `form.fpp` references `AccountId` which is not itself defined in `form.fpp`. However, if we change the "Arguments" to the following, everything works:

> $ProjectFileDir$/vendor/bin/fpp $ProjectFileDir$/config/model

This is because when `form.fpp` changes, all `.fpp` files in the directory are rebuilt together by `fpp`. This means `form.fpp` and `subscription.fpp` can safely find `AccountId` that is defined in `account.fpp`.
