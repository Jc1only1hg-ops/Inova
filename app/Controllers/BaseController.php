<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    

    

    
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        
        
        

        
        parent::initController($request, $response, $logger);

        
        
    }

    protected function json(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }

    protected function currentUser(): ?array
    {
        $user = session()->get('user');
        return is_array($user) ? $user : null;
    }

    protected function unauthorized(): ResponseInterface
    {
        return $this->json(['ok' => false, 'error' => 'Debes iniciar sesión.'], 401);
    }
}
