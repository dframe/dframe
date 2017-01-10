<?php
return array(
	'https' => false,
    'NAME_CONTROLLER' => 'page',
    'NAME_MODEL' => 'index',
    '404' => array('404', 'task=page&action=404'),
    'publicWeb' => '',
    'assetsPath' => '',
    'page/view' => array(
        'documents/[pageId]', 
        'task=page&action=show&pageId=[pageId]'
    ),
    'default' => array(
        '[task]/[action]/[params]',
        'task=[task]&action=[action]',
        'params' => '(.*)',
        '_params' => array(
            '[name]/[value]/', 
            '[name]=[value]'
            )
        ),       
);