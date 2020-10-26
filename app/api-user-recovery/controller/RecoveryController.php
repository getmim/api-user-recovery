<?php
/**
 * RecoveryController
 * @package api-user-recovery
 * @version 0.0.1
 */

namespace ApiUserRecovery\Controller;

use LibForm\Library\Form;
use LibUserRecovery\Model\UserRecovery as URecovery;
use LibUser\Library\Fetcher;
use LibOtp\Library\Otp;
use LibOtp\Model\Otp as _Otp;

class RecoveryController extends \Api\Controller
{
    private function sendOtp(object $user, string $otp): void{

    }

    public function recoveryAction() {
        if(!$this->app->isAuthorized())
            return $this->resp(401);

        $form = new Form('api.me.recovery');

        if(!($valid = $form->validate()))
            return $this->resp(422, $form->getErrors());

        $user = Fetcher::getOne(['name'=>$valid->identity]);
        if(!$user){
            if(module_exists('lib-user-main-email'))
                $user = Fetcher::getOne(['email'=>$valid->identity]);
            if(!$user && module_exists('lib-user-main-phone'))
                $user = Fetcher::getOne(['phone'=>$valid->identity]);
        }

        if(!$user){
            $form->addError('identity', '0.0', 'No user found with that identity');
            return $this->resp(422, $form->getErrors());
        }

        // create OTP to send to user as verification
        $otp = Otp::generate($user->id);
        $this->sendOtp($user, $otp);

        $otp = _Otp::getOne([
            'identity' => $user->id,
            'otp'      => $otp
        ]);

        $this->resp(0, [
            'user' => [
                'id' => (int)$user->id
            ],
            'otp' => [
                'id' => (int)$otp->id
            ]
        ]);
    }

    public function resentAction(){
        if(!$this->app->isAuthorized())
            return $this->resp(401);

        $user_id = $this->req->param->user;
        $otp_id  = $this->req->param->otp;

        $code    = _Otp::getOne([
            'id'       => $otp_id,
            'identity' => $user_id
        ]);

        if(!$code)
            return $this->show404();

        $user = Fetcher::getOne(['id'=>$user_id]);
        if(!$user)
            return $this->show404();

        $this->sendOtp($user, $code->otp);

        $this->resp(0, 'success');
    }

    public function resetAction(){
        if(!$this->app->isAuthorized())
            return $this->resp(401);

        $hash = $this->req->param->hash;
        $recovery = URecovery::getOne(['hash'=>$hash]);
        if(!$recovery)
            return $this->show404();

        $expire = strtotime($recovery->expires);
        if($expire < time()){
            URecovery::remove(['id'=>$recovery->id]);
            return $this->show404();
        }

        $form = new Form('api.me.recovery.reset');

        if(!($valid = $form->validate()))
            return $this->resp(422, $form->getErrors());

        $new_password = $this->user->hashPassword($valid->password);

        Fetcher::set(['password'=>$new_password], ['id'=>$recovery->user]);

        URecovery::remove(['id'=>$recovery->id]);

        $this->resp(0, 'success');
    }

    public function verifyAction() {
        if(!$this->app->isAuthorized())
            return $this->resp(401);

        $user_id = $this->req->param->user;
        $code    = $this->req->param->code;

        if(!Otp::validate($user_id, $code))
            return $this->show404();

        // create recovery object
        $verif = [
            'user'    => $user_id,
            'expires' => date('Y-m-d H:i:s', strtotime('+2 hour')),
            'hash'    => ''
        ];
        while(true){
            $verif['hash'] = md5(time() . '-' . uniqid() . '-' . $user_id);
            if(!URecovery::getOne(['hash'=>$verif['hash']]))
                break;
        }
        URecovery::create($verif);

        $this->resp(0, [
            'user' => [
                'id' => (int)$user_id
            ],
            'hash' => $verif['hash']
        ]);
    }
}