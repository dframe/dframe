# How to create correct Tree folders and files

If you have many many methods in one controller ex. articles you should build folder according on the modules

You do not have to have big applicaiton to create tree. Example in articles modules you should have folder tree looks like

```txt
-app
    -Controller
        -Articles // Controller folder
            -Content.php
            -Category.php
        -Controller.php
        
    -Model
        -Articles // Model folder
            -Content.php
            -Category.php
            -Commnets.php

```
For comments you don't have to Controller becouse comments will be under article so you should load
```php
$this->loadModel('Articles/Commnets');
```
in your Articles/Content

#### Router 
will be looks this
```txt
index.php?taks=articles,content&action=index
index.php?taks=articles,category&action=index
```
or Friendly Url
```txt
articles,content/index
articles,category/index
```