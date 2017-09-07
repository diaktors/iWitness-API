<?php
namespace Perpii\Premailer {

    class PremailerFactory
    {
        public function __invoke($services)
        {
            return new Premailer();
        }
    }
}
