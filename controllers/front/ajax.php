<?php
require_once(__DIR__.'/config/config.inc.php');
require_once(__DIR__.'/init.php');
require_once(__DIR__.'/foloosi.php');

$context = Context::getContext();

// Instance of module class
$module = new MyModule();

switch (Tools::getValue('action'))
{
    case 'createOrder':
        echo $module->createOrder();
        break;
    default:
        die('error');
}
