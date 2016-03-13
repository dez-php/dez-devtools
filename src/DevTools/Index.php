<?php

    namespace Dez\Dev;

    use Dez\Dev\Generator\OrmEntity;
    use Dez\Cli\Cli;
    use Dez\Cli\IO\Input;
    use Dez\Cli\IO\InputOption;
    use Dez\Cli\IO\Output;
    use Dez\Config\Config;
    use Dez\Db\Connection;
    use Dez\DependencyInjection\Container;
    use Dez\View\Engine\Php;
    use Dez\View\View;

    $application    = new Cli();

    $application->register( 'orm_entity' )
        ->setDescription( 'Entity generator for DezORM' )
        ->addOption( 'connection', 'c', InputOption::REQUIRED )
        ->addOption( 'table', 't', InputOption::REQUIRED )
        ->addOption( 'directory', 'd', InputOption::REQUIRED )
        ->addOption( 'namespace', 'n', InputOption::REQUIRED )
        ->setCallback( function( Input $input, Output $output ) use ( $application ) {

            $currentDirectory   = getcwd();
            $connectionName     = $input->getOption( 'connection' );
            $tableName          = $input->getOption( 'table' );
            $directory          = $input->getOption( 'directory' );
            $namespace          = $input->getOption( 'namespace' );

            $output->writeln( '[info]Starting...[/info]' );

            $configFile = "$currentDirectory/app/config/app.php";

            $output->writeln("[info]Load config... {$configFile}[/info]");

            $config     = Config::factory( $configFile );

            $view       = new View();
            $view->setDi( Container::instance() )
                ->registerEngine( '.html', new Php( $view ) );

            $connection = new Connection( $config['db'][$connectionName] );

            $generator  = new OrmEntity();

            $generator->setConnection( $connection );
            $generator->setView( $view );
            $generator->setCli( $application );
            $generator->setConfig( new Config( [
                'currentDirectory'  => $currentDirectory,
                'connectionName'    => $connectionName,
                'tableName'         => $tableName,
                'entityDirectory'   => $directory,
                'namespace'         => $namespace,
            ] ) );

            $generator->execute();

            $output->writeln( '[info]Finish[/info]' );

        } );

    $application->execute();