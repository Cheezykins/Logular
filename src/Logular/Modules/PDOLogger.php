<?php

/* 
 * The MIT License
 *
 * Copyright 2015 Chris Stretton <cstretton@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Logular\Modules;

use Logular as Logular;

class PDOLogger extends Logular\Logger
{

    private $pdo;
    private $insert;

    public static function getStatement($statement)
    {
        ob_start();
        $statement->debugDumpParams();
        return ob_get_clean();
    }

    public function __construct($dsn = false, $username = "", $password = "", $attributes = [], $pdo = false)
    {

        if (!$dsn && !$pdo) {
            throw new \Exception("You must provide a DSN or PDO object");
        }

        if ($dsn) {
            $pdo = new \PDO($dsn, $username, $password, $attributes);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        $this->pdo = $pdo;
        $this->insert = $pdo->prepare("INSERT INTO logging (level, message, pid) VALUES (:level, :message, :pid)");

        parent::__construct();
    }

    public function entry($message, $level = Logular\LogLevel::INFO, $variable = null)
    {
        if ($level < $this->minLogLevel) {
            return;
        }

        $params = [
            ':level' => LogLevel::getText($level),
            ':message' => $message,
            ':pid' => getmypid()
        ];

        try {
            $this->insert->execute($params);
        } catch (Exception $e) {
            
        }
    }
}
