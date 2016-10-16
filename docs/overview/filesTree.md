# How to create correct Tree folders and files

If you have many many methods in one controller ex. articles you should build folder according on the modules

You do not have to have big applicaiton to create tree. Example in articles modules you should have folder tree looks like

```txt
-app
    -Controller
        -articles // Controller folder
            -content.php
            -category.php
        -Controller.php
        
    -Model
        -articles // Model folder
            -content.php
            -category.php
            -commnets.php

```
For comments you don't have to Controller becouse comments will be under article so you should load
```php
$this->loadModel('articles/commnets');
```
in your articles/content

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
