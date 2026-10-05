<?php

class ApiCsrfController extends BaseController
{
    /**
     * Get a fresh CSRF token
     * This endpoint returns a new CSRF token without requiring authentication
     * The token is still tied to the session
     */
    public function token(): void
    {
        // Set JSON response header
        header('Content-Type: application/json');

        // Generate and return a fresh CSRF token
        $token = Csrf::token();

        echo json_encode([
            'success' => true,
            'token' => $token,
            'timestamp' => time()
        ]);
        exit;
    }
}
