<?php

namespace Martinkuhl\AutheliaOidc\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigureRedirectUri extends Command
{
    const XML_PATH_PREFIX = 'martinkuhl_authelia_oidc/general/';
    const SCOPE_DEFAULT = 'default';

    protected WriterInterface $configWriter;
    protected ScopeConfigInterface $scopeConfig;

    public function __construct(
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        string $name = null
    ) {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('authelia:config:redirect-uri')
            ->setDescription('Konfiguriert die Redirect-URI für Authelia OIDC')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Der Pfad für die Redirect-URI (z.B. /admin/authelia/oidc/callback)'
            );
        
        return parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        
        // Konfiguration speichern
        $this->configWriter->save(
            self::XML_PATH_PREFIX . 'redirect_uri_path',
            $path,
            self::SCOPE_DEFAULT,
            0
        );
        
        $output->writeln('<info>Redirect-URI-Pfad wurde konfiguriert: ' . $path . '</info>');
        $output->writeln('<info>Bitte Magento-Cache leeren mit: bin/magento cache:flush</info>');
        
        return 0;
    }
}