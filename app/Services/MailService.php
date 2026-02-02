<?php

namespace App\Services;

use App\Models\{
    MailAction,
    OtpLog,
    User,
};

use GuzzleHttp\Client;

class MailService {

    protected $data;
    protected $apiKey;
    protected $email;

    public function __construct( $data ) {
        $this->data = $data;
        $this->email = config( 'services.brevo.mail' );
        $this->apiKey = config( 'services.brevo.key' );
    }

    public function send() {
        $result = $this->sending();
        return $result;
    }

    private function sending() {
        try {

            $data = [
                'sender' => [
                    'name' => 'Same Same Oye',
                    'email' => $this->email,
                ],
                'to' => [
                    [
                        'email' => ( $this->data['type'] && $this->data['type'] == 3 ) ? config( 'services.brevo.contact_us_mail' ) : $this->data['email'],
                    ]
                ],
                'subject' => $this->getSubject(),
                'htmlContent' => $this->getView()
            ];

            $endpoint = "https://api.sendinblue.com/v3/smtp/email";
            $headers = [
                "accept: application/json",
                "content-type: application/json",
                "api-key: " . $this->apiKey
            ];

            $response = \Helper::curlPost( $endpoint, json_encode($data), $headers );
            
            if ( $response === false ) {
                
                if( isset( $this->data['otp_code'] ) ) {
                    $createLog = OtpLog::create( [
                        'email' => $this->data['email'],
                        'otp_code' => $this->data['otp_code'],
                        'status' => 20,
                        'raw_response' => json_encode( $response ),
                    ] );
                }
                return [
                    'status' => 500,
                    'message' => __( 'mail.send_fail', [ 'mail' => $this->data['email'] ] ),
                    'response' => $response
                ];
            } else {
                
                if( isset( $this->data['otp_code'] ) ) {
                    $createLog = OtpLog::create( [
                        'email' => $this->data['email'],
                        'otp_code' => $this->data['otp_code'],
                        'status' => 10,
                        'raw_response' => json_encode( $response ),
                    ] );
                }
                return [
                    'status' => 200,
                    'message' => __( 'mail.send_success', [ 'mail' => $this->data['email'] ] )
                ];
            }

        } catch (\Exception $e) {
            
            if( isset( $this->data['otp_code'] ) ) {
                $createLog = OtpLog::create( [
                    'email' => $this->data['email'],
                    'otp_code' => $this->data['otp_code'],
                    'status' => 20,
                    'raw_response' => $e->getMessage(),
                ] );
            }
            \Log::error( [
                'status' => 500,
                'message_key' => 'send_mail_client',
                'message' => $e->getMessage()
            ] );

            return [
                'status' => 500,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getView() {
        switch ( $this->data['type'] ) {
            case 1:
                return view( 'admin.mail.otp', [ 'data' => $this->data ] )->render();
            case 2:
                return view( 'admin.mail.otp', [ 'data' => $this->data ] )->render();
            case 3:
                return view( 'admin.mail.contact_us', [ 'data' => $this->data ] )->render();
            default:
                return view( 'admin.mail.otp', [ 'data' => $this->data ] )->render();
        }
    }

    public function getSubject() {
        switch ( $this->data['type'] ) {
            case 1:
                $this->data['subject'] = __( 'user.register' );
                break;
            case 2:
                $this->data['subject'] = __( 'user.request_password_reset' );
                break;
            case 3:
                $this->data['subject'] = __( 'user.contact_us' );
                break;
            default:
                $this->data['subject'] = __( 'user.register' );
                break;
        }
        return $this->data['subject'];
    }

    public function resend() {
        $result = $this->sending();
        return $result;
    }
}