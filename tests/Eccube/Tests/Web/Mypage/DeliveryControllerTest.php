<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Eccube\Tests\Web\Mypage;

use Eccube\Entity\Customer;
use Eccube\Repository\CustomerAddressRepository;
use Eccube\Tests\Web\AbstractWebTestCase;

class DeliveryControllerTest extends AbstractWebTestCase
{
    /**
     * @var Customer
     */
    protected $Customer;

    public function setUp()
    {
        parent::setUp();
        $this->Customer = $this->createCustomer();
    }

    protected function createFormData()
    {
        $faker = $this->getFaker();
        $tel = explode('-', $faker->phoneNumber);

        $email = $faker->safeEmail;
        $password = $faker->lexify('????????');

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
            'fax' => [
                'fax01' => $tel[0],
                'fax02' => $tel[1],
                'fax03' => $tel[2],
            ],
            '_token' => 'dummy',
        ];

        return $form;
    }

    public function testIndex()
    {
        $this->logInTo($this->Customer);
        $client = $this->client;

        $client->request(
            'GET',
            $this->generateUrl('mypage_delivery')
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testNew()
    {
        $this->logInTo($this->Customer);
        $client = $this->client;

        $client->request(
            'GET',
            $this->generateUrl('mypage_delivery_new')
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testNewWithPost()
    {
        $this->logInTo($this->Customer);
        $client = $this->client;

        $form = $this->createFormData();
        $crawler = $client->request(
            'POST',
            $this->generateUrl('mypage_delivery_new'),
            ['customer_address' => $form]
        );

        $this->assertTrue($client->getResponse()->isRedirect($this->generateUrl('mypage_delivery')));
    }

    public function testEdit()
    {
        $this->logInTo($this->Customer);
        $client = $this->client;

        $CustomerAddress = $this->container->get(CustomerAddressRepository::class)->findOneBy(
            ['Customer' => $this->Customer]
        );

        $crawler = $client->request(
            'GET',
            $this->generateUrl('mypage_delivery_edit', ['id' => $CustomerAddress->getId()])
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testEditWithPost()
    {
        $this->logInTo($this->Customer);

        $CustomerAddress = $this->container->get(CustomerAddressRepository::class)->findOneBy(
            ['Customer' => $this->Customer]
        );

        $form = $this->createFormData();
        $crawler = $this->client->request(
            'POST',
            $this->generateUrl('mypage_delivery_edit', ['id' => $CustomerAddress->getId()]),
            ['customer_address' => $form]
        );

        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('mypage_delivery')));

        $this->expected = $form['name']['name01'];
        $this->actual = $CustomerAddress->getName01();
        $this->verify();
    }

    public function testDelete()
    {
        $this->logInTo($this->Customer);

        $CustomerAddress = $this->container->get(CustomerAddressRepository::class)->findOneBy(
            ['Customer' => $this->Customer]
        );
        $id = $CustomerAddress->getId();

        $form = $this->createFormData();
        $crawler = $this->client->request(
            'DELETE',
            $this->generateUrl('mypage_delivery_delete', ['id' => $id])
        );

        $this->assertTrue($this->client->getResponse()->isRedirect($this->generateUrl('mypage_delivery')));

        $CustomerAddress = $this->container->get(CustomerAddressRepository::class)->find($id);
        $this->assertNull($CustomerAddress);

        $this->expected = ['mypage.address.delete.complete'];
        $this->actual = $this->container->get('session')->getFlashBag()->get('eccube.front.success');
        $this->verify();
    }

    public function testDeleteWithFailure()
    {
        $this->logInTo($this->Customer);

        $crawler = $this->client->request(
            'DELETE',
            $this->generateUrl('mypage_delivery_delete', ['id' => 999999999])
        );

        $this->expected = 404;
        $this->actual = $this->client->getResponse()->getStatusCode();
        $this->verify();
    }
}
