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
Enter an output path, such as `$Sourcepath$`
Disable the checkbox "Auto-save edited files to trigger the watcher" – in my experience this is really awkward.  
Select "On error" for "Show Console".  
Click OK.  
![Configure the File Watcher](https://raw.githubusercontent.com/prolic/fpp/master/docs/img/phpstorm_3.png)

Try it out! Have fun!
