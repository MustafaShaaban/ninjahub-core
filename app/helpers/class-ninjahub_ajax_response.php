<?php
    /**
     * @Filename: class-ninjahub_ajax_response.php
     * @Description:
     * @User: NINJA MASTER - Mustafa Shaaban
     * @Date: 21/2/2023
     */

    namespace NINJAHUB\APP\HELPERS;

    /**
     * Description...
     *
     * @class NhAjax__Response
     * @version 1.0
     * @since 1.0.0
     * @package NinjaHub
     * @author Mustafa Shaaban
     */
    class Ninjahub_Ajax_Response
    {

        public function __construct(bool $status, string $msg, array $data = [])
        {
            $this->response($status, $msg, $data);
        }

        /**
         * Description...
         *
         * @param bool   $status
         * @param string $msg
         * @param array  $data
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         * @return void
         */
        protected function response(bool $status, string $msg, array $data = []): void
        {
            $response = [
                'success' => $status,
                'msg'     => $msg,
            ];

            if (!empty($data)) {
                $response['data'] = $data;
            }

            wp_send_json($response);
        }

    }
