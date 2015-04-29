<?php
    require_once(__DIR__ . '/../core/init.php');
    function startPDOConnection() {
        try {
            $db_connection = new PDO(
                            Config::get('mysql/type') . ':host=' . Config::get('mysql/host') . ';dbname=' . Config::get('mysql/db_name') . ';charset=utf8',
                            Config::get('mysql/username'),
                            Config::get('mysql/password')
            );
            $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $db_connection;
        }
        catch (PDOException $e) {
            Logger::log ('error', 'Database Connection Error: ' . $e->getMessage());
            return false;
        }
    }
