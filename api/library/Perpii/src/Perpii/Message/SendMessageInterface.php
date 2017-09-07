<?php
namespace Perpii\Message {

    /**
     * Interface for sending message to target
     *
     */
    interface SendMessageInterface {

        /**
         * Send message to the target
         *
         * @return void
         */
        public function send();
    }
}