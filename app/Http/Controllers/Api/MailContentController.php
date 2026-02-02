<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    UserService,
};

class MailContentController extends Controller
{

    /**
     * 1. Enquiry Mail
     * 
     * @group Contact-Us API
     * 
     * @bodyParam fullname string The fullname for contact us enquiry. Example: John Wick
     * @bodyParam email string required The email for contact us enquiry. Example: johnwick@gmail.com
     * @bodyParam phone_number string required The phone_number for contact us enquiry. Example: 012342123
     * @bodyParam message string required The message for contact us enquiry. Example: lorem ipsum..
     *
     */
    public function createEnquiryMail( Request $request ) {

        return UserService::createEnquiryMail( $request );
    } 

    /**
     * 2. send Contact Us Mail
     * 
     * @group Contact-Us API
     * 
     * @bodyParam name string required The name. Example: abc
     * @bodyParam email string required The email. Example: abc@example.com
     * @bodyParam phone_number string required The phone number. Example: 1234567890
     * @bodyParam message string required The message. Example: abc
     * 
     */ 
    public function sendContactUsMail( Request $request ) {

        return UserService::sendContactUsMail( $request );
    }


}