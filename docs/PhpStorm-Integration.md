1) First you need to install the File Watchers plugin, if you haven't already done so.  
Click "File" -> "Settings" and search for "File Watchers".  
Under the plugins section, you can add the checkmark next to "File Watchers" plugin. Don't forget to restart your IDE.

![Enable the Plugin](https://raw.githubusercontent.com/prolic/fpp/master/docs/img/phpstorm_1.png)

2) Now we need to create fpp file types.  
Click "File" -> "Settings" -> "Editor" -> "File Types".  
Add a new file type, click at the "Recognized File Types" section on the "+ icon" in the upper right corner.  
Put the name to "FPP" and description to "FPP files".  
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
In the "Registered Patterns" section, click on the "+ icon" and add "*.fpp", then click OK.  

![Add FPP File Type](https://raw.githubusercontent.com/prolic/fpp/master/docs/img/phpstorm_2.png)

3) Configure the File Watcher.  
Click "File" -> "Settings" -> "Tools" -> "File Watchers".  
Click on the "+ icon" in the upper right corner and choose custom template.  
Put "FPP" as name.  
Select FPP as the File type.  
Select your Scope, if you don't have one, create it. Set it to "src" for example.  
Set "Programm" to "php".  
Set this as "Arguments": "$ProjectFileDir$/vendor/prolic/fpp/bin/fpp.php $FilePath$"  
Disable the checkbox "Auto-save edited files to trigger the watcher" - as by my experience this is really awkward.  
Select "On error" on "Show Console".  
Click OK. Try it out! Have fun!  

![Configure the File Watcher](https://raw.githubusercontent.com/prolic/fpp/master/docs/img/phpstorm_3.png)
