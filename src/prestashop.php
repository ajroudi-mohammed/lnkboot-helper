<?php namespace Acme;

use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;

//run composer dump-autoload 

class Prestashop extends Command{

    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;   

        parent::__construct();
    }

    
    public function configure(){
        $this->setName("new")
            ->setDescription("create a new prestashop application")
            ->addArgument('name', InputArgument::REQUIRED, "name of the folder")
            ->addArgument('version', InputArgument::REQUIRED, "version of prestashop");
    }

    public function execute(InputInterface $input, OutputInterface $output){
        // 
        
        $output->writeln("<info>Crafting application ...</info>");

        $directory = getcwd()."/".$input->getArgument('name'); 
        $this->assertApplicationDoesNotExist($directory, $output);
        $version = $input->getArgument("version");
        //download + extract
        $this->download($zipFile = $this->makeFileName(), $version )
            ->extract($zipFile, $directory)
            ->cleanup($zipFile);


        $output->writeln('<comment>Application ready!!</comment>');
        

    }

    private function assertApplicationDoesNotExist($dir, OutputInterface $output){
        if(is_dir($dir)){
            $output->writeln("<error>Application already exists</error>");
            exit(1);
        }

    }

    private function download($zipFile, $version){
        $response = $this->client->get("https://github.com/PrestaShop/PrestaShop/releases/download/{$version}/prestashop_{$version}.zip")->getBody();

        file_put_contents($zipFile, $response);

        return $this;
    }

    private function makeFileName(){
        return getcwd() . "/laravel_" . md5(time().uniqid()) . ".zip";
    }

    private function extract($zipFile, $directory){
        $archive = new ZipArchive();

        $archive->open($zipFile);

        $archive->extractTo($directory);

        $archive->close();

        return $this;
    }

    private function cleanup($zipFile){
        @chmod($zipFile, 0777);
        @unlink($zipFile);

        return $this;
    }
    
}