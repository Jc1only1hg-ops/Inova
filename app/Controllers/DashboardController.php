<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index(): string
    {
        return view('dashboard/index');
    }

    public function perfil(): string
    {
        return view('dashboard/perfil');
    }

    public function crearOferta(): string
    {
        return view('dashboard/crear_oferta');
    }
}
