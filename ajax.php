<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

require_once __DIR__.'/foloosi.php';

$context = Context::getContext();

// Instance of module class
$module = new Foloosi();

switch (Tools::getValue('action'))
{
    case 'createOrder':
        echo $module->createOrder();
        break;
    default:
        die('error');
}
