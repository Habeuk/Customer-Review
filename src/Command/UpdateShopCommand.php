<?php

namespace App\Command;

use App\Entity\Shop;
use Doctrine\ORM\EntityManagerInterface;
use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\Rest;
use Shopify\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-shop',
    description: 'Update shop informations',
)]
class UpdateShopCommand extends Command
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
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $shops = $this->em->getRepository(Shop::class)->findAll();
        foreach ($shops as $shop) {
            $client = new Rest($shop->getName(), $shop->getToken());
            $data = $client->get(path: 'shop')->getDecodedBody();
            if (!key_exists('errors', $data)) {
                $shop->setEmail($data["shop"]["email"]);
                $shop->setDomain($data["shop"]["domain"]);
                $shop->setPhone($data["shop"]["phone"]);
                $shop->setCustomerEmail($data["shop"]["customer_email"]);

                $this->em->persist($shop);
                $this->em->flush();
            }
        }

        $io->success('Shops informations where updated succesfully');

        return Command::SUCCESS;
    }
}
