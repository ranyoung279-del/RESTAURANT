<?php
namespace App;

final class Db {
    private static ?\mysqli $conn = null;

    public static function conn(): \mysqli {
        if (!self::$conn) {
            self::$conn = new \mysqli('localhost', 'root', '', 'restaurant_db');
            if (self::$conn->connect_error) {
                die('Kết nối thất bại: ' . self::$conn->connect_error);
            }
            self::$conn->set_charset('utf8mb4');
        }
        return self::$conn;
    }
}
