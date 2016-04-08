<?php
/* Smarty version 3.1.29, created on 2016-04-04 16:52:08
  from "D:\xampp\htdocs\dframe\View\templates\index.html.php" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.29',
  'unifunc' => 'content_57027f98741297_76779215',
  'file_dependency' => 
  array (
    '4a00fc6d4a62d82190240805eff6f3b2a826659d' => 
    array (
      0 => 'D:\\xampp\\htdocs\\dframe\\View\\templates\\index.html.php',
      1 => 1459781527,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:header.html.php' => 1,
    'file:footer.html.php' => 1,
  ),
),false)) {
function content_57027f98741297_76779215 ($_smarty_tpl) {
$_smarty_tpl->smarty->ext->_subtemplate->render($_smarty_tpl, "file:header.html.php", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>


Przykładowa strona stworzona na Frameworku Dframe  | <b>Plik:</b> View/templates/index.html.php <br>

<br><br>
Routing: <br>
<?php echo $_smarty_tpl->tpl_vars['router']->value->makeurl('page/index');?>


<?php $_smarty_tpl->smarty->ext->_subtemplate->render($_smarty_tpl, "file:footer.html.php", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
