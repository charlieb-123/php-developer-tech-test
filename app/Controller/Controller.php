<?php

namespace App\Controller;

abstract class Controller
{
    protected $db;
    protected $logger;

    protected function db()
    {
        if (!$this->db) {
            try {
                $this->db = new \PDO(
                    sprintf(
                        '%s:host=%s;port=%d;dbname=%s',
                        $_ENV['DB_TYPE'],
                        $_ENV['DB_HOST'],
                        $_ENV['DB_PORT'],
                        $_ENV['DB_NAME']
                    ),
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASSWORD']
                );
            } catch (\PDOException $e) {
                throw $e;
            }
        }

        return $this->db;
    }

    protected function settings()
    {
        return require __DIR__ . '../../settings.php';
    }

    /**
     * Renders a twig template with the params provided.
     *
     * @param string $view
     * @param array $params
     */
    protected function render(string $view, array $params = [])
    {

        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../resources/views');
        $twig = new \Twig\Environment($loader);

        echo $twig->render(sprintf('layouts/%s', $view), $params);
    }

    protected function logger()
    {
        $this->logger = new \Monolog\Logger('logger');
        $logFilePath = __DIR__ . '/../../Logs/log.log';
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($logFilePath, \Monolog\Logger::WARNING));
        return $this->logger;
    }
}
