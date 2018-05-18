<?php

namespace Eccube\Tests\Web;

use Eccube\Common\Constant;
use Eccube\Entity\Customer;

/**
 * ShoppingController 用 WebTest の抽象クラス.
 *
 * ShoppingController の WebTest をする場合に汎用的に使用する.
 *
 * @author Kentaro Ohkouchi
 */
abstract class AbstractShoppingControllerTestCase extends AbstractWebTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function createShippingFormData()
    {
        $faker = $this->getFaker();
        $tel = explode('-', $faker->phoneNumber);

        $form = [
            'name' => [
                'name01' => $faker->lastName,
                'name02' => $faker->firstName,
            ],
            'kana' => [
                'kana01' => $faker->lastKanaName,
                'kana02' => $faker->firstKanaName,
            ],
            'company_name' => $faker->company,
            'zip' => [
                'zip01' => $faker->postcode1(),
                'zip02' => $faker->postcode2(),
            ],
            'address' => [
                'pref' => '5',
                'addr01' => $faker->city,
                'addr02' => $faker->streetAddress,
            ],
            'tel' => [
                'tel01' => $tel[0],
                'tel02' => $tel[1],
                'tel03' => $tel[2],
            ],
            '_token' => 'dummy',
        ];

        return $form;
    }

    protected function scenarioCartIn(Customer $Customer = null, $product_class_id = 1)
    {
        if ($Customer) {
            $this->loginTo($Customer);
        }

        $this->client->request(
            'PUT',
            $this->generateUrl(
                'cart_handle_item',
                [
                    'operation' => 'up',
                    'productClassId' => $product_class_id,
                ]
            ),
            [Constant::TOKEN_NAME => '_dummy']
        );

        if ($Customer) {
            $this->loginTo($Customer);
        }

        return $this->client->request(
            'GET',
            $this->generateUrl('cart_buystep')
        );
    }

    protected function scenarioInput($formData)
    {
        $formData[Constant::TOKEN_NAME] = '_dummy';
        $crawler = $this->client->request(
            'POST',
            $this->generateUrl('shopping_nonmember'),
            ['nonmember' => $formData]
        );

        return $crawler;
    }

    protected function scenarioConfirm(Customer $Customer = null)
    {
        if ($Customer) {
            $this->loginTo($Customer);
        }
        $crawler = $this->client->request('GET', $this->generateUrl('shopping'));

        return $crawler;
    }

    protected function scenarioRedirectTo(Customer $Cusotmer, $parameters)
    {
        if ($Cusotmer) {
            $this->loginTo($Cusotmer);
        }

        return $this->client->request(
            'POST',
            $this->generateUrl('shopping_redirect_to'),
            $parameters
        );
    }

    protected function scenarioComplete(Customer $Customer = null, $confirm_url, array $shippings = [])
    {
        if ($Customer) {
            $this->loginTo($Customer);
        }

        $faker = $this->getFaker();
        if (count($shippings) < 1) {
            $shippings = [
                [
                    'Delivery' => 1,
                    'DeliveryTime' => 1,
                ],
            ];
        }

        $this->client->enableProfiler();

        $crawler = $this->client->request(
            'POST',
            $confirm_url,
            ['_shopping_order' => [
                      'Shippings' => $shippings,
                      'Payment' => 3,
                      'message' => $faker->realText(),
                      '_token' => 'dummy',
                  ],
            ]
        );

        return $crawler;
    }
}
