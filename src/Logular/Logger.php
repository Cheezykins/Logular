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

namespace Logular;

abstract class Logger implements LoggerInterface
{

    protected $minLogLevel;

    public function __construct()
    {
        $this->setLogLevel(LogLevel::INFO);
    }

    public function entry($message, $level = LogLevel::INFO, $variable = null)
    {
        if ($level < $this->minLogLevel) {
            throw new Exception('Log entry is below minimum level');
        }

        $timestamp = $this->getTimestamp();
        $level = LogLevel::getText($level);

        $message = "[{$level}] [{$timestamp}] {$message}";

        if (!is_null($variable) && $this->minLogLevel == LogLevel::DEBUG) {
            ob_start();
            var_dump($variable);
            $result = ob_get_clean();
            $traces = explode("\n", $result);
            $debugLevel = LogLevel::getText(LogLevel::DEBUG);
            $message .= PHP_EOL;
            foreach ($traces as $trace) {
                $trace = str_replace("\r", "", $trace);
                $message .= "[{$debugLevel}] [{$timestamp}] {$trace}" . PHP_EOL;
            }
        }

        return $message;
    }

    public function setLogLevel($level)
    {
        $this->minLogLevel = $level;
    }

    protected function getTimestamp()
    {
        return date('Y-m-d H:i:s');
    }
}
