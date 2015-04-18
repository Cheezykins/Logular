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

class FileLogger extends Logular\Logger
{

    const DEFAULT_LOG_PATH = "log.log";
    const APPEND = true;
    const TRUNCATE = false;

    private $logHandle;
    private $logPath;

    public function __construct($logPath = false, $append = self::TRUNCATE)
    {
        if (!$logPath) {
            $this->logPath = self::DEFAULT_LOG_PATH;
        } else {
            $this->logPath = $logPath;
        }

        $openMode = 'a';

        if (!$append) {
            $this->rotateLog();
            $openMode = 'w';
        }

        $handle = @fopen($this->logPath, $openMode);

        if (!$handle) {
            throw new Exception("Unable to open log path for writing: {$this->logPath}");
        }

        $this->logHandle = $handle;

        parent::__construct();
    }

    private function rotateLog()
    {
        if (file_exists($this->logPath)) {
            $logNumber = 1;

            while (file_exists($this->logPath . '.' . $logNumber)) {
                $logNumber++;
            }

            if (!@rename($this->logPath, $this->logPath . '.' . $logNumber)) {
                throw new Exception("Unable to rotate log file: {$this->logPath} to {$this->logPath}.{$logNumber}");
            }
        }
    }

    public function entry($message, $level = Logular\LogLevel::INFO, $variable = null)
    {

        try {
            $message = parent::entry($message, $level, $variable);
        } catch (Exception $e) {
            return;
        }

        if (!$this->logHandle) {
            throw new Exception('Cannot write to log file, log is not open');
        }

        fwrite($this->logHandle, $message . PHP_EOL);

        return $message;
    }

    public function __destruct()
    {
        if ($this->logHandle) {
            fclose($this->logHandle);
        }
    }
}
