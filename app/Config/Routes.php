<?php

use CodeIgniter\Router\RouteCollection;

$routes->get('/', 'Home::index');
$routes->get('sobre', 'Home::sobre');
$routes->get('login', 'AuthController::login');
$routes->get('registro', 'AuthController::login');
$routes->get('dashboard', 'DashboardController::index');
$routes->get('perfil', 'DashboardController::perfil');
$routes->get('ofertas/crear', 'DashboardController::crearOferta');

$routes->group('api/v1', static function (RouteCollection $routes): void {
	$routes->post('auth/register', 'Api\\AuthController::register');
	$routes->post('auth/login', 'Api\\AuthController::login');
	$routes->post('auth/logout', 'Api\\AuthController::logout');
	$routes->get('auth/me', 'Api\\AuthController::me');

	$routes->get('profile', 'Api\\ProfileController::show');
	$routes->put('profile', 'Api\\ProfileController::update');
	$routes->put('profile/avatar', 'Api\\ProfileController::avatar');
	$routes->put('profile/password', 'Api\\ProfileController::changePassword');
	$routes->put('profile/grades-document', 'Api\\ProfileController::uploadGradesDocument');
	$routes->put('profile/verification-document', 'Api\\ProfileController::uploadVerificationDocument');
	$routes->post('profile/request-university-change', 'Api\\ProfileController::requestUniversityChange');
	$routes->delete('profile', 'Api\\ProfileController::deleteAccount');
	$routes->get('profiles/(:num)', 'Api\\ProfileController::publicProfile/$1');
	$routes->get('universities', 'Api\\ProfileController::universities');

	$routes->get('university/students', 'Api\\UniversityController::students');
	$routes->put('university/students/(:num)', 'Api\\UniversityController::updateStudent/$1');

	$routes->get('convenios', 'Api\\ConveniosController::index');
	$routes->post('convenios', 'Api\\ConveniosController::create');
	$routes->put('convenios/(:num)/status', 'Api\\ConveniosController::updateStatus/$1');

	$routes->get('admin/pending', 'Api\\AdminController::pending');
	$routes->put('admin/coordinators/(:num)', 'Api\\AdminController::reviewCoordinator/$1');
	$routes->put('admin/companies/(:num)/verify', 'Api\\AdminController::verifyCompany/$1');
	$routes->put('admin/companies/(:num)/plan', 'Api\\AdminController::updateCompanyPlan/$1');

	$routes->get('conversations', 'Api\\MessagingController::index');
	$routes->post('conversations', 'Api\\MessagingController::create');
	$routes->get('conversations/(:segment)/messages', 'Api\\MessagingController::messages/$1');
	$routes->post('conversations/(:segment)/messages', 'Api\\MessagingController::send/$1');
	$routes->put('conversations/(:segment)/status', 'Api\\MessagingController::status/$1');
	$routes->delete('conversations/(:segment)', 'Api\\MessagingController::delete/$1');

	$routes->get('notifications', 'Api\\NotificationsController::index');
	$routes->put('notifications/read-all', 'Api\\NotificationsController::readAll');
	$routes->put('notifications/(:num)/read', 'Api\\NotificationsController::read/$1');
	$routes->delete('notifications', 'Api\\NotificationsController::clear');
	$routes->delete('notifications/(:num)', 'Api\\NotificationsController::delete/$1');

	$routes->get('offers', 'Api\\OffersController::index');
	$routes->get('offers/(:num)/applicants', 'Api\\OffersController::applicants/$1');
	$routes->get('applications', 'Api\\OffersController::myApplications');
	$routes->put('applications/(:num)/status', 'Api\\OffersController::updateApplicationStatus/$1');
	$routes->get('organisms', 'Api\\OffersController::organisms');
	$routes->post('offers', 'Api\\OffersController::create');
	$routes->put('offers/(:num)', 'Api\\OffersController::update/$1');
	$routes->delete('offers/(:num)', 'Api\\OffersController::delete/$1');
	$routes->post('offers/(:num)/save', 'Api\\OffersController::toggleSaved/$1');
	$routes->post('offers/(:num)/apply', 'Api\\OffersController::apply/$1');
	$routes->post('offers/(:num)/select-recommended', 'Api\\OffersController::selectRecommended/$1');
});
