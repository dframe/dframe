<?php return array (
  0 => 
  array (
    'page/:page' => 
    array (
      0 => 'page/[page]/',
      1 => 'task=page&action=[page]',
    ),
  ),
  1 => 
  array (
    'error/:code' => 
    array (
      0 => 'error/[code]/',
      1 => 'task=page&action=error&type=[code]',
      'args' => 
      array (
        'code' => '[code]',
      ),
    ),
  ),
  2 => 
  array (
    'default' => 
    array (
      0 => '[task]/[action]/[params]',
      1 => 'task=[task]&action=[action]',
      'params' => '(.*)',
      '_params' => 
      array (
        0 => '[name]/[value]/',
        1 => '[name]=[value]',
      ),
    ),
  ),
);