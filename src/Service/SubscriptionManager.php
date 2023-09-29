<?php

namespace App\Service;

use App\Entity\Shop;
use Doctrine\ORM\EntityManagerInterface;
use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\Graphql;
use Shopify\Context;

class SubscriptionManager
{
    public function __construct(private EntityManagerInterface $em)
    {
        Context::initialize(
            apiKey: $_ENV['SHOPIFY_API_KEY'],
            apiSecretKey: $_ENV['SHOPIFY_API_SECRET'],
            scopes: $_ENV['SHOPIFY_APP_SCOPES'],
            hostName: $_ENV['SHOPIFY_APP_HOST_NAME'],
            sessionStorage: new FileSessionStorage('/tmp/php_sessions'),
            apiVersion: '2023-04',
            isEmbeddedApp: true,
            isPrivateApp: false,
        );
    }

    public function getSubscriptionUrl($shopName)
    {
        $shop = $this->em->getRepository(Shop::class)->findOneBy(['name' => $shopName]);
        if ($shop) {
            $client = new Graphql($shop->getName(), $shop->getToken());
            $name = "Customer Review Premium";

            $query = <<<QUERY
                mutation AppSubscriptionCreate(\$name: String!, \$lineItems: [AppSubscriptionLineItemInput!]!, \$returnUrl: URL!) {
                    appSubscriptionCreate(name: \$name, returnUrl: \$returnUrl, lineItems: \$lineItems) {
                    userErrors {
                        field
                        message
                    }
                    appSubscription {
                        id
                    }
                    confirmationUrl
                    }
                }
                QUERY;


            $variables = [
                "name" => "Customer Review Premium",
                "returnUrl" => "http://super-duper.shopifyapps.com/",
                "lineItems" => [
                    "plan" => [
                        "appRecurringPricingDetails" => [
                            "price" => [
                                "amount" => 10.0,
                                "currencyCode" => "USD"
                            ], "interval" => "EVERY_30_DAYS"
                        ]
                    ]
                ],
            ];

            $response = $client->query(["query" => $query, "variables" => $variables]);
            $data = $response->getDecodedBody();
            return $data["data"]["appSubscriptionCreate"]["confirmationUrl"];
        }
    }
}
