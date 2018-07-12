<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] 					= "UserController/login";
$route['add-po'] 					= "UserController/addPo";
$route['get-clients'] 					= "UserController/getClients";
$route['get-client/(:any)'] 					= "UserController/getClient/$1";
$route['get-purchase-orders/(:any)/(:any)/(:any)'] 					= "UserController/getPurchaseOrdes/$1/$2/$3";
$route['get-purchase-orders/(:any)/(:any)/(:any)/(:any)'] 					= "UserController/getPurchaseOrdes/$1/$2/$3/$4";
$route['purchase-order-details/(:any)'] 					= "UserController/PurchaseOrderDetails/$1";
$route['change-po-status/(:any)/(:any)'] 					= "UserController/ChangePoStatus/$1/$2";
$route['upload-image'] 					= "UserController/uploadImage";
$route['upload-file'] 					= "UserController/uploadFile";
$route['update-client'] 					= "UserController/updateClient";
$route['client-details/(:any)'] 					= "UserController/clientDetails/$1";
$route['dashboard-info/(:any)/(:any)'] 					= "UserController/dashboardInfo/$1/$2";

$route['pomsginsert'] = "ClientController/PoMsgInsert";
$route['get-pomsg/(:any)'] = "ClientController/getPomsg/$1";

$route['delete/(:any)/(:any)'] 					= "ClientController/delete/$1/$2";
