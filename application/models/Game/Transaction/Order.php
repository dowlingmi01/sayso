<?php
/**
 * Class Game_Transaction_Order
 */
class Game_Transaction_Order {
    /**
     * Handles processing of a reward redemption.
     *
     * $orderData is an array that must contain at least
     *      quantity - int
     *      good_id - int
     *      user_id - int
     *      starbar_id - int
     *
     * if the order is shippable, an additional element (array)shipping
     * is to hold the shipping data
     *      order_first_name
     *      order_last_name
     *      order_address_1
     *      order_address_2
     *      order_city
     *      order_state
     *      order_zip
     *      order_country
     *      order_phone
     *
     * @param $orderData
     *
     * @return bool
     */
    public function processOrder($orderData)
    {
        if (!isset($orderData["quantity"])
            || !isset($orderData["good_id"])
            || !isset($orderData["user_id"])
            || !isset($orderData["starbar_id"])
            || !isset($orderData["game_txn_id"]))
            return FALSE;

        $quantity = $orderData["quantity"];
        $goodId = $orderData["good_id"];
        $userId = $orderData["user_id"];
        $starbarId = $orderData["starbar_id"];
        $gameTxnId = $orderData["game_txn_id"];
        /** @var $shippingInfo array */
        $shippingInfo = isset($orderData["shipping"]) ? $orderData["shipping"] : NULL;

        $economyId = Economy::getIdforStarbar($starbarId);
        $economy = Economy::getForId($economyId);

        $purchasable = new Item($economy->_purchasables[$goodId]);
        $goodTitle = $purchasable->name;

        $user = new User();
        $user->loadData($userId);

        $userEmail = new User_Email();
        $userEmail->loadData($user->primary_email_id);

        $logRecord = new GamerOrderHistory();
        $logRecord->user_id = $userId;
        $logRecord->game_asset_id = $goodId;
        $logRecord->quantity = $quantity;
        $logRecord->game_transaction_id = $gameTxnId;

        if ($shippingInfo)
        {
            $logRecord->first_name  = $shippingInfo->order_first_name;
            $logRecord->last_name   = $shippingInfo->order_last_name;
            $logRecord->street1     = $shippingInfo->order_address_1;
            $logRecord->street2     = $shippingInfo->order_address_2;
            $logRecord->locality    = $shippingInfo->order_city;
            $logRecord->region      = $shippingInfo->order_state;
            $logRecord->postalCode  = $shippingInfo->order_zip;
            $logRecord->country     = $shippingInfo->order_country;
            $logRecord->phone       = $shippingInfo->order_phone;
        }
        $logRecord->save();

        if ($shippingInfo) {
            // shippable item
            // validation done in JS

            $userAddress = new User_Address();
            if ($user->primary_address_id) {
                $userAddress->loadData($user->primary_address_id);
            } else {
                $userAddress->user_id = $userId;
            }

            $userAddress->street1       = $shippingInfo->order_address_1;
            $userAddress->street2       = $shippingInfo->order_address_2;
            $userAddress->locality      = $shippingInfo->order_city;
            $userAddress->region        = $shippingInfo->order_state;
            $userAddress->postalCode    = $shippingInfo->order_zip;
            $userAddress->country       = $shippingInfo->order_country;
            $userAddress->phone         = $shippingInfo->order_phone;
            $userAddress->save();

            if (!$user->primary_address_id) {
                $user->primary_address_id = $userAddress->id;
            }

            //consider accepting a setting before assuming name put in is their name
            $user->first_name   = $shippingInfo->order_first_name;
            $user->last_name    = $shippingInfo->order_last_name;
            $user->save();

            $starbar = new Starbar();
            $starbar->loadData($starbarId);

            /* Send a confirmation email to the admins */
            try {
                $message = '
					Redemption made for ' . $goodTitle . '

					Order Details
					=============
					Starbar: '          . $starbar->label . '
					First Name: '       . $shippingInfo->order_first_name . '
					Last Name: '        . $shippingInfo->order_last_name . '
					Street Address 1: ' . $shippingInfo->order_address_1 . '
					Street Address 2: ' . $shippingInfo->order_address_2 . '
					City: '             . $shippingInfo->order_city . '
					State/Region: '     . $shippingInfo->order_state . '
					Postal Code: '      . $shippingInfo->order_zip . '
					Country: '          . $shippingInfo->order_country . '
					Phone: '            . $shippingInfo->order_phone . '
					User ID: '          . $userId . '
					User Email: '       . $userEmail->email . '
					=============
					Thank you,
					Say.So Mailer v4.729
				';

                $mail = new Mailer();
                $mail->setFrom('rewards@say.so')
                    ->addTo('rewards@say.so')
                    ->setSubject('['.strtoupper($starbar->short_name).'] Redemption of '.$goodTitle.' for '.$userEmail->email);
                $mail->setBodyMultilineText($message);
                $mail->send(new Zend_Mail_Transport_Smtp());
            } catch (Exception $e) {
                quickLog($message);
            }

            /* Send a confirmation email to the user */
            try {
                $address = $shippingInfo->order_address_1;
                if (strlen($shippingInfo->order_address_2) > 0) {
                    $address .= "<br />".$shippingInfo->order_address_2;
                }
                if( $starbarId == 4 )
                    $htmlmessage = "<h1>Machinima | Recon redemption made for ".$goodTitle."</h1>";
                else
                    $htmlmessage = "<h1>Say.So redemption made for ".$goodTitle."</h1>";
                $htmlmessage .= sprintf("<p>Nicely done! You have successfully redeemed the item \"%s\" from the Reward Center!<br />We're kinda jealous...</p>",$goodTitle);
                $htmlmessage .= "<p>Right now, we are diligently processing your redemption so that you can receive your gift as soon as possible.</p>";
                $htmlmessage .= "<p>Delivery times vary by item type. Please allow 4-6 weeks for delivery of physical items; 5-7 days for virtual codes and items.</p>";
                if( $starbarId == 4 )
                    $htmlmessage .= "<p>Thank you for being a Machinima | Recon community member and we hope you enjoy your gift!<br />- The Machinima | Recon Team</p>";
                else
                    $htmlmessage .= "<p>Thank you for being a Say.So community member and we hope you enjoy your gift!<br />- The Say.So Team</p>";

                $message = 'Nicely done! You have successfully redeemed the item "' . $goodTitle . '" from the Reward Center!
					We\'re kinda jealous...

					Right now, we are diligently processing your redemption so that you can receive your gift as soon as possible.

					Delivery times vary by item type. Please allow 4-6 weeks for delivery of physical items; 5-7 days for virtual codes and items.

				';
                if( $starbarId == 4 )
                    $message .= 'Thank you for being a Machinima | Recon community member and we hope you enjoy your gift!
					- The Machinima | Recon Team
				';
                else
                    $message .= 'Thank you for being a Say.So community member and we hope you enjoy your gift!
					- The Say.So Team
				';

                $mail = new Mailer();
                $mail->setFrom('rewards@say.so')
                    ->addTo($userEmail->email)
                    ->setSubject($starbarId == 4 ? 'Your Machinima | Recon Item Redemption' : 'Your Item Redemption');
                $mail->setBodyMultilineText($message);
                $mail->setBodyHtml($htmlmessage);
                $mail->send(new Zend_Mail_Transport_Smtp());
            } catch (Exception $e) {
                quickLog($htmlmessage);
            }
        }
        return TRUE;
    }
}