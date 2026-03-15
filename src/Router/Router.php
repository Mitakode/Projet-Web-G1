<?php

return [

    '/' => 'HomeController@index',

    'GET /connexion' => 'AuthController@loginForm',

    'POST /connexion' => 'AuthController@loginAction',

    'GET /inscription' => 'AuthController@registerForm',

    'POST /inscription' => 'AuthController@registerAction',

    'GET /mon-compte/{id}' => 'AccountController@accountForm',

    'GET /edit-compte/{id}' => 'AccountController@editAccountForm',

    'POST /edit-compte/{id}' => 'AccountController@editAccountAction',

    'GET /fiche-profil/{id}' => 'ProfileController@profileForm',

    'GET /mon-pannel/{role}' => 'DashboardController@pannelForm',

    'POST /mon-pannel/{role}' => 'DashboardController@pannelAction',
    
    'GET /mentions-legales' => 'LegalController@index',


    ]