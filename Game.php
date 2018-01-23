<?php
    class Component
    {
        private $a;
        private $b;

        public function __construct( int $a,int $b )
        {
            $this->a = $a;
            $this->b = $b;
        }

        public function Add() : int
        {
            return $this->a + $this->b;
        }

        public function Multiply() : int
        {
            return $this->a * $this->b;
        }
    }
?>