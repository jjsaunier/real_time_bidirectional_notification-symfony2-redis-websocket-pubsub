<?php

namespace Prophet777\MoonWalk\Command;

use Gos\Bundle\PubSubRouterBundle\Generator\GeneratorInterface;
use Gos\Bundle\PubSubRouterBundle\Router\RouterInterface;
use Gos\Component\PnctlEventLoopEmitter\PnctlEmitter;
use Gos\Component\WebSocketClient\Wamp\Client;
use Prophet777\MoonWalk\Parser\MonologParser;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;
use MKraemer\ReactInotify\Inotify;

class MoonWalkCommand extends Command
{
    /**
     * @var RouterInterface
     */
    protected $generator;

    /**
     * @param RouterInterface $generator
     */
    public function __construct(RouterInterface $generator)
    {
        $this->generator = $generator;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('prophet777:watcher')
            ->setDescription('File watcher')
            ->addOption('ignore', 'i', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Ignore files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = Factory::create();
        $fileRegistry = [];
        $ignoredFiles = [];

        $loop->addPeriodicTimer(2, function() use ($output){
            $output->writeln('Memory usage : ' . round((memory_get_usage() / (1024 * 1024)), 2) . 'Mo');
        });

        $rootDir = Path::canonicalize(__DIR__.'/../../../../../');
        $watchDir = $rootDir.'/app/logs/';
        $watchDirNormalized = $rootDir.'/app/logs';

        $monologParser = new MonologParser();

        if($hasIgnore = $input->hasOption('ignore')){
            $ignoredFiles = $input->getOption('ignore');

            if(count($ignoredFiles) === 1){
                if(strpos($ignoredFiles[0], ',')){
                    $backup = $ignoredFiles[0];
                    unset($ignoredFiles[0]);

                    foreach(explode(',', $backup) as $file){
                        $ignoredFiles[] = trim($file);
                    }

                    unset($backup); //Release memory
                }
            }
        }

        foreach(scandir($watchDirNormalized) as $fileName){
            if($fileName[0] === '.'){
                continue;
            }

            $filePath = Path::join($watchDir, $fileName);

            if(true === $hasIgnore && in_array($fileName, $ignoredFiles)){
                $output->writeln('<info>'.$filePath.' was ignored</info>');
                continue;
            }

            $file = new \SplFileInfo($filePath);

            if($file->isReadable()){
                $fp = fopen($filePath, 'r');
                fseek($fp, 0, SEEK_END);
                $fileRegistry[$filePath] = ftell($fp);
                fclose($fp);
            }else{
                $output->writeln('<error>'.$filePath.' was ignored, not readable</error>');
            }

            $output->writeln('<info>'.$filePath. ' has been discovered</info>');
        }

        $websocket = new Client('notification.dev', 1337);
        $websocket->connect();

        $inotify = new Inotify($loop);
        $inotify->add($watchDir, IN_CLOSE_WRITE);

        $watchTimer = null;

        $inotify->on(IN_CLOSE_WRITE, function($path) use ($output, &$fileRegistry, $inotify, $ignoredFiles, $loop, &$watchTimer) {
            if(!file_exists($path)){
                return;
            }

            $output->writeln($path. ' has been modified');

            $filename = Path::getFilename($path);

            if(in_array($filename, $ignoredFiles)){
                return;
            }

            $fp = fopen($path, 'r');
            $data = stream_get_contents($fp, -1, $fileRegistry[$path]);
            fseek($fp, 0, SEEK_END);
            $fileRegistry[$path] = ftell($fp);
            fclose($fp);

            foreach(explode(PHP_EOL, $data) as $data){
                if(empty($data)){
                    continue;
                }

                $inotify->emit('data', [$path, $data]);
            }
        });

        $inotify->on('data', function($path, $data) use ($websocket, $monologParser) {

            $websocket->publish($this->generator->generate('watcher_notify'), json_encode([
                'type' => 'entry',
                'format' => 'monolog',
                'file_path' => $path,
                'file_name' => Path::getFilename($path),
                'data' => $monologParser->parse($data)
            ]));
        });

        $loop->run();
    }

//    protected function handlePnctlEvent(LoopInterface $loop)
//    {
//        $pnctlEmitter = new PnctlEmitter($loop);
//
//        $pnctlEmitter->on(SIGTERM, function () {
//            $this->logger->notice('Stopping server ...');
//
//            //todo
//
//            $this->logger->notice('Server stopped !');
//        });
//
//        $pnctlEmitter->on(SIGINT, function () {
//            $this->logger->notice('Press CTLR+C again to stop the server');
//
//            if (SIGINT === pcntl_sigtimedwait([SIGINT], $siginfo, 5)) {
//                $this->logger->notice('Stopping server ...');
//
//                //todo
//
//                $this->logger->notice('Server stopped !');
//            } else {
//                $this->logger->notice('CTLR+C not pressed, continue to run normally');
//            }
//        });
//    }
}