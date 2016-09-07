<?php

namespace CommerceGuys\AuthNet\Tests;

use CommerceGuys\AuthNet\CreateCustomerProfileRequest;
use CommerceGuys\AuthNet\DataTypes\BillTo;
use CommerceGuys\AuthNet\DataTypes\CreditCard;
use CommerceGuys\AuthNet\DataTypes\PaymentProfile;
use CommerceGuys\AuthNet\DataTypes\Profile;
use CommerceGuys\AuthNet\DeleteCustomerProfileRequest;
use CommerceGuys\AuthNet\GetCustomerProfileIdsRequest;
use CommerceGuys\AuthNet\GetCustomerProfileRequest;
use CommerceGuys\AuthNet\UpdateCustomerProfileRequest;

class CustomerProfileRequestTest extends TestBase
{

    public function testGetCustomerProfileIdsRequest()
    {
        $request = new GetCustomerProfileIdsRequest($this->configuration, $this->client);
        $response = $request->execute();
        $this->assertTrue(isset($response->ids));
        $this->assertEquals('I00001', $response->getMessages()[0]->getCode());
        $this->assertEquals('Successful.', $response->getMessages()[0]->getText());
        $this->assertEquals('Ok', $response->getResultCode());
    }

    public function testCreateCustomerProfileCRUDRequests()
    {
        $paymentProfile = new PaymentProfile([
          'customerType' => 'individual',
        ]);
        // @note: You must add the billTo first.
        $paymentProfile->addBillTo(new BillTo([
          'firstName' => 'Johnny',
          'lastName' => 'Appleseed',
          'address' => '1234 New York Drive',
          'city' => 'New York City',
          'state' => 'NY',
          'zip' => '12345',
          'country' => 'US',
          'phoneNumber' => '5555555555',
        ]));
        $paymentProfile->addPayment(new CreditCard([
          'cardNumber' => '4111111111111111',
          'expirationDate' => '2020-12',
        ]));

        $profile = new Profile([
          'email' => 'example+' . rand(0, 10000) . '@example.com',
        ]);
        $profile->addPaymentProfile($paymentProfile);

        $request = new CreateCustomerProfileRequest($this->configuration, $this->client);
        $request->setProfile($profile);
        $response = $request->execute();
        $this->assertTrue(isset($response->customerProfileId));
        $this->assertTrue(isset($response->customerPaymentProfileIdList));
        $this->assertTrue(isset($response->validationDirectResponseList));

        $request = new GetCustomerProfileRequest($this->configuration, $this->client, $response->customerProfileId);
        $response = $request->execute();
        $this->assertEquals('I00001', $response->getMessages()[0]->getCode());
        $this->assertEquals('Successful.', $response->getMessages()[0]->getText());
        $this->assertEquals('Ok', $response->getResultCode());
        $this->assertTrue(isset($response->profile));

        $customerProfileId = $response->profile->customerProfileId;
        $profile = new Profile([
            'email' => 'exampleUpdated+' . rand(0, 10000) . '@example.com',
            'customerProfileId' => $customerProfileId,
        ]);
        $request = new UpdateCustomerProfileRequest($this->configuration, $this->client, $profile);
        $response = $request->execute();
        $this->assertEquals('I00001', $response->getMessages()[0]->getCode());
        $this->assertEquals('Successful.', $response->getMessages()[0]->getText());
        $this->assertEquals('Ok', $response->getResultCode());

        $request = new DeleteCustomerProfileRequest(
            $this->configuration,
            $this->client,
            $customerProfileId
        );
        $response = $request->execute();
        $this->assertEquals('I00001', $response->getMessages()[0]->getCode());
        $this->assertEquals('Successful.', $response->getMessages()[0]->getText());
        $this->assertEquals('Ok', $response->getResultCode());

    }
}
